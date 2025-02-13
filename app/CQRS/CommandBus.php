<?php

namespace App\CQRS;

use App\CQRS\Commands\Command;

class CommandBus
{
    private array $handlers = [];

    public function register(string $commandClass, string $handlerClass): void
    {
        $this->handlers[$commandClass] = $handlerClass;
    }

    public function dispatch(Command $command)
    {
        $handlerClass = $this->handlers[get_class($command)]
            ?? throw new \RuntimeException("No handler for command " . get_class($command));

        $handler = app($handlerClass);
        return $handler->handle($command);
    }
}
