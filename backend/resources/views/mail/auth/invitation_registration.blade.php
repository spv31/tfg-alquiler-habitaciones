<x-mail::message>
  # Hola,

  Has recibido una invitación para formar parte del proceso de arrendamiento mediante nuestra plataforma.

  **Tipo de invitación:** Has sido invitado para gestionar **{{ $invitationType }}**.

  **Ubicación:** {{ $locationDescription }}

  A través de nuestra aplicación podrás:
  - Consultar y gestionar tu contrato de arrendamiento.
  - Comunicarte directamente con el propietario.
  - Realizar pagos y ver detalles de tu alquiler.

  Para comenzar el proceso, regístrate haciendo clic en el siguiente botón:

  <x-mail::button :url="$registerUrl">
    Registrarse en la Plataforma
  </x-mail::button>

  Si no reconoces esta invitación o no solicitaste arrendar, ignora este mensaje.

  Saludos,
  **El equipo de soporte**
  {{ config('app.name') }}
</x-mail::message>
