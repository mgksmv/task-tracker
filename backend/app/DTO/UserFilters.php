<?php

namespace App\DTO;

use Spatie\LaravelData\Data;

class UserFilters extends Data
{
    public function __construct(
        public ?string $sort_field = null,
        public ?string $sort_order = 'asc',
        public ?string $name = null,
        public ?string $email = null,
    ) {}
}
