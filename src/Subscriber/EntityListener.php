<?php

namespace App\Subscriber;

use App\Entity\History;
use App\Service\Logger\LoggerService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

#[
    AsDoctrineListener(event: Events::postUpdate, priority: 2, connection: 'default'),
    AsDoctrineListener(event: Events::postPersist, priority: 3, connection: 'default'),
    AsDoctrineListener(event: Events::preRemove, priority: 4, connection: 'default'),
]

class EntityListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly LoggerService $loggerService,
        private readonly Security $security
    ) {}

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->loggerService->updateLog($args);
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $object = $args->getObject();
        if (!$object instanceof History) {
            $this->loggerService->createLog($args->getObject());
        }
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $object = $args->getObject();
        if (!$object instanceof History) {
            // $this->loggerService->deleteLog($args->getObject());
        }
    }
}
