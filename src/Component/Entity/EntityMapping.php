<?php

namespace SYJS\JsBundle\Component\Entity;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Contracts\Translation\TranslatorInterface;

final class EntityMapping extends EntityCheck
{
    public const ERROR_CONSTRAINT_NOT_SUPPORTED = 'Constraint {{ constraint }} is not yet supported please call your administrator';
    public const ERROR_CONSTRAINT_DEPRECIATED = 'Constraint {{ constraint }} is depreciated please call your administrator ';
    public const ERROR_CONSTRAINT_DUPLICATED = 'Constraint {{ constraint }} is duplicated';

    public function __construct(
        private EntityReflection $entity,
        private TranslatorInterface $translator,
        private array $languages,
        private bool $isTypeEnable = false
    ) {
        parent::__construct($entity);
    }

    public function getPropertyConstraints(string $propertyName): \stdClass
    {
        $response = new \stdClass();

        $property = $this->entity->getPropertyConstraints($propertyName);

        if ($property) {
            foreach ($property->constraints as $name => $assert) {
                $assert = $this->parseConstraint($assert);

                switch ($assert->name) {
                    case 'Regex':
                        if (!isset($response->{$assert->name})) {
                            $response->{$assert->name} = [];
                        }

                        $response->{$assert->name}[] = $assert->definition;

                        break;

                    default:
                        if (isset($response->{$assert->name})) {
                            throw new \Exception(strtr(self::ERROR_CONSTRAINT_DUPLICATED, ['{{ constraint }}' => $assert->name]));
                        }
                        $response->{$assert->name} = $assert->definition;
                }
            }
            $this->checkPropertyContraints($propertyName);
        }

        if ($this->isTypeEnable) {
            $metadata = $this->entity->getPropertyMetaData($propertyName);
            $case1 = $metadata['length'] && !isset($response->Length);
            $case2 = $metadata['type'] && !isset($response->Type);

            if ($case1 || $case2) {
                if ($case1) {
                    $assert = $this->parseConstraint(new Length(['max' => $metadata['length'], 'min' => 0]));
                    $response->{$assert->name} = $assert->definition;
                }
                if ($case2) {
                    $assert = $this->parseConstraint(new Type(['type' => $metadata['type']]));
                    $response->{$assert->name} = $assert->definition;
                }
            }
        }

        return $response;
    }

    public function getAllContraints(): array
    {
        $response = [];
        foreach ($this->entity->getMetadata()->fieldMappings as $fieldName => $field) {
            $response[$fieldName] = $this->getPropertyConstraints($fieldName);
        }

        return $response;
    }

    private function getTrans(string $messagePattern, array $param = []): array
    {
        $response = [];
        foreach ($this->languages as $lang) {
            $this->translator->setLocale($lang);
            $response[$lang] = $this->translator->trans($messagePattern, $param, 'validators');
        }

        return $response;
    }

    private function getTransChoice(string $messagePattern, int $number, array $param = []): array
    {
        $param['%count%'] = $number;

        return $this->getTrans($messagePattern, $param);
    }

