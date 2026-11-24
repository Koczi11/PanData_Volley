<?php
$statusCode = (int) ($statusCode ?? 403);
$errorHeading = $errorHeading ?? 'Access Forbidden';
$errorMessage = $errorMessage ?? 'You do not have permission to access this resource.';
$actionHref = $actionHref ?? '/';
$actionLabel = $actionLabel ?? 'Return to Home';

require __DIR__ . '/_error-card.html.php';
