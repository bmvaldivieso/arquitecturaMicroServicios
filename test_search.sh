#!/bin/bash

GATEWAY_URL="http://localhost:8000"

echo "=== PRUEBAS DEL SERVICIO DE BÚSQUEDA ==="
echo ""

echo "1. Búsqueda general de 'Harry Potter'..."
curl -G "$GATEWAY_URL/search" \
  --data-urlencode "q=Harry Potter" \
  --data-urlencode "limit=5"
echo ""
echo ""

echo "2. Búsqueda de libros con filtros..."
curl -G "$GATEWAY_URL/search/books" \
  --data-urlencode "q=Science" \
  --data-urlencode "price_min=10" \
  --data-urlencode "price_max=50" \
  --data-urlencode "sort=price_asc"
echo ""
echo ""

echo "3. Búsqueda de autores 'Stephen'..."
curl -G "$GATEWAY_URL/search/authors" \
  --data-urlencode "q=Stephen" \
  --data-urlencode "limit=3"
echo ""
echo ""

echo "4. Sugerencias de búsqueda para 'Har'..."
curl -G "$GATEWAY_URL/search/suggestions" \
  --data-urlencode "q=Har" \
  --data-urlencode "limit=5"
echo ""
echo ""

echo "5. Búsquedas populares..."
curl "$GATEWAY_URL/search/popular"
echo ""
echo ""

echo "6. Búsqueda sin parámetros (debe dar error)..."
curl "$GATEWAY_URL/search"
echo ""
echo ""

echo "=== FIN DE PRUEBAS ==="
