# Servicio de Reviews - Microservicio

Este es el microservicio de Reviews para la arquitectura de microservicios del proyecto. Permite a los usuarios crear, leer, actualizar y eliminar reseñas de libros.

## Características

- ✅ CRUD completo de reviews
- ✅ Validación de libros existentes antes de crear reseñas
- ✅ Consumo del servicio de Books para validación
- ✅ Respuestas JSON estandarizadas
- ✅ Manejo de errores adecuado

## Configuración

### Variables de Entorno

```env
APP_NAME=LumenReviewsApi
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8003

DB_CONNECTION=sqlite
DB_DATABASE=/ruta/completa/a/LumenReviewsApi/database/database.sqlite

AUTHORS_SERVICE_BASE_URL=http://localhost:8001
BOOKS_SERVICE_BASE_URL=http://localhost:8002
```

### Instalación

1. Instalar dependencias:
```bash
composer install
```

2. Ejecutar migraciones:
```bash
php artisan migrate
```

3. Iniciar el servicio:
```bash
php -S localhost:8003 -t public
```

## Endpoints

### Reviews
- `GET /reviews` - Listar todas las reseñas
- `POST /reviews` - Crear una reseña
- `GET /reviews/{id}` - Obtener una reseña específica
- `PUT /reviews/{id}` - Actualizar una reseña
- `DELETE /reviews/{id}` - Eliminar una reseña

### Formato de Datos

**Crear/Actualizar Review:**
```json
{
    "comment": "Excelente libro, muy recomendado",
    "rating": 5,
    "book_id": 1
}
```

## Integración con Gateway

Este servicio se integra con el API Gateway en el puerto 8000. Los endpoints a través del Gateway son:

- `GET http://localhost:8000/reviews`
- `POST http://localhost:8000/reviews`
- `GET http://localhost:8000/reviews/{id}`
- `PUT http://localhost:8000/reviews/{id}`
- `DELETE http://localhost:8000/reviews/{id}`

## Pruebas

Ejecutar el script de pruebas:
```bash
bash ../test_reviews.sh
```

## Estructura del Proyecto

```
LumenReviewsApi/
├── app/
│   ├── Http/Controllers/
│   │   └── ReviewController.php
│   ├── Services/
│   │   ├── BookService.php
│   │   └── AuthorService.php
│   ├── Traits/
│   │   ├── ApiResponser.php
│   │   └── ConsumesExternalService.php
│   ├── Review.php
│   └── Exceptions/
│       └── Handler.php
├── database/
│   ├── migrations/
│   │   └── 2024_01_01_000000_create_reviews_table.php
│   └── database.sqlite
├── routes/
│   └── web.php
└── .env
