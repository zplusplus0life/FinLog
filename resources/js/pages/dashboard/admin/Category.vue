<script setup lang="ts">
import { ref, onMounted, reactive, computed } from 'vue';

// --- SIDEBAR STATE ---
const activeItem = ref(1); // Category is index 1
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
const categories = ref<any[]>([]);
const showModal = ref(false);
const isEditing = ref(false);
const notification = ref({ show: false, message: '', type: 'success' });
const searchQuery = ref('');

const form = reactive({
  id: null,
  name: '',
  type: 'expense',
  is_active: true,
});

// --- API CALLS ---
async function fetchCategories() {
  try {
    const response = await fetch('/api/categories');
    if (!response.ok) throw new Error('Failed to fetch');
    categories.value = await response.json();
  } catch (error) {
    showNotification('Gagal memuat kategori', 'error');
    console.error(error);
  }
}

async function saveCategory() {
  const url = isEditing.value ? `/api/categories/${form.id}` : '/api/categories';
  const method = isEditing.value ? 'PUT' : 'POST';

  try {
    const response = await fetch(url, {
      method: method,
      headers: { 
        'Content-Type': 'application/json', 
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({ name: form.name, type: form.type, is_active: form.is_active })
    });

    if (!response.ok) {
      const errors = await response.json();
      throw new Error(errors.message || 'Validasi gagal');
    }

    showNotification(`Kategori berhasil ${isEditing.value ? 'diperbarui' : 'disimpan'}!`, 'success');
    closeModal();
    fetchCategories(); // Refresh list
  } catch (error: any) {
    showNotification(error.message, 'error');
    console.error(error);
  }
}

async function deleteCategory(id: number) {
  if (!confirm('Apakah Anda yakin? Menghapus kategori juga akan menghapus semua transaksi terkait.')) return;

  try {
    const response = await fetch(`/api/categories/${id}`, { 
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });
    if (!response.ok) throw new Error('Failed to delete');
    showNotification('Kategori berhasil dihapus', 'success');
    fetchCategories(); // Refresh list
  } catch (error) {
    showNotification('Gagal menghapus kategori', 'error');
    console.error(error);
  }
}

async function toggleStatus(category: any) {
    const action = category.is_active ? 'menonaktifkan' : 'mengaktifkan';
    if (!confirm(`Apakah Anda yakin ingin ${action} kategori ini?`)) return;

    try {
        const response = await fetch(`/api/categories/${category.id}/status`, {
            method: 'PUT',
            headers: { 
                'Content-Type': 'application/json', 
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ is_active: !category.is_active })
        });
        if (!response.ok) throw new Error('Failed to update status');
        showNotification('Status kategori berhasil diubah', 'success');
        fetchCategories();
    } catch (error) {
        showNotification('Gagal mengubah status', 'error');
        console.error(error);
    }
}

// --- LIFECYCLE HOOK ---
onMounted(() => {
  fetchCategories();
});

// --- MODAL & FORM HANDLING ---
function openAddModal() {
  isEditing.value = false;
  form.id = null;
  form.name = '';
  form.type = 'expense';
  form.is_active = true;
  showModal.value = true;
}

function openEditModal(category: any) {
  isEditing.value = true;
  form.id = category.id;
  form.name = category.name;
  form.type = category.type;
  form.is_active = category.is_active;
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
}

// --- COMPUTED & HELPERS ---
const filteredCategories = computed(() => {
    return categories.value.filter(cat => cat.name.toLowerCase().includes(searchQuery.value.toLowerCase()));
});

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
                <h2 class="text-2xl font-bold text-gray-800">Manajemen Kategori</h2>
                <div class="flex items-center gap-4">
                    <input type="text" v-model="searchQuery" placeholder="Cari kategori..." class="search-input">
                    <button @click="openAddModal" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        Tambah Kategori
                    </button>
                </div>
            </div>
            
            <!-- Categories Table -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="table-container">
                    <table class="w-full text-sm">
                        <thead>
                            <tr>
                                <th class="text-left py-3">Kategori</th>
                                <th class="text-left py-3">Jenis</th>
                                <th class="text-left py-3">Penggunaan</th>
                                <th class="text-left py-3">Status</th>
                                <th class="text-left py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="filteredCategories.length === 0">
                                <td colspan="5" class="text-center py-12 text-gray-500">Tidak ada kategori.</td>
                            </tr>
                            <tr v-for="category in filteredCategories" :key="category.id" class="border-b border-gray-100">
                                <td class="py-3 font-medium">{{ category.name }}</td>
                                <td class="py-3"><span class="type-badge" :class="category.type">{{ category.type }}</span></td>
                                <td class="py-3">{{ category.transactions_count }} transaksi</td>
                                <td class="py-3">
                                    <span class="type-badge" :class="category.is_active ? 'active' : 'inactive'">
                                        {{ category.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <div class="flex gap-2">
                                        <button @click="openEditModal(category)" class="action-button edit">Edit</button>
                                        <button @click="toggleStatus(category)" class="action-button toggle">{{ category.is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        <button @click="deleteCategory(category.id)" class="action-button delete">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add/Edit Modal -->
    <div v-if="showModal" class="modal-overlay" @click="closeModal">
        <div class="modal-content" @click.stop>
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold">{{ isEditing ? 'Edit Kategori' : 'Tambah Kategori' }}</h3>
            </div>
            <div class="p-6">
                <form class="space-y-4" @submit.prevent="saveCategory">
                    <div>
                        <label class="form-label">Nama Kategori <span class="required">*</span></label>
                        <input type="text" v-model="form.name" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Jenis <span class="required">*</span></label>
                        <select v-model="form.type" class="form-input">
                            <option value="income">Pendapatan</option>
                            <option value="expense">Pengeluaran</option>
                        </select>
                    </div>
                     <div>
                        <label class="form-label">Status</label>
                        <select v-model="form.is_active" class="form-input">
                            <option :value="true">Aktif</option>
                            <option :value="false">Nonaktif</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="p-6 border-t flex justify-end gap-3">
                <button @click="closeModal" class="px-4 py-2 bg-gray-100 rounded-lg">Batal</button>
                <button @click="saveCategory" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Simpan</button>
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
.type-badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; }
.type-badge.income { background-color: #dcfce7; color: #16a34a; }
.type-badge.expense { background-color: #fee2e2; color: #dc2626; }
.type-badge.active { background-color: #dcfce7; color: #16a34a; }
.type-badge.inactive { background-color: #f3f4f6; color: #6b7280; }
.notification { position: fixed; top: 20px; right: 20px; padding: 16px; border-radius: 8px; color: white; z-index: 1001; transform: translateX(120%); transition: transform 0.3s ease; }
.notification.show { transform: translateX(0); }
.notification.success { background-color: #16a34a; }
.notification.error { background-color: #dc2626; }
</style>
