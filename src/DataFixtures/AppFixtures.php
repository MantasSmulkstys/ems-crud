<?php

namespace App\DataFixtures;

use App\Entity\Department;
use App\Entity\Employee;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $departments = [];

        $departmentData = [
            [
                'name' => 'Information Technology',
                'description' => 'Responsible for managing technology infrastructure, software development, and IT support services'
            ],
            [
                'name' => 'Marketing',
                'description' => 'Handles brand management, advertising campaigns, digital marketing, and customer engagement strategies'
            ],
            [
                'name' => 'Finance',
                'description' => 'Manages financial planning, accounting, budgeting, and financial reporting for the organization'
            ],
            [
                'name' => 'Human Resources',
                'description' => 'Oversees employee relations, recruitment, training, benefits administration, and organizational development'
            ],
            [
                'name' => 'Quality Assurance',
                'description' => 'Ensures product and service quality through testing, process improvement, and compliance monitoring'
            ]
        ];

        foreach ($departmentData as $deptData) {
            $department = new Department();
            $department->setName($deptData['name']);
            $department->setDescription($deptData['description']);
            
            $manager->persist($department);
            $departments[] = $department;
        }

        $employeeCount = $faker->numberBetween(25, 40);
        
        for ($i = 0; $i < $employeeCount; $i++) {
            $employee = new Employee();
            
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $fullName = $firstName . ' ' . $lastName;
            
            $employee->setName($fullName);
            $employee->setEmail($faker->unique()->safeEmail());
            $employee->setPhoneNumber($this->generatePhoneNumber($faker));
            
            $hireDate = $faker->dateTimeBetween('-5 years', '-6 months');
            $employee->setHireDate(\DateTimeImmutable::createFromMutable($hireDate));
            
            $randomDepartment = $departments[array_rand($departments)];
            $employee->setDepartment($randomDepartment);
            
            $manager->persist($employee);
        }

        $this->createDemoEmployees($manager, $departments, $faker);

        $manager->flush();
    }

    private function generatePhoneNumber($faker): string
    {
        $formats = [
            '+1 (###) ###-####',
            '(###) ###-####',
            '###-###-####',
            '### ### ####'
        ];
        
        return $faker->numerify($faker->randomElement($formats));
    }

    private function createDemoEmployees(ObjectManager $manager, array $departments, $faker): void
    {
        $demoEmployees = [
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@company.com',
                'department' => 'Information Technology',
                'role' => 'Senior Developer'
            ],
            [
                'name' => 'Michael Chen',
                'email' => 'michael.chen@company.com',
                'department' => 'Marketing',
                'role' => 'Marketing Manager'
            ],
            [
                'name' => 'Emily Rodriguez',
                'email' => 'emily.rodriguez@company.com',
                'department' => 'Human Resources',
                'role' => 'HR Director'
            ],
            [
                'name' => 'David Thompson',
                'email' => 'david.thompson@company.com',
                'department' => 'Finance',
                'role' => 'Financial Analyst'
            ],
            [
                'name' => 'Lisa Wang',
                'email' => 'lisa.wang@company.com',
                'department' => 'Quality Assurance',
                'role' => 'QA Lead'
            ]
        ];

        foreach ($demoEmployees as $demoData) {
            $employee = new Employee();
            $employee->setName($demoData['name']);
            $employee->setEmail($demoData['email']);
            $employee->setPhoneNumber($this->generatePhoneNumber($faker));
            
            $hireDate = $faker->dateTimeBetween('-3 years', '-1 year');
            $employee->setHireDate(\DateTimeImmutable::createFromMutable($hireDate));
            $department = array_filter($departments, function($dept) use ($demoData) {
                return $dept->getName() === $demoData['department'];
            });
            $employee->setDepartment(array_values($department)[0]);
            
            $manager->persist($employee);
        }
    }
}
