import { useToast } from 'primevue/usetoast'

export const useMyToast = () => {
  const toast = useToast()

  const add = (opts: {
    detail: string
    severity?: 'success' | 'info' | 'warn' | 'error' | 'secondary' | 'contrast'
    summary?: string
    life?: number
  }) => {
    toast.add({
      severity: opts.severity ?? 'info',
      summary: opts.summary ?? '',
      detail: opts.detail,
      life: opts.life ?? 3000
    })
  }

  const typed = (severity: any, summary: string) => (msg: string, life = 3000) =>
    add({ severity, summary, detail: msg, life })

  return {
    add,                    
    success:   typed('success',   'Éxito'),
    info:      typed('info',      'Información'),
    warn:      typed('warn',      'Aviso'),
    error:     typed('error',     'Error'),
    secondary: typed('secondary', 'Secundario'),
    contrast:  typed('contrast',  'Contraste')
  }
}
