<script setup>

import { ref } from 'vue';
import Navbar from './Navbar.vue';
import Sidebar from './Sidebar.vue';


const isCollapsed = ref(false);
</script>

<template>
  <div class="flex flex-col h-screen overflow-hidden">
    <Navbar @toggle-sidebar="isCollapsed = !isCollapsed" />

    <div class="flex flex-1 overflow-hidden">
      <Sidebar :collapsed="isCollapsed" />

      <main class="flex-1 p-6 overflow-y-auto">
        <router-view />
      </main>
    </div>
  </div>
</template>

<style scoped>
</style>
