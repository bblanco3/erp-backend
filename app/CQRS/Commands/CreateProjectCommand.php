<?php

namespace App\CQRS\Commands;

class CreateProjectCommand implements Command
{
    public function __construct(
        public readonly int $tenantId,
        public readonly int $userId,
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly array $attributes = []
    ) {}
}
