<?php

declare(strict_types=1);

namespace App\Transformer;

use App\DTO\PurchaseDTO;
use App\Entity\Purchase;
use DateTimeZone;

final class PurchaseToDTOTransformer
{
    public function transform(Purchase $purchase): PurchaseDTO
    {
        $productId = $purchase->getProductId();
        $purchaseLabel = $productId !== null ? (string) $productId : 'purchase';

        return new PurchaseDTO(
            id: $purchase->getId(),
            firstname: $purchase->getCustomerId()->getFirstname(),
            lastname: $purchase->getCustomerId()->getLastname(),
            purchase: $purchaseLabel,
            productQuantity: $purchase->getQuantity(),
            price: $purchase->getPrice(),
            purchase_identifier: $purchase->getPurchaseIdentifier(),
            currency: $purchase->getCurrency(),
            date: $purchase->getDate()->setTimezone(new DateTimeZone('UTC'))->format(DATE_ATOM),
        );
    }
}
