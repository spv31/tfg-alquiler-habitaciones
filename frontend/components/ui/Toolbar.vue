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

    <DropdownToolbar
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
      :isActive="(l: any) => editor.isActive('heading', { level: l })"
      @select="(l) => editor.chain().focus().toggleHeading({ level: l }).run()"
    />

    <ToolbarBtn
      :active="editor.isActive('paragraph')"
      @click="editor.chain().focus().setParagraph().run()"
      >¶</ToolbarBtn
    >

    <DropdownToolbar icon :active="currentFont !== null">
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
    </DropdownToolbar>

    <DropdownToolbar icon :active="currentSize != null">
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
    </DropdownToolbar>

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

    <DropdownToolbar icon>
      <template #button>
        <AlignLeft size="18" />
      </template>

      <button
        @click="editor.chain().focus().setTextAlign('left').run()"
        :class="{ 'bg-gray-200': editor.isActive({ textAlign: 'left' }) }"
        class="px-3 py-1 w-full text-left hover:bg-gray-100"
        :style="{ fontFamily: currentFont }"
      >
        <AlignLeft size="18" class="inline mr-2" /> Izquierda
      </button>
      <button
        @click="editor.chain().focus().setTextAlign('center').run()"
        :class="{ 'bg-gray-200': editor.isActive({ textAlign: 'center' }) }"
        class="px-3 py-1 w-full text-left hover:bg-gray-100"
        :style="{ fontFamily: currentFont }"
      >
        <AlignCenter size="18" class="inline mr-2" /> Centro
      </button>
      <button
        @click="editor.chain().focus().setTextAlign('right').run()"
        :class="{ 'bg-gray-200': editor.isActive({ textAlign: 'right' }) }"
        class="px-3 py-1 w-full text-left hover:bg-gray-100"
        :style="{ fontFamily: currentFont }"
      >
        <AlignRight size="18" class="inline mr-2" /> Derecha
      </button>
      <button
        @click="editor.chain().focus().setTextAlign('justify').run()"
        :class="{ 'bg-gray-200': editor.isActive({ textAlign: 'justify' }) }"
        class="px-3 py-1 w-full text-left hover:bg-gray-100"
        :style="{ fontFamily: currentFont }"
      >
        <AlignJustify size="18" class="inline mr-2" /> Justificado
      </button>
    </DropdownToolbar>

    <ToolbarBtn
      :active="editor.isActive('blockquote')"
      @click="editor.chain().focus().toggleBlockquote().run()"
      ><Quote size="18"
    /></ToolbarBtn>
    <ToolbarBtn @click="editor.chain().focus().setHorizontalRule().run()"
      ><Minus size="18"
    /></ToolbarBtn>

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

    <ToolbarBtn @click="editor.chain().focus().unsetAllMarks().run()"
      ><Eraser size="18"
    /></ToolbarBtn>
    <ToolbarBtn @click="editor.chain().focus().clearNodes().run()"
      ><Layout size="18"
    /></ToolbarBtn>

    <DropdownToolbar icon :active="inTable()">
      <template #button>
        <Table size="18" />
      </template>

      <button
        @click="insertDefaultTable"
        class="px-3 py-1 w-full text-left hover:bg-gray-100"
      >
        <Table size="16" class="inline mr-2" /> Insertar tabla
      </button>
      <button
        :disabled="!inTable()"
        @click="deleteTable"
        class="px-3 py-1 w-full text-left hover:bg-gray-100 disabled:opacity-40"
      >
        <Trash2 size="16" class="inline mr-2" /> Eliminar tabla
      </button>

      <hr class="my-1 border-gray-200" />

      <button
        :disabled="!inTable()"
        @click="addRowBefore"
        class="px-3 py-1 w-full text-left hover:bg-gray-100 disabled:opacity-40"
      >
        <Rows size="16" class="inline mr-2" /> Fila arriba
      </button>
      <button
        :disabled="!inTable()"
        @click="addRowAfter"
        class="px-3 py-1 w-full text-left hover:bg-gray-100 disabled:opacity-40"
      >
        <Rows3 size="16" class="inline mr-2" /> Fila abajo
      </button>
      <button
        :disabled="!inTable()"
        @click="deleteRow"
        class="px-3 py-1 w-full text-left hover:bg-gray-100 disabled:opacity-40"
      >
        <Trash2 size="16" class="inline mr-2" /> Borrar fila
      </button>

      <hr class="my-1 border-gray-200" />

      <!-- Columnas -->
      <button
        :disabled="!inTable()"
        @click="addColBefore"
        class="px-3 py-1 w-full text-left hover:bg-gray-100 disabled:opacity-40"
      >
        <Columns size="16" class="inline mr-2" /> Columna izq.
      </button>
      <button
        :disabled="!inTable()"
        @click="addColAfter"
        class="px-3 py-1 w-full text-left hover:bg-gray-100 disabled:opacity-40"
      >
        <Columns3 size="16" class="inline mr-2" /> Columna der.
      </button>
      <button
        :disabled="!inTable()"
        @click="deleteCol"
        class="px-3 py-1 w-full text-left hover:bg-gray-100 disabled:opacity-40"
      >
        <Trash2 size="16" class="inline mr-2" /> Borrar columna
      </button>

      <hr class="my-1 border-gray-200" />

      <button
        :disabled="!inTable()"
        @click="toggleHeaderRow"
        class="px-3 py-1 w-full text-left hover:bg-gray-100 disabled:opacity-40"
      >
        <Bold size="16" class="inline mr-2" /> Cabecera on/off
      </button>
      <button
        :disabled="!canMerge()"
        @click="mergeCells"
        class="px-3 py-1 w-full text-left hover:bg-gray-100 disabled:opacity-40"
      >
        <TableCellsMerge size="16" class="inline mr-2" /> Combinar celdas
      </button>
      <button
        :disabled="!canSplit()"
        @click="splitCell"
        class="px-3 py-1 w-full text-left hover:bg-gray-100 disabled:opacity-40"
      >
        <TableCellsSplit size="16" class="inline mr-2" /> Dividir celda
      </button>
    </DropdownToolbar>

    <ToolbarBtn
      tooltip="Insertar zona de firmas"
      @click="insertSignatureSection"
    >
      <FileMinus size="18" />
    </ToolbarBtn>

    <DropdownToolbar icon>
      <template #button>
        <TextCursorInput size="18" />
      </template>

      <div class="max-h-48 overflow-y-auto">
        <button
          v-for="tok in predefinedTokens"
          :key="tok.token"
          @click="applyToken(tok.token)"
          class="px-3 py-1 w-full text-left hover:bg-gray-100"
          style="font-family: Arial, Helvetica, sans-serif; font-size: 12pt"
        >
          {{ tok.label }}
        </button>
      </div>
    </DropdownToolbar>
  </div>
