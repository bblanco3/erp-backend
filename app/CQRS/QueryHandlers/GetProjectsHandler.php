<?php

namespace App\CQRS\QueryHandlers;

use App\CQRS\Queries\GetProjectsQuery;
use App\ReadModels\ProjectReadModel;
use Illuminate\Support\Collection;

class GetProjectsHandler
{
    public function __construct(
        private readonly ProjectReadModel $readModel
    ) {}

    public function handle(GetProjectsQuery $query): Collection
    {
        return $this->readModel->getProjects(
            tenantId: $query->tenantId,
            filters: $query->filters,
            includes: $query->includes,
            searchTerm: $query->searchTerm,
            sortBy: $query->sortBy,
            sortDirection: $query->sortDirection,
            limit: $query->limit
        );
    }
}
