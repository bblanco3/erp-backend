<?php

namespace App\Events;

use Illuminate\Support\Facades\Queue;

// app/Events/EventBus.php
interface EventBus {
    public function publish(DomainEvent ...$events): void;
    public function subscribe(string $event_class, callable $handler): void;
}

// Implementation
class AsyncEventBus implements EventBus {
    private array $subscribers = [];
    
    public function publish(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            $event_class = get_class($event);
            if (isset($this->subscribers[$event_class])) {
                foreach ($this->subscribers[$event_class] as $handler) {
                    // Dispatch to queue for async processing
                    Queue::push(function() use ($handler, $event) {
                        $handler($event);
                    }, [], 'events');
                }
            }
        }
    }
    
    public function subscribe(string $event_class, callable $handler): void
    {
        $this->subscribers[$event_class][] = $handler;
    }
}