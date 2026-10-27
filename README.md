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

### Probar con Postman

1. Importa `postman/Begento-API.postman_collection.json` en Postman.
2. Verifica las variables de la colección:
   - `baseUrl`: `http://localhost:8000/api`
   - `currencyId`: `1` (USD)
   - `priceCurrencyId`: `2` (EUR)
   - `productId`: se asigna automáticamente al ejecutar **Crear producto**
3. Puedes ejecutar las solicitudes en este orden que seria el camino normal, de resto se puede ejecutar en otro orden para probar validaciones:
   1. **Productos → Listar productos**
   2. **Productos → Crear producto** (asigna `productId`)
   3. **Productos → Obtener producto**
   4. **Productos → Actualizar producto**
   5. **Productos → Precios de productos → Listar precios del producto**
   6. **Productos → Precios de productos → Crear precio del producto**
   7. **Productos → Eliminar producto** (al final, para no invalidar `productId`)

También puedes ejecutar la carpeta **Productos** completa con el Collection Runner.

### Pruebas automatizadas

```bash
php artisan test
```

La suite usa SQLite en memoria y no afecta la base local.

