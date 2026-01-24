<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

// --- SIDEBAR STATE ---
const activeItem = ref(1); // Manage is index 1
const menuItems = ref([
    { name: 'Dashboard', icon: '📊' },
    { name: 'Kelola Transaksi', icon: '⚙️' },
    { name: 'Laporan', icon: '📈' }
]);

// --- NAVIGATION ---
function navigateToPage(index: number) {
    activeItem.value = index;
    const routes = [
        '/staff/overview',
        '/staff/manage', 
        '/staff/laporan'
    ];
    window.location.href = routes[index];
}

// Props from the controller
const props = defineProps<{
  categories: Array<{
    id: number;
    name: string;
    type: 'income' | 'expense';
  }>;
  transactions: Array<{
    id: number;
    transaction_date: string;
    type: 'income' | 'expense';
    category_id: number;
    category: { name: string };
    description: string;
    amount: number;
    status: 'pending' | 'approved' | 'rejected';
  }>;
}>();

// Use a local reactive ref for transactions to allow for mutation
const localTransactions = ref(props.transactions);

// Reactive state
const showAddModal = ref(false);
const notification = ref({ show: false, message: '', type: 'success' });

// Inertia form helper for the transaction modal
const form = useForm({
  id: null as number | null,
  transaction_date: new Date().toISOString().split('T')[0],
  type: 'expense',
  category_id: null,
  description: '',
  amount: null,
});

const searchQuery = ref('');
const selectedStatus = ref('all');

const filteredTransactions = computed(() => {
  let filtered = localTransactions.value; // Use local reactive ref

  if (selectedStatus.value !== 'all') {
    filtered = filtered.filter(t => t.status === selectedStatus.value);
  }

  if (searchQuery.value) {
    filtered = filtered.filter(t => 
      t.description.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      t.category.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  }
  return filtered;
});


// Functions
async function saveTransaction() {
  const isEditing = !!form.id;
  const url = isEditing ? `/api/transactions/${form.id}` : '/api/transactions';
  const method = isEditing ? 'PUT' : 'POST';

  try {
    const response = await fetch(url, {
      method: method,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      credentials: 'same-origin', // Include cookies for session authentication
      body: JSON.stringify(form.data()),
    });

    if (!response.ok) {
      const errorData = await response.json();
      console.error('Save failed:', errorData);
      throw new Error(errorData.message || 'Gagal menyimpan data.');
    }

    const savedTransaction = await response.json();

    if (isEditing) {
      // Find and update the transaction in the local array
      const index = localTransactions.value.findIndex(t => t.id === savedTransaction.id);
      if (index !== -1) {
        localTransactions.value[index] = savedTransaction;
      }
    } else {
      // Add the new transaction to the top of the local array
      localTransactions.value.unshift(savedTransaction);
    }

    closeModal();
    showNotification(`Transaksi berhasil ${isEditing ? 'diperbarui' : 'dibuat'}.`, 'success');

  } catch (error: any) {
    showNotification(error.message, 'error');
    console.error(error);
  }
}

function editTransaction(transaction: any) {
  form.id = transaction.id;
  form.transaction_date = transaction.transaction_date;
  form.type = transaction.type;
  form.category_id = transaction.category_id;
  form.description = transaction.description;
  form.amount = transaction.amount;
  showAddModal.value = true;
}

async function deleteTransaction(id: number) {
  if (!confirm('Apakah Anda yakin ingin menghapus transaksi ini? Tindakan ini tidak dapat dibatalkan.')) return;

  try {
    const response = await fetch(`/api/transactions/${id}`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      credentials: 'same-origin', // Include cookies for session authentication
    });

    if (!response.ok) {
        const errorData = await response.json();
        console.error('Delete failed:', errorData);
        throw new Error(errorData.message || 'Gagal menghapus transaksi.');
    }

    // Find and remove the transaction from the local array
    const index = localTransactions.value.findIndex(t => t.id === id);
    if (index !== -1) {
      localTransactions.value.splice(index, 1);
    }

    showNotification('Transaksi berhasil dihapus!', 'success');

  } catch (error: any) {
    showNotification(error.message, 'error');
    console.error(error);
  }
}

function closeModal() {
  showAddModal.value = false;
  form.reset();
  // Reset ID when closing modal
}

function showNotification(message: string, type: string) {
  notification.value.message = message;
  notification.value.type = type;
  notification.value.show = true;
  setTimeout(() => {
    notification.value.show = false;
  }, 3000);
}

// Computed property to filter categories based on selected transaction type
const availableCategories = computed(() => {
  const typeToMatch = form.type === 'income' ? 'income' : 'expense'; // Corrected logic
  return props.categories.filter(c => c.type === typeToMatch);
});

// Helper functions
function formatDate(dateString: string) { return new Date(dateString).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' }); }
function formatCurrency(amount: number) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount); }
function getStatusLabel(status: string) {
    switch(status) {
        case 'pending': return 'Menunggu';
        case 'approved': return 'Disetujui';
        case 'rejected': return 'Ditolak';
        default: return status;
    }
}

function canEdit(t: any){return t.status === 'pending'}
function canDelete(t: any){return t.status === 'pending'}

</script>

