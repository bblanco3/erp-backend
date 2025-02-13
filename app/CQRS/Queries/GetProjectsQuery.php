<?php

namespace App\CQRS\Queries;

class GetProjectsQuery implements Query
{
    public function __construct(
        public readonly int $tenantId,
        public readonly array $filters = [],
        public readonly array $includes = [],
        public readonly ?string $searchTerm = null,
        public readonly ?string $sortBy = null,
        public readonly string $sortDirection = 'asc',
        public readonly ?int $limit = null
    ) {}
}
