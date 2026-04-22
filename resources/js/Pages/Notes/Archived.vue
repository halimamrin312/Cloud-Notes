<template>
  <div class="min-h-screen">
    <Navbar @toggle-ai="aiOpen = !aiOpen" />
    <AiChat :show="aiOpen" @close="aiOpen = false" />

    <main class="max-w-7xl mx-auto px-4 py-8">
      <div class="flex items-center gap-4 mb-8">
        <a href="/" class="w-10 h-10 rounded-xl glass flex items-center justify-center text-indigo-400 hover:text-white transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </a>
        <div>
          <h1 class="text-2xl font-bold text-white">Archived Notes</h1>
          <p class="text-indigo-400 text-sm mt-0.5">{{ notes.length }} archived note{{ notes.length !== 1 ? 's' : '' }}</p>
        </div>
      </div>

      <div v-if="notes.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <div v-for="note in notes" :key="note.id"
          class="note-card glass rounded-2xl p-5 group relative"
          :style="{ background: `linear-gradient(135deg, ${note.color || '#1e293b'} 0%, ${note.color || '#1e293b'}dd 100%)` }">
          <div v-if="note.category" class="mb-2">
            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-white/10 text-white/60">{{ note.category }}</span>
          </div>
          <h3 class="font-semibold text-white text-base mb-2 line-clamp-2">{{ note.title }}</h3>
          <p v-if="note.content" class="text-sm text-white/60 line-clamp-3 leading-relaxed">{{ note.content }}</p>
          <div v-if="note.tags?.length" class="flex flex-wrap gap-1 mt-3">
            <span v-for="tag in note.tags.slice(0,3)" :key="tag" class="tag-chip">#{{ tag }}</span>
          </div>
          <div class="flex items-center justify-between mt-4">
            <span class="text-xs text-white/40">{{ new Date(note.updated_at).toLocaleDateString() }}</span>
            <div class="flex gap-1 opacity-60 group-hover:opacity-100 transition-opacity">
              <button @click="unarchive(note)" :id="`unarchive-${note.id}`"
                class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs text-blue-300 hover:text-white bg-blue-500/10 hover:bg-blue-500/20 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Restore
              </button>
              <button @click="deleteNote(note)" :id="`delete-archived-${note.id}`"
                class="w-7 h-7 rounded-lg flex items-center justify-center text-white/40 hover:text-red-400 hover:bg-red-400/10 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-24">
        <div class="w-24 h-24 rounded-3xl glass flex items-center justify-center mx-auto mb-6">
          <svg class="w-12 h-12 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
          </svg>
        </div>
        <h2 class="text-xl font-bold text-white mb-2">No archived notes</h2>
        <p class="text-indigo-400 text-sm">Archived notes will appear here.</p>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import AiChat from '@/Components/AiChat.vue';

defineProps({ notes: { type: Array, default: () => [] } });

const aiOpen = ref(false);

function unarchive(note) { router.post(`/notes/${note.id}/archive`); }
function deleteNote(note) {
  if (confirm('Permanently delete this note?')) router.delete(`/notes/${note.id}`);
}
</script>
