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

import Toolbar from "./Toolbar.vue";
import TokenSidebar from "./TokenSidebar.vue";

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
    Text,
    Placeholder,
    Underline, 
    TextStyle, 
    FontFamily,  
    FontSize,
  ],
  editorProps: {
    attributes: {
      class: `focus:outline-none`,
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
</style>