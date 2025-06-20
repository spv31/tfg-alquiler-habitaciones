<template>
  <Dialog
    v-model:visible="show"
    modal
    :style="{ width: '95vw', maxWidth: '700px' }"
    dismissableMask
    :header="$t('common.chat')"
  >
    <Chat
      :context="context"
      :rentable="rentable"
      :current-user-id="currentUserId"
      :current-user-role="currentUserRole"
    />
  </Dialog>
</template>

<script setup lang="ts">
import Dialog from "primevue/dialog";
import Chat from "~/components/ui/tenant/Chat.vue";
import { useI18n } from "vue-i18n";

const { t: $t } = useI18n();

const props = defineProps({
  visible: { type: Boolean, default: false },
  context: { type: Object, required: true },
  rentable: { type: Object, required: true },
  currentUserId: { type: Number, required: true },
  currentUserRole: { type: String, required: true },
});
const emit = defineEmits(["update:visible"]);

const show = ref(props.visible);

watch(
  () => props.visible,
  (v) => (show.value = v)
);

watch(show, (v) => emit("update:visible", v));
</script>
