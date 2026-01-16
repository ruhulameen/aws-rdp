<template>
  <AuthLayout>
    <h1 class="text-2xl font-bold mb-4">Forgot Password</h1>
    <Form action="/forgot-password" method="post" #default="{ errors, wasSuccessful }">
      <div class="mb-4">
        <label class="block mb-1 font-semibold">Email</label>
        <input type="email" name="email" class="w-full p-3 border rounded-lg" required />
        <div v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</div>
      </div>
      <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 transition shadow-lg">
        Send Password Reset Link
      </button>
      <div v-if="wasSuccessful" class="mt-4 text-green-600 font-bold">Reset link sent!</div>
    </Form>
  </AuthLayout>
</template>

<script setup>
import { Form } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
</script>
