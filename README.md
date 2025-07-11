# TFG: Gestión de Alquileres de Viviendas y Habitaciones 

## 📋 Requisitos

| Herramienta                         | Versión mínima    | Notas                                |
| ----------------------------------- | ----------------- | ------------------------------------ |
| PHP                                 | **8.2**           | Laravel 11                           |
| Composer                            | —                 | Dependencias PHP                     |
| Node.js                             | **18**            |                                      |
| pnpm                                | **9**             | Gestor de paquetes para proyecto JS  |
| MySQL                               | **8.0**           | Gestor de base de datos utilizado    |
| Git                                 | —                 |                                      |
| **wkhtmltopdf** / **wkhtmltoimage** | `0.12.x`          | PDFs (Laravel Snappy)                |
| **Stripe CLI**                      | `1.28` o superior | Reenvío de webhooks en local         |

---

## Instalación

### 1 – Clona el repositorio

```bash
git clone https://github.com/spv31/tfg-alquiler-habitaciones.git
cd tfg-alquiler-habitaciones
````

### 2 – Backend (Laravel)

```bash
cd backend
composer install
cp .env.example .env
```

#### 2.1. Crea la base de datos MySQL y el usuario (Linux y Mac)
En caso de utilizar Windows, es recomendable utilizar XAMPP para instalar PHP y MySQL, además de poder utilizar el panel de administrador incluido para MySQL.

```bash
# Entra al cliente de MySQL
mysql -u root -p
```

```sql
-- Dentro de MySQL
CREATE DATABASE myrenthub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'myrenthub_user'@'localhost' IDENTIFIED BY 'strongpassword';
GRANT ALL PRIVILEGES ON myrenthub.* TO 'myrenthub_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### 2.2. Edita `.env` y configura tus credenciales

Abre `backend/.env` y rellena los datos de conexión y servicios:

```dotenv
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000
SANCTUM_STATEFUL_DOMAINS=localhost:3000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=myrenthub
DB_USERNAME=myrenthub_user
DB_PASSWORD=strongpassword

# Broadcasting (Laravel Reverb) - Especificar las mismas claves para no modificar el cliente Echo
BROADCAST_CONNECTION=reverb
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_APP_ID=my-rent-hub-id
REVERB_APP_KEY=my-rent-hub-key
REVERB_APP_SECRET=my-rent-hub-secret

# Stripe
# STRIPE_KEY y STRIPE_SECRET se generan al crear una cuenta de Stripe
STRIPE_KEY=pk_test_xxxxxxxx
STRIPE_SECRET=sk_test_xxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxx

# SMTP (Mailtrap u otro servicio de pruebas) - Se debe crear una cuenta y obtener las claves
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_user
MAIL_PASSWORD=your_mailtrap_pass
MAIL_FROM_ADDRESS="myrenthub@example.com"
MAIL_FROM_NAME="MyRentHub"

# Snappy (wkhtmltopdf)
# Opción A – utilizar binarios instalados con composer, para ello comentar las dos variables de entorno (Linux y Mac)
# WKHTML_PDF_BINARY=vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64
# WKHTML_IMG_BINARY=vendor/h4cc/wkhtmltoimage-amd64/bin/wkhtmltoimage-amd64
#
# Opción B – utilizar los binarios instalados globalmente y escribir ruta absoluta (Para Windows)
# Windows ejemplo (rutas con espacios: **doble escape + comillas dobles**)
# WKHTML_PDF_BINARY="\"\"C:\\Program Files\\wkhtmltopdf\\bin\\wkhtmltopdf.exe\"\""
# WKHTML_IMG_BINARY="\"\"C:\\Program Files\\wkhtmltopdf\\bin\\wkhtmltoimage.exe\"\""
```

#### 2.3. Genera la clave de la aplicación

```bash
php artisan key:generate
```

#### 2.4. Publica config de Snappy (en caso de utilizar los binarios instalados con composer)

Laravel Snappy apunta por defecto a `/usr/local/bin/wkhtmltopdf`.  
Si prefieres usar los binarios incluidos por Composer (`h4cc/...`), sigue estos pasos:

