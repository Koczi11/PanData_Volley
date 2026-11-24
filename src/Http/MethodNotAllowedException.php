<?php

declare(strict_types=1);

namespace App\Http;

final class MethodNotAllowedException extends HttpException
{
    /**
     * @param list<string> $allowedMethods
     */
    public function __construct(
        array $allowedMethods,
        string $message = 'HTTP method is not allowed for this route.',
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            405,
            'METHOD_NOT_ALLOWED',
            $message,
            ['Allow' => implode(', ', $allowedMethods)],
            $previous
        );
    }
}
