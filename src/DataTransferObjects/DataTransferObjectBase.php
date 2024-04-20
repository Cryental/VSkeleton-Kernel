<?php

namespace Volistx\FrameworkKernel\DataTransferObjects;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

abstract class DataTransferObjectBase
{
    protected $entity;

    public function __construct($entity)
    {
        $this->entity = $entity;

        $class = new ReflectionClass(static::class);

        if ($entity instanceof stdClass) {
            $parameters = get_object_vars($entity);
        } elseif (is_array($entity)) {
            $parameters = $entity;
        } elseif (is_object($entity) && method_exists($entity, 'toArray')) {
            $parameters = $entity->toArray();
        } else {
            throw new InvalidArgumentException("Constructor parameter must be an array, a stdClass object or an object with a toArray method.");
        }

        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            $property = $reflectionProperty->getName();
            if (array_key_exists($property, $parameters)) {
                $this->{$property} = $parameters[$property];
            }
        }
    }
}
