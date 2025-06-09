<!-- <template>
  <Card class="shadow-md h-96 flex flex-col">
    <template #title>Chat con el propietario</template>

    <template #content>
      <div class="flex-1 overflow-y-auto space-y-2 p-2" ref="messagesContainer">
        <div v-for="msg in mockMessages" :key="msg.id" :class="msgClass(msg)">
          <div class="p-2 rounded-lg shadow text-sm">{{ msg.text }}</div>
        </div>
      </div>
      <div class="mt-2 flex gap-2">
        <InputText v-model="newMessage" placeholder="Escribe un mensaje…" class="flex-1" />
        <Button icon="pi pi-send" :disabled="!newMessage" @click="sendMessage" />
      </div>
    </template>
  </Card>
</template>

<script setup lang="ts">
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'

const mockMessages = ref([
  { id: 1, text: 'Hola, ¿todo bien?', fromUser: false },
  { id: 2, text: 'Sí, todo perfecto. Gracias.', fromUser: true },
])

const newMessage = ref('')
const messagesContainer = ref<HTMLElement | null>(null)

function sendMessage() {
  if (!newMessage.value) return
  mockMessages.value.push({ id: Date.now(), text: newMessage.value, fromUser: true })
  newMessage.value = ''
  nextTick(() => {
    messagesContainer.value?.scrollTo({ top: messagesContainer.value.scrollHeight, behavior: 'smooth' })
  })
}

function msgClass(msg: any) {
  return msg.fromUser ? 'text-right' : 'text-left'
}
</script>

<style scoped>
</style> -->
<template>
  <Card class="shadow-md h-96 flex flex-col">
    <template #title>Chat con el propietario</template>

    <template #content>
      <!-- HISTÓRICO -->
      <div
        ref="messagesContainer"
        class="flex-1 overflow-y-auto space-y-2 p-2"
      >
        <div
          v-for="m in chatStore.messages"
          :key="m.id"
          :class="m.sender_id === userId ? 'text-right' : 'text-left'"
        >
          <div
            class="inline-block p-2 rounded-lg shadow text-sm"
            :class="m.sender_id === userId ? 'bg-blue-50' : 'bg-gray-100'"
          >
            {{ m.body }}
          </div>
          <small class="text-xs text-gray-400">
            {{ new Date(m.sent_at).toLocaleTimeString() }}
          </small>
        </div>
      </div>

      <!-- INPUT -->
      <div class="mt-2 flex gap-2">
        <InputText
          v-model="newMessage"
          placeholder="Escribe un mensaje…"
          class="flex-1"
          @keyup.enter="send"
        />
        <Button
          icon="pi pi-send"
          :disabled="!newMessage.trim()"
          @click="send"
        />
      </div>
    </template>
  </Card>
</template>

<script setup lang="ts">
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'

import { useChatStore } from '~/store/chat'
import { useAuthStore } from '~/store/auth'
import { getCsrfToken } from '~/utils/auth'

/* ---------- props ---------- */
interface Props {
  context: { ownerId: number; tenantId: number }
}
const props = defineProps<Props>()

/* ---------- stores ---------- */
const chatStore = useChatStore()
const authStore = useAuthStore()
const userId = computed(() => authStore.user?.id)

/* ---------- state ---------- */
const newMessage = ref('')
const messagesContainer = ref<HTMLElement | null>(null)

/* ---------- helpers ---------- */
function scrollBottom () {
  messagesContainer.value?.scrollTo({
    top: messagesContainer.value.scrollHeight,
    behavior: 'smooth',
  })
}

/* ---------- montar: crear / seleccionar conversación ---------- */
// onMounted(async () => {
//   console.log('Datos recibidos owner: ', props.context.ownerId)
//     console.log('Datos recibidos tenant: ', props.context.tenantId)
//   await chatStore.openConversation(props.context.ownerId, props.context.tenantId);

//   // 2) buscar conversación existente
//   let convo = chatStore.conversations.find(
//     c =>
//       c.owner_id === props.context.ownerId &&
//       c.tenant_id === props.context.tenantId
//   )

//   // 3) si no existe, crearla vía POST /conversations
//   if (!convo) {
//     const csrf = await getCsrfToken()
//     convo = await $fetch('/api/conversations', {
//       method: 'POST',
//       credentials: 'include',
//       headers: {
//         'X-XSRF-TOKEN': csrf!,
//         Accept: 'application/json',
//       },
//       body: {
//         owner_id: props.context.ownerId,
//         tenant_id: props.context.tenantId,
//       },
//     })
//     chatStore.conversations.push(convo)
//   }

//   // 4) activar la conversación y cargar mensajes
//   await chatStore.setActiveConversation(convo!)
//   scrollBottom()
// })
onMounted(async () => {
  await chatStore.openConversation(props.context.ownerId, props.context.tenantId)
  scrollBottom()
})

/* ---------- watcher: auto-scroll cuando llegan mensajes ---------- */
watch(
  () => chatStore.messages.length,
  () => nextTick(scrollBottom)
)

/* ---------- enviar ---------- */
async function send () {
  const text = newMessage.value.trim()
  if (!text) return

  await chatStore.sendMessage(text)
  newMessage.value = ''
}
</script>
