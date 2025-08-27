<script setup>
import { onBeforeUnmount, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import { useI18n } from "vue-i18n";
import { useStore } from "vuex";
import Profile from "../users/profile.vue";

const router = useRouter();
const userName = ref("User");
const userRole = ref("User");
const dropdownOpen = ref(false);
const navbarOpen = ref(false);
const showProfile = ref(false);
const userImage = ref(null);
const dropdownRef = ref(null);
const buttonRef = ref(null);

const userId = localStorage.getItem('user_id');
const name = localStorage.getItem("user_name");
const role = localStorage.getItem("user_role");
const token = localStorage.getItem('auth_token');
const store = useStore();
const {t} = useI18n();

const { locale } = useI18n();

const availableLocales = ['en', 'de'];

const emit = defineEmits(['toggle-sidebar']);

const changeLocale = async(event) => {
    const selectedLang = event.target.value;
    try {
        await axios.put('/api/user/language', { language: selectedLang }, {
            headers: {
                Authorization: `Bearer ${token}`,
            },
        });
        store.commit('auth/SET_LANGUAGE', selectedLang);
        locale.value = selectedLang;
        localStorage.setItem("user_lang", selectedLang);
    } catch (error) {
        console.error("Failed to update language:", error);
    }
};

const getFlag = (locale) => {
  const flags = {
    en: '🇺🇸',
    de: '🇩🇪',
    fr: '🇫🇷',
  };
  return flags[locale] || '🏳️';
};

const logoutUser = () => {
    localStorage.removeItem("auth_token");

    router.push("/login");
};

const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value;
};

function openProfile() {
    showProfile.value = true;
}

const handleClickOutside = (event) => {
    if (
        dropdownRef.value &&
        !dropdownRef.value.contains(event.target) &&
        buttonRef.value &&
        !buttonRef.value.contains(event.target)
    ) {
        dropdownOpen.value = false;
    }
};


const fetchUserData = async () => {
    if(!token) {
        console.log('No auth token found');
        return;
    }
     console.log('Fetching user data with token:', token);
  try {
    const response = await axios.get(`/api/user/profile`, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    const user = response.data.user;
    userImage.value = user.image
      ? `http://localhost:8888/${user.image.replace(/^\/+/, '')}`
      : `http://localhost:8888/images/profile/sample.jpg`;

    userName.value = user.name;
    store.commit('auth/SET_NAME', user.name);
    localStorage.setItem('user_name', user.name);

    if (user.language && availableLocales.includes(user.language)) {
        locale.value = user.language;
        localStorage.setItem("user_lang", user.language);
    }
  } catch (error) {
    console.error('Failed to fetch user profile:', error);
  }
};

onMounted(() => {
    locale.value = localStorage.getItem('user_lang') || 'en';
    fetchUserData();
    
    if (name) {
        userName.value = name;
    }

    if (role) {
        userRole.value = role;
    }

    window.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
    window.removeEventListener("click", handleClickOutside);
});
</script>

<template>
        <nav class="bg-white shadow-md">
            <div class="max-w-full px-4 py-2">
                <div class="flex items-center justify-between">
                <!-- Left Section -->
                <div class="flex items-center">
                    <button @click="$emit('toggle-sidebar')" class="hover:bg-gray-700 hover:text-white p-2 rounded">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                    <a class="text-xl font-semibold text-gray-700 no-underline">
                    {{ $t('navbar.userPosts') }}
                    </a>
                </div>

                <!-- Right Section -->
                <div class="flex items-center space-x-3 flex-wrap justify-end ml-auto">
                    <!-- Language Switcher -->
                    <div class="language-switcher text-xl">
                        <select v-model="locale" @change="changeLocale" class="p-1 rounded border-1">
                            <option v-for="loc in availableLocales" :key="loc" :value="loc">
                                {{ getFlag(loc) }} {{ loc.toUpperCase() }}
                            </option>
                        </select>
                    </div>

                    <!-- Avatar + Dropdown Wrapper -->
                    <div class="relative flex items-center">
                        <!-- User Menu -->
                        <button ref="buttonRef" @click="toggleDropdown"
                            class="flex items-center justify-center w-12 h-12 rounded-full hover:bg-gray-700 focus:outline-none border-1">
                            <img class="w-10 h-10 rounded-full object-cover" :src="userImage" alt="User" />
                        </button>

                        <!-- Dropdown -->
                        <div v-if="dropdownOpen" ref="dropdownRef"
                            class="absolute right-0 top-14 bg-white border border-gray-200 rounded-lg shadow-lg w-48 cursor-pointer">
                            <a @click="openProfile"
                                class="block px-4 py-2 text-xl text-gray-700 hover:bg-gray-100 no-underline flex space-x-4">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                                <span>{{ $t('navbar.profile') }}</span>
                            </a>
                            <a href="#" @click="logoutUser"
                                class="block px-4 py-2 text-xl text-gray-700 hover:bg-gray-100 no-underline flex space-x-4 items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                                </svg>
                                <span>{{ $t('navbar.logout') }}</span>
                            </a>
                        </div>
                    </div>

                    <!-- Username -->
                    <span class="text-gray-700 text-xl font-medium whitespace-nowrap">
                        {{ userName }}
                    </span>
                </div>

                
                </div>
            </div>
        </nav>


        <Profile :show="showProfile" @close="showProfile=false" @updated="fetchUserData"/> 
</template>

<style src="">
.navbar-nav {
    display: flex;
    font-size: large;
}
</style>
