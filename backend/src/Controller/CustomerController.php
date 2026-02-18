<?php

namespace App\Controller;

use App\Repository\CustomerRepository;
use App\Repository\PurchaseRepository;
use App\Transformer\CustomerToDTOTransformer;
use App\Transformer\PurchaseToDTOTransformer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('api/customers', name: 'customer')]
final class CustomerController extends AbstractController
{

    public function __construct(
        private readonly CustomerRepository $customerRepository,
        private readonly PurchaseRepository $purchaseRepository,
        private readonly CustomerToDTOTransformer $customerToDTOTransformer,
        private readonly PurchaseToDTOTransformer $purchaseToDTOTransformer,
    ) 
    {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function index():JsonResponse
    {
        $customers = $this->customerRepository->findAll();
        $payload = array_map(
            fn ($customer): array => $this->customerToDTOTransformer->transform($customer)->toArray(),
            $customers,
        );

        return new JsonResponse($payload);
    }

     #[Route('/{customer_id}/orders', name:"orders", requirements:['customer_id' => '\\d+'], methods: ['GET'])]
    public function listOrders(int $customer_id):JsonResponse
    {
        $customer = $this->customerRepository->find($customer_id);
        if ($customer === null) {
            return new JsonResponse(['message' => 'Customer not found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $orders = $this->purchaseRepository->findByCustomerId($customer_id);
        $payload = array_map(
            fn ($order): array => $this->purchaseToDTOTransformer->transform($order)->toArray(),
            $orders,
        );
        // if(empty($payload)) {
        //     return new JsonResponse(['message' => 'No orders for this customer'],404);
        // }

        return new JsonResponse($payload);
    }
}
