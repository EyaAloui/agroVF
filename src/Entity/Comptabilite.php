<?php

namespace App\Entity;

use App\Repository\ComptabiliteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ComptabiliteRepository::class)]
class Comptabilite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_comptabilite = null;


    #[Assert\Type( type: 'float', )] 
    #[Assert\NotBlank(message:"le champ disponnible est obligatoire")]
    #[ORM\Column]
    private ?float $valeur = null;

    #[ORM\OneToMany(mappedBy: 'comptabilite', targetEntity: Facture::class)]
    private  $factures;
    public function __construct()
    {
        $this->factures = new ArrayCollection();
    }
    
   
   

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateComptabilite(): ?\DateTimeInterface
    {
        return $this->date_comptabilite;
    }

    public function setDateComptabilite(\DateTimeInterface $date_comptabilite): self
    {
        $this->date_comptabilite = $date_comptabilite;

        return $this;
    }

    public function getValeur(): ?float
    {
        return $this->valeur;
    }

    public function setValeur(float $valeur): self
    {
       
        $this->valeur = $valeur;

        return $this;
    }

    /**
     * @return Collection<int, Facture>
     */
    public function getFactures(): Collection
    {
        return $this->factures;
    }

    public function addFacture(Facture $facture): self
    {
        if (!$this->factures->contains($facture)) {
            $this->factures->add($facture);
           
          
        }

        return $this;
    }

    public function removeFacture(Facture $facture): self
    {
        if ($this->factures->removeElement($facture)) {
            // set the owning side to null (unless already changed)
            if ($facture->getComptabilite() === $this) {
                $facture->setComptabilite(null);
            }
        }

        return $this;
    }
    
        // ...
    
        public function calculerRevenu(): float
        {
            $achats = 0;
            $ventes = 0;
            $total= 0;
    
            foreach ($this->factures as $facture) {
                if ($facture->getType() === 'achat') {
                    $achats += $facture->getMontantTotale();
                } elseif ($facture->getType() === 'vente') {
                    $ventes += $facture->getMontantTotale();
                }
            }
            
            $total = $achats - $ventes;
            return $total;
        }
        
  

    public function __toString(){
        return $this->id;
    }

 /**
 * Calcule le revenu en soustrayant le montant total des achats du montant total des ventes.
 *
 * @param array $invoices Les factures à traiter.
 * @return float Le revenu calculé.
 */


}
