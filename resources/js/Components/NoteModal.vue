<template>
  <Teleport to="body">
    <Transition enter-from-class="opacity-0" enter-to-class="opacity-100"
      leave-from-class="opacity-100" leave-to-class="opacity-0"
      enter-active-class="transition-opacity duration-200" leave-active-class="transition-opacity duration-200">
      <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-overlay" @click.self="$emit('close')">
        <Transition enter-from-class="opacity-0 scale-95 translate-y-4" enter-to-class="opacity-100 scale-100 translate-y-0"
          leave-from-class="opacity-100 scale-100 translate-y-0" leave-to-class="opacity-0 scale-95 translate-y-4"
          enter-active-class="transition-all duration-300" leave-active-class="transition-all duration-200">
          <div v-if="show" class="glass-dark rounded-3xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-white/10">
              <h2 class="text-lg font-semibold text-white">{{ note ? 'Edit Note' : 'New Note' }}</h2>
              <div class="flex items-center gap-2">
                <!-- Color picker -->
                <div class="flex items-center gap-1">
                  <button v-for="color in colors" :key="color"
                    @click="form.color = color"
                    :style="{ background: color }"
                    :class="['w-6 h-6 rounded-full border-2 transition-transform hover:scale-110',
                      form.color === color ? 'border-white scale-110' : 'border-transparent']">
                  </button>
                </div>
                <button @click="$emit('close')" class="w-8 h-8 rounded-xl glass flex items-center justify-center text-indigo-400 hover:text-white transition-colors">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Body -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4">
              <!-- Title -->
              <div>
                <input v-model="form.title" type="text" id="note-title" placeholder="Note title..."
                  class="input-field w-full px-4 py-3 rounded-xl text-lg font-semibold"
                  @keydown.ctrl.enter="save"/>
              </div>

              <!-- Category -->
              <div class="flex gap-3">
                <input v-model="form.category" type="text" id="note-category" placeholder="Category (optional)"
                  class="input-field flex-1 px-4 py-2.5 rounded-xl text-sm"/>
              </div>

              <!-- Content -->
              <div>
                <textarea v-model="form.content" id="note-content" placeholder="Write your note here... (Ctrl+Enter to save)"
                  class="input-field w-full px-4 py-3 rounded-xl text-sm resize-none"
                  rows="10"></textarea>
              </div>

              <!-- Tags -->
              <div>
                <div class="flex flex-wrap gap-2 mb-2">
                  <span v-for="(tag, i) in form.tags" :key="i"
                    class="tag-chip flex items-center gap-1">
                    #{{ tag }}
                    <button @click="removeTag(i)" class="hover:text-red-400 transition-colors ml-1">×</button>
                  </span>
                </div>
                <div class="flex gap-2">
                  <input v-model="tagInput" @keydown.enter.prevent="addTag" @keydown.comma.prevent="addTag"
                    type="text" id="tag-input" placeholder="Add tag (press Enter)"
                    class="input-field flex-1 px-4 py-2 rounded-xl text-sm"/>
                  <button @click="generateAiTags" :disabled="aiLoading"
                    class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium bg-purple-600/20 border border-purple-500/30 text-purple-300 hover:bg-purple-600/30 transition-all"
                    title="Generate tags with AI">
                    <svg v-if="aiLoading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.346.346a1 1 0 01-1.414-1.414l.346-.345a3 3 0 10-4.243 0l.346.345a1 1 0 01-1.414 1.414l-.346-.346a5 5 0 010-7.072z"/>
                    </svg>
                    <span class="hidden sm:inline">AI Tags</span>
                  </button>
                </div>
              </div>

              <!-- AI Tools -->
              <div class="border-t border-white/10 pt-4">
                <p class="text-xs text-indigo-400 mb-3 font-medium uppercase tracking-wider">✨ AI Tools</p>
                <div class="flex flex-wrap gap-2">
                  <button v-for="tool in aiTools" :key="tool.mode"
                    :id="`ai-${tool.mode}-btn`"
                    @click="runAiTool(tool.mode)"
                    :disabled="aiLoading || !form.content"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium bg-purple-600/20 border border-purple-500/30 text-purple-300 hover:bg-purple-600/30 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                    {{ tool.icon }} {{ tool.label }}
                  </button>
                </div>
                <Transition enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0"
                  enter-active-class="transition-all duration-300">
                  <div v-if="aiResult" class="mt-3 p-4 rounded-xl bg-purple-900/30 border border-purple-500/30">
                    <div class="flex items-start justify-between gap-2 mb-2">
                      <p class="text-xs text-purple-400 font-medium">AI Suggestion</p>
                      <div class="flex gap-2">
                        <button @click="applyAiResult" class="text-xs text-indigo-300 hover:text-white transition-colors">Apply</button>
                        <button @click="aiResult = ''" class="text-xs text-red-400 hover:text-red-300 transition-colors">Dismiss</button>
                      </div>
                    </div>
                    <p class="text-sm text-white/80 whitespace-pre-wrap">{{ aiResult }}</p>
                  </div>
                </Transition>
              </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between px-6 py-4 border-t border-white/10">
              <p class="text-xs text-indigo-500">Ctrl + Enter to save</p>
              <div class="flex gap-3">
                <button @click="$emit('close')" id="cancel-note-btn"
                  class="px-4 py-2 rounded-xl text-sm text-indigo-400 hover:text-white hover:bg-white/5 transition-all">
                  Cancel
                </button>
                <button @click="save" id="save-note-btn" :disabled="!form.title || saving"
                  class="btn-primary px-6 py-2 rounded-xl text-white text-sm font-medium flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                  <svg v-if="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  {{ saving ? 'Saving...' : (note ? 'Update Note' : 'Create Note') }}
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  show: Boolean,
  note: { type: Object, default: null },
});

