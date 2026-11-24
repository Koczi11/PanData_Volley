<?php

declare(strict_types=1);

namespace App\Config;

final class Env
{
    /** @var array<string, string> */
    private array $values;

    /**
     * @param array<string, string> $values
     */
    public function __construct(array $values = [])
    {
        $normalized = [];
        foreach ($values as $key => $value) {
            if (is_string($key) && $key !== '') {
                $normalized[$key] = (string) $value;
            }
        }

        $this->values = $normalized;
    }

    public static function load(?string $dotenvPath = null): self
    {
        $values = [];

        $processEnv = getenv();
        if (is_array($processEnv)) {
            foreach ($processEnv as $key => $value) {
                if (is_string($key) && is_scalar($value)) {
                    $values[$key] = (string) $value;
                }
            }
        }

        foreach ([$_ENV, $_SERVER] as $source) {
            foreach ($source as $key => $value) {
                if (is_string($key) && $key !== '' && is_scalar($value) && !array_key_exists($key, $values)) {
                    $values[$key] = (string) $value;
                }
            }
        }

        if ($dotenvPath !== null && is_file($dotenvPath) && is_readable($dotenvPath)) {
            $lines = file($dotenvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (is_array($lines)) {
                foreach ($lines as $line) {
                    $trimmed = trim($line);
                    if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                        continue;
                    }

                    if (str_starts_with($trimmed, 'export ')) {
                        $trimmed = trim(substr($trimmed, 7));
                    }

                    $separatorPos = strpos($trimmed, '=');
                    if ($separatorPos === false) {
                        continue;
                    }

                    $key = trim(substr($trimmed, 0, $separatorPos));
                    if ($key === '') {
                        continue;
                    }

                    // Do not overwrite variables already provided by the OS/container environment.
                    if (array_key_exists($key, $values)) {
                        continue;
                    }

                    $rawValue = trim(substr($trimmed, $separatorPos + 1));
                    $values[$key] = self::stripQuotes($rawValue);
                }
            }
        }

        return new self($values);
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->values) && trim($this->values[$key]) !== '';
    }

    public function get(string $key, ?string $default = null): ?string
    {
        if (!array_key_exists($key, $this->values)) {
            return $default;
        }

        return $this->values[$key];
    }

    public function require(string $key): string
    {
        if (!$this->has($key)) {
            throw new \RuntimeException(sprintf('Missing required environment variable: %s', $key));
        }

        return $this->values[$key];
    }

    public function requireKeys(string ...$keys): void
    {
        $missing = [];
        foreach ($keys as $key) {
            if (!$this->has($key)) {
                $missing[] = $key;
            }
        }

        if ($missing !== []) {
            throw new \RuntimeException(
                'Missing required environment variables: ' . implode(', ', $missing)
            );
        }
    }

    public function getInt(string $key, ?int $default = null): int
    {
        if (!$this->has($key)) {
            if ($default !== null) {
                return $default;
            }

            throw new \RuntimeException(sprintf('Missing required integer environment variable: %s', $key));
        }

        $value = filter_var(trim($this->values[$key]), FILTER_VALIDATE_INT);
        if ($value === false) {
            throw new \RuntimeException(sprintf('Environment variable %s must be a valid integer.', $key));
        }

        return $value;
    }

    public function getBool(string $key, bool $default = false): bool
    {
        if (!$this->has($key)) {
            return $default;
        }

        $parsed = filter_var(trim($this->values[$key]), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        if ($parsed === null) {
            throw new \RuntimeException(sprintf('Environment variable %s must be a valid boolean.', $key));
        }

        return $parsed;
    }

    public function isProduction(): bool
    {
        return strtolower(trim((string) $this->get('APP_ENV', 'development'))) === 'production';
    }

    private static function stripQuotes(string $value): string
    {
        $length = strlen($value);
        if ($length >= 2) {
            $first = $value[0];
            $last = $value[$length - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                return substr($value, 1, -1);
            }
        }

        return $value;
    }
}
