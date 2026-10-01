<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once __DIR__ . '/auth.php';

/**
 * Customer-only guard used by the customer workbench.
 * Keeping the redirect here makes direct URL access safe even when a page
 * forgets to perform an additional role check.
 */
function requireCustomer(): void
{
    requireRole('customer');
}

requireCustomer();