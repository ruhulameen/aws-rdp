<template>
  <AdminLayout>
    <div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow">
      <h2 class="text-xl font-bold mb-4">Edit AWS Credentials</h2>
      <Form :action="route('aws-accounts.update', { id: account.id })" method="post">
        <input type="hidden" name="_method" value="PUT" />
        <div class="mb-4">
          <label class="block mb-1">Account Label</label>
          <a-input name="account_name" :value="account.account_name" required />
        </div>
        <div class="mb-4">
          <label class="block mb-1">Access Key ID</label>
          <a-input name="access_key" :value="account.access_key" required />
        </div>
        <div class="mb-4">
          <label class="block mb-1">Secret Access Key</label>
          <a-input-password name="secret_key" :value="account.secret_key" required />
        </div>
        <div class="mb-4">
          <label class="block mb-1">Default Region</label>
          <a-input name="default_region" :value="account.default_region" required />
        </div>
        <a-button html-type="submit" type="primary" class="w-full">Update Account</a-button>
      </Form>
    </div>
  </AdminLayout>
</template>
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Form } from '@inertiajs/vue3';
const props = defineProps({ account: Object });
</script>
