<?php

namespace SYJS\JsBundle\Tests;

use SYJS\JsBundle\Component\Form\FormReflection;
use SYJS\JsBundle\Tests\App\Form\UserInscription;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class FormReflectionTest extends WebTestCase
{
    private ContainerInterface $container;

    public function __construct()
    {
        $kernel = self::bootKernel(['test_case' => 'Basic']);
        $this->container = $kernel->getContainer();
        parent::__construct();
    }

    public function testEntityReflection(): void
    {
        $form = new FormReflection(
            UserInscription::class,
            $this->container->get('doctrine')->getManager(),
            $this->container->get('test.validator'),
            $this->container->get('test.form.factory'),
            $this->container->get('test.form.registry')
        );
        $this->assertEquals($form->getOwnerFields()[0], 'username');
    }
}
