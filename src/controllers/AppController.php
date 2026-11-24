<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\RedirectException;

abstract class AppController
{
    /**
     * @param array<string, mixed> $variables
     */
    protected function render(string $template, array $variables = [], int $statusCode = 200): void
    {
        $viewsPath = dirname(__DIR__, 2) . '/public/views';
        $templatePath = $viewsPath . '/' . $template . '.html.php';
        $layoutPath = $viewsPath . '/layout.html.php';

        if (!is_file($templatePath)) {
            throw new \RuntimeException('View not found: ' . $template);
        }

        http_response_code($statusCode);
        extract($variables, EXTR_SKIP);

        ob_start();
        require $templatePath;
        $content = (string) ob_get_clean();

        require $layoutPath;
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function json(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        echo $encoded !== false ? $encoded : '{}';
    }

    protected function redirect(string $path, int $statusCode = 302): never
    {
        throw new RedirectException($path, $statusCode);
    }
}
