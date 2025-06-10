<!-- <template>
  <Card class="shadow-md h-[28rem] flex flex-col rounded-2xl">
    <template #title>
      <span class="font-semibold text-base">Chat con el propietario</span>
    </template>

    <template #content>
      <div
        ref="messagesContainer"
        class="flex-1 overflow-y-auto px-4 py-2 space-y-3"
      >
        <div
          v-for="m in chatStore.messages"
          :key="m.id"
          :class="{
            'flex justify-end': m.sender_id === userId,
            'flex justify-start': m.sender_id !== userId,
          }"
        >
          <div
            class="max-w-[70%] px-3 py-2 rounded-2xl shadow-md break-words"
            :class="
              m.sender_id === userId
                ? 'bg-blue-600 text-white rounded-br-sm'
                : 'bg-gray-100 text-gray-800 rounded-bl-sm'
            "
          >
            {{ m.body }}
          </div>
        </div>

        <div
          v-for="m in chatStore.messages"
          :key="'ts-' + m.id"
          :class="{
            'flex justify-end pr-1': m.sender_id === userId,
            'flex justify-start pl-1': m.sender_id !== userId,
          }"
        >
          <small
            v-if="m.sent_at && !isNaN(Date.parse(m.sent_at))"
            class="text-[10px] text-gray-400 mt-1"
          >
            {{ timeFormatter.format(new Date(m.sent_at)) }}
          </small>
        </div>
      </div>

      <div class="mt-2 flex gap-2 px-4 pb-3">
        <InputText
          v-model="newMessage"
          placeholder="Escribe un mensaje…"
          class="flex-1 text-sm"
          @keyup.enter="send"
        />
        <Button
          icon="pi pi-send"
          rounded
          severity="info"
          :disabled="!newMessage.trim()"
          @click="send"
        />
      </div>
    </template>
  </Card>
</template>

<script setup lang="ts">
import Card from "primevue/card";
import InputText from "primevue/inputtext";
import Button from "primevue/button";

import { useChatStore } from "~/store/chat";
import { useAuthStore } from "~/store/auth";
import { getCsrfToken } from "~/utils/auth";

interface Props {
  context: { ownerId: number; tenantId: number };
}
const props = defineProps<Props>();

const chatStore = useChatStore();
const authStore = useAuthStore();
const userId = computed(() => authStore.user?.id);

const newMessage = ref("");
const messagesContainer = ref<HTMLElement | null>(null);

function scrollBottom() {
  messagesContainer.value?.scrollTo({
    top: messagesContainer.value.scrollHeight,
    behavior: "smooth",
  });
}

onMounted(async () => {
  await chatStore.openConversation(
    props.context.ownerId,
    props.context.tenantId
  );
  scrollBottom();
});

watch(
  () => chatStore.messages.length,
  () => nextTick(scrollBottom)
);

async function send() {
  const text = newMessage.value.trim();
  if (!text) return;

  await chatStore.sendMessage(text);
  newMessage.value = "";
}
</script> -->
<template>
  <div
    class="flex flex-col h-[32rem] md:h-[27.25rem] rounded-xl border border-blue-100 overflow-hidden"
  >
    <div class="border-b bg-white border-gray-200 p-4 flex items-center gap-3">
      <div class="relative">
        <img
          :src="ownerAvatar"
          class="w-10 h-10 rounded-full object-cover"
          @error="handleImageError"
        />
        <span
          v-if="isOnline"
          class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"
        />
      </div>
      <div>
        <p class="font-semibold text-gray-900">{{ ownerName }}</p>
        <p class="text-xs text-gray-500">
          <span v-if="isOnline" class="text-green-500">En línea</span>
          <span v-else>Desconectado</span>
        </p>
      </div>
    </div>

    <div
      ref="messagesContainer"
      class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50"
    >
      <div
        v-for="(message, i) in messages"
        :key="i"
        class="flex items-end gap-3"
        :class="{
          'justify-end': message.sender === 'tenant',
          'justify-start': message.sender === 'owner',
        }"
      >
        <img
          v-if="message.sender === 'owner'"
          :src="ownerAvatar"
          class="w-8 h-8 rounded-full"
          @error="handleImageError"
        />

        <div class="flex flex-col max-w-[70%]">
          <div
            class="px-4 py-2 rounded-2xl shadow-sm"
            :class="{
              'bg-white border border-gray-200 rounded-bl-none':
                message.sender === 'owner',
              'bg-blue-600 text-white rounded-br-none self-end':
                message.sender === 'tenant',
            }"
          >
            <p class="whitespace-pre-wrap">{{ message.text }}</p>
          </div>
          <span
            class="text-[10px] mt-1"
            :class="{
              'text-gray-500': message.sender === 'owner',
              'text-gray-500 self-end': message.sender === 'tenant',
            }"
          >
            {{ formatTime(message.time) }}
          </span>
        </div>

        <img
          v-if="message.sender === 'tenant'"
          :src="tenantAvatar"
          class="w-8 h-8 rounded-full"
          @error="handleImageError"
        />
      </div>
    </div>

    <!-- Separador -->
    <div class="border-t border-gray-200"></div>

    <!-- Input + Emojis -->
    <div class="bg-white p-4">
      <div class="flex items-center gap-2">
        <Button
          icon="pi pi-face-smile"
          rounded
          class="emoji-btn"
          @click="toggleEmojiPicker"
        />

        <InputText
          v-model="newMessage"
          placeholder="Escribe un mensaje…"
          class="flex-1 input-custom"
          @keyup.enter="sendMessage"
        />

        <Button
          icon="pi pi-send"
          rounded
          class="send-btn"
          :disabled="!newMessage.trim()"
          @click="sendMessage"
        />
      </div>

      <div
        v-if="showEmojiPicker"
        class="mt-3 p-3 border border-gray-200 rounded-lg bg-white"
      >
        <div class="grid grid-cols-8 gap-1">
          <span
            v-for="(emoji, idx) in emojis"
            :key="idx"
            class="text-xl cursor-pointer hover:bg-gray-100 rounded p-1 text-center"
            @click="addEmoji(emoji)"
          >
            {{ emoji }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import InputText from "primevue/inputtext";
import Button from "primevue/button";

const props = defineProps({
  context: { type: Object, required: true },
  rentable: { type: Object, default: null },
});

const newMessage = ref("");
const messages = ref([]);
const messagesContainer = ref<HTMLElement | null>(null);
const showEmojiPicker = ref(false);

const emojis = ref([
  "😀",
  "😂",
  "🥰",
  "😎",
  "🤩",
  "😍",
  "🙂",
  "🤗",
  "👍",
  "👋",
  "❤️",
  "🔥",
  "✨",
  "💯",
  "👏",
  "🎉",
]);

// Owner y tenant info
const ownerInfo = computed(() => ({
  name: props.rentable?.owner?.name || "Propietario",
  avatar: props.rentable?.owner?.avatar || "/avatars/default.jpg",
  isOnline: true,
}));
const ownerName = computed(() => ownerInfo.value.name);
const ownerAvatar = computed(() => ownerInfo.value.avatar);
const isOnline = computed(() => ownerInfo.value.isOnline);
const tenantAvatar = computed(() => "/avatars/default.jpg");

// Formato hora
const formatTime = (d: Date) =>
  new Date(d).toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });

