<?php

declare(strict_types=1);

namespace App\Http;

final class ErrorResponder
{
    public function __construct(private readonly string $viewsPath)
    {
    }

    public function respond(Request $request, HttpException $exception): void
    {
        http_response_code($exception->statusCode);

        if (!headers_sent()) {
            foreach ($exception->headers as $name => $value) {
                header($name . ': ' . $value);
            }

            if ($exception->statusCode === 401 || $exception->statusCode === 403) {
                header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                header('Pragma: no-cache');
            }
        }

        if ($request->expectsJson()) {
            $this->emitJson(
                $exception->statusCode,
                $exception->errorCode,
                $exception->getMessage()
            );
            return;
        }

        $this->emitHtml(
            $exception->statusCode,
            $this->titleForStatus($exception->statusCode),
            $exception->getMessage()
        );
    }

    public function respondServerError(Request $request, \Throwable $exception): void
    {
        error_log(sprintf(
            'Unhandled application error [%s]: %s in %s:%d',
            $exception::class,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        ));

        http_response_code(500);

        if ($request->expectsJson()) {
            $this->emitJson(500, 'INTERNAL_SERVER_ERROR', 'An internal server error occurred.');
            return;
        }

        $this->emitHtml(
            500,
            '500 - Internal Server Error',
            'An unexpected error occurred while processing your request.'
        );
    }

    private function emitJson(int $statusCode, string $errorCode, string $message): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        $encoded = json_encode([
            'error' => [
                'status' => $statusCode,
                'code' => $errorCode,
                'message' => $message,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        echo $encoded !== false ? $encoded : '{"error":{"status":500,"code":"INTERNAL_SERVER_ERROR"}}';
    }

    private function emitHtml(int $statusCode, string $pageTitle, string $errorMessage): void
    {
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
        }

        $templatePath = $this->viewsPath . '/errors/' . $statusCode . '.html.php';
        if (!is_file($templatePath)) {
            $templatePath = $this->viewsPath . '/errors/_error-card.html.php';
        }

        $layoutPath = $this->viewsPath . '/layout.html.php';

        if (!is_file($templatePath) || !is_file($layoutPath)) {
            echo sprintf(
                '<!doctype html><html lang="en"><body><h1>%d</h1><p>%s</p></body></html>',
                $statusCode,
                htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8')
            );
            return;
        }

        $pageLang = 'en';
        $errorHeading = $this->headingForStatus($statusCode);
        $actionHref = $statusCode === 401 ? '/login' : '/';
        $actionLabel = $statusCode === 401 ? 'Sign In' : 'Return to Home';

        ob_start();
        require $templatePath;
        $content = (string) ob_get_clean();

        require $layoutPath;
    }

    private function headingForStatus(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Access Forbidden',
            404 => 'Page Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
            default => 'Error',
        };
    }

    private function titleForStatus(int $statusCode): string
    {
        return match ($statusCode) {
            400 => '400 - Bad Request | PanData Volley',
            401 => '401 - Unauthorized | PanData Volley',
            403 => '403 - Forbidden | PanData Volley',
            404 => '404 - Page Not Found | PanData Volley',
            405 => '405 - Method Not Allowed | PanData Volley',
            500 => '500 - Internal Server Error | PanData Volley',
            default => $statusCode . ' - Error | PanData Volley',
        };
    }
}
