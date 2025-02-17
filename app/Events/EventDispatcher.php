<?php

namespace App\Events;

class EventDispatcher {
    private array $events = [];
    
    public function __construct(
        private readonly EventBus $event_bus
    ) {}
    
    public function record_event(DomainEvent $event): void
    {
        $this->events[] = $event;
    }
    
    public function release_events(): void
    {
        if (!empty($this->events)) {
            $this->event_bus->publish(...$this->events);
            $this->events = [];
        }
    }
}