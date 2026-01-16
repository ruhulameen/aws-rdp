<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow py-4 px-8 flex justify-between items-center">
      <h1 class="text-xl font-bold text-blue-700">User Dashboard</h1>
      <nav class="flex gap-6">
        <Link href="/user/dashboard" class="text-blue-600 font-semibold">Dashboard</Link>
        <Link href="/user/aws-accounts" class="text-gray-600 hover:text-blue-600">AWS Accounts</Link>
        <Link href="/user/aws" class="text-gray-600 hover:text-blue-600">RDP Instances</Link>
        <form method="POST" action="/auth/logout">
          <button type="submit" class="text-red-500 font-semibold ml-4">Logout</button>
        </form>
      </nav>
    </header>
    <main class="max-w-6xl mx-auto py-8 px-4">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>
