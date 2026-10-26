# Prueba técnica Backend — API REST de productos

API desarrollada con **Laravel 12 y MySQL** para administrar productos y registrar precios en distintas divisas. El proyecto cubre el CRUD solicitado y añade validaciones, autenticación para operaciones de escritura, paginación y pruebas de integración.

## Objetivo y alcance

La API permite:

- Crear, listar, consultar, actualizar y eliminar productos.
- Guardar el precio base de cada producto junto con su divisa, impuestos y costo de fabricación.
- Registrar precios adicionales de un producto en otras divisas.
- Consultar la información en JSON mediante endpoints REST.

La aplicación está orientada al **backend**; no incluye interfaz gráfica. Se adjunta una colección de Postman para facilitar la revisión funcional.

## Tecnologías utilizadas

| Componente | Tecnología |
| --- | --- |
| Framework | Laravel 12 |
| Lenguaje | PHP 8.2 o superior |
| Base de datos | MySQL 8 o MariaDB compatible |
| Acceso a datos | Eloquent ORM |
| Autenticación | Laravel Sanctum, tokens Bearer |
| Validación | Form Requests |
| Respuestas | API Resources (JSON) |
| Pruebas | PHPUnit / pruebas Feature de Laravel |
| Documentación de peticiones | Colección Postman |

## Estructura y decisiones de diseño

```text
app/
├── Console/Commands/CreateApiUser.php       # Alta local del usuario que accede a la API
├── Http/
│   ├── Controllers/Api/                     # Acciones HTTP de autenticación, productos y divisas
│   ├── Requests/                             # Validación de entradas
│   └── Resources/                            # Formato de las respuestas JSON
└── Models/                                   # Modelos y relaciones Eloquent
database/
├── migrations/                               # Definición versionada del esquema
├── seeders/                                  # Información inicial de ejemplo
└── sql/                                      # Alternativa de importación con MySQL
postman/                                      # Colección para realizar pruebas manuales
routes/api.php                                # Definición de endpoints
 tests/Feature/ProductsApiTest.php            # Casos de prueba de la API
```

Para mantener responsabilidades separadas, los controladores coordinan las peticiones, los *Form Requests* validan los datos, Eloquent gestiona la persistencia y los *API Resources* definen qué se devuelve al cliente. Las rutas usan *route model binding* para recuperar productos por su ID.

### Modelo de datos

| Tabla | Responsabilidad | Relación |
| --- | --- | --- |
| `currencies` | Divisas y tasa de cambio de referencia | Una divisa puede estar asociada a varios productos o precios |
| `products` | Datos del producto, precio base, impuestos y fabricación | Cada producto pertenece a una divisa base |
| `product_prices` | Precios adicionales registrados para otras divisas | Cada registro pertenece a un producto y a una divisa |
| `users` | Usuario para autenticarse | Puede obtener tokens de acceso |
| `personal_access_tokens` | Tokens administrados por Sanctum | Vinculados al usuario autenticado |

Las claves foráneas preservan la integridad referencial. Existe una restricción única sobre `product_prices (product_id, currency_id)` para evitar dos precios adicionales de la misma divisa. Al eliminar un producto, sus precios adicionales se eliminan en cascada.

## Instalación local

### 1. Requisitos

- PHP 8.2+ con extensiones habituales de Laravel, incluyendo `pdo_mysql`.
- Composer 2.
- MySQL 8+ o MariaDB compatible.
- Opcional: Postman y extensión PHP `pdo_sqlite` para ejecutar las pruebas automatizadas.

El repositorio no incluye `vendor/` ni credenciales. Las dependencias se instalan con Composer.

### 2. Instalar dependencias

```bash
composer install
```

Si se descarga el proyecto sin un `composer.lock` inicial, Composer resolverá las versiones compatibles y generará ese archivo localmente. Para una entrega reproducible, conviene incluir el `composer.lock` generado y revisado en el repositorio.

### 3. Crear el archivo de configuración

En **PowerShell (Windows)**:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

En **Linux / macOS**:

```bash
cp .env.example .env
php artisan key:generate
```

Configurar la conexión en `.env`:

```dotenv
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prueba_backend
DB_USERNAME=root
DB_PASSWORD=
```

Las credenciales son ilustrativas. Usar las correspondientes a la instalación local. En producción se debe utilizar `APP_DEBUG=false` y HTTPS.

### 4. Preparar la base de datos

**Opción A — Migraciones y seeder (recomendada):**

Crear una base vacía `prueba_backend` desde MySQL o ejecutando `database/sql/solo_crear_base.sql`. Luego ejecutar:

```bash
php artisan migrate --seed
```

Esto crea el esquema y agrega divisas y un producto para realizar pruebas.

**Opción B — Importación SQL:**

Importar `database/sql/prueba_backend_completa.sql` en una instalación MySQL con una base vacía. El archivo incluye tablas, relaciones, datos de ejemplo e historial de migraciones.

**Usar solamente una opción.** No importar el SQL completo sobre una base donde ya se ejecutaron las migraciones.

### 5. Crear el usuario de acceso

```bash
php artisan app:create-api-user
```

El comando solicita nombre, correo y contraseña. No se incluye ningún usuario ni contraseña predeterminada.

### 6. Iniciar el proyecto

```bash
php artisan serve
```

Endpoints de comprobación:

- `http://127.0.0.1:8000/api/health`
- `http://127.0.0.1:8000/api/currencies`
- `http://127.0.0.1:8000/api/products`

