<template>
  <div class="min-h-screen">
    <Navbar @toggle-ai="aiOpen = !aiOpen" />
    <AiChat :show="aiOpen" @close="aiOpen = false" />

    <!-- Flash messages -->
    <Transition enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0"
      leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2"
      enter-active-class="transition-all duration-300" leave-active-class="transition-all duration-200">
      <div v-if="flash.success" class="fixed top-20 right-4 z-50 max-w-sm">
        <div class="glass px-5 py-3 rounded-2xl flex items-center gap-3 border border-green-500/30 bg-green-500/10">
          <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          <p class="text-sm text-green-300">{{ flash.success }}</p>
        </div>
      </div>
    </Transition>

    <main class="max-w-7xl mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
          <h1 class="text-2xl font-bold text-white">My Notes</h1>
          <p class="text-indigo-400 text-sm mt-0.5">{{ notes.length }} note{{ notes.length !== 1 ? 's' : '' }}</p>
        </div>
        <button @click="openNew" id="new-note-btn"
          class="btn-primary px-5 py-2.5 rounded-xl text-white font-semibold text-sm flex items-center gap-2 self-start">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          New Note
        </button>
      </div>

      <!-- Search & Filters -->
      <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <div class="relative flex-1">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input v-model="searchInput" @input="debouncedSearch" type="text" id="search-notes"
            placeholder="Search notes..." class="input-field w-full pl-10 pr-4 py-2.5 rounded-xl text-sm"/>
        </div>

        <select v-if="categories.length" v-model="selectedCategory" @change="filterCategory"
          id="category-filter" class="input-field px-4 py-2.5 rounded-xl text-sm min-w-[160px]">
          <option value="">All Categories</option>
          <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
        </select>

        <!-- View toggle -->
        <div class="flex glass rounded-xl p-1 gap-1">
          <button @click="gridView = true" id="grid-view-btn"
            :class="['p-2 rounded-lg transition-colors', gridView ? 'bg-indigo-600 text-white' : 'text-indigo-400 hover:text-white']">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
          </button>
          <button @click="gridView = false" id="list-view-btn"
            :class="['p-2 rounded-lg transition-colors', !gridView ? 'bg-indigo-600 text-white' : 'text-indigo-400 hover:text-white']">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Pinned Notes -->
      <div v-if="pinnedNotes.length" class="mb-6">
        <div class="flex items-center gap-2 mb-3">
          <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/>
          </svg>
          <p class="text-xs font-semibold text-yellow-400/80 uppercase tracking-wider">Pinned</p>
        </div>
        <div :class="gridView ? 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4' : 'space-y-3'">
          <NoteCard v-for="note in pinnedNotes" :key="note.id" :note="note"
            @edit="openEdit" @pin="togglePin" @archive="toggleArchive"/>
        </div>
      </div>

      <!-- Other Notes -->
      <div v-if="unpinnedNotes.length">
        <p v-if="pinnedNotes.length" class="text-xs font-semibold text-indigo-500/80 uppercase tracking-wider mb-3">Others</p>
        <div :class="gridView ? 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4' : 'space-y-3'">
          <NoteCard v-for="note in unpinnedNotes" :key="note.id" :note="note"
            @edit="openEdit" @pin="togglePin" @archive="toggleArchive"/>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!notes.length" class="text-center py-24">
        <div class="w-24 h-24 rounded-3xl glass flex items-center justify-center mx-auto mb-6">
          <svg class="w-12 h-12 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <h2 class="text-xl font-bold text-white mb-2">{{ filters?.search ? 'No notes found' : 'No notes yet' }}</h2>
        <p class="text-indigo-400 text-sm mb-6">{{ filters?.search ? 'Try a different search term' : 'Create your first note to get started!' }}</p>
        <button v-if="!filters?.search" @click="openNew" id="empty-new-note-btn"
          class="btn-primary px-6 py-3 rounded-xl text-white font-semibold text-sm inline-flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Create Note
        </button>
      </div>
    </main>

    <!-- Note Modal -->
    <NoteModal :show="modalOpen" :note="editingNote" @close="closeModal" />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import AiChat from '@/Components/AiChat.vue';
import NoteCard from '@/Components/NoteCard.vue';
import NoteModal from '@/Components/NoteModal.vue';

const props = defineProps({
  notes: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const flash = computed(() => page.props.flash || {});
const aiOpen = ref(false);
const modalOpen = ref(false);
const editingNote = ref(null);
const gridView = ref(true);
const searchInput = ref(props.filters?.search || '');
const selectedCategory = ref(props.filters?.category || '');

const pinnedNotes = computed(() => props.notes.filter(n => n.is_pinned));
const unpinnedNotes = computed(() => props.notes.filter(n => !n.is_pinned));

let searchTimer;
function debouncedSearch() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    router.get('/', { search: searchInput.value, category: selectedCategory.value }, { preserveState: true, replace: true });
  }, 400);
}

function filterCategory() {
  router.get('/', { search: searchInput.value, category: selectedCategory.value }, { preserveState: true, replace: true });
}

function openNew() { editingNote.value = null; modalOpen.value = true; }
function openEdit(note) { editingNote.value = note; modalOpen.value = true; }
function closeModal() { modalOpen.value = false; editingNote.value = null; }

function togglePin(note) { router.post(`/notes/${note.id}/pin`); }
function toggleArchive(note) { router.post(`/notes/${note.id}/archive`); }

// Auto-hide flash
let flashTimer;
watch(() => flash.value.success, (val) => {
  if (val) {
    clearTimeout(flashTimer);
    flashTimer = setTimeout(() => {}, 3000);
  }
});
</script>
