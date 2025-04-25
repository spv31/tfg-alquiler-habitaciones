import { Mark } from "@tiptap/vue-3";

export const TokenMark = Mark.create({
  name: 'token',

  addOptions() {
    return {
      HTMLAttributes: { class: 'tt-token' },
    }
  },

  addAttributes() {
    return {
      key: { default: null },     
    }
  },

  parseHTML() {
    return [{ tag: 'span[data-token]' }]
  },

  renderHTML({ HTMLAttributes }) {
    return ['span', { ...HTMLAttributes, 'data-token': HTMLAttributes.key }, 0]
  },
})