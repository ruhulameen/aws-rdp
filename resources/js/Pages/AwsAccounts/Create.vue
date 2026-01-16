<template>
  <AdminLayout>
    <div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow">
      <h2 class="text-xl font-bold mb-4">Add AWS Credentials</h2>
      <Form :action="route('aws-accounts.store')" method="post">
        <div class="mb-4">
          <label class="block mb-1">Account Label</label>
          <a-input name="account_name" placeholder="Main Prod" required />
        </div>
        <div class="mb-4">
          <label class="block mb-1">Access Key ID</label>
          <a-input name="access_key" required />
        </div>
        <div class="mb-4">
          <label class="block mb-1">Secret Access Key</label>
          <a-input-password name="secret_key" required />
        </div>
        <div class="mb-4">
          <label class="block mb-1">Default Region</label>
          <a-input name="default_region" value="us-east-1" required />
        </div>
        <a-button html-type="submit" type="primary" class="w-full">Save Account</a-button>
      </Form>
    </div>
  </AdminLayout>
</template>
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Form } from '@inertiajs/vue3';
</script>
