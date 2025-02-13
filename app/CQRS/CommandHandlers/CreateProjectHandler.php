<?php

namespace App\CQRS\CommandHandlers;

use App\CQRS\Commands\CreateProjectCommand;
use App\Models\Project;
use App\Models\ChangeLedger;
use App\ReadModels\ProjectReadModel;

class CreateProjectHandler
{
    public function __construct(
        private readonly ProjectReadModel $readModel
    ) {}

    public function handle(CreateProjectCommand $command): Project
    {
        // Create project
        $project = Project::create([
            'tenant_id' => $command->tenantId,
            'name' => $command->name,
            'description' => $command->description,
            'created_by' => $command->userId,
        ] + $command->attributes);

        // Record change in ledger
        ChangeLedger::create([
            'tenant_id' => $command->tenantId,
            'model_type' => Project::class,
            'model_id' => $project->id,
            'action' => 'create',
            'changes' => $project->toArray(),
            'user_id' => $command->userId,
        ]);

        // Invalidate cache
        $this->readModel->invalidateCache($command->tenantId);

        return $project;
    }
}
