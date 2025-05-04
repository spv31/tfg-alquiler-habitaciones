<template>
 <ContractWizard
    :tenant-id="tenantId"
    :property-id="propertyId"
    :room-id="roomId"
  />
</template>
<script setup lang="ts">
const route = useRoute()
const router = useRouter()

const tenantId = Number(route.query.tenantId)
const propertyId = Number(route.query.propertyId)
const roomId = Number(route.query.roomId) ?? null;

if (!tenantId || !propertyId) {
  router.replace({ name: 'properties-id', params:{ id: propertyId } })
}
</script>
<style scoped>
</style>