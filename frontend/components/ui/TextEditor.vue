<template>
  <ClientOnly>
    <div class="flex justify-center gap-4">
      <div
        class="flex flex-col border rounded-2xl w-full max-w-2xl min-h-[400px]"
      >
        <Toolbar :editor="editor" />
        <div class="min-h-[600px] bg-white p-14 rounded-b-2xl">
          <TiptapEditorContent
            :editor="editor"
            class="prose max-w-none grow overflow-y-auto 
                   prose-p:my-0"
          />
        </div>
      </div>
      <!-- <TokenSidebar :tokens="tokens" @insert="insert" class="shrink-0" /> -->
    </div>
  </ClientOnly>
</template>
	
<script setup lang="ts">
import { useEditor } from "#imports";
import StarterKit from "@tiptap/starter-kit";
import Placeholder from "@tiptap/extension-placeholder";
import Underline from "@tiptap/extension-underline";
import FontFamily from "@tiptap/extension-font-family";
import TextStyle from "@tiptap/extension-text-style";
import TextAlign from "@tiptap/extension-text-align";
import { Table as TipTapTable, type TableOptions } from '@tiptap/extension-table'
import TableRow from "@tiptap/extension-table-row";
import TableHeader from "@tiptap/extension-table-header";
import TableCell from "@tiptap/extension-table-cell";

import Toolbar from "./Toolbar.vue";
import TokenSidebar from "./TokenSidebar.vue";

const CustomTable = TipTapTable.extend<TableOptions>({
  name: 'table',
  addOptions() {
    return {
      ...this.parent?.(),
      resizable: true,
      HTMLAttributes: { class: 'tt-table' },
    }
  },
  addNodeView() {
    const original = this.parent?.()!.addNodeView?.()
    return props => {
      const view = original?.(props)
      if (view && view.dom.nodeName === 'TABLE') {
        view.dom.classList.add('tt-table')
      }
      return view
    }
  },
})
const FontSize = TextStyle.extend({
  addAttributes() {
    return {
      fontSize: {
        default: null,
        parseHTML: element => element.style.fontSize || null,
        renderHTML: attributes => {
          if (!attributes.fontSize) return {};
          return { style: `font-size: ${attributes.fontSize}` };
        },
      }
    };
  }
})

const props = defineProps<{
  modelValue: string;
  placeholder?: string;
  tokens?: string[];
}>();

const emit = defineEmits(["update:modelValue"]);

const editor = useEditor({
  content: props.modelValue,
  extensions: [
    StarterKit,
    Placeholder,
    Underline, 
    TextStyle, 
    FontFamily,  
    FontSize,
    TextAlign.configure({
      types: ['heading', 'paragraph']
    }),
    CustomTable,
    TableRow,
    TableHeader,
    TableCell,
  ],
  editorProps: {
    attributes: {
      class: `focus:outline-none whitespace-normal`,
    },
  },
  onUpdate: ({ editor }) => emit("update:modelValue", editor.getHTML()),
});

// // insert token desde TokenSidebar
// function insert(token: string) {
//   editor?.commands.insertContent(`%${token}%`);
// }

onBeforeUnmount(() => editor?.destroy());
</script>

<style>
.prose {
  font-family: Arial, Helvetica, sans-serif;
  font-size: 12pt;
}

.tt-table       { width:100%; border-collapse:collapse; margin:1rem 0; }
.tt-table th,
.tt-table td    { border:1px solid #d1d5db; padding:6px 8px; }
.tt-table th    { background:#f3f4f6; font-weight:600; text-align:left; }
.tt-table tbody tr:nth-child(even) { background:#fafafa; }

.tt-table-wrapper { overflow:auto; }

/* Needed for text alignment */
.ProseMirror [style*="text-align:justify"] {
  text-align: justify !important;
  display: block;
  white-space: normal;
}
.ProseMirror p {
  white-space: normal !important;
  text-align: inherit; 
}
</style>