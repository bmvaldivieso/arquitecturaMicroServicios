#!/bin/bash

GATEWAY_URL="http://localhost:8000"

echo "=== PRUEBAS DEL SERVICIO DE REVIEWS ==="
echo ""

echo "1. Crear una reseña..."
curl -X POST $GATEWAY_URL/reviews \
  -H "Content-Type: application/json" \
  -d '{"comment":"Excelente libro","rating":5,"book_id":1}'
echo ""
echo ""

echo "2. Listar todas las reseñas..."
curl $GATEWAY_URL/reviews
echo ""
echo ""

echo "3. Intentar crear reseña con libro inexistente..."
curl -X POST $GATEWAY_URL/reviews \
  -H "Content-Type: application/json" \
  -d '{"comment":"Test","rating":5,"book_id":99999}'
echo ""
echo ""

echo "=== FIN DE PRUEBAS ==="
