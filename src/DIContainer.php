<?php

namespace Frontend100p\Frontend100p_Settings;

use Exception;
use ReflectionClass;
use ReflectionException;

class DIContainer {
  private array $instances = [];
  private array $definitions = [];
  private array $parameters = [];

  public function set( string $id, $concrete = null ): void {
    if ( $concrete === null ) {
      $concrete = $id;
    }
    $this->definitions[ $id ] = $concrete;
  }

  /**
   * @throws Exception
   */
  public function get( string $id ) {
    if ( isset( $this->instances[ $id ] ) ) {
      return $this->instances[ $id ];
    }

    $concrete = $this->definitions[ $id ] ?? $id;

    try {
      $object = $this->resolve( $concrete );
    } catch ( ReflectionException $e ) {
      throw new Exception( $e->getMessage(), 0, $e );
    }

    $this->instances[ $id ] = $object;

    return $object;
  }

  /**
   * @throws ReflectionException
   * @throws Exception
   */
  private function resolve( $concrete ) {
    if ( is_callable( $concrete ) ) {
      return $concrete( $this );
    }

    $reflector = new ReflectionClass( $concrete );

    if ( ! $reflector->isInstantiable() ) {
      throw new Exception( "Class $concrete is not instantiable" );
    }

    $constructor = $reflector->getConstructor();

    if ( null === $constructor ) {
      return new $concrete;
    }

    $parameters   = $constructor->getParameters();
    $dependencies = $this->resolveDependencies( $parameters );

    return $reflector->newInstanceArgs( $dependencies );
  }

  /**
   * @throws Exception
   */
  private function resolveDependencies( array $parameters ): array {
    $dependencies = [];

    foreach ( $parameters as $parameter ) {
      $type = $parameter->getType();

      if ( null === $type ) {
        if ( $parameter->isDefaultValueAvailable() ) {
          $dependencies[] = $parameter->getDefaultValue();
          continue;
        }
        throw new Exception( "Cannot resolve parameter {$parameter->getName()}" );
      }

      $typeName = $type->getName();

      if ( isset( $this->parameters[ $parameter->getName() ] ) ) {
        $dependencies[] = $this->parameters[ $parameter->getName() ];
        continue;
      }

      try {
        $dependencies[] = $this->get( $typeName );
      } catch ( Exception ) {
        if ( $parameter->isDefaultValueAvailable() ) {
          $dependencies[] = $parameter->getDefaultValue();
          continue;
        }
        throw new Exception( "Cannot resolve dependency $typeName" );
      }
    }

    return $dependencies;
  }

  public function setParameter( string $name, $value ): void {
    $this->parameters[ $name ] = $value;
  }
}