const emit = defineEmits(['close', 'saved']);

const colors = ['#1e293b', '#4c1d95', '#1e3a8a', '#064e3b', '#7c2d12', '#831843', '#1a1a2e'];
const aiTools = [
  { mode: 'summarize', label: 'Summarize', icon: '📝' },
  { mode: 'improve', label: 'Improve', icon: '✨' },
  { mode: 'expand', label: 'Expand', icon: '🔮' },
  { mode: 'title', label: 'Generate Title', icon: '💡' },
];

const form = reactive({ title: '', content: '', color: '#1e293b', category: '', tags: [] });
const tagInput = ref('');
const aiLoading = ref(false);
const aiResult = ref('');
const aiResultMode = ref('');
const saving = ref(false);

watch(() => props.show, (val) => {
  if (val) {
    if (props.note) {
      form.title = props.note.title;
      form.content = props.note.content || '';
      form.color = props.note.color || '#1e293b';
      form.category = props.note.category || '';
      form.tags = props.note.tags || [];
    } else {
      form.title = ''; form.content = ''; form.color = '#1e293b'; form.category = ''; form.tags = [];
    }
    aiResult.value = '';
    tagInput.value = '';
  }
});

function addTag() {
  const t = tagInput.value.trim().replace(',', '');
  if (t && !form.tags.includes(t)) form.tags.push(t);
  tagInput.value = '';
}

function removeTag(i) { form.tags.splice(i, 1); }

async function generateAiTags() {
  if (!form.content && !form.title) return;
  aiLoading.value = true;
  try {
    const res = await axios.post('/ai/assist', {
      prompt: `Generate tags for: ${form.title}`,
      mode: 'tags',
      content: form.content,
    });
    const parsed = JSON.parse(res.data.result);
    if (Array.isArray(parsed)) form.tags = [...new Set([...form.tags, ...parsed])];
  } catch (e) {
    console.error(e);
  } finally {
    aiLoading.value = false;
  }
}

async function runAiTool(mode) {
  if (!form.content) return;
  aiLoading.value = true;
  aiResult.value = '';
  aiResultMode.value = mode;
  try {
    const res = await axios.post('/ai/assist', {
      prompt: mode === 'title' ? 'Generate a title' : `${mode} this note`,
      mode,
      content: form.content || form.title,
    });
    aiResult.value = res.data.result;
  } catch (e) {
    aiResult.value = 'AI service error. Please try again.';
  } finally {
    aiLoading.value = false;
  }
}

function applyAiResult() {
  if (aiResultMode.value === 'title') {
    form.title = aiResult.value.replace(/^["']|["']$/g, '').trim();
  } else {
    form.content = aiResult.value;
  }
  aiResult.value = '';
}

function save() {
  if (!form.title || saving.value) return;
  saving.value = true;
  const data = { ...form };
  if (props.note) {
    router.put(`/notes/${props.note.id}`, data, {
      onSuccess: () => { saving.value = false; emit('close'); },
      onError: () => { saving.value = false; },
    });
  } else {
    router.post('/notes', data, {
      onSuccess: () => { saving.value = false; emit('close'); },
      onError: () => { saving.value = false; },
    });
  }
}
</script>
