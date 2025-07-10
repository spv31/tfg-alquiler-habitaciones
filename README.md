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
````

### 2 – Backend (Laravel)

```bash
cd backend
composer install
cp .env.example .env
```

#### 2.1. Crea la base de datos MySQL y el usuario

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

# Broadcasting (Laravel Reverb)
BROADCAST_CONNECTION=reverb
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_APP_ID=myrenthub
REVERB_APP_KEY=local
REVERB_APP_SECRET=local

# Stripe
# STRIPE_KEY y STRIPE_SECRET se generan al crear una cuenta de Stripe
STRIPE_KEY=pk_test_xxxxxxxx
STRIPE_SECRET=sk_test_xxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxx
```

#### 2.3. Genera la clave de la aplicación

```bash
php artisan key:generate
```

#### 2.4. (Opcional) Publica config de Snappy

```bash
php artisan vendor:publish --provider="Barryvdh\Snappy\ServiceProvider"
```

Luego ajusta `config/snappy.php`:

```php
'binary' => base_path('vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64'),
```

#### 2.5. Ejecuta migraciones & seeders

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

### 4 – Stripe CLI (webhooks en local)

```bash
stripe login
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
