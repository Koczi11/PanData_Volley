<?php
$statusCode = (int) ($statusCode ?? 405);
$errorHeading = $errorHeading ?? 'Method Not Allowed';
$errorMessage = $errorMessage ?? 'The HTTP method is not allowed for this route.';
$actionHref = $actionHref ?? '/';
$actionLabel = $actionLabel ?? 'Return to Home';

require __DIR__ . '/_error-card.html.php';
