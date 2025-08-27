<script setup>
import store from '@/store';
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n';
import i18n from '@/i18n';
import { useStore } from 'vuex';

const totalUsers = ref('')
const totalPosts = ref('')   
const userPosts = ref('')
const totalsocial = ref('')   
const totalRoles = ref('');
const totalPermissions = ref('');  
const latestPosts = ref([]);
const latestUsers = ref([]);
/* const postEngagement = ref(85)  */
const { t } = useI18n();
const name = localStorage.getItem('user_name');
const token = localStorage.getItem('auth_token');

/* const store = useStore(); */

const hasRole = (role) => store.getters['auth/hasRole'](role);
const hasPermission = (permission) => store.getters['auth/hasPermission'](permission);

const userName = computed(() => store.state.auth.name || name);

const getDataCount = async() => {
  const response = await axios.get(`api/users`, {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept-Language': i18n.global.locale.value
    }
  });
  totalUsers.value = response.data.count;
}

onMounted(getDataCount);
</script>

<template>
  <div class="p-6">
    <h2 class="text-2xl text-left font-semibold mb-4"> {{ t('dashboard_s.greeting') }} {{ userName }}! 😍</h2>
    <p class="text-lg text-gray-600 mb-6"></p>

    <div :class="['grid sm:grid-cols-1 md:grid-cols-2 gap-6', 'lg:grid-cols-4']" v-if="hasPermission('view_dashboard')">
      
      <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
        <h3 class="text-xl font-medium text-gray-700 mb-2">{{ t('dashboard_s.totalUsers') }}</h3>
        <p class="text-3xl font-semibold text-gray-900">{{ totalUsers }}</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
</style>

