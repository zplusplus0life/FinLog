<template>
  <div class="forbidden-page">
    <div class="card">
      <h1>403 — Forbidden</h1>
      <p>{{ message }}</p>
      <a href="/">Kembali ke Login</a>
    </div>
  </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
const page = usePage()
const props: any = page.props.value || {}
const message = props.message || 'Anda tidak memiliki akses.'
</script>

<style scoped>
.forbidden-page { display:flex; align-items:center; justify-content:center; min-height:80vh }
.card { background: white; padding:20px; border-radius:8px; text-align:center }
</style>
