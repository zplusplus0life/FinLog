<script setup lang="ts">
import {useForm} from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const tombolKirim = () => {
    form.post('/login',{
        preserveScroll: true,
        onSuccess: (halaman) => {
            const data = halaman.props.auth.user as any
            if(data.role === 'admin'){
                window.location.href= '/admin/overview';
            }else if(data.role === 'staff'){
                window.location.href  = '/staff/overview';
            }else if(data.role === 'manajer'){
                window.location.href = '/manajer/overview';
            }
        },
        onError: (err) => {
            alert(`gagal login, ${err.email ?? err.password}`);
            form.reset('password');
        }
    })
}
</script>

<template>
<div class="min-h-screen flex items-center justify-center bg-white text-gray-800">
<form @submit.prevent="tombolKirim" class="p-6 rounded-md shadow-md bg-gray-50 space-y-4">
<label class="block  text-gray-700 mb-1">Email:</label>
<input type="email" v-model="form.email" required class="w-full border border-gray-300 rounded-md p-2 text-gray-800 bg-white focus:ring-2 focus:ring-blue-500"/>


<label class="block text-gray-700 mb-1">Password:</label>
<input type="password" v-model="form.password" required class="w-full border border-gray-300 rounded-md p-2 text-gray-800 bg-white focus:ring-2 focus:ring-blue-500"/>


<div class="flex items-center">
<input type="checkbox" v-model="form.remember" class="mr-2 accent-blue-600"/>
<label class="text-gray-700">Remember me</label>
</div>
<button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition">
Login
</button>
<div class="flex items-center gap-2 my-2 text-gray-400 text-sm">
    <span class="flex-1 h-px bg-gray-300"></span>
    <span>or continue as</span>
    <span class="flex-1 h-px bg-gray-300"></span>
</div>

<!-- Auditor / Viewer -->
<a
    href="/viewer/overview"
    class="flex items-center gap-2 text-sm text-blue-600 hover:underline"
>
    <span class="w-2 h-2 bg-blue-600 rounded-full"></span>
    Auditor / Viewer
</a>



</form>
</div>   
</template>

<style scoped>

</style>
