<template>
  <AdminLayout>
    <div class="bg-white shadow-md rounded-xl overflow-hidden">
      <div class="p-6 border-b flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-800">My RDP Instances</h2>
        <span class="text-sm text-gray-500">Auto-refresh recommended while pending</span>
      </div>
      <a-table :dataSource="instances" :columns="columns" rowKey="id" class="w-full">
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'status'">
            <a-tag v-if="record.status === 'pending'" color="gold" class="animate-pulse">Provisioning</a-tag>
            <a-tag v-else-if="record.status === 'ready'" color="green">Ready</a-tag>
            <a-tag v-else-if="record.status === 'running'" color="green">Running</a-tag>
            <a-tag v-else-if="record.status === 'terminated'" color="red">Terminated</a-tag>
            <a-tag v-else color="red">Failed</a-tag>
          </template>
          <template v-else-if="column.key === 'credentials'">
            <div v-if="record.password" class="flex flex-col space-y-1">
              <span class="text-xs text-gray-500">User: Administrator</span>
              <a-input-password :value="record.password" readonly class="w-32" />
            </div>
            <span v-else class="text-xs text-gray-400 italic">Waiting for password...</span>
          </template>
          <template v-else-if="column.key === 'actions'">
            <a-popconfirm title="Terminate this instance?" @confirm="() => terminate(record.id)">
              <a-button type="text" danger>
                <lucide-trash-2 class="w-4 h-4" />
              </a-button>
            </a-popconfirm>
          </template>
        </template>
      </a-table>
    </div>
  </AdminLayout>
</template>
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Trash2 as LucideTrash2 } from 'lucide-vue';
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { message } from 'ant-design-vue';
const props = defineProps({ instances: Array });
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

const columns = [
  { title: 'Instance ID / IP', key: 'instance', dataIndex: 'instance_id', customRender: ({ record }) => `${record.instance_id} / ${record.public_ip || 'Allocating IP...'}` },
  { title: 'Account', key: 'account', dataIndex: ['awsAccount', 'account_name'] },
  { title: 'Status', key: 'status' },
  { title: 'Credentials', key: 'credentials' },
  { title: 'Actions', key: 'actions' },
];
function terminate(id) {
  router.delete(`/admin/aws/${id}`);
}
</script>
