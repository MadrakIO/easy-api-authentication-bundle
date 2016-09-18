<?php

namespace MadrakIO\Bundle\EasyApiAuthenticationBundle\Security\Authentication\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use MadrakIO\Bundle\EasyApiAuthenticationBundle\Security\Authentication\Token\ApiToken;

class ApiProvider implements AuthenticationProviderInterface
{
    const INVALID_SIGNATURE_MSG = 'The API Request had an invalid signature.';

    public function authenticate(TokenInterface $token)
    {
        if ($this->validateVerificationString($token) === true) {
            $token->setAuthenticated(true);

            return $token;
        }

        throw new AuthenticationException(self::INVALID_SIGNATURE_MSG);
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof ApiToken;
    }

    protected function validateVerificationString(TokenInterface $token)
    {
        $request = $token->getRequest();
        $application = $token->getApplication();

        $queryParams = $request->query->all();
        $postParams = $request->request->all();

        $mergedParams = array_merge($queryParams, $postParams);
        unset($mergedParams['verification']);

        $apiCredentials = $token->getCredentials();

        $expectedVerification = hash_hmac(
                                            'sha512',
                                            implode(
                                                        '|',
                                                        [
                                                            $application->getPublicKey(),
                                                            $apiCredentials['timestamp'],
                                                            $request->getPathInfo(),
                                                            http_build_query($mergedParams),
                                                        ]
                                            ),
                                            $application->getPrivateKey());

        return $apiCredentials['verification_string'] === $expectedVerification;
    }
}
