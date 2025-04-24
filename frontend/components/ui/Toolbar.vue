<template>
  <div
    v-if="props.editor"
    class="flex flex-wrap px-2 py-1 gap-1 bg-gray-50 border-b rounded-t-2xl"
  >
    <ToolbarBtn
      :active="editor.isActive('bold')"
      @click="editor.chain().focus().toggleBold().run()"
      ><Bold size="18"
    /></ToolbarBtn>
    <ToolbarBtn
      :active="editor.isActive('italic')"
      @click="editor.chain().focus().toggleItalic().run()"
      ><Italic size="18"
    /></ToolbarBtn>
    <ToolbarBtn
      :active="editor.isActive('underline')"
      @click="editor.chain().focus().toggleUnderline().run()"
      ><Underline size="18"
    /></ToolbarBtn>
    <ToolbarBtn
      :active="editor.isActive('strike')"
      @click="editor.chain().focus().toggleStrike().run()"
      ><Strikethrough size="18"
    /></ToolbarBtn>
    <ToolbarBtn
      :active="editor.isActive('code')"
      @click="editor.chain().focus().toggleCode().run()"
      ><Code size="18"
    /></ToolbarBtn>

    <!-- Encabezados -->
    <Dropdown
      label="H"
      :items="[
        { label: 'Encabezado 1', level: 1 },
        { label: 'Encabezado 2', level: 2 },
        { label: 'Encabezado 3', level: 3 },
        { label: 'Encabezado 4', level: 4 },
        { label: 'Encabezado 5', level: 5 },
        { label: 'Encabezado 6', level: 6 },
      ]"
      :active="isHeadingActive"
      :isActive="(l) => editor.isActive('heading', { level: l })"
      @select="(l) => editor.chain().focus().toggleHeading({ level: l }).run()"
    />

    <ToolbarBtn
      :active="editor.isActive('paragraph')"
      @click="editor.chain().focus().setParagraph().run()"
      >¶</ToolbarBtn
    >

    <Dropdown icon :active="currentFont !== null">
      <template #button>
        <Type size="18" />
      </template>

      <button
        v-for="f in fonts"
        :key="f.label"
        @click="setFont(f.value)"
        :class="[
          'px-3 py-1 w-full text-left hover:bg-gray-100 transition',
          currentFont === f.value ? 'bg-blue-100 text-blue-600' : '',
        ]"
        :style="{ fontFamily: f.value }"
      >
        {{ f.label }}
      </button>

      <hr class="my-1 border-gray-200" />

      <button
        @click="setFont(null)"
        :class="[
          'px-3 py-1 w-full text-left hover:bg-gray-100 transition',
          currentFont === null ? 'bg-blue-100 text-blue-600' : '',
        ]"
        style="font-family: Arial, Helvetica, sans-serif"
      >
        Predeterminado (Arial)
      </button>
    </Dropdown>

    <Dropdown icon :active="currentSize != null">
      <template #button>
        <TextCursorInput size="18" />
      </template>

      <button
        v-for="s in sizes"
        :key="s"
        @click="setSize(s)"
        :class="[
          'px-3 py-1 w-full text-left hover:bg-gray-100 transition',
          currentSize === s ? 'bg-blue-100 text-blue-600' : '',
        ]"
        :style="{ fontSize: s }"
      >
        {{ s }}
      </button>

      <hr class="my-1 border-gray-200" />

      <button
        @click="setSize(null)"
        :class="[
          'px-3 py-1 w-full text-left hover:bg-gray-100 transition',
          currentSize === null ? 'bg-blue-100 text-blue-600' : '',
        ]"
        style="font-size: 12pt"
      >
        Predeterminado (12pt)
      </button>
    </Dropdown>

    <!-- Listas -->
    <ToolbarBtn
      :active="editor.isActive('bulletList')"
      @click="editor.chain().focus().toggleBulletList().run()"
      ><List size="18"
    /></ToolbarBtn>
    <ToolbarBtn
      :active="editor.isActive('orderedList')"
      @click="editor.chain().focus().toggleOrderedList().run()"
      ><ListOrdered size="18"
    /></ToolbarBtn>

    <!-- <ToolbarSep /> -->

    <!-- Alineación -->
    <Dropdown icon>
      <template #button>
        <AlignLeft size="18" />
      </template>

      <button
        @click="editor.chain().focus().setTextAlign('left').run()"
        :class="{ 'bg-gray-200': editor.isActive({ textAlign: 'left' }) }"
        class="px-3 py-1 w-full text-left hover:bg-gray-100"
      >
        <AlignLeft size="18" class="inline mr-2" /> Izquierda
      </button>
      <button
        @click="editor.chain().focus().setTextAlign('center').run()"
        :class="{ 'bg-gray-200': editor.isActive({ textAlign: 'center' }) }"
        class="px-3 py-1 w-full text-left hover:bg-gray-100"
      >
        <AlignCenter size="18" class="inline mr-2" /> Centro
      </button>
      <button
        @click="editor.chain().focus().setTextAlign('right').run()"
        :class="{ 'bg-gray-200': editor.isActive({ textAlign: 'right' }) }"
        class="px-3 py-1 w-full text-left hover:bg-gray-100"
      >
        <AlignRight size="18" class="inline mr-2" /> Derecha
      </button>
      <button
        @click="editor.chain().focus().setTextAlign('justify').run()"
        :class="{ 'bg-gray-200': editor.isActive({ textAlign: 'justify' }) }"
        class="px-3 py-1 w-full text-left hover:bg-gray-100"
      >
        <AlignJustify size="18" class="inline mr-2" /> Justificado
      </button>
    </Dropdown>

    <!-- <ToolbarSep /> -->

    <!-- Bloques -->
    <ToolbarBtn
      :active="editor.isActive('blockquote')"
      @click="editor.chain().focus().toggleBlockquote().run()"
      ><Quote size="18"
    /></ToolbarBtn>
    <ToolbarBtn @click="editor.chain().focus().setHorizontalRule().run()"
      ><Minus size="18"
    /></ToolbarBtn>

    <!-- Imagen -->
    <ToolbarBtn class="relative">
      <Image size="18" />
      <input
        type="file"
        accept="image/*"
        class="absolute inset-0 opacity-0 cursor-pointer"
        @change="onImageUpload"
      />
    </ToolbarBtn>

    <ToolbarBtn
      :disabled="!editor.can().undo()"
      @click="editor.chain().focus().undo().run()"
      ><Undo2 size="18"
    /></ToolbarBtn>
    <ToolbarBtn
      :disabled="!editor.can().redo()"
      @click="editor.chain().focus().redo().run()"
      ><Redo2 size="18"
    /></ToolbarBtn>

    <ToolbarSep />

    <!-- Limpiar -->
    <ToolbarBtn @click="editor.chain().focus().unsetAllMarks().run()"
      ><Eraser size="18"
    /></ToolbarBtn>
    <ToolbarBtn @click="editor.chain().focus().clearNodes().run()"
      ><Layout size="18"
    /></ToolbarBtn>
  </div>
