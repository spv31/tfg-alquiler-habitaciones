import { useRoute, useRouter } from '#imports'
import { getFlashMessage }     from '~/composables/useFlashMessage'
import { useMyToast }          from '~/composables/useMyToast'

export const useFlashToast = () => {
  const route   = useRoute()
  const router  = useRouter()
  const toast   = useMyToast()
  const { t } = useI18n();

  const showFlash = () => {
    const flash = getFlashMessage(route.query.msg as string | undefined, t)
    if (!flash) return

    const map = {
      success : toast.success,
      info    : toast.info,
      warning : toast.warn,    
      error   : toast.error
    } as const

    map[flash.type]?.(flash.message)

    router.replace({ query: { ...route.query, msg: undefined } })
  }

  return { showFlash }
}
