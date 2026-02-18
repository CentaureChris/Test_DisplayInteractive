<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class PurchaseDTO
{
    public function __construct(
        public int $id,
        public string $firstname,
        public string $lastname,
        public string $purchase,
        public int $productQuantity,
        public float $price,
        public string $purchase_identifier,
        public string $currency,
        public string $date,
    ) {
    }

    /**
     * @return array{id:int,firstname:string,lastname:string,purchase:string,product_quantity:int,price:float,identifier_id:string,currency:string,date:string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'purchase' => $this->purchase,
            'product_quantity' => $this->productQuantity,
            'price' => $this->price,
            'purchase_identifier' => $this->purchase_identifier,
            'currency' => $this->currency,
            'date' => $this->date,
        ];
    }
}
