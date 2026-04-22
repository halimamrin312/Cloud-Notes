<template>
  <div class="note-card rounded-2xl overflow-hidden cursor-pointer group relative"
    :style="{ background: cardBackground }"
    @click="$emit('edit', note)">

    <!-- Pin indicator -->
    <div v-if="note.is_pinned" class="absolute top-3 right-3 z-10">
      <div class="w-6 h-6 rounded-full bg-yellow-400/20 flex items-center justify-center">
        <svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
          <path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/>
        </svg>
      </div>
    </div>

    <!-- Content -->
    <div class="p-5">
      <!-- Category -->
      <div v-if="note.category" class="mb-2">
        <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-white/10 text-white/60">
          {{ note.category }}
        </span>
      </div>

      <h3 class="font-semibold text-white text-base leading-snug mb-2 line-clamp-2">
        {{ note.title }}
      </h3>

      <p v-if="note.content" class="text-sm text-white/60 line-clamp-4 leading-relaxed">
        {{ note.content }}
      </p>

      <!-- Tags -->
      <div v-if="note.tags && note.tags.length" class="flex flex-wrap gap-1 mt-3">
        <span v-for="tag in note.tags.slice(0, 3)" :key="tag" class="tag-chip">#{{ tag }}</span>
        <span v-if="note.tags.length > 3" class="tag-chip">+{{ note.tags.length - 3 }}</span>
      </div>
    </div>

    <!-- Footer -->
    <div class="px-5 pb-4 flex items-center justify-between">
      <span class="text-xs text-white/40">{{ timeAgo(note.updated_at) }}</span>

      <!-- Actions (visible on hover) -->
      <div class="flex gap-1 opacity-60 group-hover:opacity-100 transition-opacity" @click.stop>
        <!-- Pin -->
        <button @click.stop="$emit('pin', note)" :id="`pin-note-${note.id}`"
          :class="['w-7 h-7 rounded-lg flex items-center justify-center transition-colors',
            note.is_pinned ? 'text-yellow-400 bg-yellow-400/10' : 'text-white/40 hover:text-yellow-400 hover:bg-yellow-400/10']"
          :title="note.is_pinned ? 'Unpin' : 'Pin'">
          <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/>
          </svg>
        </button>

        <!-- Archive -->
        <button @click.stop="$emit('archive', note)" :id="`archive-note-${note.id}`"
          class="w-7 h-7 rounded-lg flex items-center justify-center text-white/40 hover:text-blue-400 hover:bg-blue-400/10 transition-colors"
          title="Archive">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
          </svg>
        </button>

        <!-- Delete -->
        <button @click.stop="confirmDelete" :id="`delete-note-${note.id}`"
          class="w-7 h-7 rounded-lg flex items-center justify-center text-white/40 hover:text-red-400 hover:bg-red-400/10 transition-colors"
          title="Delete">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  note: { type: Object, required: true },
});

defineEmits(['edit', 'pin', 'archive']);

const cardBackground = computed(() => {
  const base = props.note.color || '#1e293b';
  return `linear-gradient(135deg, ${base} 0%, ${base}dd 100%)`;
});

function confirmDelete() {
  if (confirm('Delete this note?')) {
    router.delete(`/notes/${props.note.id}`);
  }
}

function timeAgo(dateStr) {
  const date = new Date(dateStr);
  const now = new Date();
  const diff = Math.floor((now - date) / 1000);
  if (diff < 60) return 'just now';
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
  if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`;
  return date.toLocaleDateString();
}
</script>
