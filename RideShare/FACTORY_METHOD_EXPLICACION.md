# Factory Method aplicado a RideShare

## Aplicación elegida
El patrón se aplicó al momento de crear los diferentes perfiles durante el registro: **Pasajero** y **Conductor**.

Esta decisión sigue la explicación de la presentación del profesor: existe un **Producto**, productos concretos, un **Creador** con el método fábrica y creadores concretos que deciden qué objeto instanciar.

### 1. Producto
`UsuarioProducto` define la interfaz común:

```php
interface UsuarioProducto {
    public function obtenerDatos(): array;
}
```

### 2. Productos concretos
- `Pasajero`: crea un perfil con rol `pasajero` y sin vehículo.
- `Conductor`: crea un perfil con rol `conductor`.

### 3. Creador
`UsuarioFactory` es abstracta y declara:

```php
abstract public function crearUsuario(array $datos): UsuarioProducto;
```

Ese método es el **Factory Method**.

Además, `prepararRegistro()` contiene la lógica común y trabaja con la abstracción `UsuarioProducto`, sin conocer directamente si se creó un pasajero o un conductor.

### 4. Creadores concretos
- `PasajeroFactory` retorna `new Pasajero(...)`.
- `ConductorFactory` retorna `new Conductor(...)`.

## Flujo dentro del registro
1. El usuario selecciona el tipo de cuenta.
2. El sistema crea los datos con `UsuarioBuilder`.
3. Se selecciona el creador correspondiente.
4. El Factory Method crea el producto concreto.
5. El registro continúa usando los datos devueltos por la abstracción.

```php
$factory = $rol === "conductor"
    ? new ConductorFactory()
    : new PasajeroFactory();

$datos = $factory->prepararRegistro($datos);
```

## ¿Por qué sirve en RideShare?
Evita que la lógica principal de registro tenga que crear directamente los diferentes tipos de perfiles con `new Pasajero()` o `new Conductor()`. La responsabilidad de decidir qué objeto crear queda delegada a los creadores concretos.

Esto permite agregar otro tipo de usuario en el futuro, por ejemplo `Administrador`, creando un nuevo producto y un nuevo creador, sin modificar la lógica general del creador.

## Relación con SOLID
- **OCP:** el sistema se puede extender agregando nuevos tipos de usuario.
- **SRP:** la creación de cada perfil queda separada de la lógica general del registro.
- **DIP:** la lógica común trabaja con `UsuarioProducto`, una abstracción, y no directamente con `Pasajero` o `Conductor`.
