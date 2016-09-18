<?php

namespace MadrakIO\Bundle\EasyApiAuthenticationBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;

class ApiFactory implements SecurityFactoryInterface
{
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.api.'.$id;
        $provider = $container->setDefinition($providerId, new DefinitionDecorator('madrakio.easy_api_authentication.security.authentication.provider'));

        $listenerId = 'security.authentication.listener.api.'.$id;
        $listener = $container->setDefinition($listenerId, new DefinitionDecorator('madrakio.easy_api_authentication.security.authentication.listener'));

        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'madrak_io_easy_api_authentication';
    }

    public function addConfiguration(NodeDefinition $node)
    {
    }
}
