<?php

declare(strict_types=1);

namespace App\Http;

final class BadRequestException extends HttpException
{
    public function __construct(string $message = 'Bad request.', ?\Throwable $previous = null)
    {
        parent::__construct(400, 'BAD_REQUEST', $message, [], $previous);
    }
}
