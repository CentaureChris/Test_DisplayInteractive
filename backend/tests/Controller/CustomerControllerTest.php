<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\CustomerController;
use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Repository\PurchaseRepository;
use App\Transformer\CustomerToDTOTransformer;
use App\Transformer\PurchaseToDTOTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

final class CustomerControllerTest extends TestCase
{
    public function testIndexReturnsExpectedJsonPayload(): void
    {
        $customerRepository = $this->createMock(CustomerRepository::class);
        $purchaseRepository = $this->createMock(PurchaseRepository::class);
        $customerToDTOTransformer = new CustomerToDTOTransformer();
        $purchaseToDTOTransformer = new PurchaseToDTOTransformer();

        $customer1 = (new Customer(1))
            ->setTitle(1)
            ->setLastname('Dupont')
            ->setFirstname('Alice')
            ->setPostalCode(75001)
            ->setCity('Paris')
            ->setEmail('alice.dupont@example.com');

        $customer2 = (new Customer(2))
            ->setTitle(2)
            ->setLastname('Martin')
            ->setFirstname('Bob')
            ->setPostalCode(69001)
            ->setCity('Lyon')
            ->setEmail('bob.martin@example.com');

        $customerRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$customer1, $customer2]);

        $controller = new CustomerController(
            $customerRepository,
            $purchaseRepository,
            $customerToDTOTransformer,
            $purchaseToDTOTransformer,
        );

        $response = $controller->index();

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(JsonResponse::HTTP_OK, $response->getStatusCode());
        self::assertSame(
            [
                [
                    'id' => 1,
                    'title' => 1,
                    'lastname' => 'Dupont',
                    'firstname' => 'Alice',
                    'postal_code' => 75001,
                    'city' => 'Paris',
                    'email' => 'alice.dupont@example.com',
                ],
                [
                    'id' => 2,
                    'title' => 2,
                    'lastname' => 'Martin',
                    'firstname' => 'Bob',
                    'postal_code' => 69001,
                    'city' => 'Lyon',
                    'email' => 'bob.martin@example.com',
                ],
            ],
            json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR),
        );
    }
}
