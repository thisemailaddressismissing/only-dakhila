<?php
// Router for PHP built-in web server
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Match /dakhila-print/{verify_id}
if (preg_match('#^/dakhila-print/([0-9a-zA-Z]+)#', $uri, $matches)) {
    $_GET['id'] = $matches[1];
    require __DIR__ . '/dakhila-print.php';
    exit;
}

// Serve existing physical static files directly
$filePath = __DIR__ . $uri;
if ($uri !== '/' && file_exists($filePath) && is_file($filePath)) {
    return false;
}

// Handle root /
if ($uri === '/') {
    require __DIR__ . '/index.php';
    exit;
}

// Extensionless PHP URL rewrite (e.g., /admin -> admin.php)
$phpFile = __DIR__ . $uri . '.php';
if (file_exists($phpFile) && is_file($phpFile)) {
    require $phpFile;
    exit;
}

return false;