<template>
    <div class="flex min-h-screen">
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
                <h2 class="text-2xl font-bold text-gray-800">Kelola Transaksi</h2>
                <div class="flex items-center gap-4">
                    <input type="text" v-model="searchQuery" placeholder="Cari transaksi..." class="search-input">
                    <select v-model="selectedStatus" class="form-select">
                        <option value="all">Semua Status</option>
                        <option value="pending">Menunggu</option>
                        <option value="approved">Disetujui</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                    <button @click="showAddModal = true" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Tambah Transaksi
                    </button>
                </div>
            </div>
            
            <!-- Transactions Table -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="table-container">
                    <table class="w-full text-sm">
                        <thead>
                            <tr>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">ID</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Tanggal</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Deskripsi</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Kategori</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Jenis</th>
                                <th class="text-right py-3 px-4 font-medium text-gray-600">Jumlah</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Status</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-600">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="filteredTransactions.length === 0">
                                <td colspan="8" class="text-center py-12 px-4 text-gray-500">Tidak ada transaksi.</td>
                            </tr>
                            <tr v-for="transaction in filteredTransactions" :key="transaction.id" class="border-b border-gray-100">
                                <td class="py-3 px-4 font-mono">{{ transaction.id }}</td>
                                <td class="py-3 px-4">{{ formatDate(transaction.transaction_date) }}</td>
                                <td class="py-3 px-4 font-medium">{{ transaction.description }}</td>
                                <td class="py-3 px-4"><span class="px-2 py-1 rounded bg-gray-100 text-xs">{{ transaction.category?.name || 'N/A' }}</span></td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 rounded text-xs font-medium"
                                          :class="transaction.type === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                        {{ transaction.type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-semibold" :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(transaction.amount) }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="status-badge" :class="transaction.status">
                                        {{ getStatusLabel(transaction.status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex gap-2">
                                        <button @click="editTransaction(transaction)" class="action-button edit" :disabled="!canEdit(transaction)">Edit</button>
                                        <button @click="deleteTransaction(transaction.id)" class="action-button delete" :disabled="!canDelete(transaction)">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Add Transaction Modal -->
        <div v-if="showAddModal" class="modal-overlay" @click="closeModal">
            <div class="modal-content" @click.stop>
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Tambah Transaksi Baru</h3>
                </div>
                <div class="p-6">
                    <form class="space-y-4" @submit.prevent="saveTransaction">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Tanggal <span class="required">*</span></label>
                                <input type="date" v-model="form.transaction_date" class="form-input">
                                <div v-if="form.errors.transaction_date" class="text-red-500 text-xs mt-1">{{ form.errors.transaction_date }}</div>
                            </div>
                            <div>
                                <label class="form-label">Jenis Transaksi <span class="required">*</span></label>
                                <select v-model="form.type" class="form-select">
                                    <option value="income">Pendapatan</option>
                                    <option value="expense">Pengeluaran</option>
                                </select>
                                <div v-if="form.errors.type" class="text-red-500 text-xs mt-1">{{ form.errors.type }}</div>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Kategori <span class="required">*</span></label>
                            <select v-model="form.category_id" class="form-select">
                                <option :value="null" disabled>Pilih Kategori</option>
                                <option v-for="category in availableCategories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                             <div v-if="form.errors.category_id" class="text-red-500 text-xs mt-1">{{ form.errors.category_id }}</div>
                        </div>
                        <div>
                            <label class="form-label">Deskripsi <span class="required">*</span></label>
                            <input type="text" v-model="form.description" class="form-input" placeholder="Masukkan deskripsi transaksi">
                             <div v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</div>
                        </div>
                        <div>
                            <label class="form-label">Jumlah (Rp) <span class="required">*</span></label>
                            <input type="number" v-model="form.amount" class="form-input" placeholder="Masukkan jumlah">
                             <div v-if="form.errors.amount" class="text-red-500 text-xs mt-1">{{ form.errors.amount }}</div>
                        </div>
                    </form>
                </div>
                <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
                    <button @click="closeModal" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Batal
                    </button>
                    <button @click="saveTransaction" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        Simpan Transaksi
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Notification -->
        <div :class="['notification', notification.type, notification.show ? 'show' : '']">
            {{ notification.message }}
        </div>
    </div>
</template>

<style scoped>
/* Styles are kept from the original file for consistency */
.form-input, .form-select { width: 100%; padding: 10px; border: 1px solid #e5e7eb; border-radius: 6px; }
.form-label { display: block; font-weight: 500; margin-bottom: 6px; }
.required { color: #dc2626; }
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal-content { background-color: white; border-radius: 12px; max-width: 600px; width: 90%; }
.notification { position: fixed; top: 20px; right: 20px; padding: 16px; border-radius: 8px; color: white; z-index: 1001; transform: translateX(120%); transition: transform 0.3s ease; }
.notification.show { transform: translateX(0); }
.notification.success { background-color: #16a34a; }
.notification.error { background-color: #dc2626; }
.status-badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; }
.status-badge.pending { background-color: #fef3c7; color: #d97706; }
.status-badge.approved { background-color: #dcfce7; color: #16a34a; }
.status-badge.rejected { background-color: #fee2e2; color: #dc2626; }
.action-button { padding: 6px 10px; border-radius: 6px; font-size: 12px; font-weight: 500; transition: background-color 0.2s; }
.action-button.edit { background-color: #eff6ff; color: #2563eb; }
.action-button.edit:hover { background-color: #dbeafe; }
.action-button.delete { background-color: #fff1f2; color: #e11d48; }
.action-button.delete:hover { background-color: #ffe4e6; }
.action-button:disabled { background-color: #f3f4f6; color: #9ca3af; cursor: not-allowed; }
</style>
