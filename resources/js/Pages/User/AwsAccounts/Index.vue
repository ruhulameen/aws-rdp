<template>
  <UserLayout>
    <div class="bg-white shadow rounded-lg p-6">
      <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">AWS Accounts</h2>
        <a-button type="primary" @click="router.visit('/user/aws-accounts/create')">Add Account</a-button>
      </div>
      <a-table
        :dataSource="accounts"
        :columns="columns"
        rowKey="id"
        class="w-full"
        :pagination="paginationConfig"
        @change="onTableChange"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'actions'">
            <a-space>
              <a-button type="link" @click="router.visit(`/user/aws-accounts/${record.id}/edit`)">Edit</a-button>
              <a-popconfirm title="Delete?" @confirm="() => destroy(record.id)">
                <a-button type="link" danger>Delete</a-button>
              </a-popconfirm>
            </a-space>
          </template>
        </template>
      </a-table>
    </div>
  </UserLayout>
</template>
<script setup>
import UserLayout from '@/Layouts/UserLayout.vue';
import { router, usePage } from '@inertiajs/vue3';
import { message } from 'ant-design-vue';
import { watch } from 'vue';
const props = defineProps({ accounts: Array, pagination: Object });
const page = usePage();

watch(
  () => page.props.value.success,
  (val) => { if (val) message.success(val); }
);
watch(
  () => page.props.value.error,
  (val) => { if (val) message.error(val); }
);
watch(
  () => page.props.value.warning,
  (val) => { if (val) message.warning(val); }
);

const columns = [
  { title: 'Name', key: 'account_name', dataIndex: 'account_name' },
  { title: 'Region', key: 'default_region', dataIndex: 'default_region' },
  { title: 'Access Key (Last 4)', key: 'access_key', customRender: ({ record }) => `****${record.access_key.slice(-4)}` },
  { title: 'Actions', key: 'actions' },
];

const paginationConfig = {
  current: props.pagination?.current_page || 1,
  pageSize: props.pagination?.per_page || 10,
  total: props.pagination?.total || 0,
  showSizeChanger: false,
};

function onTableChange(pagination) {
  router.get(props.pagination.route, { page: pagination.current }, { preserveState: true, replace: true });
}

function destroy(id) {
  router.delete(`/user/aws-accounts/${id}`);
}
</script>
