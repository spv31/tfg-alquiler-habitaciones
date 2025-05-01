<template>
  <ClientOnly>
    <BubbleMenu
      :editor="editor"
      :tippy-options="{ offset: [0, 8] }"
      v-if="editor"
    >
      <BubbleToolbar :editor="editor" :applyToken="applyToken" />
    </BubbleMenu>

    <div class="flex justify-center gap-4">
      <div
        class="flex flex-col border rounded-2xl w-full max-w-3xl min-h-[400px]"
      >
        <Toolbar :editor="editor" />
        <div class="min-h-[600px] bg-white p-14 rounded-b-2xl">
          <EditorContent
            :editor="editor"
            class="prose max-w-none grow overflow-y-auto prose-p:my-0"
          />
        </div>
      </div>
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
import {
  Table as TipTapTable,
  type TableOptions,
} from "@tiptap/extension-table";
import TableRow from "@tiptap/extension-table-row";
import TableHeader from "@tiptap/extension-table-header";
import TableCell from "@tiptap/extension-table-cell";
import { EditorContent, BubbleMenu } from "@tiptap/vue-3";
import BubbleMenuExtension from "@tiptap/extension-bubble-menu";

import Toolbar from "./Toolbar.vue";
import TokenSidebar from "./TokenSidebar.vue";
import { TokenMark } from "~/utils/tokenMark";

const CustomTable = TipTapTable.extend<TableOptions>({
  name: "table",
  addOptions() {
    return {
      ...this.parent?.(),
      resizable: true,
      HTMLAttributes: { class: "tt-table" },
    };
  },
  addNodeView() {
    const original = this.parent?.()!.addNodeView?.();
    return (props) => {
      const view = original?.(props);
      if (view && view.dom.nodeName === "TABLE") {
        view.dom.classList.add("tt-table");
      }
      return view;
    };
  },
});
const FontSize = TextStyle.extend({
  addAttributes() {
    return {
      fontSize: {
        default: null,
        parseHTML: (element) => element.style.fontSize || null,
        renderHTML: (attributes) => {
          if (!attributes.fontSize) return {};
          return { style: `font-size: ${attributes.fontSize}` };
        },
      },
    };
  },
});

const props = defineProps<{
  modelValue: string;
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
      types: ["heading", "paragraph"],
    }),
    CustomTable,
    TableRow,
    TableHeader,
    TableCell,
    TokenMark,
    BubbleMenuExtension.configure({
      tippyOptions: { offset: [0, 8] },
    }),
  ],
  editorProps: {
    attributes: {
      class: `focus:outline-none`,
    },
  },
  onUpdate: ({ editor }) => emit("update:modelValue", editor.getHTML()),
});

/**
 * Function to apply different kind of tokens to sections of the contract template
 *
 * @param tokenKey
 */
const applyToken = (tokenKey: string) => {
  const { state } = editor;
  const hasSelection = !state.selection.empty;

  if (hasSelection) {
    editor.chain().focus().setMark("token", { key: tokenKey }).run();
  } else {
    const placeholder = "__________";
    editor
      .chain()
      .focus()
      .insertContent({
        type: "text",
        text: placeholder,
        marks: [{ type: "token", attrs: { key: tokenKey } }],
      })
      .run();
  }
};
</script>

<style>
.prose {
  font-family: Arial, Helvetica, sans-serif;
  line-height: 1.5;
  font-size: 12pt;
}

.tt-table {
  width: 100%;
  border-collapse: collapse;
  margin: 1rem 0;
}
.tt-table th,
.tt-table td {
  border: 1px solid #d1d5db;
  padding: 6px 8px;
}
.tt-table th {
  background: #f3f4f6;
  font-weight: 600;
  text-align: left;
}
.tt-table tbody tr:nth-child(even) {
  background: #fafafa;
}

.tt-table-wrapper {
  overflow: auto;
}

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

/* Needed for tokens */
.tt-token[data-token] {
  background: #fff7d6;
}

/* To indicate token in editor */
.ProseMirror span[data-token] {
  position: relative;
  cursor: help;
}
.ProseMirror span[data-token]::after {
  content: attr(data-token);
  position: absolute;
  bottom: 100%;
  left: 0;
  white-space: nowrap;
  background: #333;
  color: #fff;
  padding: 2px 5px;
  font-size: 10px;
  border-radius: 4px;
  opacity: 0;
  transform: translateY(-5px);
  transition:
    opacity 0.2s,
    transform 0.2s;
  pointer-events: none;
}
.ProseMirror span[data-token]:hover::after {
  opacity: 1;
  transform: translateY(-10px);
}

/* PDF tokens */
@media print {
  .tt-token[data-token] {
    background: transparent;
  }
}

#bubble-menu .dropdown-panel {
  @apply shadow-md bg-white rounded-lg;
}
#bubble-menu button {
  @apply p-1 rounded hover:bg-gray-100;
}
</style>
