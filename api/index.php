<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Diagnostics endpoint to test runtime health
if (isset($_GET['test']) || (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/vercel-test') !== false)) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "MaisuApp Vercel PHP Diagnostic:\n";
    echo "PHP Version: " . phpversion() . "\n";
    echo "Loaded extensions: " . implode(', ', get_loaded_extensions()) . "\n";
    echo "Tmp directory writable: " . (is_writable('/tmp') ? 'YES' : 'NO') . "\n";
    echo "Memory Limit: " . ini_get('memory_limit') . "\n";
    exit(0);
}

// Ensure required temporary storage directories exist for Vercel Serverless
$storageDirs = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/bootstrap/cache',
];

foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Ensure critical environment variables exist
if (empty($_ENV['APP_KEY']) && empty(getenv('APP_KEY'))) {
    $_ENV['APP_KEY'] = 'base64:Uw/BK+G6f0msrdQcHP3ooj8ftPIfCu8g7ojSRqHlaho=';
    putenv('APP_KEY=base64:Uw/BK+G6f0msrdQcHP3ooj8ftPIfCu8g7ojSRqHlaho=');
}

try {
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "=== MaisuApp Vercel Runtime Exception ===\n";
    echo get_class($e) . ': ' . $e->getMessage() . "\n\n";
    echo "File: " . $e->getFile() . ':' . $e->getLine() . "\n\n";
    echo "Stack Trace:\n" . $e->getTraceAsString();
}
