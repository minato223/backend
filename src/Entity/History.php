<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\HistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\GetCollection;

#[
    ORM\Entity(repositoryClass: HistoryRepository::class),
    ApiResource(
        operations: [
            new GetCollection(
                normalizationContext: ['groups' => ['history:read', 'history:list']]
            ),
        ]
    ),
]
class History
{

    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';
    public const ACTION_DELETE = 'delete';


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $extra = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $entity = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $action = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $changes = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExtra(): array
    {
        return $this->extra;
    }

    public function setExtra(array $extra): static
    {
        $this->extra = $extra;

        return $this;
    }

    public function getEntity(): ?string
    {
        return $this->entity;
    }

    public function setEntity(?string $entity): static
    {
        $this->entity = $entity;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getChanges(): ?string
    {
        return $this->changes;
    }

    public function setChanges(?string $changes): static
    {
        $this->changes = $changes;

        return $this;
    }
}
