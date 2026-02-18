<?php

declare(strict_types=1);

namespace App\Service\Import;

final readonly class ImportPurchasesResult
{
    public function __construct(
        public int $createdCustomers,
        public int $updatedCustomers,
        public int $createdPurchases,
        public int $updatedPurchases,
    ) {
    }
}
