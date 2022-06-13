<?php

namespace SYJS\JsBundle\Tests;

use SYJS\JsBundle\Component\Entity\EntityMapping;
use SYJS\JsBundle\Component\Entity\EntityReflection;
use SYJS\JsBundle\Tests\App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class EntityMappingTest extends WebTestCase
{
    private ContainerInterface $container;

    public function __construct()
    {
        $kernel = self::bootKernel(['test_case' => 'Basic']);
        $this->container = $kernel->getContainer();

        parent::__construct();
    }

    private function getEntityReflection(string $name): EntityReflection
    {
        return new EntityReflection(
            $name,
            $this->container->get('doctrine')->getManager(),
            $this->container->get('test.validator')
        );
    }

    private function getEntityMapping(string $name, bool $isTypeEnable): EntityMapping
    {
        $entityReflection = $this->getEntityReflection($name);

        return new EntityMapping(
            $entityReflection,
            $this->container->get('translator'),
            $this->container->getParameter('SYJSJ.languages'),
            $isTypeEnable
        );
    }

    public function testEntityReflection(): void
    {
        $entityReflection = $this->getEntityReflection(User::class);
        $validConstraints = false;
        foreach ($entityReflection->getPropertyConstraints('username')->constraints as $constraint) {
            if ($constraint instanceof Length) {
                $validConstraints = true;
            }
        }
        $this->assertTrue($validConstraints);
        $this->assertSame($entityReflection->getPropertyMetaData('username')['type'], 'string');
    }

    public function testEntityMappingAssert(): void
    {
        $entityMapping = $this->getEntityMapping(User::class, false);
        $this->assertEquals(property_exists($entityMapping->getPropertyConstraints('id'), 'Type'), false);

        $entityMapping = $this->getEntityMapping(User::class, true);
        $this->assertEquals(property_exists($entityMapping->getPropertyConstraints('username'), 'NotBlank'), true);
        $this->assertEquals(property_exists($entityMapping->getPropertyConstraints('id'), 'Type'), true);
        $this->assertEquals($entityMapping->getPropertyConstraints('id')->Type->type, 'integer');
    }
}
