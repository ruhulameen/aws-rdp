<template>
  <AuthLayout>
    <h1 class="text-2xl font-bold mb-4">Reset Password</h1>
    <Form action="/reset-password" method="post" #default="{ errors, wasSuccessful }">
      <div class="mb-4">
        <label class="block mb-1 font-semibold">Email</label>
        <input type="email" name="email" class="w-full p-3 border rounded-lg" required />
        <div v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</div>
      </div>
      <div class="mb-4">
        <label class="block mb-1 font-semibold">New Password</label>
        <input type="password" name="password" class="w-full p-3 border rounded-lg" required />
        <div v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password }}</div>
      </div>
      <div class="mb-4">
        <label class="block mb-1 font-semibold">Confirm Password</label>
        <input type="password" name="password_confirmation" class="w-full p-3 border rounded-lg" required />
        <div v-if="errors.password_confirmation" class="text-red-500 text-xs mt-1">{{ errors.password_confirmation }}</div>
      </div>
      <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 transition shadow-lg">
        Reset Password
      </button>
      <div v-if="wasSuccessful" class="mt-4 text-green-600 font-bold">Password reset successful!</div>
    </Form>
  </AuthLayout>
</template>
<script setup>
import { Form } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
</script>
