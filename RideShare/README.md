# RideShare — módulo de usuario

Proyecto PHP/MySQL para XAMPP. Incluye un dashboard de pasajero con:
- Mapa interactivo de Bucaramanga/Santander (OpenStreetMap + Leaflet).
- Ubicación actual mediante geolocalización del navegador.
- Búsqueda de colegios, universidades, calles, comercios, clínicas y direcciones dentro de Santander.
- Ruta real mediante OSRM.
- Selección exclusiva de Moto o Carro.
- Tarifa estimada con Strategy (`TarifaMoto` / `TarifaCarro`).
- Pago: efectivo, tarjeta o Nequi.
- Creación del viaje en MySQL mediante `api.php`.
- Diseño responsive y animaciones.

## Instalación
1. Copiar la carpeta `RideShare` dentro de `htdocs`.
2. Crear/importar la base de datos usando `database.sql` en phpMyAdmin.
3. Revisar `conexion.php` si el usuario/clave de MySQL es diferente.
4. Abrir `http://localhost/RideShare/`.

La búsqueda y el cálculo de rutas usan servicios públicos de OpenStreetMap/Nominatim y OSRM; para una publicación real se recomienda usar proveedores con límites y claves propias.


## Solicitudes de viaje en tiempo real

El dashboard del conductor consulta las solicitudes pendientes cada segundo. Cuando un pasajero crea un viaje compatible con el tipo de vehículo del conductor, la solicitud aparece automáticamente como una ventana emergente sobre el dashboard, sin necesidad de entrar al menú de Solicitudes. Desde esa ventana se puede aceptar el viaje.

El pasajero consulta el estado de su viaje y muestra automáticamente la confirmación cuando el conductor acepta.


### Ruta activa y llegada
- El conductor recibe la solicitud en pantalla y puede aceptar, marcar `Llegué`, iniciar y finalizar.
- `Llegué` guarda la hora en `viajes.llego_en` y muestra un conteo persistente de 5 minutos al conductor y al pasajero.
- Al iniciar, la ruta cambia del punto de recogida al destino.
- La posición del conductor se comparte por `ubicaciones_viaje`; los marcadores de vehículo + persona se actualizan suavemente en ambos mapas.
- Si la base ya existía, `api.php` crea automáticamente la columna `llego_en` si falta.
