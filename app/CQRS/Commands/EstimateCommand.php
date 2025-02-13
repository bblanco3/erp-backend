<?php

namespace App\CQRS\Commands;

class EstimateCommand
{
    public const TYPE_CREATE = 'create';
    public const TYPE_UPDATE = 'update';
    public const TYPE_DELETE = 'delete';
    public const TYPE_ADD_ITEM = 'add_item';
    public const TYPE_UPDATE_ITEM = 'update_item';
    public const TYPE_DELETE_ITEM = 'delete_item';
    public const TYPE_APPROVE = 'approve';
    public const TYPE_REJECT = 'reject';
    public const TYPE_REVISE = 'revise';

    public function __construct(
        public readonly string $type,
        public readonly int $tenant_id,
        public readonly int $user_id,
        public readonly ?int $estimate_id = null,
        public readonly ?int $project_id = null,
        public readonly ?array $attributes = null
    ) {}
}
