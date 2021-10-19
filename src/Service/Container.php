<?php


namespace App\Service;

class Container
{

    public $registry = [];
    private $instances = [];
    private $factories = [];

    public function set($key, $resolver)
    {
        $this->registry[$key] = $resolver;
    }

    public function setFactory($key, $resolver)
    {
        $this->factories[$key] = $resolver;
    }

    public function setInstance($instance)
    {
        $reflection = new \ReflectionClass($instance);
        $this->instances[$reflection->getName()] = $instance;
    }

    public function get($key)
    {

        if (isset($this->factories[$key])) {
            return $this->factories[$key]();
        }

        if (!isset($this->instances[$key])) {
            if (isset($this->registry[$key])) {
                $this->instances[$key] = $this->registry[$key]();
            } else {

                $reflected_class = new \ReflectionClass($key);
                if ($reflected_class->isInstantiable()) {
                    $constructor = $reflected_class->getConstructor();
                    if ($constructor){
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
        }

        return $this->instances[$key];
    }
}
