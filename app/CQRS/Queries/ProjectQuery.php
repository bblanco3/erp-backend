<?php

namespace App\CQRS\Queries;

class ProjectQuery implements Query
{
    public const TYPE_LIST = 'list';
    public const TYPE_SINGLE = 'single';
    public const TYPE_TIMELINE = 'timeline';
    public const TYPE_COSTS = 'costs';
    public const TYPE_EVENTS = 'events';
    public const TYPE_STATS = 'stats';
    public const TYPE_REPORT = 'report';

    public function __construct(
        public readonly string $type,
        public readonly int $tenantId,
        public readonly ?int $projectId = null,
        public readonly array $filters = [],
        public readonly array $includes = [],
        public readonly ?string $searchTerm = null,
        public readonly ?string $sortBy = null,
        public readonly string $sortDirection = 'asc',
        public readonly ?int $limit = null,
        public readonly ?string $startDate = null,
        public readonly ?string $endDate = null,
        public readonly string $reportType = 'summary',
        public readonly string $format = 'json'
    ) {
        if (!in_array($type, [
            self::TYPE_LIST,
            self::TYPE_SINGLE,
            self::TYPE_TIMELINE,
            self::TYPE_COSTS,
            self::TYPE_EVENTS,
            self::TYPE_STATS,
            self::TYPE_REPORT
        ])) {
            throw new \InvalidArgumentException('Invalid query type');
        }

        if (in_array($type, [self::TYPE_SINGLE, self::TYPE_TIMELINE, self::TYPE_COSTS, self::TYPE_EVENTS, self::TYPE_REPORT]) 
            && $projectId === null
        ) {
            throw new \InvalidArgumentException('Project ID is required for this query type');
        }
    }
}
