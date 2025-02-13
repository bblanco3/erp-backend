<?php

namespace App\Queries;

use Illuminate\Http\Request;

class ProjectQuery
{
    private array $filters = [];
    private array $includes = [];
    private ?string $searchTerm = null;
    private ?string $sortBy = null;
    private string $sortDirection = 'asc';
    private ?int $limit = null;

    public function __construct(Request $request)
    {
        $this->parseRequest($request);
    }

    private function parseRequest(Request $request): void
    {
        // Parse filters
        if ($request->has('filters')) {
            $this->filters = $request->input('filters');
        }

        // Parse includes (relationships to eager load)
        if ($request->has('includes')) {
            $this->includes = explode(',', $request->input('includes'));
        }

        // Parse search term
        if ($request->has('search')) {
            $this->searchTerm = $request->input('search');
        }

        // Parse sorting
        if ($request->has('sort_by')) {
            $this->sortBy = $request->input('sort_by');
            $this->sortDirection = $request->input('sort_direction', 'asc');
        }

        // Parse pagination
        if ($request->has('limit')) {
            $this->limit = (int) $request->input('limit');
        }
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getIncludes(): array
    {
        return $this->includes;
    }

    public function getSearchTerm(): ?string
    {
        return $this->searchTerm;
    }

    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    public function getSortDirection(): string
    {
        return $this->sortDirection;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }
}
