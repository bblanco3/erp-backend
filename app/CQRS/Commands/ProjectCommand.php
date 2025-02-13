<?php

namespace App\CQRS\Commands;

class ProjectCommand implements Command
{
    public const TYPE_CREATE = 'create';
    public const TYPE_UPDATE = 'update';
    public const TYPE_DELETE = 'delete';

    public function __construct(
        public readonly string $type,
        public readonly int $tenantId,
        public readonly int $userId,
        public readonly ?int $projectId = null,
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly array $attributes = []
    ) {
        if (!in_array($type, [self::TYPE_CREATE, self::TYPE_UPDATE, self::TYPE_DELETE])) {
            throw new \InvalidArgumentException('Invalid command type');
        }

        if ($type === self::TYPE_CREATE && $name === null) {
            throw new \InvalidArgumentException('Name is required for create command');
        }

        if (($type === self::TYPE_UPDATE || $type === self::TYPE_DELETE) && $projectId === null) {
            throw new \InvalidArgumentException('Project ID is required for update/delete command');
        }
    }
}
