<?php

declare(strict_types=1);

namespace App\Http;

final readonly class Request
{
    /**
     * @param array<string, mixed>  $query
     * @param array<string, mixed>  $post
     * @param array<string, string> $cookies
     * @param array<string, string> $headers
     */
    public function __construct(
        public string $method,
        public string $uri,
        public string $path,
        public array $query = [],
        public array $post = [],
        public array $cookies = [],
        public array $headers = [],
        public ?string $clientIp = null,
        public bool $isHttps = false,
        public bool $hasValidUri = true,
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = strtoupper(trim((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')));
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');

        $parsedPath = parse_url($uri, PHP_URL_PATH);
        $hasValidUri = is_string($parsedPath) && $parsedPath !== '' && !preg_match('/[\x00-\x1F\x7F]/', $uri);
        $path = is_string($parsedPath) && $parsedPath !== '' ? $parsedPath : '/';

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (!is_string($key) || !is_scalar($value)) {
                continue;
            }

            if (str_starts_with($key, 'HTTP_')) {
                $headerName = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$headerName] = (string) $value;
            } elseif ($key === 'CONTENT_TYPE' || $key === 'CONTENT_LENGTH') {
                $headerName = strtolower(str_replace('_', '-', $key));
                $headers[$headerName] = (string) $value;
            }
        }

        $cookies = [];
        foreach ($_COOKIE as $name => $value) {
            if (is_string($name) && is_scalar($value)) {
                $cookies[$name] = (string) $value;
            }
        }

        $forwardedProto = strtolower((string) ($headers['x-forwarded-proto'] ?? ''));
        $isHttps = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
            || ((string) ($_SERVER['SERVER_PORT'] ?? '') === '443')
            || $forwardedProto === 'https';

        $clientIp = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])
            ? $_SERVER['REMOTE_ADDR']
            : null;

        return new self(
            method: $method !== '' ? $method : 'GET',
            uri: $uri,
            path: $path,
            query: $_GET,
            post: $_POST,
            cookies: $cookies,
            headers: $headers,
            clientIp: $clientIp,
            isHttps: $isHttps,
            hasValidUri: $hasValidUri,
        );
    }

    public function isApiRequest(): bool
    {
        return $this->path === '/api' || str_starts_with($this->path, '/api/');
    }

    public function expectsJson(): bool
    {
        if ($this->isApiRequest()) {
            return true;
        }

        $requestedWith = strtolower((string) $this->header('x-requested-with', ''));
        if ($requestedWith === 'xmlhttprequest') {
            return true;
        }

        $accept = strtolower((string) $this->header('accept', ''));
        return str_contains($accept, 'application/json') && !str_contains($accept, 'text/html');
    }

    public function isBrowserHtmlGet(): bool
    {
        if ($this->method !== 'GET' || $this->expectsJson()) {
            return false;
        }

        $accept = strtolower((string) $this->header('accept', ''));
        return str_contains($accept, 'text/html');
    }

    public function header(string $name, ?string $default = null): ?string
    {
        $normalized = strtolower($name);
        return $this->headers[$normalized] ?? $default;
    }

    public function cookie(string $name): ?string
    {
        return $this->cookies[$name] ?? null;
    }

    public function postString(string $key, string $default = ''): string
    {
        $value = $this->post[$key] ?? null;
        if (!is_scalar($value)) {
            return $default;
        }

        return (string) $value;
    }

    public function postBool(string $key): bool
    {
        $value = $this->post[$key] ?? null;
        if (is_bool($value)) {
            return $value;
        }

        if (!is_scalar($value)) {
            return false;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'on', 'yes'], true);
    }

    public function queryString(string $key, ?string $default = null): ?string
    {
        if (!array_key_exists($key, $this->query)) {
            return $default;
        }

        $value = $this->query[$key];
        if (!is_scalar($value)) {
            throw new BadRequestException(sprintf('Query parameter "%s" must be a scalar value.', $key));
        }

        return (string) $value;
    }

    public function queryInt(
        string $key,
        ?int $default = null,
        ?int $min = null,
        ?int $max = null,
    ): ?int {
        if (!array_key_exists($key, $this->query)) {
            return $default;
        }

        $raw = $this->query[$key];
        if ($raw === '' || $raw === null) {
            return $default;
        }

        if (!is_scalar($raw)) {
            throw new BadRequestException(sprintf('Query parameter "%s" must be an integer.', $key));
        }

        $parsed = filter_var(trim((string) $raw), FILTER_VALIDATE_INT);
        if ($parsed === false) {
            throw new BadRequestException(sprintf('Query parameter "%s" must be a valid integer.', $key));
        }

        if ($min !== null && $parsed < $min) {
            throw new BadRequestException(sprintf('Query parameter "%s" must be at least %d.', $key, $min));
        }

        if ($max !== null && $parsed > $max) {
            throw new BadRequestException(sprintf('Query parameter "%s" must not exceed %d.', $key, $max));
        }

        return $parsed;
    }
}
