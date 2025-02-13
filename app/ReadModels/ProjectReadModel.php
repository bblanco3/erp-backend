<?php

namespace App\ReadModels;

use App\Models\Project;
use App\Queries\ProjectQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class ProjectReadModel
{
    private const CACHE_PREFIX = 'project_read_model_';
    private const CACHE_TTL = 3600; // 1 hour

    public function getProjects(ProjectQuery $query, int $tenantId): Collection
    {
        $cacheKey = $this->generateCacheKey($query, $tenantId);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $tenantId) {
            return $this->queryProjects($query, $tenantId);
        });
    }

    private function queryProjects(ProjectQuery $query, int $tenantId): Collection
    {
        $queryBuilder = Project::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('is_deleted', false);

        // Apply filters
        foreach ($query->getFilters() as $field => $value) {
            $queryBuilder->where($field, $value);
        }

        // Apply search
        if ($searchTerm = $query->getSearchTerm()) {
            $queryBuilder->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Apply sorting
        if ($sortBy = $query->getSortBy()) {
            $queryBuilder->orderBy($sortBy, $query->getSortDirection());
        }

        // Apply includes
        if ($includes = $query->getIncludes()) {
            $queryBuilder->with($includes);
        }

        // Apply limit
        if ($limit = $query->getLimit()) {
            $queryBuilder->limit($limit);
        }

        return $queryBuilder->get();
    }

    private function generateCacheKey(ProjectQuery $query, int $tenantId): string
    {
        $parts = [
            self::CACHE_PREFIX,
            $tenantId,
            md5(serialize($query->getFilters())),
            md5($query->getSearchTerm() ?? ''),
            $query->getSortBy(),
            $query->getSortDirection(),
            implode(',', $query->getIncludes()),
            $query->getLimit()
        ];

        return implode('_', array_filter($parts));
    }

    public function invalidateCache(int $tenantId): void
    {
        $pattern = self::CACHE_PREFIX . $tenantId . '_*';
        foreach (Cache::get($pattern) as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Get a specific project with optional includes.
     */
    public function getProject(int $projectId, array $includes = []): ?Project
    {
        $cacheKey = self::CACHE_PREFIX . "project_{$projectId}_" . implode(',', $includes);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($projectId, $includes) {
            return Project::with($includes)
                ->where('is_deleted', false)
                ->find($projectId);
        });
    }

    /**
     * Get project timeline data.
     */
    public function getProjectTimeline(int $projectId): Collection
    {
        $cacheKey = self::CACHE_PREFIX . "timeline_{$projectId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($projectId) {
            return Project::findOrFail($projectId)
                ->events()
                ->with(['type', 'laborGroups.assignments'])
                ->orderBy('start_date')
                ->get()
                ->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'type' => $event->type->name,
                        'start_date' => $event->start_date,
                        'end_date' => $event->end_date,
                        'duration' => $event->duration,
                        'status' => $event->status,
                        'labor_count' => $event->laborGroups->sum(function ($group) {
                            return $group->assignments->count();
                        })
                    ];
                });
        });
    }

    /**
     * Get project costs data.
     */
    public function getProjectCosts(int $projectId): array
    {
        $cacheKey = self::CACHE_PREFIX . "costs_{$projectId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($projectId) {
            $project = Project::findOrFail($projectId);
            
            $laborCosts = $project->events()
                ->with('laborGroups.assignments')
                ->get()
                ->sum(function ($event) {
                    return $event->laborGroups->sum(function ($group) {
                        return $group->assignments->sum('cost');
                    });
                });

            $materialCosts = $project->materials()->sum('cost');
            $equipmentCosts = $project->equipment()->sum('cost');
            
            return [
                'labor' => $laborCosts,
                'materials' => $materialCosts,
                'equipment' => $equipmentCosts,
                'total' => $laborCosts + $materialCosts + $equipmentCosts,
                'budget' => $project->budget,
                'variance' => $project->budget - ($laborCosts + $materialCosts + $equipmentCosts)
            ];
        });
    }

    /**
     * Get project events with filters and date range.
     */
    public function getProjectEvents(
        int $projectId,
        array $filters = [],
        array $includes = [],
        ?string $startDate = null,
        ?string $endDate = null
    ): Collection {
        $cacheKey = self::CACHE_PREFIX . "events_{$projectId}_" . 
            md5(serialize([$filters, $includes, $startDate, $endDate]));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () 
            use ($projectId, $filters, $includes, $startDate, $endDate) {
            $query = Project::findOrFail($projectId)
                ->events()
                ->with($includes);

            // Apply filters
            foreach ($filters as $field => $value) {
                $query->where($field, $value);
            }

            // Apply date range
            if ($startDate) {
                $query->where('start_date', '>=', $startDate);
            }
            if ($endDate) {
                $query->where('end_date', '<=', $endDate);
            }

            return $query->orderBy('start_date')->get();
        });
    }

    /**
     * Get project statistics.
     */
    public function getProjectStats(
        int $tenantId,
        array $filters = [],
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $cacheKey = self::CACHE_PREFIX . "stats_{$tenantId}_" . 
            md5(serialize([$filters, $startDate, $endDate]));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () 
            use ($tenantId, $filters, $startDate, $endDate) {
            $query = Project::where('tenant_id', $tenantId)
                ->where('is_deleted', false);

            // Apply filters
            foreach ($filters as $field => $value) {
                $query->where($field, $value);
            }

            // Apply date range
            if ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->where('created_at', '<=', $endDate);
            }

            $projects = $query->get();

            return [
                'total_count' => $projects->count(),
                'active_count' => $projects->where('status', 'active')->count(),
                'completed_count' => $projects->where('status', 'completed')->count(),
                'on_hold_count' => $projects->where('status', 'on_hold')->count(),
                'total_budget' => $projects->sum('budget'),
                'average_duration' => $projects->avg('estimated_duration'),
                'status_distribution' => $projects->groupBy('status')
                    ->map(fn($group) => $group->count()),
                'monthly_trend' => $projects
                    ->groupBy(fn($project) => $project->created_at->format('Y-m'))
                    ->map(fn($group) => $group->count()),
            ];
        });
    }

    /**
     * Generate project report.
     */
    public function generateProjectReport(
        int $projectId,
        string $reportType = 'summary',
        ?string $startDate = null,
        ?string $endDate = null,
        string $format = 'json'
    ): array {
        $cacheKey = self::CACHE_PREFIX . "report_{$projectId}_{$reportType}_" . 
            md5(serialize([$startDate, $endDate, $format]));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () 
            use ($projectId, $reportType, $startDate, $endDate) {
            $project = Project::with([
                'events.type',
                'events.laborGroups.assignments',
                'materials',
                'equipment'
            ])->findOrFail($projectId);

            $costs = $this->getProjectCosts($projectId);
            $timeline = $this->getProjectTimeline($projectId);

            $report = [
                'project' => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'status' => $project->status,
                    'created_at' => $project->created_at,
                    'updated_at' => $project->updated_at,
                ],
                'costs' => $costs,
                'timeline' => $timeline,
                'metrics' => [
                    'completion_percentage' => $this->calculateCompletionPercentage($project),
                    'cost_variance_percentage' => $costs['variance'] / $project->budget * 100,
                    'on_schedule' => $this->isProjectOnSchedule($project),
                ]
            ];

            if ($reportType === 'detailed') {
                $report['details'] = [
                    'events' => $project->events,
                    'materials' => $project->materials,
                    'equipment' => $project->equipment,
                    'change_history' => $this->getProjectChangeHistory($project)
                ];
            }

            return $report;
        });
    }

    /**
     * Calculate project completion percentage.
     */
    private function calculateCompletionPercentage(Project $project): float
    {
        $totalEvents = $project->events()->count();
        if ($totalEvents === 0) {
            return 0;
        }

        $completedEvents = $project->events()
            ->where('status', 'completed')
            ->count();

        return ($completedEvents / $totalEvents) * 100;
    }

    /**
     * Check if project is on schedule.
     */
    private function isProjectOnSchedule(Project $project): bool
    {
        $latestEvent = $project->events()
            ->orderBy('end_date', 'desc')
            ->first();

        if (!$latestEvent) {
            return true;
        }

        return $latestEvent->end_date <= $project->estimated_completion_date;
    }

    /**
     * Get project change history.
     */
    private function getProjectChangeHistory(Project $project): Collection
    {
        return $project->changeLedger()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($change) {
                return [
                    'action' => $change->action,
                    'changes' => $change->changes,
                    'user_id' => $change->user_id,
                    'created_at' => $change->created_at
                ];
            });
    }
}
