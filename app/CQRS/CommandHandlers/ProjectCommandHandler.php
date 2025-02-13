<?php

namespace App\CQRS\CommandHandlers;

use App\CQRS\Commands\ProjectCommand;
use App\Models\Project;
use App\Models\ChangeLedger;
use App\ReadModels\ProjectReadModel;

class ProjectCommandHandler
{
    public function __construct(
        private readonly ProjectReadModel $readModel
    ) {}

    public function handle(ProjectCommand $command): ?Project
    {
        return match($command->type) {
            ProjectCommand::TYPE_CREATE => $this->handleCreate($command),
            ProjectCommand::TYPE_UPDATE => $this->handleUpdate($command),
            ProjectCommand::TYPE_DELETE => $this->handleDelete($command),
            default => throw new \InvalidArgumentException('Invalid command type')
        };
    }

    private function handleCreate(ProjectCommand $command): Project
    {
        $project = Project::create([
            'tenant_id' => $command->tenantId,
            'name' => $command->name,
            'description' => $command->description,
            'created_by' => $command->userId,
        ] + $command->attributes);

        $this->recordChange($command, $project, 'create');
        $this->readModel->invalidateCache($command->tenantId);

        return $project;
    }

    private function handleUpdate(ProjectCommand $command): Project
    {
        $project = Project::findOrFail($command->projectId);
        $oldData = $project->toArray();

        $updateData = array_filter([
            'name' => $command->name,
            'description' => $command->description,
        ] + $command->attributes);

        $project->update($updateData);

        $this->recordChange($command, $project, 'update', $oldData);
        $this->readModel->invalidateCache($command->tenantId);

        return $project;
    }

    private function handleDelete(ProjectCommand $command): ?Project
    {
        $project = Project::findOrFail($command->projectId);
        $project->update(['is_deleted' => true]);

        $this->recordChange($command, $project, 'delete');
        $this->readModel->invalidateCache($command->tenantId);

        return null;
    }

    private function recordChange(ProjectCommand $command, Project $project, string $action, ?array $oldData = null): void
    {
        $changes = $action === 'update'
            ? ['old' => $oldData, 'new' => $project->toArray()]
            : $project->toArray();

        ChangeLedger::create([
            'tenant_id' => $command->tenantId,
            'model_type' => Project::class,
            'model_id' => $project->id,
            'action' => $action,
            'changes' => $changes,
            'user_id' => $command->userId,
        ]);
    }
}
