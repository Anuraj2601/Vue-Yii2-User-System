<script setup>
import store from '@/store';
import { computed, ref, watch, watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import { useStore } from 'vuex';

const { t } = useI18n();
const route = useRoute();
const props = defineProps({
    collapsed: Boolean
});

const emit = defineEmits(['toggle-sidebar']);

const hasRole = (role) => store.getters['auth/hasRole'](role);

const permissions = computed(() => store.getters['auth/permissions']);
const hasPermission = (permission) => permissions.value.includes(permission);

const isActive = (path) => route.path.startsWith(path);

watchEffect(() => {
  console.log("Permissions in Sidebar:", permissions.value);
});
</script>

<template>
  <aside :class="['bg-gray-800 text-white', collapsed ? 'w-20' : 'w-64']">
    <div class="space-y-4">
      <h2 class="text-lg font-semibold"></h2>
      <ul class="ml-4">
        <li>
            <router-link to="/dashboard"
                :class="[
                    'block py-2 px-4 hover:bg-gray-700 rounded text-base flex items-center space-x-2',
                    isActive('/dashboard') ? 'bg-gray-700 font-bold' : ''
                ]"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" />
                </svg>
                <span v-if="!collapsed">{{ $t('dashboard') }}</span>
            </router-link>
        </li>
        <!-- <li v-if="hasPermission('manage_social_media')">
            <router-link to="/posts"
                :class="[
                    'block py-2 px-4 hover:bg-gray-700 rounded text-base flex items-center space-x-2',
                    isActive('/posts') ? 'bg-gray-700 font-bold' : ''
                ]"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                </svg>
            <span v-if="!collapsed">{{ $t('posts') }}</span>
            </router-link>
        </li> -->
        <!-- <li v-if="hasPermission('manage_my_posts')">
            <router-link to="/myposts"
                :class="[
                    'block py-2 px-4 hover:bg-gray-700 rounded text-base flex items-center space-x-2',
                    isActive('/myposts') ? 'bg-gray-700 font-bold' : ''
                ]"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" />
                </svg>
                <span v-if="!collapsed">{{$t('my_posts')}}</span>
            </router-link>
        </li> -->
        <li v-if="hasPermission('manage_users')">
            <router-link to="/users"
                :class="[
                    'block py-2 px-4 hover:bg-gray-700 rounded text-base flex items-center space-x-2',
                    isActive('/users') ? 'bg-gray-700 font-bold' : ''
                ]"
            >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
            </svg>
                <span v-if="!collapsed">{{ $t('users') }}</span>
            </router-link>
        </li>
         <!-- <li v-if="hasPermission('manage_roles')">
            <router-link to="/roles"
                :class="[
                    'block py-2 px-4 hover:bg-gray-700 rounded text-base flex items-center space-x-2',
                    isActive('/roles') ? 'bg-gray-700 font-bold' : ''
                ]"
            >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
            </svg>
                <span v-if="!collapsed">{{ $t('roles') }}</span>
            </router-link>
        </li> -->
        <!-- <li v-if="hasPermission('manage_permissions')">
            <router-link to="/permissions"
                :class="[
                    'block py-2 px-4 hover:bg-gray-700 rounded text-base flex items-center space-x-2',
                    isActive('/permissions') ? 'bg-gray-700 font-bold' : ''
                ]"
            >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
            </svg>
                <span v-if="!collapsed">{{ $t('permissions') }}</span>
            </router-link>
        </li> -->
      </ul>
    </div>
  </aside>
</template>

<style scoped>
</style>
