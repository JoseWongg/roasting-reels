<?php

namespace App\Services;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * This class is used to add the user roles to the JWT payload.
 * It will be invoked every time a JWT is created, ensuring that the token includes the roles of the authenticated user.
 * The roles can then be used for role-based access control in the application.
 * For instance, use attributes such as #[IsGranted('ROLE_EDITOR')] to restrict access to certain routes to users with the 'ROLE_EDITOR' role.
 */
class JWTCreatedSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'lexik_jwt_authentication.on_jwt_created' => 'onJWTCreated',
        ];
    }

    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();
        $payload = $event->getData();

        // Include user roles in the payload
        $payload['roles'] = $user->getRoles();

        $event->setData($payload);
    }
}