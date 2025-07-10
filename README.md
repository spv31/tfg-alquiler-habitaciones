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

> **Tip (macOS / Linux)**
> `brew install wkhtmltopdf` instala rápidamente los binarios de *wkhtmltopdf*.

---

## Instalación

### 1 – Clona el repositorio

```bash
git clone https://github.com/spv31/tfg-alquiler-habitaciones.git
cd tfg-alquiler-habitaciones
```

### 2 – Backend (Laravel)

```bash
cd backend

# Dependencias PHP
composer install

# Copia .env de ejemplo y genera APP_KEY
cp .env.example .env
php artisan key:generate
```

1. **Edita `.env`** y rellena:

   ```dotenv
   APP_URL=http://localhost:8000
   FRONTEND_URL=http://localhost:3000
   SANCTUM_STATEFUL_DOMAINS=localhost:3000
   
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=myrenthub
   DB_USERNAME=root
   DB_PASSWORD=secret

   # Broadcasting (Laravel Reverb)
   BROADCAST_CONNECTION=reverb
   REVERB_HOST=127.0.0.1
   REVERB_PORT=8080
   REVERB_APP_ID=myrenthub
   REVERB_APP_KEY=local
   REVERB_APP_SECRET=local

   # Stripe
   STRIPE_KEY=pk_test_xxxxxxxxxxxxxxxxx
   STRIPE_SECRET=sk_test_xxxxxxxxxxxxxxxxx
   STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxxxxxx
   ```

2. **Publica la configuración de Laravel Snappy** (opcional si quieres cambiar la ruta del binario instalado wkhtmltopdf):

   ```bash
   php artisan vendor:publish --provider="Barryvdh\Snappy\ServiceProvider"
   # edita config/snappy.php → 'binary' => base_path('vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64')
   ```

3. **Migraciones + seeds**:

   ```bash
   php artisan migrate --seed
   ```

### 3 – Frontend (Nuxt 3)

```bash
cd ../frontend
pnpm install
```

### 4 – Stripe CLI (utilizar webhooks en local)

```bash
stripe login
stripe listen --forward-to http://127.0.0.1:8000/api/stripe/webhook
```

Copia el valor que aparece como **Webhook signing secret** y pégalo en `STRIPE_WEBHOOK_SECRET` dentro de `backend/.env`.

---

## Arrancar en local 

> Abre **tres** terminales.

| Terminal | Comando                                                               | Carpeta     |
| -------- | ----------------------------------------------------------------------| ----------- |
| **#1**   | `php artisan serve`                                                   | `backend/`  |
| **#2**   | `php artisan reverb:start --host=127.0.0.1 --port=8080 --debug`       | `backend/`  |
| **#3**   | `stripe listen --forward-to http://127.0.0.1:8000/api/stripe/webhook` | ``  |
| **#4**   | `pnpm dev`                                                            | `frontend/` |

Ejecutar el tercer comando en caso de haberlo cerrado anteriormente

Accede a [http://localhost:3000](http://localhost:3000) para usar la aplicación.

---

## Tests

```bash
cd backend
php artisan test
```

---

---

## Licencia

Código bajo licencia **MIT**.
© 2025 – Sergio Pérez (spv31)

