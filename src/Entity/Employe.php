<?php

namespace App\Entity;

use App\Repository\EmployeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: EmployeRepository::class)]
class Employe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[Assert\NotBlank]
    #[Assert\Type(
        type: 'string',
        message: 'le nom {{ value }} n est pas valide {{ type }}.',)]
    #[Assert\Length(
        min: 4,
        max: 10,
        minMessage: 'donner le nom au moins {{ limit }} caractères ',
        maxMessage: 'donner le nom maximum {{ limit }} caractères',)]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;
    #[Assert\NotBlank]
    #[Assert\Type(
        type: 'string',
        message: 'le prenom {{ value }} n est pas valide {{ type }}.',)]
    #[Assert\Length(
        min: 3,
        max: 10,
        minMessage: 'donner un prenom au moins {{ limit }} caractères ',
        maxMessage: 'donner un prenom maximum {{ limit }} caractères',)]
    #[ORM\Column(length: 255)]
    private ?string $prenom = null;
    #[Assert\NotBlank]
    #[Assert\Type(
        type: 'string',
        message: 'le cin {{ value }} n est pas valide {{ type }}.',)]
    #[Assert\Length(
        min: 8,
        max: 8,
        minMessage: 'le cin doit etre de {{ limit }} caractères ',
        maxMessage: 'le cin doit etre de  {{ limit }} caractères',)]
    #[ORM\Column(length: 255)]
    private ?string $cin = null;

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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getCin(): ?string
    {
        return $this->cin;
    }

    public function setCin(string $cin): self
    {
        $this->cin = $cin;

        return $this;
    }
    public function __toString(){
        return $this->nom.' '.$this->prenom.' '.$this->cin;
       }
}
