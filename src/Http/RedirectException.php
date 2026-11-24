<?php

declare(strict_types=1);

namespace App\Http;

final class RedirectException extends \RuntimeException
{
    public function __construct(
        public readonly string $location,
        public readonly int $statusCode = 302,
    ) {
        parent::__construct(sprintf('Redirect to %s', $location), $statusCode);
    }
}
