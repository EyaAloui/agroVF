<?php

namespace App\Entity;

use App\Repository\EquipementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: EquipementRepository::class)]
class Equipement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[Assert\NotBlank(message:"le champ type est obligatoire")]
    #[Assert\Length(
        min: 4,
        max: 10,
        minMessage: 'donner un type au moins de {{ limit }} caractères ',
        maxMessage: 'donner un type maximum de {{ limit }} caractères',)]
        #[Assert\Type(
            type: 'string',
            message: 'le type {{ value }} n est pas valide {{ type }}.',)]
    #[ORM\Column(length: 255)]
    private ?string $type = null;
    #[Assert\NotBlank(message:"le champ disponnible est obligatoire")]
    #[Assert\Length(
        min: 4,
        max: 10,
        minMessage: 'donner une marque au moins de {{ limit }} caractères ',
        maxMessage: 'donner une marque maximum de {{ limit }} caractères',)]
        #[Assert\Type(
            type: 'string',
            message: 'la marque {{ value }} n est pas valide {{ type }}.',)]
    #[ORM\Column(length: 255)]
    private ?string $marque = null;
    #[Assert\NotBlank(message:"le champ disponnible est obligatoire")]
    #[Assert\Type(
        type: 'bool',
        message: '{{ value }} n est pas valide {{ type }}.',)]
    #[ORM\Column]
    private ?bool $disponnible = null;
    #[Assert\NotBlank(message:"le champ etat est obligatoire")]
    #[Assert\Type(
        type: 'string',
        message: 'l etat {{ value }} n est pas valide {{ type }}.',)]
    #[ORM\Column(length: 255)]
    private ?string $etat = null;
    #[Assert\NotBlank(message:"le champ matricule est obligatoire")]
    #[Assert\Length(
        min: 10,
        max: 10,
        minMessage: 'donner une matricule au moins de {{ limit }} caractères ',
        maxMessage: 'donner une matricule maximum de {{ limit }} caractères',)]
    #[ORM\Column(length: 255)]
    private ?string $matricule = null;
    #[Assert\NotBlank(message:"le champ matricule est obligatoire")]
    
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Employe $employe = null;

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

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(string $marque): self
    {
        $this->marque = $marque;

        return $this;
    }

    public function isDisponnible(): ?bool
    {
        return $this->disponnible;
    }

    public function setDisponnible(bool $disponnible): self
    {
        $this->disponnible = $disponnible;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getMatricule(): ?string
    {
        return $this->matricule;
    }

    public function setMatricule(string $matricule): self
    {
        $this->matricule = $matricule;

        return $this;
    }

    public function getEmploye(): ?Employe
    {
        return $this->employe;
    }

    public function setEmploye(?Employe $employe): self
    {
        $this->employe = $employe;

        return $this;
    }
}
