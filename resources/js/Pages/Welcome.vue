<template>
  <div class="flex flex-col items-center justify-center min-h-screen bg-gray-50 dark:bg-gray-900">
    <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Welcome to AWS RDP Manager</h1>
    <p class="text-lg text-gray-600 dark:text-gray-300 mb-8">Your unified dashboard for managing AWS RDP instances and accounts.</p>
    <div class="flex gap-4">
      <Link href="/auth/login" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Login</Link>
      <Link href="/auth/register" class="px-6 py-2 bg-gray-200 text-gray-900 rounded hover:bg-gray-300 transition dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700">Register</Link>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<style scoped>
</style>
