<?php

namespace SYJS\JsBundle\Component\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata as OrmClassMetadata;
use Symfony\Component\Validator\Mapping\ClassMetadata as SymfonyClassMetadata;
use Symfony\Component\Validator\Mapping\PropertyMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class EntityReflection
{
    public const ERROR_ENTITY_NOT_FOUND = 'The entity {{ entity }} is not found';
    public const ERROR_PROPERTY_NOT_FOUND = 'The property {{ property }} is not found';

    private $instance;
    private SymfonyClassMetadata $constraints;
    private OrmClassMetadata $metaData;

    public function __construct(
        private string $entityAlias,
        private EntityManagerInterface $em,
        private ValidatorInterface $validator
    ) {
        if (!class_exists($entityAlias)) {
            throw new \Exception(strtr(self::ERROR_ENTITY_NOT_FOUND, ['{{ entity }}' => $entityAlias]));
        }
        $entity = new $entityAlias();
        $this->instance = $entity;
        $this->constraints = $this->validator->getMetadataFor($this->instance);
        $this->metaData = $this->em->getClassMetadata(\get_class($this->instance));
    }

    public function getConstraints(): SymfonyClassMetadata
    {
        return $this->constraints;
    }

    public function getMetadata(): OrmClassMetadata
    {
        return $this->metaData;
    }

    public function getPropertyConstraints(string $propertyName): ?PropertyMetadata
    {
        $constraints = $this->getConstraints();

        return \array_key_exists($propertyName, $constraints->properties) ? $constraints->properties[$propertyName] : null;
    }

    public function getPropertyMetaData(string $propertyName): array
    {
        $metaData = $this->getMetadata();
        if (!\array_key_exists($propertyName, $metaData->fieldMappings)) {
            throw new \Exception(strtr(self::ERROR_PROPERTY_NOT_FOUND, ['{{ property }}' => $propertyName]));
        }

        return $metaData->fieldMappings[$propertyName];
    }

    public function getName()
    {
        return $this->entityAlias;
    }
}
