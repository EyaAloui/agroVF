<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
#[Vich\Uploadable]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"Ce champs est obligatoire")]
    #[Assert\Length(
        min:5 ,
        minMessage: 'Veillez introduire au minimum 5 caractères.',)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message:"Ce champs est obligatoire")]
    #[Assert\GreaterThan("today", message:"La date de votre évènement ne doit pas être en passé!")]
    private ?\DateTimeInterface $date = null;

    #[Assert\NotBlank(message:"Ce champs est obligatoire")]
    #[Assert\Length(
        min:10 ,
        minMessage: 'Veillez introduire au minimum 10 caractères.',)]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'evenement', targetEntity: Participation::class)]
    private Collection $participants;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"Ce champs est obligatoire")]
    #[Assert\Length(
        min:5 ,
        minMessage: 'Veillez introduire au minimum 5 caractères.',)]
    private ?string $Lieu = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $image;


    #[Vich\UploadableField(mapping: "my_events_uploads", fileNameProperty: "image")]
    private $imageFile = null;


    #[ORM\Column(type: "datetime", nullable: true)]
    private $updatedAt;

    #[ORM\ManyToMany(targetEntity: Utilisateur::class, inversedBy: 'evenements')]
    private Collection $Utilisateur;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->Utilisateur = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

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

    /**
     * @return Collection<int, Participation>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participation $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
            $participant->setEvenement($this);
        }

        return $this;
    }

    public function removeParticipant(Participation $participant): self
    {
        if ($this->participants->removeElement($participant)) {
            // set the owning side to null (unless already changed)
            if ($participant->getEvenement() === $this) {
                $participant->setEvenement(null);
            }
        }

        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->Lieu;
    }

    public function setLieu(string $Lieu): self
    {
        $this->Lieu = $Lieu;

        return $this;
    }

    public function __toString() {
        return $this->id;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    public function setImageFile(?File $image = null): void
    {
        $this->imageFile = $image;

        if ($image) {
            $this->updatedAt = new \DateTimeImmutable('now');
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return Collection<int, Utilisateur>
     */
    public function getUtilisateur(): Collection
    {
        return $this->Utilisateur;
    }

    public function addUtilisateur(Utilisateur $utilisateur): self
    {
        if (!$this->Utilisateur->contains($utilisateur)) {
            $this->Utilisateur->add($utilisateur);
        }

        return $this;
    }

    public function removeUtilisateur(Utilisateur $utilisateur): self
    {
        $this->Utilisateur->removeElement($utilisateur);

        return $this;
    }
}
