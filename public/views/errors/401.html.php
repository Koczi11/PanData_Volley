<?php
$statusCode = (int) ($statusCode ?? 401);
$errorHeading = $errorHeading ?? 'Unauthorized';
$errorMessage = $errorMessage ?? 'Authentication is required to access this resource.';
$actionHref = $actionHref ?? '/login';
$actionLabel = $actionLabel ?? 'Sign In';

require __DIR__ . '/_error-card.html.php';
