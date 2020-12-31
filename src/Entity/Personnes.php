<?php

namespace App\Entity;

use App\Repository\PersonnesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PersonnesRepository::class)
 */
class Personnes
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sexe;

    /**
     * @ORM\ManyToOne(targetEntity=Adresses::class, inversedBy="personnes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $adresse;

    /**
     * @ORM\ManyToMany(targetEntity=Cadeaux::class, inversedBy="personnes")
     */
    private $cadeaux;

    /**
     * @ORM\Column(type="date")
     */
    private $naissance;

    public function __construct()
    {
        $this->cadeaux = new ArrayCollection();
    }

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

    public function getSexe(): ?string
    {
        return $this->sexe;
    }

    public function setSexe(string $sexe): self
    {
        $this->sexe = $sexe;

        return $this;
    }

    public function getAdresse(): ?Adresses
    {
        return $this->adresse;
    }

    public function setAdresse(?Adresses $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * @return Collection|Cadeaux[]
     */
    public function getCadeaux(): Collection
    {
        return $this->cadeaux;
    }

    public function addCadeaux(Cadeaux $cadeaux): self
    {
        if (!$this->cadeaux->contains($cadeaux)) {
            $this->cadeaux[] = $cadeaux;
        }

        return $this;
    }

    public function removeCadeaux(Cadeaux $cadeaux): self
    {
        $this->cadeaux->removeElement($cadeaux);

        return $this;
    }

    public function getNaissance(): ?\DateTimeInterface
    {
        return $this->naissance;
    }

    public function setNaissance(\DateTimeInterface $naissance): self
    {
        $this->naissance = $naissance;

        return $this;
    }
    //nouvelle adresse n'existe pas dans la classe Personnes mais on la rajouter dans PersonnesType comme un sous formulaire
    // ce qui introduit l'erreur lors de la creation de l'objet personnes aprtir de ($formAdresse->submit($data)) dans notre controlleur
    //l'ajout de cette methode est pour surmanter l'erreur sans aucun autre effet
    public function getNouvelleAdresse(){
        return $this->getAdresse();
    }
}
