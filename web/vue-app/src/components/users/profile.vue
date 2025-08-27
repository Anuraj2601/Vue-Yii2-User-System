<script setup>
import { onMounted, ref, watch } from 'vue';
import { TransitionRoot, TransitionChild } from '@headlessui/vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import i18n from '../../i18n';
import { useField, useForm } from 'vee-validate';
import { useI18n } from 'vue-i18n';
import * as yup from 'yup';
import store from '@/store';
const props = defineProps({
  show: Boolean,
});
const {t} = useI18n();
const userId = localStorage.getItem('user_id');
const token = localStorage.getItem('auth_token');
const emit = defineEmits(['close', 'save', 'updated']);
const router = useRouter();

const defaultImage = 'storage/profile_images/sample.jpg';

const imagePreview = ref(null);
const selectedFile = ref(null);

function handleFileChange(event) {
  const file = event.target.files[0];
  if (file) {
    selectedFile.value = file; 
    imagePreview.value = URL.createObjectURL(file);
    image.value = selectedFile.value;
    imageError.value = '';
  }
}

function removeProfileImage() {
  selectedFile.value = null;
  imagePreview.value = null;
  image.value = '';
  /* errors.value.image = '';
  imageError.value = ''; */
}

function cancel () {
  emit('close');
  emit('updated');
}

const user = ref({
  name: '',
  email: '',
  role: '',
  image: ''
});

 const errors = ref({
        name: '',
        email: '',
        role: '',
        image: ''
    });

function resetForm() {
  user.value = {
    name: '',
    email: '',
    role: '',
    image: ''
  };
  imagePreview.value = null;
  selectedFile.value = null;
  errors.value = {
    name: '',
    email: '',
    role: '',
    image: ''
  };
}

useForm({
    validateOnInput: true,
});

const {
  value: image,
  errorMessage: imageError
} = useField('image', yup.string().required(t('profile_edit.errors.image')))

const {
  value: name,
  errorMessage: nameError
} = useField('name', yup.string().required(t('profile_edit.errors.name')))

const {
  value: email,
  errorMessage: emailError
} = useField('email', yup.string().required(t('profile_edit.errors.email')))

const {
  value: role,
  errorMessage: roleError
} = useField('role', yup.string().required(t('profile_edit.errors.role')))


watch(name, (newVal) => {
  user.name = newVal;
});

watch(email, (newVal) => {
  user.email = newVal;
});

watch(role, (newVal) => {
  user.role = newVal;
});

const getProfileInfo = async () => {
    try {
        const response = await axios.get(`api/user/profile` , {
        headers: {
            'Authorization': `Bearer ${token}`,
            "Accept-Language": i18n.global.locale.value
        }
    });
    console.log(response.data);
    user.value = response.data.user;
    name.value = response.data.user.name;
    email.value = response.data.user.email;
    role.value = response.data.user.role[0];
    image.value = `http://localhost:8888/${response.data.user.image.replace(/^\/+/, '')}`;
    //user.value.role = response.data.user.role[0];
    if (!image.value) {
        image.value = `http://localhost:8888/images/profile/sample.jpg`;
    }
    console.log(response.data);
    } catch (error) {
        console.error('Unable to get profile details', error);
    }
}

const updateProfileInfo = async () => {
  try {
    const formData = new FormData();
    formData.append('name', name.value);
    formData.append('email', email.value);
    formData.append('role', role.value);

    if (selectedFile.value) {
        formData.append('image', selectedFile.value);
    } else {
        formData.append('image', image.value);
    }

    console.log(formData);
    const response = await axios.post('/api/user/update', formData, {
      headers: {
        'Authorization': `Bearer ${token}`,
        "Accept-Language": i18n.global.locale.value,
        'Content-Type': 'multipart/form-data',
      }
    });
    console.log(response.data);

    if (response.data.status === 'success') {
      emit('close');
      emit('updated');
      getProfileInfo();
      router.push('dashboard');
      store.commit('auth/SET_USER', { ...store.state.auth.user, name: name.value, image: image.value });
        store.commit('auth/SET_NAME', name.value);
      console.log(response.data);
    }
  } catch (error) {
            if(error.response) {
                if (error.response && error.response.status === 422) {  
                errors.value = error.response.data.error; 
                console.error('Validation errors:', errors.value);
                }
            }
            console.log("User Updated failed. " , error);
        }
};



onMounted(getProfileInfo);

watch(() => props.show, async (newVal) => {
  if (newVal) {
    resetForm();
    await getProfileInfo();
  }
});
</script>

