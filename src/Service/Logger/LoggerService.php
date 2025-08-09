<?php

namespace App\Service\Logger;

use App\Entity\History;
use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class LoggerService
{
    public function __construct(
        private readonly Security $security,
        private readonly EntityManagerInterface $em,
        private readonly RequestStack $requestStack,
    ) {}

    public function createLog($object): void
    {
        if (!$this->canWriteLog()) return;
        $log = (new History())
            ->setEntity(get_class($object))
            ->setAction(History::ACTION_CREATE)
            ->setChanges(null);
        $this->em->persist($log);
        $this->em->flush();
    }

    public function updateLog(PostUpdateEventArgs $args): void
    {
        if (!$this->canWriteLog()) return;
        try {
            $entity = $args->getObject();
            // Vérifiez si ObjectManager est une instance d'EntityManager
            $objectManager = $args->getObjectManager();
            if (!$objectManager instanceof EntityManagerInterface) {
                return; // Si ce n'est pas le cas, retournez sans rien faire
            }
            // Récupérez le UnitOfWork depuis l'EntityManager
            $unitOfWork = $objectManager->getUnitOfWork();
            // Récupérez le tableau des modifications sur l'entité
            $changes = $unitOfWork->getEntityChangeSet($entity);
            $action = History::ACTION_UPDATE;
            $object = $args->getObject();
            $log = (new History())
                ->setEntity(get_class($object))
                ->setAction($action)
                ->setChanges(json_encode($changes));
            $this->em->persist($log);
            $this->em->flush();
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    public function deleteLog($object, $changes): void
    {
        if (!$this->canWriteLog()) return;
        $log = (new History())
            ->setEntity(get_class($object))
            ->setAction(History::ACTION_DELETE)
            ->setChanges($changes);
        $this->em->persist($log);
        $this->em->flush();
    }

    private function canWriteLog(): bool
    {
        $accessor = new PropertyAccessor();
        $currentRequest = $this->requestStack->getCurrentRequest();
        if (!$currentRequest) {
            return false;
        }
        $offerIndexation = $accessor->getValue($currentRequest->attributes->all(), '[offer_indexation]');
        if ($offerIndexation === true) {
            return false;
        }
        return true;
    }
}
