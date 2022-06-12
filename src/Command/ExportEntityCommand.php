<?php

namespace SYJS\JsBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use SYJS\JsBundle\Component\Entity\EntityMapping;
use SYJS\JsBundle\Component\Entity\EntityReflection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExportEntityCommand extends Command
{
    public function __construct(
        private array $languages,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private TranslatorInterface $translator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('js:entity')
            ->setDescription('Export Entity')
            ->addArgument('entity', InputArgument::REQUIRED, 'What is your entity?')
            ->addArgument('output', InputArgument::OPTIONAL, 'Where you want to export your mapping?')
            ->addOption('type', null, InputArgument::OPTIONAL, 'Do you want Orm Type Contraints ?', 'yes')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entityInstance = new EntityReflection($input->getArgument('entity'), $this->entityManager, $this->validator);
        $entity = new EntityMapping($entityInstance, $this->translator, $this->languages, 'yes' === $input->getOption('type'));
        $output->writeln(json_encode($entity->getAllContraints()));

        return Command::SUCCESS;
    }
}
