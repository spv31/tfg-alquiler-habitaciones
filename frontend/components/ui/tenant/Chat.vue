<template>
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
</style>