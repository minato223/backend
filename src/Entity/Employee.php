<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Repository\EmployeeRepository;
use App\Validator\ValidDate;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ORM\Entity(repositoryClass: EmployeeRepository::class),
    ApiResource(
        operations: [
            new GetCollection(
                normalizationContext: ['groups' => ['employee:read', 'employee:list']]
            ),
            new Get(
                normalizationContext: ['groups' => ['employee:read', 'employee:item']]
            ),
            new Post(
                denormalizationContext: ['groups' => ['employee:write', 'employee:create']],
                normalizationContext: ['groups' => ['employee:read', 'employee:item']]
            ),
            new Patch(
                denormalizationContext: ['groups' => ['employee:write', 'employee:update']],
                normalizationContext: ['groups' => ['employee:read', 'employee:item']]
            ),
            new Delete()
        ]
    ),
    UniqueEntity(
        fields: ['email'],
        message: 'Cet email est déjà utilisé par un autre employé.'
    )
]
class Employee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['employee:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['employee:read', 'employee:write'])]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Le prénom doit faire au moins {{ limit }} caractères',
        maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    #[Groups(['employee:read', 'employee:write'])]
    #[Assert\NotBlank(message: 'Le nom de famille est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Le nom de famille doit faire au moins {{ limit }} caractères',
        maxMessage: 'Le nom de famille ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $lastname = null;

    #[ORM\Column(length: 100)]
    #[Groups(['employee:read', 'employee:write'])]
    #[Assert\NotBlank(message: 'Le poste est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Le poste doit faire au moins {{ limit }} caractères',
        maxMessage: 'Le poste ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $job = null;

    #[ORM\Column(length: 255)]
    #[Groups(['employee:read', 'employee:write'])]
    #[Assert\NotBlank(message: 'L\'email est obligatoire')]
    #[Assert\Email(message: 'L\'email "{{ value }}" n\'est pas valide')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'L\'email ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(['employee:read', 'employee:write'])]
    #[Assert\NotNull(message: 'La date d\'embauche est obligatoire')]
    #[ValidDate()]
    private ?\DateTimeImmutable $hiringDate = null;

    // Getters et Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function getJob(): ?string
    {
        return $this->job;
    }

    public function setJob(string $job): static
    {
        $this->job = $job;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getHiringDate(): ?\DateTimeImmutable
    {
        return $this->hiringDate;
    }

    public function setHiringDate(\DateTimeImmutable $hiringDate): static
    {
        $this->hiringDate = $hiringDate;
        return $this;
    }
}
