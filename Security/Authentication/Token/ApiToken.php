<?php

namespace MadrakIO\Bundle\EasyApiAuthenticationBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class ApiToken extends AbstractToken
{
    public $credentials;

    public function __construct($credentials)
    {
        parent::__construct();
        $this->credentials = $credentials;
    }

    public function getRequest()
    {
        return $this->credentials['request'];
    }

    public function getApplication()
    {
        return $this->credentials['application'];
    }

    public function getCredentials()
    {
        return $this->credentials;
    }
}
