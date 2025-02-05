@component('mail::message')
  # Hola, {{ $user }}

  Has solicitado restablecer tu contraseña. Haz clic en el siguiente botón para proceder:

  @component('mail::button', ['url' => $url])
    Restablecer Contraseña
  @endcomponent

  Si no solicitaste este cambio, ignora este mensaje.

  Gracias,<br>
  {{ config('app.name') }}
@endcomponent
