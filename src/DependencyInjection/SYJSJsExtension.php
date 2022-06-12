<?php

namespace SYJS\JsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SYJSJsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        if (!isset($configs[0]['languages'])) {
            throw new \InvalidArgumentException('The languages option must be set in your config  syjs_js => [ languages => ["fr","en"] ]');
        }

        $container->setParameter('SYJSJ.languages', $configs[0]['languages']);
    }
}
