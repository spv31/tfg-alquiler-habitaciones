import { defineNuxtPlugin } from '#app'
import StarterKit      from '@tiptap/starter-kit'
import Placeholder     from '@tiptap/extension-placeholder'

export default defineNuxtPlugin(() => {
  return {
    provide: {
      tiptapExtensions: [
        StarterKit,
        Placeholder.configure({ placeholder: 'Escribe tu contrato…' }),
      ],
    },
  }
})
