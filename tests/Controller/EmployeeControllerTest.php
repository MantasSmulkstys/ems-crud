<?php

namespace App\Tests\Controller;

use App\Entity\Employee;
use App\Entity\Department;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EmployeeControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $employeeRepository;
    private EntityRepository $departmentRepository;
    private string $path = '/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->employeeRepository = $this->manager->getRepository(Employee::class);
        $this->departmentRepository = $this->manager->getRepository(Department::class);

        foreach ($this->employeeRepository->findAll() as $object) {
            $this->manager->remove($object);
        }
        foreach ($this->departmentRepository->findAll() as $object) {
            $this->manager->remove($object);
        }
        $this->manager->flush();
    }

    private function createDepartment(string $name = 'IT'): Department
    {
        $department = new Department();
        $department->setName($name);
        $department->setDescription('Test department');
        $this->manager->persist($department);
        $this->manager->flush();
        return $department;
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
    }

    public function testNew(): void
    {
        $department = $this->createDepartment('HR');
        $this->client->request('GET', sprintf('%snew', $this->path));
        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save Employee', [
            'employee[name]' => 'Testing User',
            'employee[email]' => 'testing.user@example.com',
            'employee[phoneNumber]' => '123-456-7890',
            'employee[hireDate]' => (new \DateTimeImmutable('-1 day'))->format('Y-m-d'),
            'employee[department]' => $department->getId(),
        ]);

        self::assertResponseRedirects($this->path);
        self::assertSame(1, $this->employeeRepository->count([]));
    }

    public function testShow(): void
    {
        $department = $this->createDepartment('Finance');
        $employee = new Employee();
        $employee->setName('My Title');
        $employee->setEmail('my.title@example.com');
        $employee->setPhoneNumber('555-555-5555');
        $employee->setHireDate(new \DateTimeImmutable('-2 days'));
        $employee->setDepartment($department);
        $this->manager->persist($employee);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $employee->getId()));
        self::assertResponseStatusCodeSame(200);
    }

    public function testEdit(): void
    {
        $department = $this->createDepartment('QA');
        $employee = new Employee();
        $employee->setName('Value');
        $employee->setEmail('value@example.com');
        $employee->setPhoneNumber('111-222-3333');
        $employee->setHireDate(new \DateTimeImmutable('-3 days'));
        $employee->setDepartment($department);
        $this->manager->persist($employee);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $employee->getId()));
        $this->client->submitForm('Update', [
            'employee[name]' => 'Something New',
            'employee[email]' => 'something.new@example.com',
            'employee[phoneNumber]' => '999-888-7777',
            'employee[hireDate]' => (new \DateTimeImmutable('-1 day'))->format('Y-m-d'),
            'employee[department]' => $department->getId(),
        ]);

        self::assertResponseRedirects('/');
        $fixture = $this->employeeRepository->findAll();
        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('something.new@example.com', $fixture[0]->getEmail());
        self::assertSame('999-888-7777', $fixture[0]->getPhoneNumber());
        self::assertInstanceOf(\DateTimeImmutable::class, $fixture[0]->getHireDate());
        self::assertSame($department->getId(), $fixture[0]->getDepartment()->getId());
    }

    public function testRemove(): void
    {
        $department = $this->createDepartment('Marketing');
        $employee = new Employee();
        $employee->setName('Value');
        $employee->setEmail('value@example.com');
        $employee->setPhoneNumber('111-222-3333');
        $employee->setHireDate(new \DateTimeImmutable('-3 days'));
        $employee->setDepartment($department);
        $this->manager->persist($employee);
        $this->manager->flush();

        self::assertSame(1, $this->employeeRepository->count([]), 'Pre-condition: One employee should exist.');
        $crawler = $this->client->request('GET', sprintf('%s%s', $this->path, $employee->getId()));
        self::assertResponseStatusCodeSame(200);
        
        $csrfToken = $crawler->filter('form[action$="' . $employee->getId() . '"] input[name="_token"]')->attr('value');
        $this->client->request('POST', sprintf('%s%s', $this->path, $employee->getId()), [
            '_token' => $csrfToken,
        ]);

        self::assertResponseRedirects('/');
        $this->client->followRedirect();
        self::assertSame(0, $this->employeeRepository->count([]), 'Post-condition: Employee should be deleted.');
    }
}
