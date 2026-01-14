#!/bin/bash

# ==========================================================
# SCRIPT DE PRUEBAS AUTOMATIZADAS DE MICROSERVICIOS
# Arquitectura: Microservicios con API Gateway
# Objetivo: Verificar disponibilidad, comunicación y consistencia
# ==========================================================

echo "=== PRUEBA COMPLETA DE MICROSERVICIOS ==="
echo "Este script valida el funcionamiento individual y conjunto"
echo "de los microservicios y el API Gateway"
echo ""

# ----------------------------------------------------------    
# Colores para salida en consola
# ----------------------------------------------------------
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# ----------------------------------------------------------
# Función genérica para probar endpoints HTTP
#
# Parámetros:
# 1. URL
# 2. Método HTTP (GET por defecto)
# 3. Payload JSON (solo para POST)
# 4. Descripción funcional de la prueba
# 5. Qué valida la prueba
# ----------------------------------------------------------
test_endpoint() {
    local url=$1
    local method=${2:-GET}
    local data=$3
    local description=$4
    local validation=$5

    echo -e "${BLUE}📌 Prueba:${NC} $description"
    echo -e "${YELLOW}➡️  Qué valida:${NC} $validation"
    echo "URL: $url"
    echo "Método: $method"

    # Ejecutar petición HTTP
    if [ "$method" = "POST" ]; then
        response=$(curl -s -w "\n%{http_code}" -X POST "$url" \
            -H "Content-Type: application/json" \
            -d "$data")
    else
        response=$(curl -s -w "\n%{http_code}" "$url")
    fi

    # Separar body y status code
    http_body=$(echo "$response" | sed '$d')
    http_code=$(echo "$response" | tail -n 1)

    # Evaluar resultado
    if [ "$http_code" = "200" ] || [ "$http_code" = "201" ]; then
        echo -e "${GREEN}✅ SUCCESS${NC} → El servicio respondió correctamente ($http_code)"
    else
        echo -e "${RED}❌ FAILED${NC} → Error o servicio no disponible ($http_code)"
    fi

    # Imprimir respuesta formateada
    echo -e "${CYAN}📦 Respuesta del servicio:${NC}"

    if [ -z "$http_body" ]; then
        echo "(Respuesta vacía)"
    else
        # Pretty print si es JSON válido
        echo "$http_body" | jq . 2>/dev/null
        if [ $? -ne 0 ]; then
            echo "$http_body"
        fi
    fi

    echo "--------------------------------------------------"
    echo ""
}

# ==========================================================
# PRUEBAS DEL MICROSERVICIO DE AUTORES
# ==========================================================
echo "🔵 MICROSERVICIO: AUTHORS (Puerto 8001)"
echo "======================================"

test_endpoint \
  "http://localhost:8001/authors" \
  "GET" \
  "" \
  "Listar todos los autores" \
  "Verifica que el servicio Authors esté activo y devuelva datos"

test_endpoint \
  "http://localhost:8001/authors/1" \
  "GET" \
  "" \
  "Obtener autor por ID" \
  "Comprueba acceso a recursos individuales mediante ID"

# ==========================================================
# PRUEBAS DEL MICROSERVICIO DE LIBROS
# ==========================================================
echo "🔵 MICROSERVICIO: BOOKS (Puerto 8002)"
echo "===================================="

test_endpoint \
  "http://localhost:8002/books" \
  "GET" \
  "" \
  "Listar todos los libros" \
  "Verifica que el catálogo de libros esté disponible"

test_endpoint \
  "http://localhost:8002/books/1" \
  "GET" \
  "" \
  "Obtener libro por ID" \
  "Valida consulta directa de un libro específico"

# ==========================================================
# PRUEBAS DEL MICROSERVICIO DE RESEÑAS
# ==========================================================
echo "🔵 MICROSERVICIO: REVIEWS (Puerto 8003)"
echo "======================================"

test_endpoint \
  "http://localhost:8003/reviews" \
  "GET" \
  "" \
  "Listar todas las reseñas" \
  "Verifica acceso a reseñas almacenadas"

test_endpoint \
  "http://localhost:8003/reviews" \
  "POST" \
  '{"comment":"Test review","rating":5,"book_id":1}' \
  "Crear nueva reseña" \
  "Valida creación de datos y persistencia en Reviews"

# ==========================================================
# PRUEBAS DEL MICROSERVICIO DE BÚSQUEDA
# ==========================================================
echo "🔵 MICROSERVICIO: SEARCH (Puerto 8013)"
echo "====================================="

test_endpoint \
  "http://localhost:8013/search?q=Harry" \
  "GET" \
  "" \
  "Búsqueda general" \
  "Verifica agregación de resultados desde varios servicios"

test_endpoint \
  "http://localhost:8013/search/books?q=Science" \
  "GET" \
  "" \
  "Búsqueda solo de libros" \
  "Valida filtrado por dominio específico"

test_endpoint \
  "http://localhost:8013/search/suggestions?q=Har" \
  "GET" \
  "" \
  "Sugerencias de búsqueda" \
  "Comprueba funcionalidad de autocompletado"

# ==========================================================
# PRUEBAS DEL API GATEWAY
# ==========================================================
echo "🔵 API GATEWAY (Puerto 8000)"
echo "============================"

test_endpoint \
  "http://localhost:8000/authors" \
  "GET" \
  "" \
  "Gateway → Authors" \
  "Valida enrutamiento desde Gateway hacia Authors"

test_endpoint \
  "http://localhost:8000/books" \
  "GET" \
  "" \
  "Gateway → Books" \
  "Verifica proxy del Gateway hacia Books"

test_endpoint \
  "http://localhost:8000/reviews" \
  "GET" \
  "" \
  "Gateway → Reviews" \
  "Confirma comunicación centralizada"

test_endpoint \
  "http://localhost:8000/search?q=Potter" \
  "GET" \
  "" \
  "Gateway → Search" \
  "Verifica búsquedas pasando por el Gateway"

# ==========================================================
# PRUEBA DE COMUNICACIÓN ENTRE MICROSERVICIOS
# ==========================================================
echo "🔵 PRUEBA DE COMUNICACIÓN ENTRE SERVICIOS"
echo "========================================"

test_endpoint \
  "http://localhost:8003/reviews" \
  "POST" \
  '{"comment":"Cross-service test","rating":4,"book_id":1}' \
  "Reseña con validación de libro existente" \
  "Verifica que Reviews consulte Books antes de aceptar datos"

# ==========================================================
# RESUMEN FINAL
# ==========================================================
echo "=== RESUMEN DE PRUEBAS ==="
echo "✅ SUCCESS  → Servicio activo y funcionando correctamente"
echo "❌ FAILED   → Servicio caído, error interno o mala configuración"
echo ""
echo "Para iniciar los servicios:"
echo "php -S localhost:8001 -t LumenAuthorsApi/public"
echo "php -S localhost:8002 -t LumenBooksApi/public"
echo "php -S localhost:8003 -t LumenReviewsApi/public"
echo "php -S localhost:8013 -t LumenSearchApi/public"
echo "php -S localhost:8000 -t LumenGatewayApi/public"
