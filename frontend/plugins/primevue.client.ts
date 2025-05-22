import { defineNuxtPlugin } from '#app'
import ConfirmationService from 'primevue/confirmationservice'
import ToastService        from 'primevue/toastservice'   

export default defineNuxtPlugin(nuxtApp => {
  nuxtApp.vueApp
    .use(ToastService)     
})
