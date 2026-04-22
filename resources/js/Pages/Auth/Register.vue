<template>
  <div class="min-h-screen flex items-center justify-center px-4 py-8">
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
      <div v-for="i in 6" :key="i"
        class="absolute rounded-full opacity-10 animate-pulse"
        :style="particleStyle(i)">
      </div>
    </div>

    <div class="w-full max-w-md animate-fade-in">
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl btn-primary mb-4 animate-pulse-glow">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-white">Cloud Notes</h1>
        <p class="text-indigo-300 mt-1">Start organizing your thoughts</p>
      </div>

      <div class="glass rounded-3xl p-8">
        <h2 class="text-xl font-semibold text-white mb-6">Create Account ✨</h2>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-indigo-300 mb-1.5">Full Name</label>
            <input v-model="form.name" type="text" id="name" placeholder="John Doe"
              class="input-field w-full px-4 py-3 rounded-xl text-sm" required/>
            <p v-if="errors.name" class="mt-1 text-xs text-red-400">{{ errors.name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-indigo-300 mb-1.5">Email</label>
            <input v-model="form.email" type="email" id="email" placeholder="you@example.com"
              class="input-field w-full px-4 py-3 rounded-xl text-sm" required/>
            <p v-if="errors.email" class="mt-1 text-xs text-red-400">{{ errors.email }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-indigo-300 mb-1.5">Password</label>
            <div class="relative">
              <input v-model="form.password" :type="showPass ? 'text' : 'password'" id="password"
                placeholder="Min. 8 characters" class="input-field w-full px-4 py-3 rounded-xl text-sm pr-10" required/>
              <button type="button" @click="showPass = !showPass"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-indigo-400 hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    :d="showPass ? 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21' : 'M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'"/>
                </svg>
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-indigo-300 mb-1.5">Confirm Password</label>
            <input v-model="form.password_confirmation" :type="showPass ? 'text' : 'password'" id="password_confirmation"
              placeholder="Repeat password" class="input-field w-full px-4 py-3 rounded-xl text-sm" required/>
            <p v-if="errors.password" class="mt-1 text-xs text-red-400">{{ errors.password }}</p>
          </div>

          <button type="submit" id="register-btn" :disabled="processing"
            class="btn-primary w-full py-3 rounded-xl text-white font-semibold text-sm flex items-center justify-center gap-2">
            <svg v-if="processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span>{{ processing ? 'Creating account...' : 'Create Account' }}</span>
          </button>
        </form>

        <p class="text-center text-sm text-indigo-400 mt-6">
          Already have an account?
          <a href="/login" class="text-indigo-300 font-semibold hover:text-white transition-colors">Sign in</a>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();
const errors = ref(page.props.errors || {});
const processing = ref(false);
const showPass = ref(false);

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

function particleStyle(i) {
  const sizes = [300, 200, 400, 150, 250, 350];
  const positions = [
    { top: '10%', left: '5%' }, { top: '60%', right: '10%' },
    { bottom: '20%', left: '20%' }, { top: '30%', right: '30%' },
    { bottom: '10%', right: '5%' }, { top: '70%', left: '50%' },
  ];
  const colors = ['#6366f1', '#8b5cf6', '#4f46e5', '#7c3aed', '#6d28d9', '#5b21b6'];
  return { width: sizes[i-1]+'px', height: sizes[i-1]+'px', background: colors[i-1], filter: 'blur(80px)', ...positions[i-1] };
}

function submit() {
  processing.value = true;
  router.post('/register', form, {
    onError: (e) => { errors.value = e; },
    onFinish: () => { processing.value = false; },
  });
}
</script>
