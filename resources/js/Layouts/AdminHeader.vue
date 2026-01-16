<template>
  <a-layout-header class="flex items-center justify-between px-4 py-3 bg-white border-b border-gray-200">
    <div class="flex items-center">
      <a-button type="text" class="lg:hidden">
        <lucide-menu class="w-6 h-6" />
      </a-button>
      <a-input-search placeholder="Search" class="ml-4 w-48 sm:w-64" />
    </div>
    <div class="flex items-center space-x-4">
      <a-dropdown>
        <template #overlay>
          <a-menu>
            <a-menu-item key="profile">Profile</a-menu-item>
            <a-menu-item key="logout">Logout</a-menu-item>
          </a-menu>
        </template>
        <a-avatar size="large" class="shadow ring-2 ring-blue-500">
          <lucide-user class="w-6 h-6" />
        </a-avatar>
      </a-dropdown>
    </div>
  </a-layout-header>
</template>
<script setup>
import { Menu as LucideMenu, User as LucideUser } from 'lucide-vue';
</script>
