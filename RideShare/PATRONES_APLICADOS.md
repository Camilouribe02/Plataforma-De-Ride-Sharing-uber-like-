# Patrones aplicados en RideShare

Este proyecto conserva Singleton y Factory Method y ahora usa también:

## Abstract Factory
`patrones/Patrones.php` contiene `VehiculoAbstractFactory` y las fábricas `MotoAbstractFactory` y `CarroAbstractFactory`.
Cada fábrica crea una familia de objetos relacionados: el vehículo y su configuración.

Se utiliza durante el registro de un conductor. El tipo seleccionado (moto o carro) determina automáticamente la familia correspondiente.

## Builder
`UsuarioBuilder` construye los datos del usuario paso a paso antes de guardarlos en MySQL.

## Prototype
`PerfilUsuario` implementa `__clone()` y `clonar()`. Durante el registro se crea el perfil base y se obtiene una copia independiente antes de continuar con el guardado.

## Compatibilidad
No se cambian las tablas ni los nombres de campos existentes. El flujo de login, registro y los demás módulos continúan usando la misma base de datos.
