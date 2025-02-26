<?php

namespace Frontend100p\Frontend100p_Settings\Services;

use Exception;
use ReflectionClass;
use ReflectionException;

class DIContainerService
{
  private array $instances = [];
  private array $definitions = [];
  private array $parameters = [];

  /**
   * Registra múltiples servicios a partir de un array
   *
   * @param array $services Array asociativo con [id => concrete]
   * @return void
   */
  public function setMany(array $services): void
  {
    foreach ($services as $id => $concrete) {
      // Si la clave es numérica, asumimos que $concrete es tanto el ID como la implementación
      if (is_int($id)) {
        $this->set($concrete);
        return;
      }

      $this->set($id, $concrete);
    }
  }

  public function set(string $id, $concrete = null): void
  {
    if ($concrete === null) {
      $concrete = $id;
    }
    $this->definitions[$id] = $concrete;
  }

  /**
   * Obtiene múltiples servicios a partir de un array de IDs
   *
   * @param array $ids Array de IDs de servicios
   * @return array Array asociativo con [id => instancia]
   * @throws Exception
   */
  public function getMany(array $ids): array
  {
    $services = [];

    foreach ($ids as $id) {
      $services[$id] = $this->get($id);
    }

    return $services;
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

  /**
   * @throws ReflectionException
   * @throws Exception
   */
  private function resolve($concrete)
  {
    if (is_callable($concrete)) {
      return $concrete($this);
    }

    $reflector = new ReflectionClass($concrete);

    if ( ! $reflector->isInstantiable()) {
      throw new Exception("Class $concrete is not instantiable");
    }

    $constructor = $reflector->getConstructor();

    if (null === $constructor) {
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

  public function setParameter(string $name, $value): void
  {
    $this->parameters[$name] = $value;
  }
}
