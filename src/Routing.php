<?php

declare(strict_types=1);

namespace App;

use App\Controller\AppController;
use App\Http\BadRequestException;
use App\Http\ErrorResponder;
use App\Http\HttpException;
use App\Http\MethodNotAllowedException;
use App\Http\NotFoundException;
use App\Http\RedirectException;
use App\Http\Request;

final class Routing
{
    /**
     * @var list<array{
     *     method: string,
     *     path: string,
     *     isStatic: bool,
     *     segments: list<array{isParam: bool, literal?: string, name?: string, type?: 'int'|'string', regex?: string}>,
     *     pattern: string,
     *     parameterTypes: array<string, 'int'|'string'>,
     *     controller: class-string<AppController>,
     *     action: string
     * }>
     */
    private array $routes = [];

    /**
     * @param \Closure(class-string<AppController>): AppController $controllerFactory
     */
    public function __construct(
        private readonly \Closure $controllerFactory,
        private readonly ErrorResponder $errorResponder,
    ) {
    }

    /**
     * @param class-string<AppController> $controller
     */
    public function get(string $path, string $controller, string $action): void
    {
        $this->add('GET', $path, $controller, $action);
    }

    /**
     * @param class-string<AppController> $controller
     */
    public function post(string $path, string $controller, string $action): void
    {
        $this->add('POST', $path, $controller, $action);
    }

    /**
     * @param class-string<AppController> $controller
     */
    public function put(string $path, string $controller, string $action): void
    {
        $this->add('PUT', $path, $controller, $action);
    }

    /**
     * @param class-string<AppController> $controller
     */
    public function patch(string $path, string $controller, string $action): void
    {
        $this->add('PATCH', $path, $controller, $action);
    }

    /**
     * @param class-string<AppController> $controller
     */
    public function delete(string $path, string $controller, string $action): void
    {
        $this->add('DELETE', $path, $controller, $action);
    }

    /**
     * @param class-string<AppController> $controller
     */
    public function add(
        string $method,
        string $path,
        string $controller,
        string $action,
    ): void {
        $normalizedMethod = strtoupper(trim($method));
        $normalizedPath = $this->normalizePath($path);
        $compiled = $this->compileRoute($normalizedPath);

        $this->routes[] = [
            'method' => $normalizedMethod,
            'path' => $normalizedPath,
            'isStatic' => $compiled['isStatic'],
            'segments' => $compiled['segments'],
            'pattern' => $compiled['pattern'],
            'parameterTypes' => $compiled['parameterTypes'],
            'controller' => $controller,
            'action' => $action,
        ];
    }

    public function dispatch(Request $request): void
    {
        try {
            $this->dispatchInternal($request);
        } catch (RedirectException $redirect) {
            if (!headers_sent()) {
                header('Location: ' . $redirect->location, true, $redirect->statusCode);
            }
        } catch (HttpException $exception) {
            $this->errorResponder->respond($request, $exception);
        }
    }

    private function dispatchInternal(Request $request): void
    {
        if (!$request->hasValidUri) {
            throw new BadRequestException('Malformed request URI.');
        }

        $method = strtoupper(trim($request->method));
        $path = $this->normalizePath($request->path);

        // Static routes are evaluated before parameterized routes so exact paths always win.
        $orderedRoutes = array_merge(
            array_values(array_filter($this->routes, static fn (array $r): bool => $r['isStatic'])),
            array_values(array_filter($this->routes, static fn (array $r): bool => !$r['isStatic'])),
        );

        foreach ($orderedRoutes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (!preg_match($route['pattern'], $path, $matches)) {
                continue;
            }

            $parameters = $this->extractTypedParameters($matches, $route['parameterTypes']);

            $controllerClass = $route['controller'];
            $controller = ($this->controllerFactory)($controllerClass);

            if (!$controller instanceof AppController) {
                throw new \RuntimeException(sprintf('Invalid controller: %s', $controllerClass));
            }

            $action = $route['action'];
            if (!method_exists($controller, $action)) {
                throw new \RuntimeException(sprintf('Action %s::%s does not exist.', $controllerClass, $action));
            }

            $controller->{$action}(...$parameters);
            return;
        }

        // If the path fits a parameterized route skeleton for this method, a parameter constraint failed (400).
        if ($this->matchesAnyRouteSkeleton($orderedRoutes, $method, $path)) {
            throw new BadRequestException('Invalid route parameter.');
        }

        // Check if the path exists under other HTTP methods (405 Method Not Allowed).
        $allowedMethods = [];
        foreach ($orderedRoutes as $route) {
            if ($route['method'] !== $method && preg_match($route['pattern'], $path) === 1) {
                $allowedMethods[] = $route['method'];
            }
        }

        $allowedMethods = array_values(array_unique($allowedMethods));
        if ($allowedMethods !== []) {
            throw new MethodNotAllowedException($allowedMethods);
        }

        throw new NotFoundException('The requested resource was not found.');
    }

