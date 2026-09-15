<?php
/* Patrones GoF aplicados a RideShare. */

// 1. Abstract Factory
// Crea familias de objetos relacionados (vehículo + configuración) sin que
// el registro tenga que conocer las clases concretas.
interface VehiculoProducto {
    public function obtenerDatos(): array;
}

interface ConfiguracionVehiculoProducto {
    public function obtenerDatos(): array;
}

interface VehiculoAbstractFactory {
    public function crearVehiculo(array $datos): VehiculoProducto;
    public function crearConfiguracion(array $datos): ConfiguracionVehiculoProducto;
}

class MotoProducto implements VehiculoProducto {
    public function __construct(private array $datos) {
        $this->datos['tipo_vehiculo'] = 'moto';
    }
    public function obtenerDatos(): array { return $this->datos; }
}

class CarroProducto implements VehiculoProducto {
    public function __construct(private array $datos) {
        $this->datos['tipo_vehiculo'] = 'carro';
    }
    public function obtenerDatos(): array { return $this->datos; }
}

class MotoConfiguracion implements ConfiguracionVehiculoProducto {
    public function __construct(private array $datos) {}
    public function obtenerDatos(): array {
        return [
            'tipo_vehiculo' => 'moto',
            'placa' => $this->datos['placa'] ?? null,
            'color_vehiculo' => $this->datos['color_vehiculo'] ?? null
        ];
    }
}

class CarroConfiguracion implements ConfiguracionVehiculoProducto {
    public function __construct(private array $datos) {}
    public function obtenerDatos(): array {
        return [
            'tipo_vehiculo' => 'carro',
            'placa' => $this->datos['placa'] ?? null,
            'color_vehiculo' => $this->datos['color_vehiculo'] ?? null
        ];
    }
}

class MotoAbstractFactory implements VehiculoAbstractFactory {
    public function crearVehiculo(array $datos): VehiculoProducto {
        return new MotoProducto($datos);
    }
    public function crearConfiguracion(array $datos): ConfiguracionVehiculoProducto {
        return new MotoConfiguracion($datos);
    }
}

class CarroAbstractFactory implements VehiculoAbstractFactory {
    public function crearVehiculo(array $datos): VehiculoProducto {
        return new CarroProducto($datos);
    }
    public function crearConfiguracion(array $datos): ConfiguracionVehiculoProducto {
        return new CarroConfiguracion($datos);
    }
}

class VehiculoFactoryProvider {
    public static function obtener(string $tipo): VehiculoAbstractFactory {
        return match ($tipo) {
            'moto' => new MotoAbstractFactory(),
            'carro' => new CarroAbstractFactory(),
            default => throw new InvalidArgumentException('Tipo de vehículo no válido.')
        };
    }
}


// 2. Factory Method
// Producto: define lo que todos los perfiles creados por la fábrica deben entregar.
interface UsuarioProducto {
    public function obtenerDatos(): array;
}

// Productos concretos.
class Pasajero implements UsuarioProducto {
    public function __construct(private array $datos) {
        $this->datos['rol'] = 'pasajero';
        $this->datos['tipo_vehiculo'] = null;
        $this->datos['placa'] = null;
        $this->datos['color_vehiculo'] = null;
    }

    public function obtenerDatos(): array {
        return $this->datos;
    }
}

class Conductor implements UsuarioProducto {
    public function __construct(private array $datos) {
        $this->datos['rol'] = 'conductor';
    }

    public function obtenerDatos(): array {
        return $this->datos;
    }
}

// Creador: contiene la lógica común y delega la creación concreta.
abstract class UsuarioFactory {
    // Factory Method.
    abstract public function crearUsuario(array $datos): UsuarioProducto;

    public function prepararRegistro(array $datos): array {
        $usuario = $this->crearUsuario($datos);
        return $usuario->obtenerDatos();
    }

    // Alias para mantener compatible el código existente del proyecto.
    public function crear(array $datos): array {
        return $this->prepararRegistro($datos);
    }
}

// Creadores concretos.
class PasajeroFactory extends UsuarioFactory {
    public function crearUsuario(array $datos): UsuarioProducto {
        return new Pasajero($datos);
    }
}

class ConductorFactory extends UsuarioFactory {
    public function crearUsuario(array $datos): UsuarioProducto {
        return new Conductor($datos);
    }
}

