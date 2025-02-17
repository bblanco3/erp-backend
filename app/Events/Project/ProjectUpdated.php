<?php

namespace App\Events\Project;

use App\Events\DomainEvent;
use DateTimeImmutable;

class ProjectUpdated implements DomainEvent
{
    private DateTimeImmutable $occurred_on;

    public function __construct(
        private readonly string $project_id,
        private readonly array $changed_fields,
        private readonly string $updated_by
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

    public function changed_fields(): array
    {
        return $this->changed_fields;
    }

    public function updated_by(): string
    {
        return $this->updated_by;
    }
}
