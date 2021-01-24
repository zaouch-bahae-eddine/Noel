<?php

namespace App\Entity;

use App\Repository\PersonnesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=PersonnesRepository::class)
 */
class Personnes implements UserInterface
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
    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

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
    public function getAge(){
        //$naissanceObj = new \DateTime($this->naissance->format('d-m-Y'));
        $now = new \DateTime();
        $age = $now->diff($this->naissance)->y;
        return $age;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }
    public function getPassword()
    {
        return (string) $this->password;
    }
    public function setPassword($password)
    {
        return $this->password = $password;
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->nom;
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }
}