// Foto por defecto sin bucle
function handleImageError(e: Event) {
  const img = e.target as HTMLImageElement;
  if (img.dataset.fallback) return;
  img.dataset.fallback = "1";
  img.src = "/avatars/default.jpg";
}

// Emoji picker
const toggleEmojiPicker = () =>
  (showEmojiPicker.value = !showEmojiPicker.value);
const addEmoji = (emoji: string) => (newMessage.value += emoji);

// Enviar mensajes
const scrollToBottom = () =>
  nextTick(() => {
    if (messagesContainer.value)
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  });

const sendMessage = () => {
  if (!newMessage.value.trim()) return;
  messages.value.push({
    text: newMessage.value,
    sender: "tenant",
    time: new Date(),
    status: "sent",
  });
  newMessage.value = "";
  showEmojiPicker.value = false;
  scrollToBottom();

  setTimeout(() => {
    messages.value.push({
      text: "Respuesta simulada del propietario.",
      sender: "owner",
      time: new Date(),
      status: "delivered",
    });
    scrollToBottom();
  }, 1500);
};

// Mensajes iniciales
onMounted(() => {
  messages.value = [
    {
      text: "Hola, ¿cómo estás?",
      sender: "owner",
      time: new Date(Date.now() - 3600000),
      status: "read",
    },
    {
      text: "Bien, gracias. Tú?",
      sender: "tenant",
      time: new Date(Date.now() - 3500000),
      status: "read",
    },
    {
      text: "Todo ok.",
      sender: "owner",
      time: new Date(Date.now() - 3400000),
      status: "read",
    },
  ];
  scrollToBottom();
});
watch(() => messages.value.length, scrollToBottom);
</script>

<style scoped>
.bg-blue-600 {
  @apply bg-info/90;
}
.bg-white {
  background-color: #ffffff;
}
.rounded-bl-none {
  border-bottom-left-radius: 0 !important;
}
.rounded-br-none {
  border-bottom-right-radius: 0 !important;
}
::v-deep(.emoji-btn.p-button) {
  @apply bg-transparent border-none shadow-none !important;
}
::v-deep(.emoji-btn .p-button-icon) {
  @apply text-info !important;
}
.input-custom {
  @apply border bg-gray-50 border-gray-300 rounded px-3 py-2 transition;
}
.send-btn {
  @apply bg-info text-white border border-info shadow-none;
}
.send-btn .p-button-icon {
  @apply text-white;
}
.send-btn:hover {
  @apply bg-info/90;
}
.send-btn:disabled {
  @apply opacity-80 cursor-not-allowed;
}
::v-deep(.input-custom.p-inputtext:focus),
::v-deep(.input-custom.p-inputtext:focus-visible) {
  outline: none !important;
  box-shadow: none !important;
  border-color: transparent !important;
}
::v-deep(.input-custom.p-inputtext:focus) {
  @apply ring-2 ring-info !important;
}
</style>
