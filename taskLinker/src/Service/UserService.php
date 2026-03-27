<?php

namespace App\Service;

use App\Repository\EmployeeRepository;
use App\Entity\Employee;

final class UserService
{
    public function __construct(
        private EmployeeRepository $employeeRepository
    ) {}

    /**
     * Get all users (employees)
     * 
     * @return Employee[]
     */
    public function getUsers(): array
    {
        return $this->employeeRepository->findAll();
    }

    /**
     * Get active users only
     * 
     * @return Employee[]
     */
    public function getActiveUsers(): array
    {
        return $this->employeeRepository->findBy(['isActive' => true]);
    }

    /**
     * Get a user by ID
     */
    public function getUserById(int $id): ?Employee
    {
        return $this->employeeRepository->find($id);
    }
}
