<?php

declare(strict_types=1);

namespace App\Transformer;

use App\DTO\CustomerListItemDTO;
use App\Entity\Customer;

final class CustomerToDTOTransformer
{
    public function transform(Customer $customer): CustomerListItemDTO
    {
        return new CustomerListItemDTO(
            id: $customer->getId(),
            title: $customer->getTitle(),
            lastname: $customer->getLastname(),
            firstname: $customer->getFirstname(),
            postalCode: $customer->getPostalCode(),
            city: $customer->getCity(),
            email: $customer->getEmail(),
        );
    }
}
