<script setup lang="ts">
import { ref, onMounted, reactive, computed } from 'vue';

// --- SIDEBAR STATE ---
const activeItem = ref(2); // User is index 2
const menuItems = ref([
    { name: 'Dashboard', icon: '📊' },
    { name: 'Kelola Kategori', icon: '💳' },
    { name: 'Kelola User', icon: '👥' },
    { name: 'Laporan', icon: '📈' }
]);

// --- NAVIGATION ---
function navigateToPage(index: number) {
    activeItem.value = index;
    const routes = [
        '/admin/overview',
        '/admin/category', 
        '/admin/user',
        '/admin/laporan'
    ];
    window.location.href = routes[index];
}

// --- STATE MANAGEMENT ---
const users = ref<any[]>([]);
const showModal = ref(false);
const isEditing = ref(false);
const notification = ref({ show: false, message: '', type: 'success' });
const searchQuery = ref('');

const form = reactive({
  id: null,
  nama_lengkap: '',
  email: '',
  role: 'staff',
  password: '',
  password_confirmation: '',
  status: true,
});

// --- API CALLS ---
async function fetchUsers() {
  try {
    const response = await fetch('/api/users', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin'
    });
    if (!response.ok) {
      console.error('Fetch failed with status:', response.status, response.statusText);
      const responseBody = await response.text();
      console.error('Response body:', responseBody);
      throw new Error('Failed to fetch users');
    }
    users.value = await response.json();
  } catch (error) {
    showNotification('Gagal memuat data pengguna', 'error');
    console.error(error);
  }
}

async function saveUser() {
  const url = isEditing.value ? `/api/users/${form.id}` : '/api/users';
  const method = isEditing.value ? 'PUT' : 'POST';

  try {
    const payload: any = {
      nama_lengkap: form.nama_lengkap,
      email: form.email,
      role: form.role,
      status: form.status,
    };

    if (form.password) {
      payload.password = form.password;
      payload.password_confirmation = form.password_confirmation;
    }

    const response = await fetch(url, {
      method: method,
      headers: { 
        'Content-Type': 'application/json', 
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify(payload)
    });

    if (!response.ok) {
      const errors = await response.json();
      // Log the full error object to see its structure
      console.error('Server validation response:', errors);

      // Handle validation errors from Laravel
      let errorMessage = 'Validasi gagal:';
      if (errors && errors.errors) {
        for (const key in errors.errors) {
          errorMessage += `\n- ${errors.errors[key][0]}`;
        }
      }
      throw new Error(errorMessage);
    }

    showNotification(`Pengguna berhasil ${isEditing.value ? 'diperbarui' : 'ditambahkan'}!`, 'success');
    closeModal();
    fetchUsers(); // Refresh list
  } catch (error: any) {
    showNotification(error.message, 'error');
    console.error(error);
  }
}

async function deleteUser(id: number) {
  if (!confirm('Apakah Anda yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan.')) return;

  try {
    const response = await fetch(`/api/users/${id}`, { 
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });
    if (!response.ok) throw new Error('Gagal menghapus pengguna');
    showNotification('Pengguna berhasil dihapus!', 'success');
    fetchUsers(); // Refresh list
  } catch (error) {
    showNotification('Gagal menghapus pengguna', 'error');
    console.error(error);
  }
}

async function toggleStatus(user: any) {
    const action = user.status ? 'menonaktifkan' : 'mengaktifkan';
    if (!confirm(`Apakah Anda yakin ingin ${action} pengguna ini?`)) return;

    try {
        const response = await fetch(`/api/users/${user.id}/status`, {
            method: 'PUT',
            headers: { 
                'Content-Type': 'application/json', 
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ status: !user.status })
        });
        if (!response.ok) throw new Error('Gagal mengubah status');
        showNotification('Status pengguna berhasil diubah', 'success');
        fetchUsers();
    } catch (error) {
        showNotification('Gagal mengubah status', 'error');
        console.error(error);
    }
}

// --- LIFECYCLE HOOK ---
onMounted(() => {
  fetchUsers();
});

// --- MODAL & FORM HANDLING ---
function openAddModal() {
  isEditing.value = false;
  form.id = null;
  form.nama_lengkap = '';
  form.email = '';
  form.role = 'staff';
  form.password = '';
  form.password_confirmation = '';
  form.status = true;
  showModal.value = true;
}

function openEditModal(user: any) {
  isEditing.value = true;
  form.id = user.id;
  form.nama_lengkap = user.nama_lengkap;
  form.email = user.email;
  form.role = user.role;
  form.password = ''; // Clear password for security
  form.password_confirmation = '';
  form.status = user.status;
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
}

// --- COMPUTED & HELPERS ---
const filteredUsers = computed(() => {
    return users.value.filter(user => {
        return user.nama_lengkap.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
               user.email.toLowerCase().includes(searchQuery.value.toLowerCase());
    });
});

function getInitials(name: string = '') {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
}

function getRoleLabel(role: string) {
    switch(role) {
        case 'admin': return 'Admin';
        case 'manager': return 'Manager';
        case 'staff': return 'Staff';
        case 'viewer': return 'Viewer';
        default: return role;
    }
}

function showNotification(message: string, type: 'success' | 'error') {
  notification.value = { show: true, message, type };
  setTimeout(() => { notification.value.show = false; }, 3000);
}

</script>

