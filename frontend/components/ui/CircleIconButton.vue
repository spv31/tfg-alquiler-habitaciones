<template>
  <div class="flex flex-col items-center w-16">
    <button
      @click="$emit('click')"
      class="flex items-center justify-center w-12 h-12 bg-white rounded-full
             shadow-top-md hover:shadow-top-lg transition p-2 shadow-top"
    >
      <slot name="icon"></slot>
    </button>

    <span class="text-xs mt-2 font-medium text-center">
      {{ label }}
    </span>
  </div>
</template>


<script setup lang="ts">
const props = defineProps({
  label: {
    type: String,
    required: true,
  },
});
defineEmits(["click"]);
</script>

<style scoped>

.shadow-top-md {
  --tw-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1),
               0 2px 4px -2px rgb(0 0 0 / 0.1),
               0 -1px 3px rgba(0, 0, 0, 0.08); 
  --tw-shadow-colored: 0 4px 6px -1px var(--tw-shadow-color),
                       0 2px 4px -2px var(--tw-shadow-color),
                       0 -1px 3px rgba(0, 0, 0, 0.08);
  box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000),
              var(--tw-ring-shadow, 0 0 #0000),
              var(--tw-shadow);
}

.hover\:shadow-top-lg:hover {
  --tw-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1),
               0 4px 6px -4px rgb(0 0 0 / 0.1),
               0 -2px 5px rgba(0, 0, 0, 0.07); 
  --tw-shadow-colored: 0 10px 15px -3px var(--tw-shadow-color),
                       0 4px 6px -4px var(--tw-shadow-color),
                       0 -2px 5px rgba(0, 0, 0, 0.07);
  box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000),
              var(--tw-ring-shadow, 0 0 #0000),
              var(--tw-shadow);
}
</style>