    private function parseConstraint(Constraint $assert): \stdClass
    {
        $func = new \ReflectionClass($assert);
        $name = $func->getShortName();
        $definition = new \stdClass();

        switch ($name) {
            case 'NotBlank':
            case 'Blank':
            case 'NotNull':
            case 'IsNull':
            case 'IsTrue':
            case 'IsFalse':
            case 'Date':
            case 'Time':
            case 'Bic':
            case 'Currency':
            case 'Luhn':
            case 'Iban':
            case 'UserPassword':
            case 'Email':
                $definition->message = $this->getTrans($assert->message);

                break;

            case 'Type':
                $definition->type = $assert->type;
                $definition->message = $this->getTrans($assert->message, ['{{ type }}' => $assert->type]);

                break;

            case 'Range':
                $definition->min = $assert->min;
                $definition->max = $assert->max;
                $definition->minMessage = $this->getTransChoice($assert->minMessage, $assert->min, ['{{ limit }}' => $assert->min]);
                $definition->maxMessage = $this->getTransChoice($assert->maxMessage, $assert->max, ['{{ limit }}' => $assert->max]);
                $definition->invalidMessage = $assert->invalidMessage;

                break;

            case 'Length':
                $definition->min = $assert->min;
                $definition->max = $assert->max;
                $definition->minMessage = $this->getTransChoice($assert->minMessage, $assert->min, ['{{ limit }}' => $assert->min]);
                $definition->maxMessage = $this->getTransChoice($assert->maxMessage, $assert->max, ['{{ limit }}' => $assert->max]);
                if ($assert->min == $assert->max) {
                    $definition->exactMessage = $this->getTrans($assert->exactMessage, ['{{ limit }}' => $assert->max]);
                }

                break;

            case 'Url':
                $definition->message = $this->getTrans($assert->message);
                $definition->protocols = $this->getTrans($assert->protocols);

                break;

            case 'Regex':
                $definition->message = $this->getTrans($assert->message);
                $definition->htmlPattern = $assert->htmlPattern;
                $definition->pattern = $assert->pattern;
                $definition->match = $assert->match;

                break;

            case 'Ip':
                $definition->version = $assert->version;
                $definition->message = $this->getTrans($assert->message);

                break;

            case 'Uuid':
                $definition->strict = $assert->strict;
                $definition->versions = $assert->versions;
                $definition->message = $this->getTrans($assert->message);

                break;

            case 'EqualTo':
            case 'NotEqualTo':
            case 'IdenticalTo':
            case 'NotIdenticalTo':
            case 'LessThan':
            case 'LessThanOrEqual':
            case 'GreaterThan':
            case 'GreaterThanOrEqual':
                $definition->value = $assert->value;
                $definition->message = $this->getTrans($assert->message, ['{{ compared_value }}' => $assert->value]);

                break;

            case 'DateTime':
                $definition->format = $assert->format;
                $definition->message = $this->getTrans($assert->message);

                break;

            case 'Image':
                $definition->minRatio = $assert->minRatio;
                $definition->maxRatio = $assert->maxRatio;
                $definition->allowSquare = $assert->allowSquare;
                $definition->allowLandscape = $assert->allowLandscape;
                $definition->allowPortrait = $assert->allowPortrait;
                $definition->maxWidth = $assert->maxWidth;
                $definition->maxWidthMessage = $this->getTrans($assert->maxSizeMessage, ['{{ width }}' => '%width%', '{{ max_width }}' => $assert->maxWidth]);
                $definition->minWidth = $assert->minWidth;
                $definition->minWidthMessage = $this->getTrans($assert->minSizeMessage, ['{{ width }}' => '%width%', '{{ min_width }}' => $assert->minWidth]);
                $definition->maxHeight = $assert->maxHeight;
                $definition->maxHeightMessage = $this->getTrans($assert->maxSizeMessage, ['{{ height }}' => '%height%', '{{ max_height }}' => $assert->maxHeight]);
                $definition->minHeight = $assert->minHeight;
                $definition->minHeightMessage = $this->getTrans($assert->minSizeMessage, ['{{ height }}' => '%height%', '{{ min_height }}' => $assert->minHeight]);
                // no break
            case 'File':
                $upload_max_size = \ini_get('upload_max_filesize');
                $definition->maxSize = $assert->maxSize < $upload_max_size ? $assert->maxSize : $upload_max_size;
                $definition->mimeTypes = $assert->mimeTypes;
                $definition->disallowEmptyMessage = $this->disallowEmptyMessage;
                $definition->uploadErrorMessage = $this->getTrans($assert->uploadErrorMessage);
                $definition->uploadFormSizeErrorMessage = $this->getTrans($assert->uploadFormSizeErrorMessage);
                $definition->mimeTypesMessage = $this->getTrans($assert->mimeTypesMessage, ['{{ types  }}' => '%type%']);
                $definition->maxSizeMessage = $this->getTrans($assert->maxSizeMessage, ['{{ limit }}' => $assert->maxSize, '{{ suffix }}' => '%suffix%']);

                break;

            case 'CardScheme ':
                $definition->message = $this->getTrans($assert->message);
                $definition->schemes = $assert->schemes;

                break;

            case 'Isbn':
                $definition->type = $assert->type;
                $definition->message = $this->getTrans($assert->message);
                $definition->isbn10Message = $this->getTrans($assert->isbn10Message);
                $definition->isbn13Message = $this->getTrans($assert->isbn13Message);
                $definition->sbn10Message = $this->getTrans($assert->sbn10Message);

                break;

            case 'Issn':
                $definition->caseSensitive = $assert->caseSensitive;
                $definition->requireHyphen = $assert->requireHyphen;
                $definition->message = $this->getTrans($assert->message);

                break;

            case 'Callback':
            case 'Expression':
            case 'All':
            case 'Valid':
                throw new \Exception(strtr(self::ERROR_CONSTRAINT_DEPRECIATED, ['{{ constraint }}' => $name]));

            default:
                throw new \Exception(strtr(self::ERROR_CONSTRAINT_NOT_SUPPORTED, ['{{ constraint }}' => $name]));
        }
        $response = new \stdClass();
        $response->name = $name;
        $response->definition = $definition;

        return $response;
    }
}
