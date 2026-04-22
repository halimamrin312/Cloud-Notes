<template>
  <nav class="glass-dark sticky top-0 z-40 px-4 py-3">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
      <!-- Logo -->
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl btn-primary flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <span class="font-bold text-lg text-white">Cloud Notes</span>
      </div>

      <!-- Center Nav -->
      <div class="hidden md:flex items-center gap-1">
        <a href="/" :class="['px-4 py-2 rounded-xl text-sm font-medium transition-all',
          currentPath === '/' ? 'bg-indigo-600 text-white' : 'text-indigo-300 hover:bg-white/5 hover:text-white']">
          <span class="flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Notes
          </span>
        </a>
        <a href="/archived" :class="['px-4 py-2 rounded-xl text-sm font-medium transition-all',
          currentPath === '/archived' ? 'bg-indigo-600 text-white' : 'text-indigo-300 hover:bg-white/5 hover:text-white']">
          <span class="flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
            </svg>
            Archived
          </span>
        </a>
      </div>

      <!-- User Menu -->
      <div class="flex items-center gap-3">
        <button @click="$emit('toggle-ai')"
          id="toggle-ai-btn"
          class="hidden md:flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium text-purple-300 hover:text-white hover:bg-purple-600/20 transition-all border border-purple-500/30">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.346.346a1 1 0 01-1.414-1.414l.346-.345a3 3 0 10-4.243 0l.346.345a1 1 0 01-1.414 1.414l-.346-.346a5 5 0 010-7.072z"/>
          </svg>
          AI Assistant
        </button>

        <div class="relative" ref="dropdown">
          <button @click="menuOpen = !menuOpen" id="user-menu-btn"
            class="flex items-center gap-2 px-3 py-2 rounded-xl glass hover:bg-white/10 transition-all">
            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-400 to-purple-600 flex items-center justify-center text-white text-xs font-bold">
              {{ userInitial }}
            </div>
            <span class="text-white text-sm hidden sm:block">{{ user.name }}</span>
            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>

          <Transition enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95"
            enter-active-class="transition transform duration-100" leave-active-class="transition transform duration-100">
            <div v-if="menuOpen" class="absolute right-0 mt-2 w-48 glass-dark rounded-2xl shadow-xl overflow-hidden">
              <div class="px-4 py-3 border-b border-white/10">
                <p class="text-xs text-indigo-400">Signed in as</p>
                <p class="text-sm font-medium text-white truncate">{{ user.email }}</p>
              </div>
              <div class="md:hidden">
                <a href="/" class="flex items-center gap-2 px-4 py-2.5 text-sm text-indigo-300 hover:bg-white/5 hover:text-white">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                  Notes
                </a>
                <a href="/archived" class="flex items-center gap-2 px-4 py-2.5 text-sm text-indigo-300 hover:bg-white/5 hover:text-white">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                  </svg>
                  Archived
                </a>
                <button @click="$emit('toggle-ai')" class="w-full text-left flex items-center gap-2 px-4 py-2.5 text-sm text-purple-300 hover:bg-white/5 hover:text-white">
                  AI Assistant
                </button>
              </div>
              <button @click="logout" id="logout-btn"
                class="w-full text-left flex items-center gap-2 px-4 py-2.5 text-sm text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Sign Out
              </button>
            </div>
          </Transition>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

defineEmits(['toggle-ai']);

const page = usePage();
const user = computed(() => page.props.auth.user);
const userInitial = computed(() => user.value?.name?.charAt(0).toUpperCase() || 'U');
const currentPath = computed(() => window.location.pathname);

const menuOpen = ref(false);
const dropdown = ref(null);

function logout() {
  router.post('/logout');
}

function handleClickOutside(e) {
  if (dropdown.value && !dropdown.value.contains(e.target)) {
    menuOpen.value = false;
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside));
</script>
