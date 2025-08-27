<script setup>
import { onMounted, ref } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from "@headlessui/vue";
/* import Edit from "./edit.vue"; */
import { useStore } from "vuex";
import i18n from "../../i18n";
import { toast } from "vue3-toastify";
import Edit from "./edit.vue";
import WarningModal from "../modal/WarningModal.vue";
import { useI18n } from "vue-i18n";


const users = ref([]);
const router = useRouter();
const showWarning = ref(false);
const userToDelete = ref(null);
const edit = ref(false);
const selectedUserId = ref(null);
const loading = ref(false);
const modalTitle = ref('');
const modalMessage = ref('');
const modalRef = ref(null);
const showEdit = ref(false);
const currentPage = ref(1);
const searchQuery = ref('');
const totalPages = ref(1);
const totalUsers = ref(0);
const permissionError  = ref('');
const perPage = ref(7);
const {t} = useI18n();

const showModal = (title, message) => {
  modalTitle.value = title;
  modalMessage.value = message;
  modalRef.value?.show();
};

const token = localStorage.getItem('auth_token');

function closeEdit() {
    edit.value = false;
}

const store = useStore();

const hasRole = (role) => store.getters['auth/hasRole'](role);
const hasPermission = (permission) => store.getters['auth/hasPermission'](permission);

function editUser(id) {
    selectedUserId.value = id;
    edit.value = true;
    showEdit.value = true;
}

function addUser() {
    selectedUserId.value = null;
    edit.value = true;
    showEdit.value = false;
}

const axiosInstance = axios.create({
    headers: {
      Authorization: `Bearer ${token}`
    }
});

const showSuccessToast = (message) => {
        toast.success(message, {
            position: "top-right",
            autoClose: 3000, // 3 seconds
            hideProgressBar: false,
            closeOnClick: true,
            pauseOnHover: true,
            draggable: true,
    });
}


    const showErrorToast = (message) => {
        toast.error(message, {
            position: "top-right",
            autoClose: 3000,
        });
    };

function confirmDelete(id) {
    userToDelete.value = id;
    showWarning.value = true;
}

const fetchUsers = async () => {
    loading.value = true;
    try {
        const response = await axios.get(`/api/users`, {
            headers: {
                "Authorization": `Bearer ${token}`,
                "Accept-Language": i18n.global.locale.value
            }
        });
        users.value = response.data.users; 
        /* totalUsers.value = response.data.total;
        totalPages.value = response.data.last_page; */
        loading.value = false;
    } catch (response) {
        if(error.response) {
            if (error.response.status === 403) {
                permissionError.value = error.response.data.message;
            }
        }
        console.error('Fetching user details error', response);
    }
} 

const changePage = (page) => {
  if (page > 0 && page <= totalPages.value) {
    currentPage.value = page;
    fetchUsers();
  }
}

/* const editUser = (userId) => {
    try {
        router.push(`/edit-user/${userId}`);
    } catch (error) {
        console.error('Unable to redirect edit user', error);
    }
    
} */


const deleteUser = async () => {
    try {
        showWarning.value = false;
        loading.value = true;
        const response = await axios.delete(`/api/user/delete/${userToDelete.value}`, {
            headers: {
                "Authorization" : `Bearer ${token}`,
                "Accept-Language": i18n.global.locale.value
            }
        });
        loading.value = false;
        console.log(response.data);
        if(response.data.status == 'success') {
            fetchUsers();
            showSuccessToast(t('messages.user.delete_success'));
        }
    } catch (error) {
        console.error("Delete user Error" , error);
        showErrorToast(t('fix_errors'));
    }
}

onMounted(fetchUsers);
</script>

