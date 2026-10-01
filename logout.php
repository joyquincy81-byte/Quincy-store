<?php

require_once __DIR__ . '/config/config.php';
require_once ROOT_PATH . '/includes/session.php';

clearUserSession();
header('Location: ' . BASE_URL . '/login.php');
exit;