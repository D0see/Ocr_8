<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use App\Repository\StateRepository;
use App\Repository\TaskRepository;
use App\Repository\EmployeeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private StateRepository $stateRepository,
        private TaskRepository $taskRepository,
        private EmployeeRepository $employeeRepository
    ) {}

    #[Route('/', name: 'app_main')]
    public function index(): Response
    {
        $projects = $this->projectRepository->findAll();
        $tasks = $this->taskRepository->findAll();
        $states = $this->stateRepository->findAll();
        $employees = $this->employeeRepository->findAll();

        return $this->render('main/index.html.twig', [
            'projects' => $projects,
            'tasks' => $tasks,
            'states' => $states,
            'employees' => $employees
        ]);
    }
}
