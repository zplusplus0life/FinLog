<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';

// --- SIDEBAR STATE ---
const activeItem = ref(1); // Verification is index 1
const menuItems = ref([
    { name: 'Dashboard', icon: '📊' },
    { name: 'Verifikasi Transaksi', icon: '⚙️' },
    { name: 'Laporan', icon: '📈' }
]);

// --- NAVIGATION ---
function navigateToPage(index: number) {
    activeItem.value = index;
    const routes = [
        '/manajer/overview',
        '/manajer/verification', 
        '/manajer/laporan'
    ];
    window.location.href = routes[index];
}

// --- DYNAMIC DATA ---
const transactions = ref<any[]>([]);

async function fetchPendingTransactions() {
  try {
    const response = await fetch('/api/transactions/pending', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin'
    });
    if (!response.ok) throw new Error('Failed to fetch pending transactions');
    transactions.value = await response.json();
  } catch (error) {
    console.error("Error fetching transactions:", error);
    showNotification('Gagal memuat data transaksi', 'error');
  }
}

onMounted(() => {
  fetchPendingTransactions();
});

// --- LOCAL UI STATE ---
const searchQuery = ref('');
const notification = ref({ show: false, message: '', type: 'success' });
const showRejectModalFlag = ref(false);
const rejectReason = ref('');
const transactionToHandle = ref<any>(null);

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

// --- DYNAMIC FUNCTIONS ---
async function approveTransaction(transaction: any) {
  if (!confirm(`Apakah Anda yakin ingin menyetujui transaksi: "${transaction.description}"?`)) return;

  try {
    const response = await fetch(`/api/transactions/${transaction.id}/approve`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
    });

    if (!response.ok) throw new Error('Approval failed');

    // Optimistically update UI
    transactions.value = transactions.value.filter(t => t.id !== transaction.id);
    showNotification('Transaksi berhasil disetujui', 'success');
  } catch (error) {
    console.error("Approve error:", error);
    showNotification('Gagal menyetujui transaksi', 'error');
  }
}

function openRejectModal(transaction: any) {
  transactionToHandle.value = transaction;
  rejectReason.value = '';
  showRejectModalFlag.value = true;
}

async function confirmReject() {
  if (!rejectReason.value.trim()) {
    showNotification('Alasan penolakan wajib diisi!', 'error');
    return;
  }

  try {
    const response = await fetch(`/api/transactions/${transactionToHandle.value.id}/reject`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify({ rejection_reason: rejectReason.value })
    });

    if (!response.ok) throw new Error('Rejection failed');

    // Optimistically update UI
    transactions.value = transactions.value.filter(t => t.id !== transactionToHandle.value.id);
    showNotification('Transaksi berhasil ditolak', 'success');
    showRejectModalFlag.value = false;
  } catch (error) {
    console.error("Reject error:", error);
    showNotification('Gagal menolak transaksi', 'error');
  }
}

// --- HELPER & DUMMY FUNCTIONS ---
const filteredTransactions = computed(() => {
    return transactions.value.filter(transaction => {
        return transaction.description.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
               (transaction.user?.nama_lengkap || '').toLowerCase().includes(searchQuery.value.toLowerCase());
    });
});

function formatDate(dateString: string) { return new Date(dateString).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' }); }
function formatCurrency(amount: number) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount); }
function getInitials(name: string = '') { return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2); }
function showNotification(message: string, type: 'success' | 'error' | 'warning') {
  notification.value.message = message;
  notification.value.type = type;
  notification.value.show = true;
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
                <h2 class="text-2xl font-bold text-gray-800">Verifikasi Transaksi</h2>
                <input type="text" v-model="searchQuery" placeholder="Cari..." class="search-input">
            </div>
            
            <!-- Transactions Table -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="table-container">
                    <table class="w-full text-sm">
                        <thead>
                            <tr>
                                <th class="text-left py-3">Staff</th>
                                <th class="text-left py-3">Tanggal</th>
                                <th class="text-left py-3">Deskripsi</th>
                                <th class="text-left py-3">Kategori</th>
                                <th class="text-left py-3">Jenis</th>
                                <th class="text-right py-3 px-4">Jumlah</th>
                                <th class="text-left py-3 px-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                             <tr v-if="filteredTransactions.length === 0">
                                <td colspan="7" class="text-center py-12 text-gray-500">Tidak ada transaksi untuk diverifikasi.</td>
                            </tr>
                            <tr v-for="transaction in filteredTransactions" :key="transaction.id" class="border-b border-gray-100">
                                <td class="py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="user-avatar">{{ getInitials(transaction.user?.nama_lengkap) }}</div>
                                        <span>{{ transaction.user?.nama_lengkap || 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="py-3">{{ formatDate(transaction.transaction_date) }}</td>
                                <td class="py-3 font-medium">{{ transaction.description }}</td>
                                <td class="py-3"><span class="px-2 py-1 rounded bg-gray-100 text-xs">{{ transaction.category?.name || 'N/A' }}</span></td>
                                <td class="py-3">
                                    <span class="px-2 py-1 rounded text-xs font-medium"
                                          :class="transaction.type === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                        {{ transaction.type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                                    </span>
                                </td>
                                <td class="py-3 text-right font-semibold" :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(transaction.amount) }}
                                </td>
                                <td class="py-3">
                                    <div class="flex gap-2">
                                        <button @click="approveTransaction(transaction)" class="action-button action-approve">Setujui</button>
                                        <button @click="openRejectModal(transaction)" class="action-button action-reject">Tolak</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Reject Transaction Modal -->
        <div v-if="showRejectModalFlag" class="modal-overlay" @click="showRejectModalFlag = false">
            <div class="modal-content" @click.stop>
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold">Tolak Transaksi</h3>
                </div>
                <div class="p-6">
                    <div class="form-group">
                        <label class="form-label">Alasan Penolakan <span class="required">*</span></label>
                        <textarea v-model="rejectReason" class="form-input" rows="4" placeholder="Masukkan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="p-6 border-t flex justify-end gap-3">
                    <button @click="showRejectModalFlag = false" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg">Batal</button>
                    <button @click="confirmReject" class="px-4 py-2 bg-red-600 text-white rounded-lg">Tolak Transaksi</button>
                </div>
            </div>
        </div>

        <!-- Notification -->
        <div :class="['notification', notification.type, notification.show ? 'show' : '']">{{ notification.message }}</div>
    </div>
</template>

<style scoped>
/* Minimal styles for functionality */
.search-input { padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 6px; }
.user-avatar { width: 24px; height: 24px; border-radius: 50%; background-color: #3b82f6; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px; }
.action-button { padding: 6px 12px; border-radius: 6px; font-size: 12px; }
.action-approve { background-color: #dcfce7; color: #16a34a; }
.action-reject { background-color: #fee2e2; color: #dc2626; }
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal-content { background-color: white; border-radius: 12px; max-width: 500px; width: 90%; }
.form-input { width: 100%; padding: 10px; border: 1px solid #e5e7eb; border-radius: 6px; }
.form-label { display: block; font-weight: 500; margin-bottom: 6px; }
.required { color: #dc2626; }
.notification { position: fixed; top: 20px; right: 20px; padding: 16px; border-radius: 8px; color: white; z-index: 1001; transform: translateX(120%); transition: transform 0.3s ease; }
.notification.show { transform: translateX(0); }
.notification.success { background-color: #16a34a; }
.notification.error { background-color: #dc2626; }
</style>
