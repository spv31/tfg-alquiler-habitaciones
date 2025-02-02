export default defineI18nConfig(() => ({
  messages: {
    es: {
      register: {
        title: "Forma parte de MyRentHub",
        register_as: "Te estás registrando como",
        individual: "Individual",
        company: "Empresa",
        continue: "Continuar",
        full_name: "Nombre completo",
        company_name: "Nombre comercial",
        email: "Correo electrónico",
        password: "Contraseña",
        id_card: "DNI",
        tax_id: "NIF",
        phone: "Teléfono",
        address: "Dirección",
        go_back: "Volver atrás",
        sign_up: "Registrarse",
        errors: {
          name_required: "El nombre debe tener al menos 1 carácter.",
          email_invalid: "Por favor, introduce un correo electrónico válido.",
          password_length: "La contraseña debe tener al menos 6 caracteres.",
          identifier_required: "Este campo es obligatorio.",
          phone_required: "El número de teléfono es obligatorio y solo debe contener números.",
          address_required: "La dirección debe tener al menos 5 caracteres.",
          registration_failed: "Error al registrar. Inténtalo de nuevo.",
        }
      },
      login: {
        title: "Inicia sesión en MyRentHub",
        email: "Correo electrónico",
        password: "Contraseña",
        sign_in: "Iniciar sesión",
        forgot_password: "¿Has olvidado tu contraseña?",
        success: "Inicio de sesión exitoso.",
        errors: {
          fields_required: "Todos los campos son obligatorios.",
          invalid_credentials: "Correo o contraseña incorrectos.",
        }
      },
      forgot_password: {
        title: "Recuperar contraseña",
        email: "Correo electrónico",
        submit: "Enviar",
        back_to_login: "Volver a iniciar sesión",
        success: "Se ha enviado un enlace para recuperar tu contraseña.",
        errors: {
          email_required: "El correo electrónico es obligatorio.",
          failed: "No se pudo enviar el enlace de recuperación. Inténtalo de nuevo.",
        }
      }
    },
    en: {
      register: {
        title: "Join MyRentHub",
        register_as: "Registering as",
        individual: "Individual",
        company: "Company",
        continue: "Continue",
        full_name: "Full Name",
        company_name: "Company Name",
        email: "Email",
        password: "Password",
        id_card: "ID Card",
        tax_id: "Tax ID",
        phone: "Phone",
        address: "Address",
        go_back: "Go Back",
        sign_up: "Sign Up",
        errors: {
          name_required: "The name must be at least 1 character long.",
          email_invalid: "Please enter a valid email address.",
          password_length: "The password must be at least 6 characters long.",
          identifier_required: "This field is required.",
          phone_required: "The phone number is required and should contain only numbers.",
          address_required: "The address must be at least 5 characters long.",
          registration_failed: "Registration failed. Please try again.",
        }
      },
      login: {
        title: "Sign in to MyRentHub",
        email: "Email",
        password: "Password",
        sign_in: "Sign In",
        forgot_password: "Forgot your password?",
        success: "Login successful.",
        errors: {
          fields_required: "All fields are required.",
          invalid_credentials: "Invalid email or password.",
        }
      },
      forgot_password: {
        title: "Recover Password",
        email: "Email",
        submit: "Send",
        back_to_login: "Back to login",
        success: "A password recovery link has been sent.",
        errors: {
          email_required: "Email is required.",
          failed: "Failed to send the recovery link. Please try again.",
        }
      }
    }
  }
}));
