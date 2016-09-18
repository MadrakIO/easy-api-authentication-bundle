<?php

namespace MadrakIO\Bundle\EasyApiAuthenticationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use MadrakIO\Bundle\EasyApiAuthenticationBundle\DependencyInjection\Security\Factory\ApiFactory;

class MadrakIOEasyApiAuthenticationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new ApiFactory());
    }
}
