<?php


namespace App\Service;

class Container
{

    private array $instances = [];
    private bool $verif;

    public function __construct() {
        $this->verif = false;
    }

    public function getController(string $key): object
    {

        if (!isset($this->instances[$key])) {
            $reflected_class = new \ReflectionClass($key);

            if ($reflected_class->isInstantiable()) {
                $constructor = $reflected_class->getConstructor();
                if ($constructor) {
                    $parameters = $constructor->getParameters();
                    $const_parameters = [];
                    foreach ($parameters as $parameter) {
                        if ($parameter->getType() && $parameter->getType()->getName() !== 'int' && $parameter->getType()->getName() !== 'string' && $parameter->getType()->getName() !== 'array') {
                            $const_parameters[] = $this->getController($parameter->getType()->getName());
                        } else {
                            $const_parameters[] = $parameter->getDefaultValue();
                        }
                    }
                    $this->instances[$key] = $reflected_class->newInstanceArgs($const_parameters);
                } else {
                    $this->instances[$key] = $reflected_class->newInstance();
                }
            } else {
                throw new \Exception($key .' is not intanciable');
            }
        }

        return $this->instances[$key];
    }

    public function getMethod(string $class, string $method, object $controllerDependancies, array $matches): object
    {
        $reflected_method = new \ReflectionMethod($class, $method);

        $parameters = $reflected_method->getParameters();

        $method_param = [];

        foreach ($parameters as $parameter) {

            if ($parameter->getType() && $parameter->getType()->getName() !== 'int' && $parameter->getType()->getName() !== 'string' && $parameter->getType()->getName() !== 'array') {
                $method_param[] = $this->getController($parameter->getType()->getName());
            } elseif ($parameter->isOptional()) {

                if (isset($matches[1]) && isset($matches[2])) {
                    $method_param[] = $matches[2];
                } elseif (isset($matches[1]) && !isset($matches[2]) && !$this->verif) {
                    $method_param[] = $matches[1];
                } else {
                    $method_param[] = $parameter->getDefaultValue();
                }
            } elseif (isset($matches[1]) && isset($matches[2]) && !$parameter->isOptional()) {
                $method_param[] = $matches[1];
            } elseif (isset($matches[1]) && !isset($matches[2]) && !$parameter->isOptional()) {
                $method_param[] = $matches[1];
                $this->verif = true;
            }

        }

        return $reflected_method->invokeArgs($controllerDependancies, $method_param);
    }
}
