<?php

namespace App\CQRS\QueryHandlers;

use App\CQRS\Queries\ProjectQuery;
use App\ReadModels\ProjectReadModel;
use Illuminate\Support\Collection;

class ProjectQueryHandler
{
    public function __construct(
        private readonly ProjectReadModel $readModel
    ) {}

    public function handle(ProjectQuery $query): mixed
    {
        return match($query->type) {
            ProjectQuery::TYPE_LIST => $this->readModel->getProjects(
                tenantId: $query->tenantId,
                filters: $query->filters,
                includes: $query->includes,
                searchTerm: $query->searchTerm,
                sortBy: $query->sortBy,
                sortDirection: $query->sortDirection,
                limit: $query->limit
            ),
            ProjectQuery::TYPE_SINGLE => $this->readModel->getProject(
                projectId: $query->projectId,
                includes: $query->includes
            ),
            ProjectQuery::TYPE_TIMELINE => $this->readModel->getProjectTimeline(
                projectId: $query->projectId
            ),
            ProjectQuery::TYPE_COSTS => $this->readModel->getProjectCosts(
                projectId: $query->projectId
            ),
            ProjectQuery::TYPE_EVENTS => $this->readModel->getProjectEvents(
                projectId: $query->projectId,
                filters: $query->filters,
                includes: $query->includes,
                startDate: $query->startDate,
                endDate: $query->endDate
            ),
            ProjectQuery::TYPE_STATS => $this->readModel->getProjectStats(
                tenantId: $query->tenantId,
                filters: $query->filters,
                startDate: $query->startDate,
                endDate: $query->endDate
            ),
            ProjectQuery::TYPE_REPORT => $this->readModel->generateProjectReport(
                projectId: $query->projectId,
                reportType: $query->reportType,
                startDate: $query->startDate,
                endDate: $query->endDate,
                format: $query->format
            ),
            default => throw new \InvalidArgumentException('Invalid query type')
        };
    }
}
