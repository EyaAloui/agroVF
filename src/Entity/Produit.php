<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[UniqueEntity(fields: ['nom'], message: 'Ce nom est déjà utilisée.')]
#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups("produits")]
    private ?int $id = null;

//    #[Assert\Type('string',message:"Nom doit etre de type chaine de caracteres")]
    #[Assert\NotNull(message:"Le nom ne peut pas être vide")]
    #[ORM\Column(length: 255)]
    #[Groups("produits")]
    private ?string $nom = null;

    #[Assert\NotNull(message:"Le prix ne peut pas être vide")]
    #[Assert\Type(type: 'float', message: "Le prix doit être un nombre décimal")]
    #[Assert\Positive(message: "Le prix doit être un nombre positive")]
    #[ORM\Column]
    #[Groups("produits")]
    private ?float $prix = null;

    #[Assert\NotNull(message:"La description ne peut pas être vide")]
    #[ORM\Column(length: 255)]
    #[Groups("produits")]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups("produits")]
    private ?bool $status = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["produits", "categories"])]
    private ?Categorie $categorie = null;

//    #[Assert\NotBlank(message:"Veuillez télécharger un fichier JPEG, PNG ou JPG valide")]
    #[ORM\Column(length: 255)]
    #[Groups("produits")]
    private ?string $image = null;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }
}
