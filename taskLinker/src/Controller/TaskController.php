<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Repository\StateRepository;
use App\Repository\EmployeeAssignementRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/task')]
final class TaskController extends AbstractController
{
    public function __construct(
        private TaskRepository $taskRepository,
        private StateRepository $stateRepository,
        private EmployeeAssignementRepository $employeeAssignementRepository,
        private EntityManagerInterface $entityManager,
        private UserService $userService
    ) {}

    #[Route('/{id}/edit', name: 'app_task_edit_form', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function editForm(
        int $id
    ): Response {

        /**
         * @var Task $task
         */
        $task = $this->taskRepository->find($id);

        if (!$task) {
            throw $this->createNotFoundException('Task not found');
        }

        $idProject = $task->getState()->getProject()->getId();

        $states = $this->stateRepository->findByProjectId($idProject);
        
        // Fetch the single assignment for this task and extract the linked user
        $assignment = $task->getEmployeeAssignement();
        $user = $assignment ? $assignment->getEmployee() : null;

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'states' => $states,
            'user' => $user,
            'users' => $this->userService->getUsers(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_task_edit', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function edit(
        int $id,
        Request $request
    ): Response {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            throw $this->createNotFoundException('Task not found');
        }

        $task->setLabel($request->request->get('label') ?? $task->getLabel());
        $task->setDescription($request->request->get('description') ?? $task->getDescription());

        $deadlineStr = $request->request->get('dateDeadline');
        if ($deadlineStr) {
            try {
                $task->setDateDeadline(new \DateTime($deadlineStr));
            } catch (\Exception $e) {
                // Keep existing deadline if invalid
            }
        }

        $stateId = $request->request->get('state');
        if ($stateId) {
            $state = $this->stateRepository->find($stateId);
            if ($state) {
                $task->setState($state);
            }
        }

        $assignedUser = $request->request->get('assignedUser');

        dump($assignedUser);
        // if ($assignmentId) {
        //     $assignment = $this->employeeAssignementRepository->find($assignmentId);
        //     if ($assignment) {
        //         $task->setEmployeeAssignement($assignment);
        //     }
        // }

        $this->entityManager->flush();

        $this->addFlash('success', 'Task updated successfully');
        return $this->redirect("/task/$id/edit");
    }

    #[Route('/{id}/delete', name: 'app_task_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(
        int $id
    ): Response {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            throw $this->createNotFoundException('Task not found');
        }

        // Get the assignment before deleting the task
        $assignment = $task->getEmployeeAssignement();

        // Clear the reference to avoid FK constraint issues

        $this->entityManager->remove($task);
        $this->entityManager->flush();

        $this->addFlash('success', 'Task deleted successfully');
        return $this->redirect('/');
    }

    #[Route('/{idProject}/create', name: 'app_task_create_form', requirements: ['idProject' => '\d+'], methods: ['GET'])]
    public function showCreateForm(
        int $idProject,
        Request $request
    ): Response {

        $users = $this->userService->getUsers();
        $states = $this->stateRepository->findByProjectId($idProject);

        return $this->render('task/create.html.twig', [
            'idProject' => $idProject,
            'states' => $states,
            'users' => $users,
        ]);
    }

    #[Route('/create', name: 'app_task_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $stateId = $request->request->get('state');
        $state = $this->stateRepository->find($stateId);

        if (!$state) {
            throw $this->createNotFoundException('State not found');
        }

        $task = new \App\Entity\Task();
        $task->setLabel($request->request->get('label'));
        $task->setDescription($request->request->get('description'));
        
        $deadlineString = $request->request->get('dateDeadline');
        if ($deadlineString) {
            $task->setDateDeadline(new \DateTime($deadlineString));
        }

        $task->setState($state);

        // Handle optional employee assignment
        $employeeId = $request->request->get('assignedUser');
        if ($employeeId) {
            $employee = $this->userService->getUserById((int)$employeeId);
            if ($employee) {
                $assignment = new \App\Entity\EmployeeAssignement();
                $assignment->setEmployee($employee);
                if ($state && method_exists($state, 'getProject')) {
                    $assignment->setProject($state->getProject());
                }
                $assignment->addTask($task);
                $this->entityManager->persist($assignment);
            }
        }

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        // Redirect to the project view containing the task
        $project = $state->getProject();
        return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
    }
}