<template>
    <div class="flex min-h-screen bg-gray-50">
        <!-- Sidebar -->
        <div class="w-60 bg-white shadow-lg fixed h-full p-5">
             <h1 class="text-xl font-bold text-blue-600 mb-6">FinLog</h1>
             <ul>
                    <li v-for="(item, index) in menuItems" :key="index" 
                        :class="['px-4 py-3 rounded-lg mb-1 flex items-center gap-3 cursor-pointer transition-all', 
                                 activeItem === index ? 'bg-blue-50 text-blue-600' : 'hover:bg-gray-50']"
                        @click="navigateToPage(index)">
                        <span class="text-lg">{{ item.icon }}</span>
                        <span class="text-sm font-medium">{{ item.name }}</span>
                    </li>
                </ul>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 ml-60 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8 pb-4 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-800">Manajemen Pengguna</h2>
                <div class="flex items-center gap-4">
                    <input type="text" v-model="searchQuery" placeholder="Cari pengguna..." class="search-input">
                    <button @click="openAddModal" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        Tambah Pengguna
                    </button>
                </div>
            </div>
            
            <!-- Users Table -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="table-container">
                    <table class="w-full text-sm">
                        <thead>
                            <tr>
                                <th class="text-left py-3">Pengguna</th>
                                <th class="text-left py-3">Email</th>
                                <th class="text-left py-3">Role</th>
                                <th class="text-left py-3">Status</th>
                                <th class="text-left py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="filteredUsers.length === 0">
                                <td colspan="5" class="text-center py-12 text-gray-500">Tidak ada pengguna.</td>
                            </tr>
                            <tr v-for="user in filteredUsers" :key="user.id" class="border-b border-gray-100">
                                <td class="py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="avatar">{{ getInitials(user.nama_lengkap) }}</div>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ user.nama_lengkap }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-gray-700">{{ user.email }}</td>
                                <td class="py-3">
                                    <span class="role-badge" :class="'role-' + user.role">
                                        {{ getRoleLabel(user.role) }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <span class="role-badge" :class="user.status ? 'status-active' : 'status-inactive'">
                                        {{ user.status ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <div class="flex gap-2">
                                        <button @click="openEditModal(user)" class="action-button edit">Edit</button>
                                        <button @click="toggleStatus(user)" class="action-button toggle">{{ user.status ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        <button @click="deleteUser(user.id)" class="action-button delete">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add/Edit User Modal -->
    <div v-if="showModal" class="modal-overlay" @click="closeModal">
        <div class="modal-content" @click.stop>
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold">{{ isEditing ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}</h3>
            </div>
            <div class="p-6">
                <form class="space-y-4" @submit.prevent="saveUser">
                    <div>
                        <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                        <input type="text" v-model="form.nama_lengkap" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Email <span class="required">*</span></label>
                        <input type="email" v-model="form.email" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Role <span class="required">*</span></label>
                        <select v-model="form.role" class="form-input">
                            <option value="staff">Staff</option>
                            <option value="manajer">Manajer</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div v-if="!isEditing" class="form-group">
                        <label class="form-label">Password <span class="required">*</span></label>
                        <input type="password" v-model="form.password" class="form-input">
                    </div>
                    <div v-if="!isEditing" class="form-group">
                        <label class="form-label">Konfirmasi Password <span class="required">*</span></label>
                        <input type="password" v-model="form.password_confirmation" class="form-input">
                    </div>
                    <div v-if="isEditing" class="form-group">
                        <label class="form-label">Ubah Password (opsional)</label>
                        <input type="password" v-model="form.password" class="form-input" placeholder="Biarkan kosong jika tidak ingin mengubah">
                    </div>
                    <div v-if="isEditing" class="form-group">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" v-model="form.password_confirmation" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Status</label>
                        <select v-model="form.status" class="form-input">
                            <option :value="true">Aktif</option>
                            <option :value="false">Nonaktif</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="p-6 border-t flex justify-end gap-3">
                <button @click="closeModal" class="px-4 py-2 bg-gray-100 rounded-lg">Batal</button>
                <button @click="saveUser" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Simpan</button>
            </div>
        </div>
    </div>

    <!-- Notification -->
    <div :class="['notification', notification.type, notification.show ? 'show' : '']">{{ notification.message }}</div>
</template>

<style scoped>
/* Basic styles for functionality */
.search-input, .form-input { padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 6px; width: 100%; }
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal-content { background-color: white; border-radius: 12px; max-width: 500px; width: 90%; }
.form-label { display: block; font-weight: 500; margin-bottom: 6px; }
.required { color: #dc2626; }
.action-button { padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.edit { background-color: #dbeafe; color: #1d4ed8; }
.delete { background-color: #fee2e2; color: #dc2626; }
.toggle { background-color: #f3f4f6; color: #374151; }
.role-badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; }
.role-admin { background-color: #dbeafe; color: #1d4ed8; }
.role-manager { background-color: #dcfce7; color: #16a34a; }
.role-staff { background-color: #fef3c7; color: #d97706; }
.role-viewer { background-color: #e0e7ff; color: #4f46e5; }
.status-active { background-color: #dcfce7; color: #16a34a; }
.status-inactive { background-color: #fef3c7; color: #d97706; }
.avatar { width: 36px; height: 36px; border-radius: 50%; background-color: #3b82f6; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px; }
.notification { position: fixed; top: 20px; right: 20px; padding: 16px; border-radius: 8px; color: white; z-index: 1001; transform: translateX(120%); transition: transform 0.3s ease; }
.notification.show { transform: translateX(0); }
.notification.success { background-color: #16a34a; }
.notification.error { background-color: #dc2626; }
</style>
