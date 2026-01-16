<template>
  <AdminLayout>
    <div class="bg-white shadow rounded-lg p-6">
      <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">AWS Accounts</h2>
        <a-button type="primary" @click="router.visit(route('aws-accounts.create'))">Add Account</a-button>
      </div>
      <a-table :dataSource="accounts" :columns="columns" rowKey="id" class="w-full">
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'actions'">
            <a-space>
              <a-button type="link" @click="router.visit(route('aws-accounts.edit', { id: record.id }))">Edit</a-button>
              <a-popconfirm title="Delete?" @confirm="() => destroy(record.id)">
                <a-button type="link" danger>Delete</a-button>
              </a-popconfirm>
            </a-space>
          </template>
        </template>
      </a-table>
    </div>
  </AdminLayout>
</template>
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
const props = defineProps({ accounts: Array });
const columns = [
  { title: 'Name', key: 'account_name', dataIndex: 'account_name' },
  { title: 'Region', key: 'default_region', dataIndex: 'default_region' },
  { title: 'Access Key (Last 4)', key: 'access_key', customRender: ({ record }) => `****${record.access_key.slice(-4)}` },
  { title: 'Actions', key: 'actions' },
];
function destroy(id) {
  router.delete(`/aws-accounts/${id}`);
}
</script>
