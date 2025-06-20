<template>
  <div
    class="flex flex-col h-[32rem] md:h-[27.25rem] rounded-xl border border-blue-100 overflow-hidden"
  >
    <div class="border-b bg-white border-gray-200 p-4 flex items-center gap-3">
      <div class="relative">
        <img
          :src="peerAvatar"
          class="w-10 h-10 rounded-full object-cover"
          @error="handleImageError"
        />
        <span
          v-if="isPeerOnline"
          class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"
        />
      </div>
      <div>
        <p class="font-semibold text-gray-900">{{ peerName }}</p>
        <p class="text-xs text-gray-500">
          <span v-if="isPeerOnline" class="text-green-500">En línea</span>
          <span v-else>Desconectado</span>
        </p>
      </div>
    </div>

    <div
      ref="messagesContainer"
      class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50"
    >
      <div
        v-for="m in chatMessages"
        :key="m.id"
        class="flex items-end gap-3"
        :class="{
          'justify-end': m.sender === 'me',
          'justify-start': m.sender === 'peer',
        }"
      >
        <img
          v-if="m.sender === 'peer'"
          :src="peerAvatar"
          class="w-8 h-8 rounded-full object-cover"
          @error="handleImageError"
        />

        <div class="flex flex-col max-w-[70%]">
          <div
            class="px-4 py-2 rounded-2xl shadow-sm"
            :class="{
              'bg-blue-600 text-white rounded-br-none self-end':
                m.sender === 'me',
              'bg-white border border-gray-200 rounded-bl-none':
                m.sender === 'peer',
            }"
          >
            <p class="whitespace-pre-wrap">{{ m.text }}</p>
          </div>
          <span
            class="text-[10px] mt-1"
            :class="{
              'text-gray-500 self-end': m.sender === 'me', 
              'text-gray-500': m.sender === 'peer', 
            }"
          >
            {{ formatTime(m.time) }}
          </span>
        </div>

        <img
          v-if="m.sender !== 'peer'"
          :src="myAvatar"
          class="w-8 h-8 rounded-full object-cover"
          @error="handleImageError"
        />
      </div>
    </div>

    <div class="border-t border-gray-200"></div>

    <div class="bg-white p-4">
      <div class="flex items-center gap-2">
        <Button
          icon="pi pi-face-smile"
          rounded
          class="emoji-btn"
          @click="toggleEmojiPicker"
        />

        <div class="flex-1">
          <InputText
            v-model="newMessage"
            placeholder="Escribe un mensaje…"
            class="w-full input-custom"
            @keyup.enter="sendMessage"
          />
        </div>

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
import type { Property } from "~/types/property";
import type { Room } from "~/types/room";
import { useChatStore } from "~/store/chat";
import type { PropType } from "vue";
import { useAuthStore } from "~/store/auth";

const authStore = useAuthStore();
const chatStore = useChatStore();
const { messages: storeMessages, isPeerOnline } = storeToRefs(chatStore);

type RentableWrapper = {
  type: "Property" | "Room";
  rentable: Property | Room;
};

const props = defineProps({
  context: {
    type: Object as PropType<{ ownerId: number; tenantId: number }>,
    required: true,
  },
  rentable: { type: Object as PropType<RentableWrapper>, required: true },
  currentUserId: { type: Number, required: true },
  currentUserRole: {
    type: String as PropType<"tenant" | "owner">,
    required: true,
  },
});

const getAvatar = (u: any) =>
  u?.profile_image_url || u?.profile_image || "/avatars/default.jpg";

const meIsOwner = computed(() => props.currentUserRole === "owner");

const peer = computed(() =>
  meIsOwner.value
    ? props.rentable.rentable
    : (props.rentable.rentable as any).owner
);

const peerName = computed(() => peer.value?.name ?? "—");
const peerAvatar = computed(() => getAvatar(peer.value));

const myAvatar = computed(() => getAvatar(authStore.user));

const parseDate = (iso: string) => new Date(iso);

const chatMessages = computed(() =>
  storeMessages.value.map((m) => ({
    id: m.id,
    text: m.body,
    sender: m.sender_id === props.currentUserId ? "me" : "peer",
    time: parseDate(m.sent_at),
  }))
);

onMounted(async () => {
  await chatStore.openConversation(
    props.context.ownerId,
    props.context.tenantId
  );
  scrollToBottom();
});

const newMessage = ref("");
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

const formatTime = (d: Date) =>
  new Date(d).toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });

const handleImageError = (e: Event) => {
  const img = e.target as HTMLImageElement;
  if (img.dataset.fallback) return;
  img.dataset.fallback = "1";
  img.src = "/avatars/default.jpg";
};

const toggleEmojiPicker = () =>
  (showEmojiPicker.value = !showEmojiPicker.value);
const addEmoji = (emoji: string) => (newMessage.value += emoji);

const scrollToBottom = () =>
  nextTick(() => {
    if (messagesContainer.value)
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  });

const sendMessage = async () => {
  if (!newMessage.value.trim()) return;
  try {
    await chatStore.sendMessage(newMessage.value.trim());
    newMessage.value = "";
    showEmojiPicker.value = false;
  } catch (err) {
    console.error("Error enviando mensaje:", err);
  }
};

watch(() => storeMessages.value.length, scrollToBottom);
</script>

<style scoped>
.bg-blue-600 {
  @apply bg-info/90;
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
