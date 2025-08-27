<script setup>
import { onBeforeMount, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { useRoute, useRouter } from 'vue-router';
import i18n from '../../i18n';
import store, { useStore } from 'vuex';
import * as yup from 'yup';
import { useI18n } from 'vue-i18n';
import { useField, useForm } from 'vee-validate';
import { toast } from 'vue3-toastify';

const { t } = useI18n();
const emit = defineEmits(['update:formsU', 'close', 'created']);
const router = useRouter();
const route  = useRoute();
const token = localStorage.getItem('auth_token');

const props = defineProps({
    userId: {
        type: [String, Number],
        required: false,
        default: null
    },
    showEdit: {
        type: Boolean,
        required: false,
        default: false
    }
});

const isEdit = ref(props.showEdit);

const roles = ref([]);
const user = ref({
    name: '',
    email: '',
    user_role: '',
    password: '',
    password_confirmation: '',
    id: ''
});

const errors = ref({});

// Toast helpers
const showSuccessToast = (message) => {
    toast.success(message, {
        position: "top-right",
        autoClose: 3000,
        hideProgressBar: false,
        closeOnClick: true,
        pauseOnHover: true,
        draggable: true,
    });
};

const showErrorToast = (message) => {
    toast.error(message, {
        position: "top-right",
        autoClose: 3000,
    });
};

// Toggle password visibility
const showPassword = ref(false);
const showPasswordConfirm = ref(false);

// Cancel
function cancel() {
    emit('close');
}

// Vee-validate form
useForm({ validateOnInput: true });

// Fields
const { value: name, errorMessage: nameError } = useField('name', yup.string().required(t('user_manage.errors.name')));
const { value: email, errorMessage: emailError } = useField('email', yup.string().required(t('user_manage.errors.email')));
const { value: user_role, errorMessage: user_roleError } = useField('user_role', yup.string().required(t('user_manage.errors.user_role')));

const { value: password, errorMessage: passwordError } = useField(
  'password',
  yup.string()
    .nullable()
    .test('password-required', t('user_manage.errors.password'), function(value) {
        // If adding a user, password is required
        if (!isEdit.value && (!value || value.length === 0)) return false;
        // If provided, must be at least 8 characters
        if (value && value.length > 0 && value.length < 8) return this.createError({ message: t('user_manage.password.min') });
        return true;
    })
);

const { value: password_confirmation, errorMessage: passwordConfirmError } = useField(
  'password_confirmation',
  yup.string()
    .nullable()
    .test('password-confirm-match', t('user_manage.password.match'), function(value) {
        // Only check confirmation if password is filled
        if (password.value && password.value.length > 0) {
            return value === password.value;
        }
        return true;
    })
);


// Sync form fields with user object
watch(name, val => user.value.name = val);
watch(email, val => user.value.email = val);
watch(user_role, val => user.value.user_role = val);
watch(password, val => user.value.password = val);
watch(password_confirmation, val => user.value.password_confirmation = val);

// Fetch Roles
const fetchRoles = async () => {
    try {
        const response = await axios.get('api/roles', {
            headers: {
                'Authorization': `Bearer ${token}`,
                "Accept-Language": i18n.global.locale.value
            }
        });
        roles.value = response.data.roles;
    } catch (error) {
        console.error("Error fetching roles", error);
    }
};

// Fetch user details for edit
const fetchUser = async () => {
    if (!isEdit.value) return;
    try {
        const response = await axios.get(`/api/user/edit/${props.userId}`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                "Accept-Language": i18n.global.locale.value
            }
        });

        user.value = response.data.user;
        name.value = response.data.user.name;
        email.value = response.data.user.email;
        user_role.value = response.data.user.user_role[0] || '';
        roles.value = response.data.roles || [];
        password.value = null;
        password_confirmation.value = null;
    } catch (error) {
        console.error("Retrieve User details error", error);
    }
};

// Submit form (Add or Edit)
const submitForm = async () => {
    errors.value = {};
    try {
        let payload = {
            name: name.value,
            email: email.value,
            role: user_role.value
        };
        if(password.value) {
            payload.password = password.value;
            payload.password_confirmation = password_confirmation.value;
        }

        let response;
        if(isEdit.value) {
            response = await axios.put(`/api/user/update/${user.value.id}`, payload, {
                headers: { 'Authorization': `Bearer ${token}`, "Accept-Language": i18n.global.locale.value }
            });
        } else {
            response = await axios.post('api/user/create', payload, {
                headers: { 'Authorization': `Bearer ${token}`, "Accept-Language": i18n.global.locale.value }
            });
        }

        if(response.data.status === 'success') {
            emit('close');
            emit('updated');
            showSuccessToast(isEdit.value ? t('user_edit.success.title') : t('user_add.success.title'));
        }else if(response.data.status === 'error') {
            errors.value = response.data.errors || {};
            showErrorToast(t('fix_errors'));
        }
    } catch (error) {
        if(error.response && error.response.status === 422) {
            errors.value = error.response.data.error || {};
            showErrorToast(t('fix_errors'));
        } else {
            console.error("Submit failed", error);
        }
    }
};