// 3. Builder
class UsuarioBuilder {
    private array $d=[];
    public function nombre($v): self {$this->d['nombre']=$v; return $this;}
    public function apellido($v): self {$this->d['apellido']=$v; return $this;}
    public function correo($v): self {$this->d['correo']=$v; return $this;}
    public function telefono($v): self {$this->d['telefono']=$v; return $this;}
    public function password($v): self {$this->d['password']=$v; return $this;}
    public function tipoVehiculo($v): self {$this->d['tipo_vehiculo']=$v; return $this;}
    public function placa($v): self {$this->d['placa']=$v; return $this;}
    public function colorVehiculo($v): self {$this->d['color_vehiculo']=$v; return $this;}
    public function construir(): array { return $this->d; }
}

// 4. Prototype
class PerfilUsuario implements Stringable {
    public function __construct(public array $datos) {}
    public function __clone() {
        $this->datos = array_merge([], $this->datos);
    }
    public function clonar(): self {
        return clone $this;
    }
    public function __toString(): string {
        return $this->datos['correo'] ?? '';
    }
}

// 5. Adapter
interface ServicioCorreo { public function enviarCodigo(string $destino,string $nombre,string $codigo): void; }
class SMTPAdapter implements ServicioCorreo {
    public function enviarCodigo(string $destino,string $nombre,string $codigo): void { enviarCodigoPorCorreo($destino,$nombre,$codigo); }
}

// 6. Facade
class AutenticacionFacade {
    public function __construct(private mysqli $db) {}
    public function iniciarSesion(string $correo,string $password): ?array {
        $stmt=$this->db->prepare("SELECT id,nombre,apellido,correo,password,rol FROM usuarios WHERE correo=? AND estado='activo' LIMIT 1");
        $stmt->bind_param('s',$correo); $stmt->execute(); $u=$stmt->get_result()->fetch_assoc();
        return ($u && password_verify($password,$u['password'])) ? $u : null;
    }
}

// 7. Proxy
interface Panel { public function permitir(): bool; }
class PanelReal implements Panel { public function permitir(): bool { return true; } }
class PanelSeguroProxy implements Panel {
    public function __construct(private Panel $real) {}
    public function permitir(): bool { return isset($_SESSION['usuario_id']) && $this->real->permitir(); }
}

// 8. Strategy
interface TarifaStrategy { public function calcular(float $km): float; }
class TarifaMoto implements TarifaStrategy { public function calcular(float $km): float { return 3000 + ($km*900); } }
class TarifaCarro implements TarifaStrategy { public function calcular(float $km): float { return 4000 + ($km*1300); } }
class CalculadoraTarifa { public function __construct(private TarifaStrategy $s) {} public function calcular(float $km): float { return $this->s->calcular($km); } }

// 9. Observer
interface Observer { public function actualizar(string $evento,array $datos=[]): void; }
class EventoRecuperacion {
    private array $observadores=[];
    public function suscribir(Observer $o): void {$this->observadores[]= $o;}
    public function notificar(string $evento,array $datos=[]): void { foreach($this->observadores as $o) $o->actualizar($evento,$datos); }
}
class RegistroEventoObserver implements Observer {
    public function actualizar(string $evento,array $datos=[]): void { error_log('[RideShare] '.$evento); }
}

// 10. State
interface CuentaState { public function nombre(): string; public function permiteAcceso(): bool; }
class CuentaActiva implements CuentaState { public function nombre(): string{return 'activo';} public function permiteAcceso(): bool{return true;} }
class CuentaInactiva implements CuentaState { public function nombre(): string{return 'inactivo';} public function permiteAcceso(): bool{return false;} }

// 11. Command
interface Command { public function ejecutar(): void; }
class CrearSesionCommand implements Command {
    public function __construct(private array $u) {}
    public function ejecutar(): void { session_regenerate_id(true); foreach(['id'=>'usuario_id','nombre'=>'nombre','apellido'=>'apellido','correo'=>'correo','rol'=>'rol'] as $origen=>$destino) $_SESSION[$destino]=$this->u[$origen]; }
}

// 12. Decorator
interface DescripcionViaje { public function descripcion(): string; public function costoExtra(): float; }
class ViajeBasico implements DescripcionViaje { public function descripcion(): string{return 'Viaje básico';} public function costoExtra(): float{return 0;} }
abstract class ViajeDecorator implements DescripcionViaje { public function __construct(protected DescripcionViaje $viaje){} }
class EquipajeDecorator extends ViajeDecorator { public function descripcion(): string{return $this->viaje->descripcion().' + equipaje';} public function costoExtra(): float{return $this->viaje->costoExtra()+1000;} }
