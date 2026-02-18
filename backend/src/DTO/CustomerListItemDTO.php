<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class CustomerListItemDTO
{
    public function __construct(
        public int $id,
        public ?int $title,
        public ?string $lastname,
        public ?string $firstname,
        public ?int $postalCode,
        public ?string $city,
        public ?string $email,
    ) {
    }

    /**
     * @return array{id:int,title:int|null,lastname:string|null,firstname:string|null,postal_code:int|null,city:string|null,email:string|null}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'lastname' => $this->lastname,
            'firstname' => $this->firstname,
            'postal_code' => $this->postalCode,
            'city' => $this->city,
            'email' => $this->email,
        ];
    }
}
