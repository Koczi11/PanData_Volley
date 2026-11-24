<?php
$statusCode = (int) ($statusCode ?? 400);
$errorHeading = $errorHeading ?? 'Bad Request';
$errorMessage = $errorMessage ?? 'The request parameters are invalid.';
$actionHref = $actionHref ?? '/';
$actionLabel = $actionLabel ?? 'Return to Home';

require __DIR__ . '/_error-card.html.php';
