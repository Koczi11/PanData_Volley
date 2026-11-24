<?php
$statusCode = (int) ($statusCode ?? 404);
$errorHeading = $errorHeading ?? 'Page Not Found';
$errorMessage = $errorMessage ?? 'The requested resource was not found.';
$actionHref = $actionHref ?? '/';
$actionLabel = $actionLabel ?? 'Return to Home';

require __DIR__ . '/_error-card.html.php';
