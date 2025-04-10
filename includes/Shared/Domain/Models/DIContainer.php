<?php
declare(strict_types=1);

namespace Fronpe\Fronpe_Settings\Shared\Domain\Models;

use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

/**
 * Contenedor de inyección de dependencias simple.
 * @template-covariant T
 */
class DIContainer
{

  /** @var array<string, object> Instancias ya creadas de los servicios. */
  private array $instances = [];
  /** @var array<string, string|callable> Definiciones de cómo resolver los servicios. */
  private array $definitions = [];
  /** @var array<string, mixed> Parámetros configurados en el contenedor. */
  private array $parameters = [];

  /**
   * Registra un servicio en el contenedor.
   *
   * @param class-string<T>|string $id El identificador del servicio (normalmente el FQCN)
   * @param (callable(self): T)|null $concrete Una función que crea la instancia o null para usar autowiring
   *
   * @return void
   */
  public function set(string $id, ?callable $concrete = null): void
  {
    if ($concrete === null) {
      $this->definitions[$id] = $id;

      return;
    }
    $this->definitions[$id] = $concrete;
  }

  /**
   * Obtiene un servicio del contenedor.
   *
   * @template S
   * @param class-string<S>|string $id El identificador del servicio a obtener
   *
   * @return S La instancia del servicio
   *
   * @throws Exception Si no se puede resolver el servicio
   */
  public function get(string $id)
  {
    // Devolver la instancia si ya existe
    if (isset($this->instances[$id])) {
      return $this->instances[$id];
    }

    // Buscar la definición del servicio, o usar el ID como clase si no está definida
    $concrete = $this->definitions[$id] ?? $id;

    try {
      // Resolver la instancia
      $object = $this->resolve($concrete);
    } catch (ReflectionException $e) {
      throw new Exception("Error al resolver la dependencia $id: {$e->getMessage()}", 0, $e);
    }

    $this->instances[$id] = $object;

    return $object;
  }

  /**
   * Comprueba si un servicio está definido en el contenedor.
   *
   * @param string $id El identificador del servicio
   *
   * @return bool True si el servicio está definido, false en caso contrario
   */
  public function has(string $id): bool
  {
    return isset($this->definitions[$id]) || isset($this->instances[$id]);
  }

  /**
   * Establece un valor de parámetro en el contenedor.
   *
   * @param string $name Nombre del parámetro
   * @param mixed $value Valor del parámetro
   *
   * @return void
   */
  public function setParameter(string $name, mixed $value): void
  {
    $this->parameters[$name] = $value;
  }

  /**
   * Obtiene un parámetro del contenedor.
   *
   * @param string $name Nombre del parámetro
   * @param mixed $default Valor por defecto si el parámetro no existe
   *
   * @return mixed El valor del parámetro o el valor por defecto
   */
  public function getParameter(string $name, mixed $default = null): mixed
  {
    return $this->parameters[$name] ?? $default;
  }

  /**
   * Resuelve una definición de servicio.
   *
   * @template R
   * @param class-string<R>|callable $concrete La clase o función a resolver
   *
   * @return R La instancia resuelta
   *
   * @throws ReflectionException Si hay un error en la reflexión
   * @throws Exception Si la clase no es instanciable o no se pueden resolver sus dependencias
   */
  private function resolve(callable|string $concrete)
  {
    // Si es una función, invocarla con el contenedor
    if (is_callable($concrete)) {
      return $concrete($this);
    }

    // Usar reflexión para analizar la clase
    $reflector = new ReflectionClass($concrete);

    if ( ! $reflector->isInstantiable()) {
      throw new Exception("La clase $concrete no es instanciable");
    }

    // Obtener el constructor
    $constructor = $reflector->getConstructor();

    // Si no hay constructor, crear una instancia sin parámetros
    if ($constructor === null) {
      return new $concrete();
    }

    // Resolver los parámetros del constructor
    $parameters   = $constructor->getParameters();
    $dependencies = $this->resolveDependencies($parameters);

    // Crear una nueva instancia con los parámetros resueltos
    return $reflector->newInstanceArgs($dependencies);
  }

  /**
   * Resuelve las dependencias de un conjunto de parámetros.
   *
   * @param ReflectionParameter[] $parameters Parámetros a resolver
   *
   * @return array<int, mixed> Array de dependencias resueltas
   *
   * @throws Exception Si no se puede resolver un parámetro
   */
  private function resolveDependencies(array $parameters): array
  {
    $dependencies = [];

    foreach ($parameters as $parameter) {
      $type = $parameter->getType();

      // Si el parámetro no tiene tipo, intentar usar el valor por defecto
      if (null === $type) {
        if ($parameter->isDefaultValueAvailable()) {
          $dependencies[] = $parameter->getDefaultValue();
          continue;
        }
        throw new Exception("No se puede resolver el parámetro {$parameter->getName()} sin tipo");
      }

      $typeName  = $type->getName();
      $paramName = $parameter->getName();

      // Verificar si el parámetro está definido por nombre
      if (isset($this->parameters[$paramName])) {
        $dependencies[] = $this->parameters[$paramName];
        continue;
      }

      // Intentar resolver el tipo como servicio
      try {
        $dependencies[] = $this->get($typeName);
      } catch (Exception) {
        // Si falla, intentar usar el valor por defecto
        if ($parameter->isDefaultValueAvailable()) {
          $dependencies[] = $parameter->getDefaultValue();
          continue;
        }

        // Si es un tipo unión (PHP 8+), podríamos manejar casos adicionales aquí

        // Si el tipo es opcional (permite null)
        if ($type->allowsNull()) {
          $dependencies[] = null;
          continue;
        }

        throw new Exception("No se puede resolver la dependencia {$typeName} para el parámetro {$paramName}");
      }
    }

    return $dependencies;
  }
}
