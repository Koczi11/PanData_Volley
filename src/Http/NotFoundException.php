<?php

declare(strict_types=1);

namespace App\Http;

final class NotFoundException extends HttpException
{
    public function __construct(string $message = 'The requested resource was not found.', ?\Throwable $previous = null)
    {
        parent::__construct(404, 'NOT_FOUND', $message, [], $previous);
    }
}