<template>
        <div class="title text-center mb-6">
            <h2 class="text-2xl font-semibold">{{ $t('users_s.title') }}</h2>
        </div>
        <Modal
                ref="modalRef"
                :title="modalTitle"
                :message="modalMessage"
                modal-id="feedbackModal"
            />

            <div class="title flex justify-between items-center" v-if="hasPermission('create_user')">
                <button 
                    class="bg-gray-200 text-black mr-32 px-2 py-2 rounded-lg focus:outline-none hover:bg-gray-800 hover:text-gray-100 focus:ring-2 focus:ring-black-500 flex items-center space-x-2"
                    @click="addUser()"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                        {{ $t('users_s.add') }}
                </button>
                <!-- <div class="space-x-2">
                    <select 
                        v-model="perPage" 
                        @change="fetchUsers(1)"
                        class="mt-2 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                        <option v-for="n in [5, 6, 7, 8]" :key="n" :value="n">
                            {{ n }} rows
                        </option>
                    </select>
                        <input 
                        v-model="searchQuery" 
                        @input="fetchUsers(1)" 
                        type="text" 
                        class="mt-2 w-65 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" 
                        :placeholder="$t('placeholders.search_users')"
                        />
                    </div> -->
            </div>

            <div v-if="permissionError" class="text-red-600 text-sm ml-6 my-2">
                {{ permissionError }}
            </div>
        
            <!-- <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 ml-20 mr-20">
                <div v-for="(user, index) in users" :key="user.id" class="bg-white shadow rounded-lg p-6 space-y-4" v-if="!loading">
                    <div class="flex items-center justify-between">
                    <div class="text-left">
                        <h2 class="text-lg font-semibold text-gray-800">{{ user.name }}</h2>
                        <p class="text-sm text-gray-600">{{ user.email }}</p>
                    </div>
                    <span class="text-gray-400 text-sm">#{{ index + 1 }}</span>
                    </div>

                    <div class="flex flex-wrap gap-2">
                    <span 
                        v-for="(role, idx) in user.role" 
                        :key="idx"
                        class="inline-flex items-center bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded-full"
                    >
                        {{ role }}
                    </span>
                    </div>

                    <div class="flex space-x-3 pt-2">
                    <button
                        class="bg-yellow-500 text-white px-3 py-1 rounded-md hover:bg-yellow-400 text-sm flex items-center"
                        @click="openDrawer(user.id)"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                        Edit
                    </button>
                    <button
                        class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-400 text-sm flex items-center"
                        @click="confirmDelete(user.id)"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Delete
                    </button>
                    </div>
                </div>
                <div v-else class="items-center p-4">
                    <button type="button" class="flex" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 animate-spin">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    <div class="text-base">Processing....</div>
                    </button>
                </div>
                </div> -->
                <div class="overflow-x-auto ml-6 mr-10">
                    <div class="list-container" v-if="!loading">
                        <div class="flex items-center text-left font-semibold text-gray-800 mt-6 px-10">
                            <span class="flex-1 text-sm">{{ $t('users_s.name') }}</span>
                            <span class="flex-1 text-sm">{{ $t('users_s.email') }}</span>
                            <span class="flex-1 text-sm">{{ $t('users_s.roles') }}</span>
                            <span class="w-auto text-sm" v-if="hasPermission('edit_user') || hasPermission('delete_user')">{{ $t('users_s.actions') }}</span>
                        </div>

                        <ul class="w-full bg-white p-4 space-y-2 rounded-lg mt-2">
                        <li
                            v-for="(user, index) in users"
                            :key="user.id"
                            class=" py-2 cursor-pointer bg-gray-100 flex justify-between items-center border-b-2 border-gray-200 rounded-lg"
                        >
                            <!-- <div class="flex items-center text-left mt-2"> -->
                                <span class="flex-1 text-lg text-left ml-6 truncate">{{ user.name }}</span>
                                <span class="flex-1 text-sm truncate">{{ user.email }}</span>
                                <span class="flex-2 text-sm truncate flex-wrap">
                                <span
                                    v-for="(role, idx) in user.roles"
                                    :key="idx"
                                    class="inline-block bg-green-100 text-green-800 px-2 py-0.5 rounded-full text-xs font-semibold"
                                >
                                    {{ role }}
                                </span>
                                </span>

                            <div class="flex space-x-2">
                            <button
                                v-if="hasPermission('edit_user')"
                                @click.stop="editUser(user.id)"
                                class="bg-yellow-500 text-white px-3 py-1 rounded-md hover:bg-yellow-400 text-sm flex items-center"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                                {{ $t('users_s.edit') }}
                            </button>

                            <button
                                v-if="hasPermission('delete_user')"
                                @click.stop="confirmDelete(user.id)"
                                class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-400 text-sm flex items-center"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                {{ $t('users_s.delete') }}
                            </button>
                            </div>
                           <!--  </div> -->
                        </li>
                        </ul>
                    </div>
                    <div v-else class="items-center p-4">
                        <button type="button" class="flex" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 animate-spin">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                        <div class="text-base">Processing....</div>
                        </button>
                    </div>
                </div>



            <WarningModal
        :show="showWarning"
        :title="$t('users_s.deleteTitle')"
        :message="$t('users_s.deleteMessage')"
        @close="showWarning=false"
        @confirm="deleteUser"
      >

      </WarningModal>

      <TransitionRoot as="template" :show="edit">
            <Dialog class="relative z-10">
            <TransitionChild
                as="template"
                enter="ease-out duration-300"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in duration-200"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div
                class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
                >
                <TransitionChild
                    as="template"
                    enter="ease-out duration-300"
                    enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to="opacity-100 translate-y-0 sm:scale-100"
                    leave="ease-in duration-200"
                    leave-from="opacity-100 translate-y-0 sm:scale-100"
                    leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <DialogPanel
                    class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 w-full sm:max-w-lg"
                    >
                    
                    <Edit :userId="selectedUserId" :showEdit="showEdit" @close="closeEdit" @updated="fetchUsers" /> 
                    
                    </DialogPanel>
                </TransitionChild>
                </div>
            </div>
            </Dialog>
      </TransitionRoot>

      <!-- <div class="mt-2">
                    <nav aria-label="Page navigation example">
                    <ul class="flex justify-center space-x-2">
                        <li class="page-item" :class="{ 'opacity-50 cursor-not-allowed': currentPage === 1 }">
                        <a 
                            class="page-link inline-flex items-center px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-md hover:bg-gray-200"
                            href="#" 
                            aria-label="Previous" 
                            @click.prevent="changePage(currentPage - 1)">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                        </li>

                        <li 
                        v-for="pageNumber in totalPages" 
                        :key="pageNumber"
                        
                        class="page-item">
                        <a 
                            :class="{ 'bg-blue-500 rounded-md text-white': pageNumber === currentPage, 'text-gray-700': pageNumber !== currentPage }"
                            class="page-link inline-flex items-center px-4 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-200 hover:text-black "
                            href="#" 
                            @click.prevent="changePage(pageNumber)">
                            {{ pageNumber }}
                        </a>
                        </li>

                        <li class="page-item" :class="{ 'opacity-50 cursor-not-allowed': currentPage === totalPages }">
                        <a 
                            class="page-link inline-flex items-center px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-md hover:bg-gray-200"
                            href="#" 
                            aria-label="Next" 
                            @click.prevent="changePage(currentPage + 1)">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                        </li>
                    </ul>
                </nav>
            </div> -->

      <!-- <EditDrawer :show="showDrawer" :userId="selectedUserId"  @close="showDrawer=false" @updated="fetchUsers" /> -->
</template>

<style scoped>
.custom-badge {
    background-color: green;
    color: white;
    width: 86px;
    height: 23px;
    margin: 10px;
}

.form-label {
    text-align: left;
    font-size: large;
    display: flex;
}

.table {
    font-size: large;
    text-align: center;
}

.title {
    display: flex;
    /* justify-content:space-between; */
    margin-top: 20px;
    margin-left: 40px;
    margin-right: 60px;
    text-align: left;
    font-size: small;
}

.pagination {
  position: fixed;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  z-index: 1000; 
  background-color: white; 
  width: 100%;
  padding: 10px 0; 
}
</style>