<?php

namespace App\Events\Project;

use App\Events\DomainEvent;
use DateTimeImmutable;

class ProjectCreated implements DomainEvent
{
    private DateTimeImmutable $occurred_on;

    public function __construct(
        private readonly string $project_id,
        private readonly string $project_name,
        private readonly string $created_by
    ) {
        $this->occurred_on = new DateTimeImmutable();
    }

    public function occurred_on(): DateTimeImmutable
    {
        return $this->occurred_on;
    }

    public function project_id(): string
    {
        return $this->project_id;
    }

    public function project_name(): string
    {
        return $this->project_name;
    }

    public function created_by(): string
    {
        return $this->created_by;
    }
}
