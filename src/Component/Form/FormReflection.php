<?php

namespace SYJS\JsBundle\Component\Form;

use Doctrine\ORM\EntityManagerInterface;
use SYJS\JsBundle\Component\Entity\EntityReflection;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @TODO : Children
 */
final class FormReflection
{
    public const ERROR_PROPERTY_NOT_FOUND = 'The property {{ property }} is not found';

    public const REQUIRED = ['data_class'];

    private $instance;

    private string $name;
    private EntityReflection $owner;
    private array $entityFields = [];
    private array $extraFields = [];

    public function __construct(
        private string $formAlias,
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
        private FormFactoryInterface $formFactory,
        private FormRegistryInterface $formRegistry
    ) {
        $type = $formRegistry->getType($formAlias);
        $this->formAlias = $formAlias;
        $this->instance = $type;
        $formEntity = new $formAlias();
        $optionFormResolve = $type->getOptionsResolver();
        $optionFormResolve->setRequired(self::REQUIRED);
        $optionFormResolve->setDefault('extra_fields', []);
        $optionFormResolve->setDefault('style', false);
        $optionFormResolve->setDefault('component', false);
        $formEntity->setDefaultOptions($optionFormResolve);
        $formResolve = $optionFormResolve->resolve();
        $this->owner = new EntityReflection($formResolve['data_class'], $em, $validator);
        $push = $type->createBuilder($formFactory, $type->getBlockPrefix(), []);
        $type->buildForm($push, $formResolve);

        $this->name = $formEntity->getName();
        foreach ($push->all() as $name => $field) {
            if (\in_array($name, $formResolve['extra_fields'])) {
                $this->extraFields[] = $name;
            } else {
                if ($this->owner->getPropertyMetaData($name)) {
                    $this->entityFields[] = $name;
                } else {
                    throw new \Exception(strtr(self::ERROR_PROPERTY_NOT_FOUND, ['{{ property }}' => $propertyName]));
                }
            }
        }
    }

    public function getExtraFields(): array
    {
        return $this->extraFields;
    }

    public function getOwnerFields(): array
    {
        return $this->entityFields;
    }

    public function getOwner(): EntityReflection
    {
        return $this->owner;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
