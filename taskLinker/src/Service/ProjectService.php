<?php

namespace App\Service;

use App\Service\UserService;
use Throwable;
use Doctrine\DBAL\Exception\ConnectionException;

use App\Entity\Project;
use App\Entity\State;
use App\Entity\EmployeeAssignement;
use Doctrine\ORM\EntityManagerInterface;

class ProjectService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserService $userService
    ) {}

    /**
     * Create a project with default states and optional employee assignments.
     * Returns the persisted Project.
     *
     * @param string $label
     * @param array<int>|null $employeeIds
     */
    public function createProject(string $label, ?array $employeeIds = null): Project
    {
        $project = new Project();
        $project->setLabel($label);

        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->persist($project);

            $defaultStates = ['À faire', 'En cours', 'Fait'];
            foreach ($defaultStates as $stateLabel) {
                $state = new State();
                $state->setLabel($stateLabel);
                $state->setProject($project);
                $this->entityManager->persist($state);
            }

            if (!empty($employeeIds)) {
                foreach ($employeeIds as $employeeId) {
                    $employee = $this->userService->getUserById($employeeId);
                    if ($employee) {
                        $assignment = new EmployeeAssignement();
                        $assignment->setProject($project);
                        $assignment->setEmployee($employee);
                        $this->entityManager->persist($assignment);
                    }
                }
            }

            $this->entityManager->flush();
            $this->entityManager->commit();

            return $project;
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
}