1. Publica la configuración (solo la primera vez):

   ```bash
   php artisan vendor:publish \
     --provider="Barryvdh\Snappy\ServiceProvider" --tag=config
    ````

2. Abre `config/snappy.php` y reemplaza las claves `binary` en caso de que no sean las siguientes:

   ```php
   'pdf' => [
       'enabled' => true,
       'binary'  => env(
           'WKHTML_PDF_BINARY',
           base_path('vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64')
       ),
   ],

   'image' => [
       'enabled' => true,
       'binary'  => env(
           'WKHTML_IMG_BINARY',
           base_path('vendor/h4cc/wkhtmltoimage-amd64/bin/wkhtmltoimage-amd64')
       ),
   ],
   ```

3. En el `.env` **comenta o elimina** las rutas si usas los binarios de Composer
   (Snappy utilizará las de arriba).
   Solo define rutas si vas a emplear los binarios globales del sistema:

   ```dotenv
   # Para binarios globales (ejemplo)
   # WKHTML_PDF_BINARY="\"\"C:\\Program Files\\wkhtmltopdf\\bin\\wkhtmltopdf.exe\"\""
   # WKHTML_IMG_BINARY="\"\"C:\\Program Files\\wkhtmltopdf\\bin\\wkhtmltoimage.exe\"\""
   ```

4. Limpia la caché de configuración:

   ```bash
   php artisan config:clear
   ```

#### 2.5. Ejecuta migraciones & seeders (para listar las plantillas de contratos estándar)

```bash
php artisan migrate --seed
```

---

### 3 – Frontend (Nuxt 3)

```bash
cd ../frontend
pnpm install
```

---

Aquí tienes los pasos reorganizados con un formato uniforme:

### 4 – Stripe CLI (webhooks en local)

#### Instalación

##### macOS

```bash
brew install stripe/stripe-cli/stripe
```

##### Linux (sin gestor de paquetes)

1. Descarga el archivo `tar.gz` desde:
   [https://github.com/stripe/stripe-cli/releases/latest](https://github.com/stripe/stripe-cli/releases/latest)

2. Descomprime:

   ```bash
   tar -xvf stripe_X.X.X_linux_x86_64.tar.gz
   ```

3. Mueve el ejecutable:

   ```bash
   sudo mv stripe /usr/local/bin/
   sudo chmod +x /usr/local/bin/stripe
   ```

4. Verifica:

   ```bash
   stripe version
   ```

##### Windows

1. Descarga el ZIP desde:
   [https://github.com/stripe/stripe-cli/releases/latest](https://github.com/stripe/stripe-cli/releases/latest)

2. Extrae `stripe.exe` en una carpeta como `C:\stripe-cli\`.

3. Añádela al `Path` de tu usuario. En PowerShell:

   ```powershell
   [Environment]::SetEnvironmentVariable("Path", $Env:Path + ";C:\stripe-cli", "User")
   ```

4. Cierra la terminal, vuelve a abrirla y ejecuta:

   ```powershell
   stripe version
   ```

> Más información en la documentación oficial:
> [https://docs.stripe.com/stripe-cli](https://docs.stripe.com/stripe-cli)


---

#### Autenticación y escucha de webhooks

1. Iniciar sesión en Stripe CLI:

   ```bash
   stripe login
   ```
2. Escuchar eventos y reenviar webhooks al endpoint local:

   ```bash
   stripe listen --forward-to http://127.0.0.1:8000/api/stripe/webhook
   ```


Copia el **Webhook signing secret** que te muestre Stripe CLI y pégalo en `backend/.env` en `STRIPE_WEBHOOK_SECRET`.

---

## Arrancar en local

> Abre **cuatro** terminales:

| Terminal | Comando                                                               | Carpeta     |
| -------- | --------------------------------------------------------------------- | ----------- |
| **#1**   | `php artisan serve`                                                   | `backend/`  |
| **#2**   | `php artisan reverb:start --host=127.0.0.1 --port=8080 --debug`       | `backend/`  |
| **#3**   | `stripe listen --forward-to http://127.0.0.1:8000/api/stripe/webhook` | `backend/`  |
| **#4**   | `pnpm dev`                                                            | `frontend/` |

Luego visita [http://localhost:3000](http://localhost:3000).

---

## Tests

```bash
cd backend
php artisan test
```

---

## Licencia

Código bajo licencia **MIT**.
© 2025 – Sergio Pérez (spv31)