onBeforeMount(async() => {
    if (props.userId) {
        await fetchUser();
    }
})

// Mounted
onMounted(async () => {
    await fetchRoles();
    await fetchUser();
});
</script>

<template>
<div class="w-full">
    <div class="bg-white p-6 rounded-lg">
        <div class="flex justify-between">
            <h2 class="text-2xl font-semibold text-gray-800">
                {{ isEdit ? $t('user_manage.edit_title') : $t('user_manage.create_title') }}
            </h2>
            <button type="button" class="inline-flex justify-center rounded-md bg-red-600 px-2 py-2 text-sm font-semibold text-gray-100 hover:bg-red-800 sm:mt-0 sm:w-auto" @click="cancel">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form @submit.prevent="submitForm">
            <div class="mb-4">
                <label for="name" class="block text-lg font-medium text-gray-700 text-left">{{ $t('user_manage.form.name_label') }} <span class="text-red-500">*</span> </label>
                <input v-model="name" type="text" :placeholder="$t('user_manage.form.name_placeholder')" class="mt-2 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm" />
                <span v-if="errors.name" class="text-red-500 text-sm mt-2">{{ errors.name }}</span>
                <span v-else-if="nameError" class="text-red-500 text-sm mt-2">{{ nameError }}</span>
            </div>

            <div class="mb-4">
                <label for="email" class="block text-lg font-medium text-gray-700 text-left">{{ $t('user_manage.form.email_label') }} <span class="text-red-500">*</span></label>
                <input v-model="email" type="email" :placeholder="$t('user_manage.form.email_placeholder')" class="mt-2 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm" />
                <span v-if="errors.email" class="text-red-500 text-sm mt-2">{{ errors.email }}</span>
                <span v-else-if="emailError" class="text-red-500 text-sm mt-2">{{ emailError }}</span>
            </div>

            <div class="mb-4">
                <label for="role" class="block text-lg font-medium text-gray-700 text-left">{{ $t('user_manage.form.role_label') }} <span class="text-red-500">*</span></label>
                <select v-model="user_role" class="mt-2 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm" id="role">
                    <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
                </select>
                <span v-if="errors.user_role" class="text-red-500 text-sm mt-2">{{ errors.user_role }}</span>
                <span v-else-if="user_roleError" class="text-red-500 text-sm mt-2">{{ user_roleError }}</span>
            </div>

            <div class="mb-4 relative">
                <label class="block text-lg font-medium text-gray-700 text-left">
                    {{ $t('password') }}
                    <span class="text-red-500" v-if="!isEdit">*</span>
                    <small class="text-gray-500" v-if="isEdit">({{ $t('password_hint_edit') }})</small>
                </label>
                <input :type="showPassword ? 'text' : 'password'" v-model="password" placeholder="Enter password" class="mt-2 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm" />
                <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-11 text-gray-500">{{ showPassword ? 'Hide' : 'Show' }}</button>
                <span v-if="errors.password" class="text-red-500 text-sm mt-2">{{ errors.password }}</span>
                <span v-else-if="passwordError" class="text-red-500 text-sm mt-2">{{ passwordError }}</span>
            </div>

            <div class="mb-4 relative">
                <label class="block text-lg font-medium text-gray-700 text-left">
                    {{ $t('confirm_password') }}
                    <span class="text-red-500" v-if="!isEdit">*</span>
                    <small class="text-gray-500" v-if="isEdit">({{ $t('confirm_password_hint_edit') }})</small>
                </label>
                <input :type="showPasswordConfirm ? 'text' : 'password'" v-model="password_confirmation" placeholder="Confirm password" class="mt-2 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm" />
                <button type="button" @click="showPasswordConfirm = !showPasswordConfirm" class="absolute right-3 top-11 text-gray-500">{{ showPasswordConfirm ? 'Hide' : 'Show' }}</button>
                <span v-if="errors.password_confirmation" class="text-red-500 text-sm mt-2">{{ errors.password_confirmation }}</span>
                <span v-else-if="passwordConfirmError" class="text-red-500 text-sm mt-2">{{ passwordConfirmError }}</span>
            </div>

            <div class="mt-6">
                <button type="submit" class="w-full py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 text-sm">
                    {{ isEdit ? $t('user_manage.form.update_button') : $t('user_manage.form.submit_button') }}
                </button>
            </div>
        </form>
    </div>
</div>
</template>
