<?php
$statusCode = (int) ($statusCode ?? 500);
$errorHeading = $errorHeading ?? 'Internal Server Error';
$errorMessage = $errorMessage ?? 'An unexpected error occurred while processing your request.';
$actionHref = $actionHref ?? '/';
$actionLabel = $actionLabel ?? 'Return to Home';

require __DIR__ . '/_error-card.html.php';
