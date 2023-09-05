<?php

namespace App\Entity;

use App\Repository\CultureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\Constructor;
use Symfony\Component\Validator\Constraints as Assert;
use symfony\Component\Serializer\Annotation\Groups;
#[ORM\Entity(repositoryClass: CultureRepository::class)]
class Culture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups("culture")]
    private ?int $id = null;
    #[Assert\NotBlank(message:"ce champ est obligatoire")]
    #[Assert\Type(
        type: 'string',
        message: 'le type {{ value }} n est pas valide {{ type }}.',)]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'donner le type au moins {{ limit }} caractères ',
        maxMessage: 'donner le type maximum {{ limit }} caractères',)]
    #[ORM\Column(length: 255)]
    #[Groups("culture")]
    private ?string $type = null;


    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups("culture")]
    #[Assert\NotNull(message:"ce champ est obligatoire")]
    private ?\DateTimeInterface $date_planting = null;
    // #[Assert\NotBlank]
    #[Assert\NotBlank(message:"ce champ est obligatoire")]
    #[Assert\Type(
        type: 'float',
        message: 'la quantité {{ value }} nest pas valide {{ type }}.',)]

    #[ORM\Column]
    #[Groups("culture")]
    private ?float $quantite = null;

    #[Assert\NotBlank(message:"ce champ est obligatoire")]
    #[Groups("culture")]
    #[ORM\OneToMany(mappedBy: 'culture', targetEntity: Terrain::class)]
    private Collection $terrain;

    public function __construct()
    {

        $this->terrain = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDatePlanting(): ?\DateTimeInterface
    {
        return $this->date_planting;
    }

    public function setDatePlanting(\DateTimeInterface $date_planting): self
    {
        $this->date_planting = $date_planting;

        return $this;
    }

    public function getQuantite(): ?float
    {
        return $this->quantite;
    }

    public function setQuantite(float $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    /**
     * @return Collection<int, Terrain>
     */
    public function getTerrain(): Collection
    {
        return $this->terrain;
    }

    public function addTerrain(Terrain $terrain): self
    {
        if (!$this->terrain->contains($terrain)) {
            $this->terrain->add($terrain);
            $terrain->setCulture($this);
        }

        return $this;
    }

    public function removeTerrain(Terrain $terrain): self
    {
        if ($this->terrain->removeElement($terrain)) {
            // set the owning side to null (unless already changed)
            if ($terrain->getCulture() === $this) {
                $terrain->setCulture(null);
            }
        }

        return $this;
    }
    public function __toString():string
    {
        return $this->type." ".$this->quantite;
    }
}