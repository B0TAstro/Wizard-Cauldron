<?php

namespace App\Entity;

use App\Repository\UserSpellRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserSpellRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_user_spell', columns: ['user_id', 'spell_id'])]
class UserSpell
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Spell::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Spell $spell = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $obtainedAt;

    public function __construct()
    {
        $this->obtainedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(User $user): self { $this->user = $user; return $this; }

    public function getSpell(): ?Spell { return $this->spell; }
    public function setSpell(Spell $spell): self { $this->spell = $spell; return $this; }

    public function getObtainedAt(): \DateTimeImmutable { return $this->obtainedAt; }
    public function setObtainedAt(\DateTimeImmutable $d): self { $this->obtainedAt = $d; return $this; }
}
