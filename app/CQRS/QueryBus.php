<?php

namespace App\CQRS;

use App\CQRS\Queries\Query;

class QueryBus
{
    private array $handlers = [];

    public function register(string $queryClass, string $handlerClass): void
    {
        $this->handlers[$queryClass] = $handlerClass;
    }

    public function ask(Query $query)
    {
        $handlerClass = $this->handlers[get_class($query)]
            ?? throw new \RuntimeException("No handler for query " . get_class($query));

        $handler = app($handlerClass);
        return $handler->handle($query);
    }
}