<template>
  <TransitionRoot :show="show" as="template">
    <div class="fixed inset-0 z-50 overflow-y-auto">
      <!-- Background Overlay -->
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-500/75 transition-opacity" @click="cancel" @updated="getProfileInfo" />
      </TransitionChild>

      <!-- Modal Dialog -->
      <div class="flex items-center justify-center min-h-full p-4 text-center">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="ease-in duration-200"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <div
            class="relative w-full max-w-md transform overflow-hidden rounded-lg bg-white p-6 text-left shadow-xl transition-all"
          >
            <!-- Close Button -->
            <button @click="cancel" @updated="getProfileInfo" class="absolute top-2 ml-95 text-gray-500 hover:text-gray-700">
              ✕
            </button>

            <h2 class="text-xl font-semibold text-gray-800 text-center mb-4">{{ $t('profile_edit.title') }}</h2>

            <form @submit.prevent="saveChanges" class="space-y-4">
              <!-- <div class="flex justify-center">
                <img
                  :src="defaultImage"
                  alt="Profile"
                  class="w-24 h-24 object-cover rounded-full border border-gray-300"
                />
              </div> -->
               <div class="col-span-full">
                <div class="mt-4 flex flex-col items-center text-sm text-gray-600">
                <label for="file-upload" class="cursor-pointer relative group">
                    <img
                    :src="imagePreview || (image)"
                    alt="Profile Preview"
                    class="w-24 h-24 rounded-full object-cover border border-gray-300 hover:opacity-80 transition"
                    />
                    <input
                    id="file-upload"
                    name="file-upload"
                    type="file"
                    class="hidden"
                    accept="image/*"
                    @change="handleFileChange"
                    />
                    <button
                      type="button"
                      @click="removeProfileImage"
                      class="absolute bottom-0 right-0 bg-white border border-gray-300 rounded-full p-1 hover:bg-gray-100 shadow-md"
                    >
                      <!-- Heroicon X Mark -->
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                </label>

                <div class="flex space-x-3 mt-2">
                  <span class="text-indigo-600 hover:text-indigo-500 font-semibold cursor-pointer">
                    {{ $t('profile_edit.upload_photo') }}
                  </span>
                </div>

                <span v-if="errors.image" class="text-red-500 text-sm mt-2">
                            {{ errors.image }}
                    </span>
                     <span v-else-if="imageError" class="text-red-500 text-sm mt-2">
                            {{ imageError }}
                    </span>
                <!-- <span class="mt-2 text-indigo-600 hover:text-indigo-500 font-semibold cursor-pointer">
                    {{ $t('profile_edit.upload_photo') }}
                </span> -->
                </div>
            </div>

              <div>
                <label class="block text-lg font-medium text-gray-700">{{ $t('profile_edit.labels.name') }}</label>
                <input
                  v-model="name"
                  type="text"
                  class="mt-2 block w-full p-3 rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                />
                <span v-if="errors.name" class="text-red-500 text-sm mt-2">
                            {{ errors.name }}
                    </span>
                <span v-else-if="nameError" class="text-red-500 text-sm mt-2">
                            {{ nameError }}
                    </span>
              </div>

              <div>
                <label class="block text-lg font-medium text-gray-700">{{ $t('profile_edit.labels.email') }}</label>
                <input
                  v-model="email"
                  type="email"
                  class="mt-2 p-3 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                />
                <span v-if="errors.email" class="text-red-500 text-sm mt-2">
                            {{ errors.email }}
                </span>
                <span v-else-if="emailError" class="text-red-500 text-sm mt-2">
                            {{ emailError }}
                </span>
              </div>

              <div>
                <label class="block text-lg font-medium text-gray-700">{{ $t('profile_edit.labels.role') }}</label>
                <input
                  v-model="role"
                  type="text"
                  class="mt-2 p-3 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  disabled
                />
                <span v-if="errors.role" class="text-red-500 text-sm mt-2">
                            {{ errors.role }}
                </span>
                <span v-else-if="roleError" class="text-red-500 text-sm mt-2">
                            {{ roleError }}
                </span>
              </div>

              <div class="mt-6 flex justify-end space-x-3">
                <button
                  type="button"
                  @click="cancel"
                  class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 text-sm"
                >
                  {{ $t('profile_edit.buttons.cancel') }}
                </button>
                <button
                  @click="updateProfileInfo"
                  type="submit"
                  class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm"
                >
                  {{ $t('profile_edit.buttons.save') }}
                </button>
              </div>
            </form>
          </div>
        </TransitionChild>
      </div>
    </div>
  </TransitionRoot>
</template>

