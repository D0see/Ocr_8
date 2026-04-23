<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\EmployeeAssignement;
use App\Entity\State;
use App\Service\ProjectService;
use App\Repository\ProjectRepository;
use App\Repository\StateRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/project')]
final class ProjectController extends AbstractController
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private UserService $userService,
        private EntityManagerInterface $entityManager,
        private ProjectService $projectService
    ) {}

    #[Route('/create', name: 'app_project_create', methods: ['GET'])]
    public function showCreateForm(): Response {
        $users = $this->userService->getUsers();

        return $this->render('project/create.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/create', name: 'app_project_create_submit', methods: ['POST'])]
    public function create(
        Request $request
    ): Response {
        $label = (string) $request->request->get('label');
        $employeeIds = $request->request->all('employees');

        $project = $this->projectService->createProject($label, $employeeIds);

        $this->addFlash('success', 'Project created successfully');
        return $this->redirectToRoute('app_project_list');
    }

    #[Route('/list', name: 'app_project_list', methods: ['GET'])]
    public function list(): Response
    {
        $projects = $this->projectRepository->findAll();

        return $this->render('project/list.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/{id}', name: 'app_project_show', methods: ['GET'])]
    public function view(int $id): Response
    {
        $project = $this->projectRepository->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Project not found');
        }

        $states = $project->getStates();
        $tasksByState = [];
        foreach ($states as $state) {
            $tasksByState[$state->getLabel()] = $state->getTasks();
        }

        $assignedUsers = [];
        foreach ($project->getEmployeeAssignements() as $assignment) {
            $employee = $assignment->getEmployee();
            if ($employee) {
                $assignedUsers[] = $employee;
            }
        }

        return $this->render('project/view.html.twig', [
            'project' => $project,
            'tasksByState' => $tasksByState,
            'assignedUsers' => $assignedUsers,
        ]);
    }
    #[Route('/{id}/edit', name: 'app_project_edit_get', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function editForm(
        int $id
    ): Response {
        $project = $this->projectRepository->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Project not found');
        }

        $users = $this->userService->getUsers();
        $assignedEmployeeIds = [];
        foreach ($project->getEmployeeAssignements() as $assignment) {
            $assignedEmployeeIds[] = $assignment->getEmployee()->getId();
        }

        return $this->render('project/edit.html.twig', [
            'project' => $project,
            'users' => $users,
            'assignedEmployeeIds' => $assignedEmployeeIds,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_project_edit_post', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function edit(
        int $id,
        Request $request
    ): Response {
        $project = $this->projectRepository->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Project not found');
        }

        $project->setLabel($request->request->get('label') ?? $project->getLabel());

        // Handle employee assignments
        $selectedEmployeeIds = $request->request->all('employees') ?? [];
        $currentAssignments = $project->getEmployeeAssignements();

        // Remove assignments not in selected
        foreach ($currentAssignments as $assignment) {
            if (!in_array($assignment->getEmployee()->getId(), $selectedEmployeeIds)) {
                $this->entityManager->remove($assignment);
            }
        }

        // Add new assignments
        foreach ($selectedEmployeeIds as $employeeId) {
            $alreadyAssigned = false;
            foreach ($currentAssignments as $assignment) {
                if ($assignment->getEmployee()->getId() == $employeeId) {
                    $alreadyAssigned = true;
                    break;
                }
            }
            if (!$alreadyAssigned) {
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

        $this->addFlash('success', 'Project updated successfully');
        return $this->redirectToRoute('app_project_edit_get', ['id' => $id]);
    }

    #[Route('/{id}/delete', name: 'app_project_delete', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function delete(
        int $id,
    ): Response {

        $project = $this->projectRepository->find($id);
        if (!$project) {
            throw $this->createNotFoundException('Project not found');
        }

        // Rely on DB-level ON DELETE CASCADE for related states, tasks and assignments
        $this->entityManager->remove($project);
        $this->entityManager->flush();

        $this->addFlash('success', 'Project deleted successfully');
        return $this->redirectToRoute('app_main');
    }
}
