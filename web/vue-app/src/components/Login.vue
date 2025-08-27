<script setup>
    import { ref } from 'vue';
    import axios from 'axios';
    import { useRouter } from 'vue-router';
    import { useForm, useField, ErrorMessage } from 'vee-validate'
    import * as yup from 'yup'
    import { toast } from 'vue3-toastify';
    import 'vue3-toastify/dist/index.css';
import store from '@/store';

    const router = useRouter();
    const errorl = ref('');
    const showError = ref(false);
    const modalTitle = ref('');
    const modalMessage = ref('');
    const modalRef = ref(null);

    const showModal = (title, message) => {
        modalTitle.value = title;
        modalMessage.value = message;
        modalRef.value?.show();
    };

    const schema = yup.object({
      email: yup.string().email('Enter a valid email').required('Email field is required'),
      password: yup.string().required('Password field is required').min(8, 'Password must be at least 8 characters'),
    })

    const { handleSubmit, setErrors } = useForm({
      validationSchema: schema
    })

    const { value: email, errorMessage: emailError } = useField('email')
    const { value: password, errorMessage: passwordError } = useField('password')

    const loginUser = async () => {
        /* errors.value = {}; */
        errorl.value = '';
        const formData = new URLSearchParams();
        formData.append('email', email.value);
        formData.append('password', password.value);
        try {
            const response = await axios.post('/api/login', formData);
            if(response.data.status == 'success') {
                /* localStorage.setItem('auth_token', response.data.token); */
                
                /* store.commit('auth/SET_LANGUAGE', userLang); */
                localStorage.setItem('auth_token', response.data.access_token);
                store.commit('auth/SET_TOKEN', response.data.access_token);
                store.commit('auth/SET_ROLES', Array.isArray(response.data.user_role) ? response.data.user_role : [response.data.user_role]);
                store.commit('auth/SET_PERMISSIONS', Array.isArray(response.data.user_permissions) ? response.data.user_permissions : [response.data.user_permissions]);
                store.commit('auth/SET_USER', { name: response.data.user_name });
                store.dispatch("auth/setPermissions", response.data.user_permissions);
                router.push('/dashboard');
                setTimeout(() => {
                  toast.success('Login successful 🎉', {
                    position: "top-right",
                    autoClose: 3000,
                 });
                }, 1500);
            }
            else if(response.data.status == 'error') {
                errorl.value = response.data.msg;
                showError.value = true;
                toast.error('Incorrect Email or Password ⚠️', {
                  position: "top-right",
                  autoClose: 4000,
                });
            }
            console.log(response.data);
        } catch (error) {
            if(error.response) {
                if(error.response.status === 422) {
                    const backendErrors = error.response.data.errors;
                    setErrors(backendErrors);
                } else if (error.response.status === 401) {
                    errorl.value = error.response.data.msg;
                    showError.value = true;
                    
                }  else {
                    errorl.value = error.response.data.msg;
                      toast.error('Incorrect Email or Password ⚠️', {
                      position: "top-right",
                      autoClose: 4000,
                    });
                    console.error('test', errorl);
                    console.error('Unexpected error: ', error.response.data.msg);
                }
            }
        }
    }
    const onSubmit = handleSubmit(loginUser)
</script>

<template>
  <section class="h-screen flex items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-black">
    <div class="flex flex-col md:flex-row items-center justify-center w-full max-w-5xl px-6">
      
      <!-- Image Section -->
      <div class="hidden md:block md:w-1/2 lg:w-2/5">
        <img 
          src="https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-login-form/draw2.webp"
          alt="Sample image"
          class="w-full h-auto rounded-2xl shadow-2xl"
        />
      </div>

      <!-- Form Section -->
      <div class="w-full md:w-1/2 lg:w-2/5 backdrop-blur-sm p-8 rounded-2xl shadow-2xl border border-white/20">
        <h2 class="text-3xl font-bold text-white text-center mb-6">Welcome Back</h2>
        
        <form @submit.prevent="onSubmit" class="space-y-5">
          
          <!-- Error Modal -->
          <!-- <ErrorModal 
            :show="showError"
            title="Error"
            message="Incorrect Username or Password"
            @close="showError=false"
          /> -->

          <!-- Email input -->
          <div>
            <label for="email" class="block text-sm text-left font-medium text-gray-200">Email Address</label>
            <input 
              v-model="email" 
              type="email" 
              id="email"
              class="w-full mt-2 px-4 py-3 rounded-lg bg-gray-900/70 border border-gray-600 text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-gray-500 focus:outline-none text-sm"
              placeholder="example@email.com"
            />
            <span class="block text-red-400 text-sm text-left">{{ emailError }}</span>
          </div>

          <!-- Password input -->
          <div>
            <label for="password" class="block text-sm text-left font-medium text-gray-200">Password</label>
            <input 
              v-model="password" 
              type="password" 
              id="password"
              class="w-full mt-2 px-4 py-3 rounded-lg bg-gray-900/70 border border-gray-600 text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-gray-500 focus:outline-none text-sm"
              placeholder="••••••••"
            />
            <span class="block text-red-400 text-sm text-left">{{ passwordError }}</span>
          </div>

          <!-- Forgot password -->
          <div class="flex justify-end">
            <router-link to="/forgot-password" class="text-sm text-gray-400 hover:text-gray-500">
              Forgot Password?
            </router-link>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            class="w-full py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium text-sm rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-gray-500 transition"
          >
            Login
          </button>

          <!-- Register link -->
          <p class="text-sm text-center text-gray-300">
            Don't have an account?
            <router-link to="/signup" class="text-white hover:text-white font-medium">Register</router-link>
          </p>

        </form>
      </div>
    </div>
  </section>
</template>


