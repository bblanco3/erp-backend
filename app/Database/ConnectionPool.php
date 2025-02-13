<?php

namespace App\Database;

use Closure;
use PDO;
use SplQueue;
use RuntimeException;

class ConnectionPool
{
    private $connections;
    private $factory;
    private $minConnections;
    private $maxConnections;
    private $currentConnections = 0;
    private $inUseConnections = [];
    private $lastPing = [];

    public function __construct(int $minConnections, int $maxConnections, Closure $factory)
    {
        $this->connections = new SplQueue();
        $this->factory = $factory;
        $this->minConnections = $minConnections;
        $this->maxConnections = $maxConnections;
        
        $this->initialize();
    }

    private function initialize()
    {
        for ($i = 0; $i < $this->minConnections; $i++) {
            $this->addConnection();
        }
    }

    private function addConnection()
    {
        if ($this->currentConnections >= $this->maxConnections) {
            throw new RuntimeException('Connection pool is full');
        }

        $connection = ($this->factory)();
        $this->connections->enqueue($connection);
        $this->currentConnections++;
        $this->lastPing[spl_object_hash($connection)] = time();
    }

    public function getConnection(): PDO
    {
        if ($this->connections->isEmpty() && $this->currentConnections < $this->maxConnections) {
            $this->addConnection();
        }

        if ($this->connections->isEmpty()) {
            throw new RuntimeException('No available connections in the pool');
        }

        $connection = $this->connections->dequeue();
        $hash = spl_object_hash($connection);

        // Check if connection is still alive
        if (!$this->ping($connection)) {
            $connection = ($this->factory)();
            $this->lastPing[$hash] = time();
        }

        $this->inUseConnections[$hash] = $connection;
        return $connection;
    }

    public function releaseConnection(PDO $connection)
    {
        $hash = spl_object_hash($connection);
        
        if (isset($this->inUseConnections[$hash])) {
            unset($this->inUseConnections[$hash]);
            $this->connections->enqueue($connection);
        }
    }

    private function ping(PDO $connection): bool
    {
        try {
            $connection->query('SELECT 1');
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function close()
    {
        while (!$this->connections->isEmpty()) {
            $connection = $this->connections->dequeue();
            $connection = null;
            $this->currentConnections--;
        }

        foreach ($this->inUseConnections as $connection) {
            $connection = null;
            $this->currentConnections--;
        }

        $this->inUseConnections = [];
        $this->lastPing = [];
    }
}
