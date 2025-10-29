<?php

namespace App\Entity;

use App\Repository\SpellRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SpellRepository::class)]
#[ORM\Index(columns: ['rarity'])]
class Spell
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank, Assert\Length(max: 120)]
    private ?string $name = null;

    #[ORM\Column(length: 160, unique: true)]
    #[Assert\NotBlank, Assert\Length(max: 160)]
    private ?string $slug = null;

    #[ORM\Column(length: 16)]
    #[Assert\Choice(['common','rare','epic','legendary'])]
    private string $rarity = 'common';

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url]
    private ?string $imageUrl = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private string $description = '';

    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(string $slug): self { $this->slug = $slug; return $this; }

    public function getRarity(): string { return $this->rarity; }
    public function setRarity(string $rarity): self { $this->rarity = $rarity; return $this; }

    public function getImageUrl(): ?string { return $this->imageUrl; }
    public function setImageUrl(?string $u): self { $this->imageUrl = $u; return $this; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $d): self { $this->description = $d; return $this; }

    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $b): self { $this->isActive = $b; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
