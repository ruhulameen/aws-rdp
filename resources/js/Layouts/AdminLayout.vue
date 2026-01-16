<template>
  <div class="flex h-screen bg-gray-200">
    <Sidebar />
    <div class="flex-1 flex flex-col overflow-hidden">
      <Header />
      <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200">
        <div class="mx-auto px-6 py-8">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>
<script setup>
import Sidebar from './AdminSidebar.vue';
import Header from './AdminHeader.vue';
</script>
