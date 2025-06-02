<template>
  <Card class="shadow-md">
    <template #title>Contrato</template>

    <template #content>
      <div v-if="!contract" class="text-gray-500 text-center py-8">
        No hay contrato asociado a tu alquiler.
      </div>

      <div v-else class="space-y-4">
        <p>
          <span class="font-medium">Estado:</span>
          <span :class="statusColor" class="ml-2 capitalize">{{ contract.status.replaceAll('_', ' ') }}</span>
        </p>

        <div class="flex gap-4 flex-wrap">
          <Button v-if="canDownload" label="Ver contrato" icon="pi pi-eye" @click="openPdf" />
          <Button
            v-if="canSign"
            label="Subir contrato firmado"
            icon="pi pi-upload"
            severity="success"
            @click="triggerUpload"
          />
        </div>
      </div>
    </template>
  </Card>
</template>

<script setup lang="ts">
import Card from 'primevue/card'
import Button from 'primevue/button'
import type { Contract } from '@/types/contract'
import { useTenantStore } from '~/store/tenant'

const props = defineProps<{ contract: Contract | null; rentableType?: 'Property' | 'Room' }>()

const statusColor = computed(() => {
  switch (props.contract?.status) {
    case 'draft':
      return 'text-yellow-600'
    case 'signed_by_owner':
      return 'text-blue-600'
    case 'active':
      return 'text-green-600'
    case 'finished':
      return 'text-gray-500'
    default:
      return ''
  }
})

const canSign = computed(() => props.contract?.status === 'signed_by_owner')
const canDownload = computed(() => Boolean(props.contract?.pdf_path_signed || props.contract?.pdf_path))

function openPdf() {
  if (!props.contract) return
  window.open(`/api/contracts/${props.contract.id}/pdf`, '_blank')
}

function triggerUpload() {
  alert('Funcionalidad de subida no implementada todavía.')
}
</script>

<style scoped>
</style>