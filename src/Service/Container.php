<?php


namespace App\Service;

class Container
{

    private $instances = [];

    public function get($key)
    {

        if (!isset($this->instances[$key])) {
            $reflected_class = new \ReflectionClass($key);
            if ($reflected_class->isInstantiable()) {
                $constructor = $reflected_class->getConstructor();
                if ($constructor) {
                    $parameters = $constructor->getParameters();
                    $constructor_parameters = [];
                    foreach ($parameters as $parameter) {
                        if ($parameter->getType() && $parameter->getType()->getName() !== 'int' && $parameter->getType()->getName() !== 'string' && $parameter->getType()->getName() !== 'array') {
                            $constructor_parameters[] = $this->get($parameter->getType()->getName());
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
}
