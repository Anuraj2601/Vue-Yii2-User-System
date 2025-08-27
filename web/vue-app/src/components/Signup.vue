<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useForm, useField } from 'vee-validate';
import * as yup from 'yup';

const router = useRouter();
const errorl = ref('');
const showError = ref(false);
const errors = ref({});

// Schema validation
const schema = yup.object({
  name: yup.string().required('Name field is required'),
  email: yup.string().email('Enter a valid email').required('Email field is required'),
  password: yup.string().required('Password field is required').min(8, 'Password must be at least 8 characters'),
  password_confirmation: yup
    .string()
    .oneOf([yup.ref('password')], 'Passwords must match')
    .required('Password confirmation is required'),
});

const { handleSubmit, setErrors } = useForm({
  validationSchema: schema,
});

// Fields
const { value: name, errorMessage: nameError } = useField('name');
const { value: email, errorMessage: emailError } = useField('email');
const { value: password, errorMessage: passwordError } = useField('password');
const { value: password_confirmation, errorMessage: passwordConfirmationError } = useField('password_confirmation');

// Register function
const registerUser = async () => {
  errorl.value = '';
  const formData = new URLSearchParams();
    formData.append('name', name.value);
    formData.append('email', email.value);
    formData.append('password', password.value);
  try {
    const response = await axios.post('/api/register',
      formData
    );
    if (response.data.status === 'success') {
      router.push('/login');
    }
    else if (response.data.status === 'error') {
        errors.value = response.data.errors;
    }
  } catch (error) {
    if (error.response) {
        if (err.response.data.errors) {
            errors.value = err.response.data.errors; // assign errors
            console.log(errors.value);
        }
      } else {
        errorl.value = error.response.data.msg || 'Unexpected error';
        showError.value = true;
      }
  }
};

const onSubmit = handleSubmit(registerUser);
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
        <h2 class="text-3xl font-bold text-white text-center mb-6">Create an Account</h2>
        
        <form @submit.prevent="onSubmit" class="space-y-5">

          <!-- Name -->
          <div>
            <label for="name" class="block text-sm text-left font-medium text-gray-200">Name</label>
            <input 
              v-model="name" 
              type="text" 
              id="name"
              class="w-full mt-2 px-4 py-3 rounded-lg bg-gray-900/70 border border-gray-600 text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-gray-500 focus:outline-none text-sm"
              placeholder="Your name"
            />
            <span class="block text-red-400 text-sm text-left">{{ nameError }}</span>
            <span class="block text-red-400 text-sm text-left">{{ errors.name }}</span>
          </div>

          <!-- Email -->
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
            <span class="block text-red-400 text-sm text-left">{{ errors.email }}</span>
          </div>

          <!-- Password -->
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
            <span class="block text-red-400 text-sm text-left">{{ errors.password }}</span>
          </div>

          <!-- Password Confirmation -->
          <div>
            <label for="password_confirmation" class="block text-sm text-left font-medium text-gray-200">Confirm Password</label>
            <input 
              v-model="password_confirmation" 
              type="password" 
              id="password_confirmation"
              class="w-full mt-2 px-4 py-3 rounded-lg bg-gray-900/70 border border-gray-600 text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-gray-500 focus:outline-none text-sm"
              placeholder="••••••••"
            />
            <span class="block text-red-400 text-sm text-left">{{ passwordConfirmationError }}</span>
            
          </div>

          <!-- Submit -->
          <button 
            type="submit" 
            class="w-full py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium text-sm rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-gray-500 transition"
          >
            Register
          </button>

          <!-- Already have an account -->
          <p class="text-sm text-center text-gray-300">
            Already have an account?
            <router-link to="/login" class="text-white hover:text-white font-medium">Login</router-link>
          </p>
        </form>
      </div>
    </div>
  </section>
</template>
