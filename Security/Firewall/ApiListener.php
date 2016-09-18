<?php

namespace MadrakIO\Bundle\EasyApiAuthenticationBundle\Security\Firewall;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use MadrakIO\Bundle\EasyApiAuthenticationBundle\Security\Authentication\Token\ApiToken;

class ApiListener implements ListenerInterface
{
    const REQUEST_AGE_LIMIT = 300;
    const NOT_SIGNED_CORRECTLY_MSG = 'The API Request was not signed appropriately.';
    const INVALID_TIMESTAMP_MSG = 'The Timestamp provided was invalid.';
    const INVALID_API_KEY_MSG = 'The API Key provided was not valid.';
    const DISABLED_APPLICATION_MSG = 'The API Key provided belongs to an application which has been disabled.';

    protected $tokenStorage;
    protected $authenticationManager;
    protected $applicationRepository;
    protected $applicationEntityClass;

    public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager, EntityManagerInterface $entityManager, $applicationEntityClass)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->applicationRepository = $entityManager->getRepository($applicationEntityClass);
        $this->applicationEntityClass = $applicationEntityClass;
    }

    public function handle(GetResponseEvent $event)
    {
        $credentials = $this->getCredentialsFromRequest($event->getRequest());
        $response = $this->generateAccessDeniedError('ERROR002: An unexpected error has occurred.');

        try {
            if (empty($credentials['public_key']) === true || empty($credentials['verification_string']) === true || empty($credentials['timestamp']) === true) {
                throw new BadCredentialsException(self::NOT_SIGNED_CORRECTLY_MSG);
            }

            if (is_numeric($credentials['timestamp']) === false || $credentials['timestamp'] > time() || time() - $credentials['timestamp'] > self::REQUEST_AGE_LIMIT) {
                throw new BadCredentialsException(self::INVALID_TIMESTAMP_MSG);
            }

            $application = $this->applicationRepository->findOneBy(['publicKey' => $credentials['public_key']]);
            if (($application instanceof $this->applicationEntityClass) === false) {
                throw new BadCredentialsException(self::INVALID_API_KEY_MSG);
            }

            if ($application->getEnabled() === false) {
                throw new BadCredentialsException(self::DISABLED_APPLICATION_MSG);
            }

            $credentials['application'] = $application;

            $this->tokenStorage->setToken($this->authenticationManager->authenticate(new ApiToken($credentials)));

            return;
        } catch (AuthenticationException $failed) {
            $response = $this->generateAccessDeniedError($failed->getMessage());
        }

        $event->setResponse($response);

        return;
    }

    protected function getCredentialsFromRequest(Request $request)
    {
        $credentials = ['request' => $request];

        $apiRegex = '/ApiAuthorization PublicKey="([^"]+)", Verification="([^"]+)", Timestamp="([^"]+)")"/';
        if ($request->headers->has('api_auth') && 1 === preg_match($apiRegex, $request->headers->get('api_auth'), $matches)) {
            $credentials['public_key'] = $matches[1];
            $credentials['verification_string'] = $matches[2];
            $credentials['timestamp'] = $matches[3];

            return $credentials;
        }

        $sources = ['headers', 'query', 'request'];
        foreach ($sources as $source) {
            if ($request->{$source}->has('key') && $request->{$source}->has('verification') && $request->{$source}->has('timestamp')) {
                $credentials['public_key'] = $request->{$source}->get('key');
                $credentials['verification_string'] = $request->{$source}->get('verification');
                $credentials['timestamp'] = $request->{$source}->get('timestamp');
            }
        }

        return $credentials;
    }

    protected function generateAccessDeniedError($message)
    {
        return new JsonResponse(['error' => true, 'errors' => [$message]], 403);
    }
}
