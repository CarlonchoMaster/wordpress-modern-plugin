<?php
declare(strict_types=1);

namespace Fronpe\Fronpe_Settings\Shared\Domain\Models;

use Exception;
use ReflectionClass;
use ReflectionException;

class DIContainer
{
  private array $instances = [];
  /**
   * @var array<string, string|callable> $definitions
   */
  private array $definitions = [];
  private array $parameters = [];

  public function set(string $id, ?callable $concrete = null): void
  {
    if ($concrete === null) {
      $this->definitions[$id] = $id;

      return;
    }
    $this->definitions[$id] = $concrete;
  }

  /**
   * @throws Exception
   */
  public function get(string $id)
  {
    if (isset($this->instances[$id])) {
      return $this->instances[$id];
    }

    $concrete = $this->definitions[$id] ?? $id;

    try {
      $object = $this->resolve($concrete);
    } catch (ReflectionException $e) {
      throw new Exception($e->getMessage(), 0, $e);
    }

    $this->instances[$id] = $object;

    return $object;
  }

  public function setParameter(string $name, mixed $value): void
  {
    $this->parameters[$name] = $value;
  }

  /**
   * @throws ReflectionException
   * @throws Exception
   */
  private function resolve(callable|string $concrete)
  {
    if (is_callable($concrete)) {
      return $concrete($this);
    }

    $reflector = new ReflectionClass($concrete);

    if ( ! $reflector->isInstantiable()) {
      throw new Exception("Class $concrete is not instantiable");
    }

    $constructor = $reflector->getConstructor();

    if ($constructor === null) {
      return new $concrete;
    }

    $parameters   = $constructor->getParameters();
    $dependencies = $this->resolveDependencies($parameters);

    return $reflector->newInstanceArgs($dependencies);
  }

  /**
   * @throws Exception
   */
  private function resolveDependencies(array $parameters): array
  {
    $dependencies = [];

    foreach ($parameters as $parameter) {
      $type = $parameter->getType();

      if (null === $type) {
        if ($parameter->isDefaultValueAvailable()) {
          $dependencies[] = $parameter->getDefaultValue();
          continue;
        }
        throw new Exception("Cannot resolve parameter {$parameter->getName()}");
      }

      $typeName = $type->getName();

      if (isset($this->parameters[$parameter->getName()])) {
        $dependencies[] = $this->parameters[$parameter->getName()];
        continue;
      }

      try {
        $dependencies[] = $this->get($typeName);
      } catch (Exception) {
        if ($parameter->isDefaultValueAvailable()) {
          $dependencies[] = $parameter->getDefaultValue();
          continue;
        }
        throw new Exception("Cannot resolve dependency $typeName");
      }
    }

    return $dependencies;
  }
}
