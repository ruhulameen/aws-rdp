<template>
  <AdminLayout>
    <div class="max-w-3xl mx-auto">
      <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
        <div class="bg-gray-50 border-b p-6 flex justify-between items-center">
          <h2 class="text-2xl font-bold text-gray-800">Provision Windows RDP</h2>
          <a-radio-group v-model:value="mode" class="inline-flex p-1 bg-gray-200 rounded-lg">
            <a-radio-button value="single">Single</a-radio-button>
            <a-radio-button value="bulk">Bulk (Up to 100)</a-radio-button>
          </a-radio-group>
        </div>
        <div class="p-8">
          <Form v-if="mode === 'single'" :action="route('aws.store')" method="post">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
              <div>
                <label class="block text-gray-700 font-semibold mb-2">AWS Account</label>
                <a-select name="aws_account_id" required>
                  <a-select-option v-for="account in accounts" :key="account.id" :value="account.id">
                    {{ account.account_name }}
                  </a-select-option>
                </a-select>
              </div>
              <div>
                <label class="block text-gray-700 font-semibold mb-2">Region</label>
                <a-select name="region" required>
                  <a-select-option v-for="(label, key) in regions" :key="key" :value="key">
                    {{ label }}
                  </a-select-option>
                </a-select>
              </div>
            </div>
            <div class="mb-6">
              <label class="block text-gray-700 font-semibold mb-2">Instance Name Prefix</label>
              <a-input name="name_prefix" placeholder="e.g. Workstation-A" />
            </div>
            <a-button html-type="submit" type="primary" class="w-full py-4 rounded-xl font-bold">Launch Single Instance</a-button>
          </Form>
          <Form v-else :action="route('aws.bulk-store')" method="post">
            <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6">
              <p class="text-sm text-amber-800">
                <strong>Bulk Mode:</strong> The system will automatically distribute instances across all available accounts and regions to maximize your capacity.
              </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
              <div>
                <label class="block text-gray-700 font-semibold mb-2">Total RDPs to Create</label>
                <a-input-number name="count" min="1" max="100" :value="5" required class="w-full" />
                <p class="text-xs text-gray-500 mt-1">Recommended: Max 100 per batch.</p>
              </div>
              <div>
                <label class="block text-gray-700 font-semibold mb-2">Global Prefix</label>
                <a-input name="prefix" placeholder="e.g. Bulk-Node" />
              </div>
            </div>
            <a-button html-type="submit" type="primary" class="w-full py-4 rounded-xl font-bold">Dispatch Bulk Provisioning Jobs</a-button>
          </Form>
          <div class="mt-8 pt-6 border-t border-gray-100 flex items-center text-gray-500 text-sm">
            <lucide-info class="w-5 h-5 mr-2 text-blue-400" />
            <span>AWS provisioning and Windows boot-up usually takes 5-10 minutes.</span>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Info as LucideInfo } from 'lucide-vue';
import { ref } from 'vue';
import { Form, router } from '@inertiajs/vue3';
const props = defineProps({ accounts: Array, regions: Object });
const mode = ref('single');
</script>
