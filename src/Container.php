<?php

declare(strict_types=1);

namespace App;

final class Container
{
    /** @var array<string, array{factory: callable(self): mixed, shared: bool}> */
    private array $definitions = [];

    /** @var array<string, mixed> */
    private array $instances = [];

    /** @var array<string, bool> */
    private array $resolving = [];

    /**
     * @param callable(self): mixed $factory
     */
    public function singleton(string $id, callable $factory): void
    {
        $this->definitions[$id] = [
            'factory' => $factory,
            'shared' => true,
        ];
        unset($this->instances[$id]);
    }

    /**
     * @param callable(self): mixed $factory
     */
    public function bind(string $id, callable $factory): void
    {
        $this->definitions[$id] = [
            'factory' => $factory,
            'shared' => false,
        ];
        unset($this->instances[$id]);
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->definitions) || array_key_exists($id, $this->instances);
    }

    public function get(string $id): mixed
    {
        if (array_key_exists($id, $this->instances)) {
            return $this->instances[$id];
        }

        if (!array_key_exists($id, $this->definitions)) {
            throw new \RuntimeException(sprintf('Service "%s" is not registered in the container.', $id));
        }

        if (isset($this->resolving[$id])) {
            throw new \RuntimeException(sprintf('Circular dependency detected while resolving "%s".', $id));
        }

        $this->resolving[$id] = true;

        try {
            $definition = $this->definitions[$id];
            $instance = ($definition['factory'])($this);

            if ($definition['shared']) {
                $this->instances[$id] = $instance;
            }

            return $instance;
        } finally {
            unset($this->resolving[$id]);
        }
    }
}
