<template>
  <Teleport to="body">
    <Transition enter-from-class="opacity-0 translate-x-8" enter-to-class="opacity-100 translate-x-0"
      leave-from-class="opacity-100 translate-x-0" leave-to-class="opacity-0 translate-x-8"
      enter-active-class="transition-all duration-300" leave-active-class="transition-all duration-200">
      <div v-if="show" class="fixed right-0 top-0 bottom-0 w-80 z-50 glass-dark border-l border-white/10 flex flex-col ai-glow"
        style="max-height: 100vh;">
        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-white/10 flex-shrink-0">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center animate-pulse-glow">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.346.346a1 1 0 01-1.414-1.414l.346-.345a3 3 0 10-4.243 0l.346.345a1 1 0 01-1.414 1.414l-.346-.346a5 5 0 010-7.072z"/>
              </svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-white">AI Assistant</p>
              <p class="text-xs text-purple-400">Powered by OpenRouter</p>
            </div>
          </div>
          <button @click="$emit('close')" id="close-ai-btn"
            class="w-7 h-7 rounded-lg glass flex items-center justify-center text-indigo-400 hover:text-white transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Messages -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3" ref="messagesEl">
          <div v-if="messages.length === 0" class="text-center py-8">
            <div class="w-16 h-16 rounded-2xl bg-purple-600/20 flex items-center justify-center mx-auto mb-3">
              <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
              </svg>
            </div>
            <p class="text-sm text-indigo-400 font-medium">Ask me anything!</p>
            <p class="text-xs text-indigo-500 mt-1">I can help with your notes, writing, and ideas.</p>
            <!-- Suggestions -->
            <div class="mt-4 space-y-2">
              <button v-for="s in suggestions" :key="s" @click="sendSuggestion(s)"
                class="w-full text-left px-3 py-2 rounded-xl text-xs text-indigo-300 hover:text-white bg-indigo-600/10 hover:bg-indigo-600/20 border border-indigo-600/20 transition-all">
                {{ s }}
              </button>
            </div>
          </div>

          <TransitionGroup enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0"
            enter-active-class="transition-all duration-200">
            <div v-for="(msg, i) in messages" :key="i"
              :class="['flex', msg.role === 'user' ? 'justify-end' : 'justify-start']">
              <div :class="['max-w-[85%] px-4 py-2.5 rounded-2xl text-sm',
                msg.role === 'user'
                  ? 'bg-indigo-600 text-white rounded-br-sm'
                  : 'glass text-white/90 rounded-bl-sm']">
                <p class="whitespace-pre-wrap leading-relaxed">{{ msg.content }}</p>
              </div>
            </div>
          </TransitionGroup>

          <div v-if="loading" class="flex justify-start">
            <div class="glass px-4 py-3 rounded-2xl rounded-bl-sm">
              <div class="flex gap-1">
                <span class="w-2 h-2 rounded-full bg-purple-400 animate-bounce" style="animation-delay:0s"></span>
                <span class="w-2 h-2 rounded-full bg-purple-400 animate-bounce" style="animation-delay:0.15s"></span>
                <span class="w-2 h-2 rounded-full bg-purple-400 animate-bounce" style="animation-delay:0.3s"></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Input -->
        <div class="flex-shrink-0 p-4 border-t border-white/10">
          <div class="flex gap-2">
            <button @click="clearChat" v-if="messages.length > 0" id="clear-chat-btn"
              class="w-9 h-9 rounded-xl glass flex items-center justify-center text-indigo-400 hover:text-red-400 transition-colors flex-shrink-0"
              title="Clear chat">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </button>
            <textarea v-model="input" @keydown.enter.exact.prevent="send"
              id="ai-chat-input"
              placeholder="Message AI... (Enter to send)"
              class="input-field flex-1 px-3 py-2.5 rounded-xl text-sm resize-none"
              rows="2"></textarea>
            <button @click="send" id="ai-send-btn" :disabled="loading || !input.trim()"
              class="w-9 h-9 rounded-xl btn-primary flex items-center justify-center flex-shrink-0 self-end disabled:opacity-50 disabled:cursor-not-allowed">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, nextTick } from 'vue';
import axios from 'axios';

defineProps({ show: Boolean, noteContext: { type: String, default: '' } });
defineEmits(['close']);

const messages = ref([]);
const input = ref('');
const loading = ref(false);
const messagesEl = ref(null);

const suggestions = [
  '💡  Help me brainstorm ideas for my note',
  '📝  Summarize my current note',
  '✍️  How do I write better notes?',
  '🗂️  Help me organize my thoughts',
];

async function send() {
  const text = input.value.trim();
  if (!text || loading.value) return;

  messages.value.push({ role: 'user', content: text });
  input.value = '';
  loading.value = true;
  await scrollBottom();

  try {
    const chatMessages = messages.value.map(m => ({ role: m.role, content: m.content }));
    const res = await axios.post('/ai/chat', { messages: chatMessages });
    messages.value.push({ role: 'assistant', content: res.data.message });
  } catch {
    messages.value.push({ role: 'assistant', content: 'Sorry, I encountered an error. Please try again.' });
  } finally {
    loading.value = false;
    await scrollBottom();
  }
}

async function sendSuggestion(s) {
  input.value = s.replace(/^[^\s]+\s+/, '');
  await send();
}

function clearChat() { messages.value = []; }

async function scrollBottom() {
  await nextTick();
  if (messagesEl.value) messagesEl.value.scrollTop = messagesEl.value.scrollHeight;
}
</script>
