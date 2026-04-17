<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Repository\EmployeeRepository;
use App\Repository\TypeContractRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/employee')]
final class EmployeeController extends AbstractController
{
    public function __construct(
        private EmployeeRepository $employeeRepository,
        private TypeContractRepository $typeContractRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/{id}/edit', name: 'app_employee_edit_get', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function editForm(
        int $id
    ): Response {
        $employee = $this->employeeRepository->find($id);

        if (!$employee) {
            throw $this->createNotFoundException('Employee not found');
        }

        $typeContracts = $this->typeContractRepository->findAll();

        return $this->render('employee/edit.html.twig', [
            'employee' => $employee,
            'typeContracts' => $typeContracts,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_employee_edit_post', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function edit(
        int $id,
        Request $request
    ): Response {
        $employee = $this->employeeRepository->find($id);

        if (!$employee) {
            throw $this->createNotFoundException('Employee not found');
        }

        $employee->setFirstName($request->request->get('firstName') ?? $employee->getFirstName());
        $employee->setLastName($request->request->get('lastName') ?? $employee->getLastName());
        $employee->setMail($request->request->get('mail') ?? $employee->getMail());
        
        $typeContractId = $request->request->get('typeContract');
        if ($typeContractId) {
            $typeContract = $this->typeContractRepository->find($typeContractId);
            if ($typeContract) {
                $employee->setTypeContract($typeContract);
            }
        }

        $isActive = $request->request->get('isActive');
        $employee->setIsActive($isActive === 'on' || $isActive === '1');

        $this->entityManager->flush();

        $this->addFlash('success', 'Employee updated successfully');
        return $this->redirect("/employee/$id/edit");
    }

    #[Route('/list', name: 'app_employee_list', methods: ['GET'])]
    public function list(): Response
    {
        $employees = $this->employeeRepository->findAll();

        return $this->render('employee/list.html.twig', [
            'employees' => $employees,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_employee_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(
        int $id
    ): Response {
        $employee = $this->employeeRepository->find($id);

        if (!$employee) {
            throw $this->createNotFoundException('Employee not found');
        }

        $this->entityManager->remove($employee);
        $this->entityManager->flush();

        $this->addFlash('success', 'Employee deleted successfully');
        return $this->redirect('/');
    }
}
