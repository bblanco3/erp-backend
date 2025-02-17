<?php

namespace App\Events;

interface DomainEvent {
    public function occurred_on(): \DateTimeImmutable;
}