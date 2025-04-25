<template>
  <div class="flex gap-1 rounded-lg shadow-lg bg-white border p-1 z-50">
    <ToolbarBtn :active="ed.isActive('bold')" @click="cmd('toggleBold')"
      ><Bold size="16"
    /></ToolbarBtn>
    <ToolbarBtn :active="ed.isActive('italic')" @click="cmd('toggleItalic')"
      ><Italic size="16"
    /></ToolbarBtn>
    <ToolbarBtn
      :active="ed.isActive('underline')"
      @click="cmd('toggleUnderline')"
      ><Underline size="16"
    /></ToolbarBtn>
    <ToolbarBtn :active="ed.isActive('strike')" @click="cmd('toggleStrike')"
      ><Strikethrough size="16"
    /></ToolbarBtn>

    <ToolbarBtn
      :active="ed.isActive({ textAlign: 'left' })"
      @click="cmd('setTextAlign', 'left')"
      ><AlignLeft size="16"
    /></ToolbarBtn>
    <ToolbarBtn
      :active="ed.isActive({ textAlign: 'center' })"
      @click="cmd('setTextAlign', 'center')"
      ><AlignCenter size="16"
    /></ToolbarBtn>
    <ToolbarBtn
      :active="ed.isActive({ textAlign: 'right' })"
      @click="cmd('setTextAlign', 'right')"
      ><AlignRight size="16"
    /></ToolbarBtn>
    <ToolbarBtn
      :active="ed.isActive({ textAlign: 'justify' })"
      @click="cmd('setTextAlign', 'justify')"
      ><AlignJustify size="16"
    /></ToolbarBtn>

    <Dropdown icon size="sm">
      <template #button><Italic size="16" class="rotate-90" /></template>
      <button
        v-for="l in [1, 2, 3]"
        :key="l"
        @click="cmd('toggleHeading', { level: l })"
        class="px-2 py-1 w-full text-left hover:bg-gray-100 text-sm"
      >
        H{{ l }}
      </button>
    </Dropdown>

    <Dropdown icon size="sm">
      <template #button><TextCursorInput size="16" /></template>
      <div class="max-h-40 overflow-y-auto">
        <button
          v-for="tok in predefinedTokens"
          :key="tok.token"
          @mousedown.prevent.stop="applyToken(tok.token)"
          class="px-2 py-1 w-full text-left hover:bg-gray-100 text-sm"
        >
          {{ tok.label }}
        </button>
      </div>
    </Dropdown>
  </div>
</template>

<script setup lang="ts">
import { toRef } from "vue";
import { predefinedTokens } from "~/utils/tokens";
import ToolbarBtn from "./ToolbarBtn.vue";
import Dropdown from "./Dropdown.vue";
import {
  Bold,
  Italic,
  Underline,
  Strikethrough,
  AlignLeft,
  AlignCenter,
  AlignRight,
  AlignJustify,
  TextCursorInput,
} from "lucide-vue-next";

const props = defineProps<{ editor: any; applyToken: (k: string) => void }>();
const ed = toRef(props, "editor");

const cmd = (name: string, arg?: any) => {
  if (!ed.value) return;
  const c = ed.value.chain().focus();
  arg !== undefined ? c[name](arg).run() : c[name]().run();
}

const applyToken = (k: string) => {
  if (!ed.value) return;
  const { state } = ed.value;
  const hasSelection = !state.selection.empty;

  ed.value.chain().focus();

  if (hasSelection) {
    ed.value.chain().setMark('token', { key: k }).run();
  } else {
    ed.value.chain()
      .insertContent({
        type: 'text',
        text: '__________',
        marks: [{ type: 'token', attrs: { key: k } }],
      })
      .run();
  }
}

</script>

<style scoped>
button {
  @apply p-1 rounded hover:bg-gray-100;
}
.dropdown-panel {
  @apply shadow-md bg-white rounded-lg;
}
</style>
