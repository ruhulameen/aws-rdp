<template>
  <UserLayout>
    <div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow">
      <h2 class="text-xl font-bold mb-4">Edit AWS Credentials</h2>
      <Form :action="`/user/aws-accounts/${account.id}`" method="post">
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
  </UserLayout>
</template>

<script setup>
import UserLayout from '@/Layouts/UserLayout.vue';
import { Form, usePage } from '@inertiajs/vue3';
import { message } from 'ant-design-vue';
const props = defineProps({ account: Object });
const page = usePage();

if (page.props.success) {
  message.success(page.props.success);
}
if (page.props.error) {
  message.error(page.props.error);
}
if (page.props.warning) {
  message.warning(page.props.warning);
}
</script>
