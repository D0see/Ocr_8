<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $label = null;

    /**
     * @var Collection<int, State>
     */
    #[ORM\OneToMany(targetEntity: State::class, mappedBy: 'project')]
    private Collection $states;

    /**
     * @var Collection<int, EmployeeAssignement>
     */
    #[ORM\OneToMany(targetEntity: EmployeeAssignement::class, mappedBy: 'project')]
    private Collection $employeeAssignements;

    public function __construct()
    {
        $this->states = new ArrayCollection();
        $this->employeeAssignements = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return Collection<int, State>
     */
    public function getStates(): Collection
    {
        return $this->states;
    }

    public function addState(State $state): static
    {
        if (!$this->states->contains($state)) {
            $this->states->add($state);
            $state->setProject($this);
        }

        return $this;
    }

    public function removeState(State $state): static
    {
        if ($this->states->removeElement($state)) {
            // set the owning side to null (unless already changed)
            if ($state->getProject() === $this) {
                $state->setProject(null);
            }
        }

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
            $employeeAssignement->setProject($this);
        }

        return $this;
    }

    public function removeEmployeeAssignement(EmployeeAssignement $employeeAssignement): static
    {
        if ($this->employeeAssignements->removeElement($employeeAssignement)) {
            // set the owning side to null (unless already changed)
            if ($employeeAssignement->getProject() === $this) {
                $employeeAssignement->setProject(null);
            }
        }

        return $this;
    }
}
