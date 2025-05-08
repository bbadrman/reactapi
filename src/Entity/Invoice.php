<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\InvoiceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\User;
use App\Controller\InvoiceIncrementationController;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new GetCollection(
            uriTemplate: '/customers/{id}/invoices',
            normalizationContext: ['groups' => ['invoices_subresource']],
            paginationEnabled: false,
            paginationItemsPerPage: 10,
            order: ['amount' => 'desc']
        ),
        new Get(),
        new Put(),
        new Delete(),
        new Post(),
        new Post(
            uriTemplate: '/invoices/{id}/increment',
            controller: InvoiceIncrementationController::class,
            openapiContext: [
                'summary' => 'Incrémente une facture',
                'description' => 'Incremente le chrono d\'une facture donnée'
            ]
        )
    ],
    paginationItemsPerPage: 10,
    order: ['amount' => 'DESC'],
    normalizationContext: ['groups' => ['invoice:read']],
    denormalizationContext: ['disable_type_enforcement' => true]
)]
#[ApiFilter(OrderFilter::class, properties: ['amount', 'sentAt'])]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['invoice:read', 'customer:read', 'invoices_subresource'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['invoice:read', 'customer:read', 'invoices_subresource'])]
    #[Assert\NotBlank(message: 'Le montant de la facture est obligatoire')]
    private ?float $amount = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['invoice:read', 'customer:read', 'invoices_subresource'])]
    private ?\DateTimeInterface $sentAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['invoice:read', 'customer:read', 'invoices_subresource'])]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[Groups(['invoice:read'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customer = null;

    #[ORM\Column]
    #[Groups(['invoice:read', 'customer:read'])]
    private ?int $chrono = null;

    //Permet de récupérer le User à qui appartient finalement la facture
    #[Groups(['invoice:read', 'invoices_subresource'])]
    public function getUser(): ?User
    {
        return $this->customer ? $this->customer->getUser() : null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount($amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt($sentAt): static
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getChrono(): ?int
    {
        return $this->chrono;
    }

    public function setChrono($chrono): static
    {
        $this->chrono = $chrono;

        return $this;
    }
}
