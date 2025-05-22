import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'
import { useI18n } from 'vue-i18n'

export function useMyConfirm() {
  const confirm = useConfirm()
  const toast = useToast()
  const { t } = useI18n()

  function show(options: {
    message: string
    header?: string
    icon?: string
    acceptLabel?: string
    rejectLabel?: string
    onAccept?: () => void
    onReject?: () => void
    acceptSeverity?: string
    rejectSeverity?: string
  }) {
    confirm.require({
      message: options.message,
      header: options.header ?? t('common.confirmation'),
      icon: options.icon ?? 'pi pi-question-circle',
      rejectProps: {
        label: options.rejectLabel ?? t('common.cancel'),
        severity: options.rejectSeverity ?? 'secondary',
        outlined: true,
      },
      acceptProps: {
        label: options.acceptLabel ?? t('common.accept'),
        severity: options.acceptSeverity ?? 'primary',
      },
      accept: () => {
        options.onAccept?.()
      },
      reject: () => {
        options.onReject?.()
        toast.add({
          severity: 'warn',
          summary: t('common.cancel'),
          detail: t('common.actionCancelled'),
          life: 3000,
        })
      },
    })
  }

  return { show }
}