    /**
     * @return array{
     *     isStatic: bool,
     *     segments: list<array{isParam: bool, literal?: string, name?: string, type?: 'int'|'string', regex?: string}>,
     *     pattern: string,
     *     parameterTypes: array<string, 'int'|'string'>
     * }
     */
    private function compileRoute(string $normalizedPath): array
    {
        if ($normalizedPath === '/') {
            return [
                'isStatic' => true,
                'segments' => [],
                'pattern' => '~^/$~',
                'parameterTypes' => [],
            ];
        }

        $rawSegments = explode('/', ltrim($normalizedPath, '/'));
        $isStatic = true;
        $segments = [];
        $patternParts = [];
        $parameterTypes = [];

        foreach ($rawSegments as $segment) {
            if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)(?::(.+))?\}$/', $segment, $match) === 1) {
                $isStatic = false;
                $paramName = $match[1];
                $customRegex = $match[2] ?? null;

                if ($customRegex === null) {
                    $type = 'int';
                    $regex = '\d+';
                } else {
                    $type = 'string';
                    $regex = $customRegex;
                }

                $parameterTypes[$paramName] = $type;
                $segments[] = [
                    'isParam' => true,
                    'name' => $paramName,
                    'type' => $type,
                    'regex' => $regex,
                ];
                $patternParts[] = '(?P<' . $paramName . '>' . str_replace('~', '\~', $regex) . ')';
                continue;
            }

            $segments[] = [
                'isParam' => false,
                'literal' => $segment,
            ];
            $patternParts[] = preg_quote($segment, '~');
        }

        return [
            'isStatic' => $isStatic,
            'segments' => $segments,
            'pattern' => '~^/' . implode('/', $patternParts) . '$~',
            'parameterTypes' => $parameterTypes,
        ];
    }

    /**
     * @param array<int|string, string>      $matches
     * @param array<string, 'int'|'string'>  $parameterTypes
     * @return array<string, int|string>
     */
    private function extractTypedParameters(array $matches, array $parameterTypes): array
    {
        $parameters = [];

        foreach ($parameterTypes as $name => $type) {
            if (!array_key_exists($name, $matches)) {
                continue;
            }

            $rawValue = $matches[$name];
            if ($type === 'int') {
                $parsed = filter_var($rawValue, FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1],
                ]);
                if ($parsed === false) {
                    throw new BadRequestException(sprintf('Route parameter "%s" must be a positive integer.', $name));
                }

                $parameters[$name] = $parsed;
                continue;
            }

            $parameters[$name] = $rawValue;
        }

        return $parameters;
    }

    /**
     * @param list<array<string, mixed>> $routes
     */
    private function matchesAnyRouteSkeleton(array $routes, string $method, string $path): bool
    {
        if ($path === '/') {
            return false;
        }

        $pathSegments = explode('/', ltrim($path, '/'));

        foreach ($routes as $route) {
            if ($route['method'] !== $method || $route['isStatic']) {
                continue;
            }

            $routeSegments = $route['segments'];
            if (count($routeSegments) !== count($pathSegments)) {
                continue;
            }

            $skeletonMatches = true;
            foreach ($routeSegments as $index => $segmentDef) {
                if ($segmentDef['isParam']) {
                    if ($pathSegments[$index] === '') {
                        $skeletonMatches = false;
                        break;
                    }
                    continue;
                }

                if (($segmentDef['literal'] ?? '') !== $pathSegments[$index]) {
                    $skeletonMatches = false;
                    break;
                }
            }

            if ($skeletonMatches) {
                return true;
            }
        }

        return false;
    }

    private function normalizePath(string $path): string
    {
        $trimmed = trim($path);
        if ($trimmed === '' || $trimmed === '/') {
            return '/';
        }

        $withLeadingSlash = str_starts_with($trimmed, '/') ? $trimmed : '/' . $trimmed;
        return rtrim($withLeadingSlash, '/') ?: '/';
    }
}