> Todas las rutas definidas en `routes/api.php` llevan el prefijo `/api` que agrega Laravel.

## Endpoints

| Método | Endpoint | Acceso | Descripción |
| --- | --- | --- | --- |
| `GET` | `/api/health` | Público | Verificar respuesta de la API |
| `GET` | `/api/currencies` | Público | Consultar divisas registradas |
| `POST` | `/api/auth/login` | Público | Iniciar sesión y obtener token |
| `POST` | `/api/auth/logout` | Token | Revocar el token actual |
| `GET` | `/api/products` | Público | Listar productos (paginado) |
| `POST` | `/api/products` | Token | Crear producto |
| `GET` | `/api/products/{id}` | Público | Consultar producto |
| `PUT / PATCH` | `/api/products/{id}` | Token | Actualizar campos del producto |
| `DELETE` | `/api/products/{id}` | Token | Eliminar producto |
| `GET` | `/api/products/{id}/prices` | Público | Listar precios adicionales |
| `POST` | `/api/products/{id}/prices` | Token | Crear precio adicional |

El listado admite los parámetros opcionales `search` (nombre del producto) y `per_page` (entre 1 y 100, con 10 por defecto).

### Autenticación

Enviar a `POST /api/auth/login` un JSON con `email` y `password`. La respuesta contiene un `token`. Incluirlo en las peticiones que modifican datos:

```http
Authorization: Bearer TU_TOKEN
Accept: application/json
Content-Type: application/json
```

Se utiliza un límite de **5 intentos por minuto** en la ruta de inicio de sesión. Las consultas GET se dejaron públicas de forma intencional para facilitar la revisión del ejercicio. Las operaciones que modifican datos requieren un token válido.

### Ejemplo: crear producto

**POST** `/api/products` (requiere token)

```json
{
  "name": "Mouse inalámbrico",
  "description": "Mouse ergonómico para oficina",
  "price": 25.50,
  "currency_id": 1,
  "tax_cost": 3.32,
  "manufacturing_cost": 10.00
}
```

Un producto creado devuelve `201 Created` y sus datos bajo la propiedad `data`. Los valores `decimal` pueden serializarse como cadenas para conservar la precisión.

### Ejemplo: registrar precio en otra divisa

**POST** `/api/products/1/prices` (requiere token)

```json
{
  "currency_id": 2,
  "price": 12750.00
}
```

El `id` del producto y la divisa deben existir. El precio adicional no puede utilizar la misma divisa base del producto ni repetir otra ya registrada.

### Respuestas HTTP principales

| Código | Significado |
| --- | --- |
| `200` | Consulta, actualización o eliminación realizada |
| `201` | Recurso creado |
| `401` | Token ausente o inválido |
| `404` | Producto inexistente |
| `422` | Error de validación |
| `429` | Se excedió el límite de intentos de inicio de sesión |

Los errores de las rutas API se presentan como JSON.

## Pruebas

Las pruebas de integración se encuentran en `tests/Feature/ProductsApiTest.php` y cubren:

- Respuesta del endpoint de estado.
- Restricción de escritura sin autenticación.
- Flujo de creación, consulta, actualización y eliminación de productos.
- Registro de precios adicionales y validación de divisas duplicadas.
- Rechazo de importes negativos o divisas inexistentes.
- Eliminación en cascada de los precios asociados.

Ejecutarlas con:

```bash
php artisan test
```

La configuración de PHPUnit utiliza **SQLite en memoria**, con `pdo_sqlite` habilitado, para no alterar los datos de la base MySQL de desarrollo. También se incluye el flujo `.github/workflows/tests.yml` para ejecutar estas pruebas en GitHub Actions.

**Estado de verificación:** se adjuntan las pruebas, pero su ejecución satisfactoria debe verificarse en el ambiente de instalación o en CI antes de dar la entrega por validada.

### Colección Postman

Importar `postman/Prueba_Backend.postman_collection.json`, definir las variables de usuario y ejecutar primero la solicitud de inicio de sesión. La colección permite recorrer los endpoints y enviar automáticamente el token a las rutas protegidas.

## Consideraciones y límites del ejercicio

1. **Precios por moneda:** se registran manualmente. `exchange_rate` contiene tasas ficticias para los datos de ejemplo; no se consulta una API financiera ni se calcula automáticamente la conversión.
2. **Actualización de divisa base:** la implementación permite cambiar `currency_id` de un producto, pero no reorganiza precios adicionales ya registrados. En una evolución del sistema, habría que incorporar una regla de negocio explícita para ese caso.
3. **Autenticación:** se implementó un único nivel de acceso para escritura; no se incluyen permisos por rol ni interfaz administrativa, porque no forman parte del alcance solicitado.
4. **Recursos de divisas:** las divisas se consultan mediante la API y se cargan por seeder/SQL; no se agregó un CRUD de divisas, ya que no fue requerido.
5. **Despliegue:** `php artisan serve` se utiliza exclusivamente para desarrollo. Para un servidor público se debe configurar HTTPS, variables de entorno y un servidor web adecuado.

## Archivos de apoyo

- `database/migrations/`: esquema versionado.
- `database/sql/prueba_backend_completa.sql`: instalación alternativa de base de datos.
- `database/seeders/DatabaseSeeder.php`: datos de prueba.
- `postman/Prueba_Backend.postman_collection.json`: ejemplos ejecutables de peticiones.

Este README busca facilitar una revisión técnica rápida: cómo ejecutar el proyecto, dónde se implementó cada parte y cuáles decisiones corresponden al alcance del ejercicio.
