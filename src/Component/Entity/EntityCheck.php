<?php

namespace SYJS\JsBundle\Component\Entity;

use Symfony\Component\Validator\Constraint;

/**
 * @TODO : Recursive collection
 * @TODO : Finish checkContraintByOrmMetadata
 */
class EntityCheck
{
    public const ERROR_CONFIG_FILE_SIZE_MAX = 'Max size {{ max_size }} is greater than php allowed configuration {{ upload_max_filesize }} please call your administrator';
    public const ERROR_CONFIG_FILE_SIZE_RANGE = 'Min size {{ min_size }} is greater than Max size {{ max_size }}';
    public const ERROR_CONFIG_RANGE = 'Min range {{ min_range }} is greater than Max range {{ max_range }}';
    public const ERROR_CONFIG_TYPE = 'Constraint {{ constraint }} is not valid for this type {{ type }}';
    public const ERROR_CONFIG_CONSTRAINT_NOT_SUPPORTED = 'Check Constraint {{ constraint }} is not yet supported please call your administrator';
    public const ERROR_CONFIG_RANGE_ORM = 'Max range {{ max_range }} is greater than allowed database configuration {{ length }} ';

    public function __construct(
        private EntityReflection $entity
    ) {
    }

    public static function isChar(string $type): bool
    {
        return 'string' === $type || 'text' === $type;
    }

    public static function isNumeric(string $type): bool
    {
        return 'integer' === $type || 'smallint' === $type || 'bigint' === $type || 'decimal' === $type || 'float' === $type;
    }

    public function checkPropertyContraints(string $propertyName): void
    {
        $metadata = $this->entity->getPropertyMetaData($propertyName);
        foreach ($this->entity->getPropertyConstraints($propertyName)->constraints as $assert) {
            try {
                $this->checkContraintByOrmMetadata($assert, $metadata);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }
    }

    private function checkContraintByOrmMetadata(Constraint $constraint, array $ormMetadata): void
    {
        $entityType = $ormMetadata['type'];
        $entityLength = $ormMetadata['length'];

        $func = new \ReflectionClass($constraint);

        $name = $func->getShortName();

        switch ($name) {
            case 'NotBlank':
            case 'Blank':
            case 'Regex':
                break;

            case 'Url':
            case 'Email':
                if (!self::isChar($entityType)) {
                    throw new \Exception(strtr(self::ERROR_CONFIG_TYPE, ['{{ constraint }}' => $name, '{{ type }}' => $entityType]));
                }

                 break;

            case 'Length':
                if (!self::isChar($entityType)) {
                    throw new \Exception(strtr(self::ERROR_CONFIG_TYPE, ['{{ constraint }}' => $name, '{{ type }}' => $entityType]));
                }
                if ($constraint->min > $constraint->max) {
                    throw new \Exception(strtr(self::ERROR_CONFIG_RANGE, ['{{ min_range }}' => $constraint->min, '{{ max_range }}' => $constraint->max]));
                }
                if ($entityLength && ($constraint->max > $entityLength)) {
                    throw new \Exception(strtr(self::ERROR_CONFIG_RANGE_ORM, ['{{ max_range }}' => $constraint->max, '{{ length }}' => $entityLength]));
                }

                break;

            case 'Range':
                if (!self::isNumeric($entityType)) {
                    throw new \Exception(strtr(self::ERROR_CONFIG_TYPE, ['{{ constraint }}' => $name, '{{ type }}' => $entityType]));
                }
                if ($constraint->min > $constraint->max) {
                    throw new \Exception(strtr(self::ERROR_CONFIG_RANGE, ['{{ min_range }}' => $constraint->min, '{{ max_range }}' => $constraint->max]));
                }
                if ($entityLength && ($constraint->max > $entityLength)) {
                    throw new \Exception(strtr(self::ERROR_CONFIG_RANGE_ORM, ['{{ max_range }}' => $constraint->max, '{{ length }}' => $entityLength]));
                }

                break;

            case 'Image':
            case 'File':
                $upload_max_size = \ini_get('upload_max_filesize');
                if ($constraint->maxSize > $upload_max_size) {
                    throw new \Exception(strtr(self::ERROR_CONFIG_FILE_SIZE_MAX, ['{{ upload_max_filesize }}' => $upload_max_size, '{{ max_size }}' => $constraint->maxSize]));
                }
                if ($constraint->maxSize > $constraint->minSize) {
                    throw new \Exception(strtr(self::ERROR_CONFIG_FILE_SIZE_RANGE, ['{{ min_size }}' => $constraint->minSize, '{{ max_size }}' => $constraint->maxSize]));
                }

                break;

            default:
                throw new \Exception(strtr(self::ERROR_CONFIG_CONSTRAINT_NOT_SUPPORTED, ['{{ constraint }}' => $name]));
        }
    }
}