</template>
<script setup lang="ts">
import { predefinedTokens } from "~/utils/tokens";
import {
  Type,
  TextCursorInput,
  Bold,
  Italic,
  Underline,
  Strikethrough,
  Code,
  List,
  ListOrdered,
  Quote,
  Minus,
  Undo2,
  Redo2,
  Image,
  AlignLeft,
  AlignCenter,
  AlignRight,
  AlignJustify,
  Eraser,
  Layout,
  Table,
  Rows,
  Rows3,
  Columns,
  Columns3,
  TableCellsMerge,
  TableCellsSplit,
  Trash2,
  Trash,
  FileMinus,
} from "lucide-vue-next";

const props = defineProps<{ editor: any }>();

const currentFont = ref<string | null>(null);
const currentSize = ref<string | null>(null);

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

// Helper to set font family
const setFont = (family: string | null) => {
  props.editor.chain().focus().setFontFamily(family).run();
};

// Helper to set font size
const setSize = (size: string | null) => {
  const chain = props.editor.chain().focus();

  if (size) {
    chain.setMark("textStyle", { fontSize: size }).run();
  } else {
    chain.unsetMark("textStyle").run();
  }
};

/**
 * Function to apply different kind of tokens to sections of the contract template
 * 
 * @param tokenKey 
 */
const applyToken = (tokenKey: string) => {
  const { state } = props.editor
  const hasSelection = !state.selection.empty

  if (hasSelection) {
    props.editor
      .chain()
      .focus()
      .setMark('token', { key: tokenKey })
      .run()
  } else {
    const placeholder = '__________'     
    props.editor
      .chain()
      .focus()
      .insertContent({
        type: 'text',
        text: placeholder,
        marks: [ { type: 'token', attrs: { key: tokenKey } } ],
      })
      .run()
  }
}


const insertSignatureSection = () => {
  props.editor
    .chain()
    .focus()
    .insertTable({ rows: 1, cols: 2, withHeaderRow: false })
    .insertContent(
      `
      <p class="text-center">
        El Arrendador<br/><br/>
        ___________________________<br/>
        Nombre y firma
      </p>
    `
    )
    .goToNextCell()
    .insertContent(
      `
      <p class="text-center">
        El Arrendatario<br/><br/>
        ___________________________<br/>
        Nombre y firma
      </p>
    `
    )
    .run();
};

const insertDefaultTable = () =>
  props.editor
    .chain()
    .focus()
    .insertTable({ rows: 2, cols: 2, withHeaderRow: true })
    .run();

const addRowBefore = () => props.editor.chain().focus().addRowBefore().run();
const addRowAfter = () => props.editor.chain().focus().addRowAfter().run();
const deleteRow = () => props.editor.chain().focus().deleteRow().run();

const addColBefore = () => props.editor.chain().focus().addColumnBefore().run();
const addColAfter = () => props.editor.chain().focus().addColumnAfter().run();
const deleteCol = () => props.editor.chain().focus().deleteColumn().run();

const toggleHeaderRow = () =>
  props.editor.chain().focus().toggleHeaderRow().run();
const mergeCells = () => props.editor.chain().focus().mergeCells().run();
const splitCell = () => props.editor.chain().focus().splitCell().run();
const deleteTable = () => props.editor.chain().focus().deleteTable().run();

const inTable = () => props.editor.can().deleteTable();
const canMerge = () => props.editor.can().mergeCells();
const canSplit = () => props.editor.can().splitCell();

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
