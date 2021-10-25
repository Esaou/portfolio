<?php


namespace App\Service;

class Container
{

    private $instances = [];

    public function getController($key)
    {

        if (!isset($this->instances[$key])) {

            $reflected_class = new \ReflectionClass($key);
            if ($reflected_class->isInstantiable()) {
                $constructor = $reflected_class->getConstructor();
                if ($constructor){
                    $parameters = $constructor->getParameters();
                    $constructor_parameters = [];
                    foreach ($parameters as $parameter) {
                        if ($parameter->getType() && $parameter->getType()->getName() !== 'int' && $parameter->getType()->getName() !== 'string' && $parameter->getType()->getName() !== 'array') {
                            $constructor_parameters[] = $this->getController($parameter->getType()->getName());
                        } else {
                            $constructor_parameters[] = $parameter->getDefaultValue();
                        }
                    }
                    $this->instances[$key] = $reflected_class->newInstanceArgs($constructor_parameters);
                } else {
                    $this->instances[$key] = $reflected_class->newInstance();
                }
            } else {
                throw new \Exception($key .' is not intanciable');
            }

        }

        return $this->instances[$key];
    }

    public function getMethod($class,$method,$controllerDependancies,$matches) {
        $reflected_method = new \ReflectionMethod($class,$method);

        $parameters = $reflected_method->getParameters();

        $method_parameters = [];


        foreach ($parameters as $parameter) {
            if ($parameter->getType() && $parameter->getType()->getName() !== 'int' && $parameter->getType()->getName() !== 'string' && $parameter->getType()->getName() !== 'array') {
                $method_parameters[] = $this->getController($parameter->getType()->getName());
            } elseif($parameter->isOptional()){

                if (isset($matches[1]) && !isset($matches[2])) {
                    $method_parameters[] = $matches[1];
                } elseif(isset($matches[1]) && isset($matches[2])) {
                    $method_parameters[] = $matches[2];
                } else {
                    $method_parameters[] = $parameter->getDefaultValue();
                }

            } elseif(isset($matches[1])) {
                $method_parameters[] = $matches[1];
            }
        }

        return $reflected_method->invokeArgs($controllerDependancies,$method_parameters);

    }
}
