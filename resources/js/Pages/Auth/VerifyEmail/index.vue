<template>
  <AuthLayout>
    <h1 class="text-2xl font-bold mb-4">Verify Email</h1>
    <Form action="/verify-email" method="post" #default="{ errors, wasSuccessful }">
      <div class="mb-4">
        <label class="block mb-1 font-semibold">Verification Code</label>
        <input type="text" name="code" class="w-full p-3 border rounded-lg" required />
        <div v-if="errors.code" class="text-red-500 text-xs mt-1">{{ errors.code }}</div>
      </div>
      <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 transition shadow-lg">
        Verify Email
      </button>
      <div v-if="wasSuccessful" class="mt-4 text-green-600 font-bold">Email verified!</div>
    </Form>
  </AuthLayout>
</template>

<script setup>
import { Form } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
</script>
