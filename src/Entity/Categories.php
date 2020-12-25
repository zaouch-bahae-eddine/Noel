<?php

namespace App\Entity;

use App\Repository\CategoriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CategoriesRepository::class)
 */
class Categories
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
     * @ORM\OneToMany(targetEntity=Cadeaux::class, mappedBy="categorie")
     */
    private $cadeaux;

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
            $cadeaux->setCategorie($this);
        }

        return $this;
    }

    public function removeCadeaux(Cadeaux $cadeaux): self
    {
        if ($this->cadeaux->removeElement($cadeaux)) {
            // set the owning side to null (unless already changed)
            if ($cadeaux->getCategorie() === $this) {
                $cadeaux->setCategorie(null);
            }
        }

        return $this;
    }
}
