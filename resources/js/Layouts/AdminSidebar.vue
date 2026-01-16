<template>
  <a-layout-sider width="220" class="h-screen bg-white shadow-md">
    <div class="flex items-center justify-center mt-8 mb-6">
      <img :src="logoUrl" alt="logo" class="w-40" />
    </div>
    <a-menu mode="inline" :default-selected-keys="['dashboard']" class="border-none bg-transparent">
      <a-menu-item key="dashboard">
        <lucide-home class="inline-block mr-2" />
        Dashboard
      </a-menu-item>
      <a-sub-menu key="offers" title="Offers">
        <a-menu-item key="manage">
          <lucide-server class="inline-block mr-2" />
          Manage
        </a-menu-item>
        <a-menu-item key="add">
          <lucide-plus class="inline-block mr-2" />
          Add Offer
        </a-menu-item>
      </a-sub-menu>
      <a-menu-item key="aws">
        <lucide-cloud class="inline-block mr-2" />
        AWS Accounts
      </a-menu-item>
      <a-menu-item key="logout">
        <lucide-log-out class="inline-block mr-2" />
        Logout
      </a-menu-item>
    </a-menu>
  </a-layout-sider>
</template>
<script setup>
import { Home as LucideHome, Server as LucideServer, Plus as LucidePlus, Cloud as LucideCloud, LogOut as LucideLogOut } from 'lucide-vue';
const logoUrl = '/logo.png';
</script>
