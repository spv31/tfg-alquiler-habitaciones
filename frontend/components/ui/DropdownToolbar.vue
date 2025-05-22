<template>
  <div class="relative group">
    <ToolbarBtn :active="props.active">
      <div class="h-8 w-4 flex items-center justify-center">
        <slot name="button">{{ label }}</slot>
      </div>
    </ToolbarBtn>

    <div
      class="absolute z-20 hidden group-hover:block bg-white border shadow rounded p-1 space-y-1 min-w-[10rem]"
    >
      <slot v-if="!items" />
      <template v-else>
        <button
          v-for="it in items"
          :key="it.label"
          @click="emit('select', it.level || it)"
          :class="{
            'px-3 py-1 text-left w-full hover:bg-gray-100': true,
            'bg-blue-100 text-blue-600': isActive && isActive(it.level || it),
          }"
        >
          <component
            :is="'h' + (it.level || 1)"
            :class="headingClass[it.level || 1]"
            style="font-family: Arial, Helvetica, sans-serif;"
          >
            {{ it.label }}
          </component>
        </button>
      </template>
    </div>
  </div>
</template>
<script setup lang="ts">
const props = defineProps<{
  label?: string;
  items?: any[];
  isActive?: Function;
  icon?: boolean;
  active?: boolean;
}>();
const emit = defineEmits(["select"]);

// Classes for headings
const headingClass: Record<number, string> = {
  1: "text-2xl font-bold",
  2: "text-xl  font-bold",
  3: "text-lg  font-semibold",
  4: "text-base font-semibold",
  5: "text-sm  font-medium",
  6: "text-xs  font-medium",
};
</script>
