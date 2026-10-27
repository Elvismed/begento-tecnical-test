Prueba técnica Elvis Rafael Medina Díaz Begento API.

Esta API maneja validaciones en request para cada campo, modelos y relaciones con Eloquent, use una base de datos ligera como es sqlite, en un caso mas real usaria PostgreSQL, test de integración, migraciones y seeds de currencies ya que no se habla de un endpoint que permita agregarlos o modificarlos decidí agregar las currencies por un seed, manejé commits por cada CRUD, muchas gracias!

# begento-tecnical-test

## Cómo ejecutar el proyecto en local

### Requisitos

- PHP 8.3 o superior
- Composer
- SQLite (incluido con PHP)

### Instalación

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

> En Linux/macOS usa `cp .env.example .env`. Si `database/database.sqlite` no existe, créalo con `type nul > database\database.sqlite` en Windows o `touch database/database.sqlite` en Linux/macOS, o acepta la creación automática al ejecutar `php artisan migrate`.

La API queda disponible en `http://localhost:8000/api`. El seed carga 15 divisas (USD, EUR, GBP, JPY, CNY, CHF, CAD, AUD, BRL, MXN, COP, ARS, CLP, PEN e INR); los productos se crean desde la API.

### Usuario de la API

El seed también crea el usuario que emite los tokens de acceso:

| Campo | Valor por defecto |
| --- | --- |
| Email | `admin@begento.test` |
| Password | `password` |

Puedes cambiarlos en `.env` (`API_USER_EMAIL`, `API_USER_PASSWORD`) antes de ejecutar `php artisan migrate --seed`.

Todas las rutas de productos y precios requieren un token Bearer. Para obtenerlo:

```bash
curl -X POST http://localhost:8000/api/auth/token \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"admin@begento.test\",\"password\":\"password\"}"
```

Respuesta:

```json
{
  "token": "1|begento_...",
  "token_type": "Bearer"
}
```

Luego envía el token en cada solicitud:

```bash
curl http://localhost:8000/api/products \
  -H "Authorization: Bearer <token>"
```

### Seguridad

- Autenticación con Sanctum mediante Bearer tokens.
- Tokens con prefijo `begento_` y expiración de 7 días (`SANCTUM_TOKEN_EXPIRATION`).
- Rate limit de 60 peticiones por minuto en la API y 5 intentos por minuto en el login.
- Respuestas `401` sin token y `429` al exceder el límite, siempre en JSON.
- `POST /api/auth/logout` revoca el token actual.
- CORS restringido por `CORS_ALLOWED_ORIGINS`.
- En producción: usa credenciales propias, `APP_DEBUG=false` y HTTPS.

### Probar con Postman

1. Importa `postman/Begento-API.postman_collection.json` en Postman.
2. Verifica las variables de la colección:
   - `baseUrl`: `http://localhost:8000/api`
   - `authEmail`: `admin@begento.test`
   - `authPassword`: `password`
   - `accessToken`: se asigna al ejecutar **Obtener token**
   - `currencyId`: `1` (USD)
   - `priceCurrencyId`: `2` (EUR)
   - `productId`: se asigna automáticamente al ejecutar **Crear producto**
3. Ejecuta **Autenticación → Obtener token**: el token se guarda solo en `accessToken` y la colección lo envía como `Authorization: Bearer {{accessToken}}`.
4. Puedes ejecutar las solicitudes en este orden que seria el camino normal, de resto se puede ejecutar en otro orden para probar validaciones:
   1. **Autenticación → Obtener token**
   2. **Productos → Listar productos**
   3. **Productos → Crear producto** (asigna `productId`)
   4. **Productos → Obtener producto**
   5. **Productos → Actualizar producto**
   6. **Productos → Precios de productos → Listar precios del producto**
   7. **Productos → Precios de productos → Crear precio del producto**
   8. **Productos → Eliminar producto** (al final, para no invalidar `productId`)

También puedes ejecutar la colección completa con el Collection Runner: **Autenticación → Productos**.

### Pruebas automatizadas

```bash
php artisan test
```

La suite usa SQLite en memoria y no afecta la base local.

