<?php

namespace SYJS\JsBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use SYJS\JsBundle\Component\Entity\EntityMapping;
use SYJS\JsBundle\Component\Form\FormReflection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExportFormCommand extends Command
{
    public function __construct(
        private array $languages,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private TranslatorInterface $translator,
        private FormFactoryInterface $formFactory,
        private FormRegistryInterface $formRegistry
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('js:form')
            ->setDescription('Export Form')
            ->addArgument('form', InputArgument::REQUIRED, 'What is your form?')
            ->addArgument('output', InputArgument::OPTIONAL, 'Where you want to export your mapping?')
            ->addOption('type', null, InputArgument::OPTIONAL, 'Do you want Orm Type Contraints ?', 'no')
        ;
    }

    protected function build(EntityMapping $ownerMapping, FormReflection $formReflection): array
    {
        $entityFields = [];
        $extraFields = [];
        foreach ($formReflection->getOwnerFields() as $field) {
            $entityFields[$field] = $ownerMapping->getPropertyConstraints($field);
        }
        foreach ($formReflection->getExtraFields() as $field) {
            $extraFields[$field] = 'todo';
        }

        return [
            $formReflection->getName() => [
                'extraFields' => $extraFields,
                'ownerFields' => [
                    $formReflection->getOwner()->getName() => [
                        'fields' => $entityFields,
                    ],
                ],
                'children' => [],
            ],
        ];
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $formReflection = new FormReflection($input->getArgument('form'), $this->entityManager, $this->validator, $this->formFactory, $this->formRegistry);
        $ownerMapping = new EntityMapping($formReflection->getOwner(), $this->translator, $this->languages, 'yes' === $input->getOption('type'));
        $output->writeln(json_encode($this->build($ownerMapping, $formReflection)));

        return Command::SUCCESS;
    }
}
