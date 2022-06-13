<?php

namespace SYJS\JsBundle\Tests\App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends BaseKernel
{
    use MicroKernelTrait;

    private function getBundlesPath(): string
    {
        return __DIR__.'/bundles.php';
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config_test.yml');
    }
}
