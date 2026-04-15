<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

require_login();

header('Location: index.php', true, 302);
exit;
