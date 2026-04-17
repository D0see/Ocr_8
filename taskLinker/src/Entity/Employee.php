<?php

namespace App\Entity;

use App\Entity\TypeContract;
use App\Repository\EmployeeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
class Employee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $mail = null;

    #[ORM\Column]
    private ?\DateTime $dateEntry = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\ManyToOne(inversedBy: 'employees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeContract $typeContract = null;

    /**
     * @var Collection<int, EmployeeAssignement>
     */
    #[ORM\OneToMany(targetEntity: EmployeeAssignement::class, mappedBy: 'employee', orphanRemoval: true)]
    private Collection $employeeAssignements;

    public function __construct()
    {
        $this->employeeAssignements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
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

    public function getDateEntry(): ?\DateTime
    {
        return $this->dateEntry;
    }

    public function setDateEntry(\DateTime $dateEntry): static
    {
        $this->dateEntry = $dateEntry;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getTypeContract(): ?TypeContract
    {
        return $this->typeContract;
    }

    public function setTypeContract(?TypeContract $typeContract): static
    {
        $this->typeContract = $typeContract;

        return $this;
    }

    /**
     * @return Collection<int, EmployeeAssignement>
     */
    public function getEmployeeAssignements(): Collection
    {
        return $this->employeeAssignements;
    }

    public function addEmployeeAssignement(EmployeeAssignement $employeeAssignement): static
    {
        if (!$this->employeeAssignements->contains($employeeAssignement)) {
            $this->employeeAssignements->add($employeeAssignement);
            $employeeAssignement->setEmployee($this);
        }

        return $this;
    }

    public function removeEmployeeAssignement(EmployeeAssignement $employeeAssignement): static
    {
        if ($this->employeeAssignements->removeElement($employeeAssignement)) {
            // set the owning side to null (unless already changed)
            if ($employeeAssignement->getEmployee() === $this) {
                $employeeAssignement->setEmployee(null);
            }
        }

        return $this;
    }
}
