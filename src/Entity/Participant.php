<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[UniqueEntity(fields: "pseudo", message: "Ce pseudo est déjà utilisé.")]
#[UniqueEntity(fields: "mail", message: "Cette email est déjà utilisé.")]
class Participant implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "Le mail est requis.")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas valide.")]
    #[Assert\Length(max: 180, maxMessage: "Le mail ne peut pas dépasser 180 carcatères")]
    private ?string $mail = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank(message: "Le mot de passe est requis.")]
    #[Assert\Length(min: 8, max: 255, minMessage: "Le mot de passe doit avoir au moins 8 caractères.")]
    //    #[Assert\Regex(
    //        pattern:"/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]+$/",
    //        message:"Le mot de passe doit contenir au moins une lettre, un chiffre et un caractère spécial.")]
    private ?string $motPasse = null;

    #[ORM\Column(length: 30, unique: true)]
    #[Assert\NotBlank(message: "Le pseudo ne peut pas être vide.")]
    #[Assert\Length(
        min: 3,
        max: 30,
        minMessage: "Le pseudo ne peut pas faire moins de 3 caractères.",
        maxMessage: "Le pseudo ne peut pas dépasser 30 caractères."
    )]
    private ?string $pseudo = null;

    #[ORM\Column(length: 50)]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: "Le nom ne peut pas faire moins de 3 caractères.",
        maxMessage: "Le nom ne peut pas dépasser 50 caractères."
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: "Le prénom ne peut pas faire moins de 3 caractères.",
        maxMessage: "Le prénom ne peut pas dépasser 50 caractères."
    )]
    private ?string $prenom = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Length(max: 20, maxMessage: "Le numéro de téléphone ne peut pas dépasser 20 caractères.")]
    private ?string $telephone = null;

    #[ORM\Column]
    private ?bool $administrateur = null;

    #[ORM\Column]
    private ?bool $actif = null;

    #[ORM\ManyToOne(inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le campus ne doit pas être vide.")]
    private ?Campus $campus = null;

    #[ORM\ManyToMany(targetEntity: Sortie::class, inversedBy: 'participants')]
    private Collection $sortiesInscrites;

    #[ORM\OneToMany(mappedBy: 'organisateur', targetEntity: Sortie::class, orphanRemoval: true)]
    private Collection $sortiesOrganisees;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoDeProfil = null;

    public function __construct()
    {
        $this->sortiesInscrites = new ArrayCollection();
        $this->sortiesOrganisees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->mail;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->mail;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }


    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->getMotPasse();
    }

    public function getMotPasse(): string
    {
        return $this->motPasse;
    }

    public function setMotPasse(string $motPasse): static
    {
        $this->motPasse = $motPasse;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function isAdministrateur(): ?bool
    {
        return $this->administrateur;
    }

    public function setAdministrateur(bool $administrateur): static
    {
        $this->administrateur = $administrateur;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(?string $pseudo): void
    {
        $this->pseudo = $pseudo;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): static
    {
        $this->campus = $campus;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortiesInscrites(): Collection
    {
        return $this->sortiesInscrites;
    }

    public function addSortiesInscrites(Sortie $sortiesInscrites): static
    {
        if (!$this->sortiesInscrites->contains($sortiesInscrites)) {
            $this->sortiesInscrites->add($sortiesInscrites);
        }

        return $this;
    }

    public function removeSortiesInscrites(Sortie $sortiesInscrites): static
    {
        $this->sortiesInscrites->removeElement($sortiesInscrites);

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortiesOrganisees(): Collection
    {
        return $this->sortiesOrganisees;
    }

    public function addSortiesOrganisees(Sortie $sortiesOrganisees): static
    {
        if (!$this->sortiesOrganisees->contains($sortiesOrganisees)) {
            $this->sortiesOrganisees->add($sortiesOrganisees);
            $sortiesOrganisees->setOrganisateur($this);
        }

        return $this;
    }

    public function removeSortiesOrganisees(Sortie $sortiesOrganisees): static
    {
        if ($this->sortiesOrganisees->removeElement($sortiesOrganisees)) {
            // set the owning side to null (unless already changed)
            if ($sortiesOrganisees->getOrganisateur() === $this) {
                $sortiesOrganisees->setOrganisateur(null);
            }
        }

        return $this;
    }

    public function getPhotoDeProfil(): ?string
    {
        return $this->photoDeProfil;
    }

    public function setPhotoDeProfil(?string $photoDeProfil): static
    {
        $this->photoDeProfil = $photoDeProfil;

        return $this;
    }
}