</template>
<script setup lang="ts">
import {
  Type,
  Text,
  TextCursorInput,
  Braces,
  Bold,
  Italic,
  Underline,
  Strikethrough,
  Code,
  List,
  ListOrdered,
  CheckSquare2,
  Quote,
  FileCode,
  Minus,
  Undo2,
  Redo2,
  Link,
  Link2Off,
  Image,
  AlignLeft,
  AlignCenter,
  AlignRight,
  AlignJustify,
  Eraser,
  Layout,
} from "lucide-vue-next";

const props = defineProps<{ editor: any }>();

// Needed for heading icon
const isHeadingActive = computed(() =>
  [1, 2, 3, 4, 5, 6].some((level) =>
    props.editor?.isActive("heading", { level })
  )
);

// Predefined fonts
const fonts = [
  { label: "Arial", value: "Arial, Helvetica, sans-serif" },
  { label: "Times New Roman", value: '"Times New Roman", serif' },
  { label: "Courier New", value: '"Courier New", monospace' },
  { label: "Calibri", value: "Calibri, sans-serif" },
  { label: "Cambria", value: "Cambria, serif" },
];

// Predefined font sizes
const sizes = [
  "9pt",
  "10pt",
  "11pt",
  "12pt",
  "14pt",
  "16pt",
  "18pt",
  "20pt",
  "24pt",
];

function promptForLink() {
  const url = window.prompt("URL");
  if (url)
    props.editor
      .chain()
      .focus()
      .extendMarkRange("link")
      .setLink({ href: url })
      .run();
}
function removeLink() {
  props.editor.chain().focus().unsetLink().run();
}
function onImageUpload(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0];
  if (!file) return;
  const r = new FileReader();
  r.onload = () =>
    props.editor
      .chain()
      .focus()
      .setImage({ src: r.result as string })
      .run();
  r.readAsDataURL(file);
}

const setFont = (family: string) => {
  props.editor.chain().focus().setFontFamily(family).run();
};

const setSize = (size: string | null) => {
  const chain = props.editor.chain().focus();

  if (size) {
    chain.setMark("textStyle", { fontSize: size }).run();
  } else {
    chain.unsetMark("textStyle").run();
  }
};

function insertToken(tok: string) {
  props.editor.chain().focus().insertContent(`%${tok}%`).run();
}

const currentFont = ref<string | null>(null);
const currentSize = ref<string | null>(null);

watchEffect(() => {
  if (!props.editor) return;
  const attrs = props.editor.getAttributes("textStyle");
  currentFont.value = attrs.fontFamily || null;
  currentSize.value = attrs.fontSize || null;
});
</script>


<style scoped>
button {
  @apply rounded p-1 hover:bg-gray-200 transition;
}
button.is-active {
  @apply bg-blue-100 text-blue-600;
}
</style>
