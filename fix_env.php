<?php
// Script to fix the .env file on the server
error_reporting(E_ALL);
ini_set('display_errors', 1);

$base = __DIR__;
$envFile = $base . '/.env';

function respond($ok, $msg) {
    header('Content-Type: application/json');
    echo json_encode(['ok'=>$ok, 'msg'=>$msg]);
    exit;
}

if (!file_exists($envFile)) {
    respond(false, '.env file not found at ' . $envFile);
}

// Read the current .env file
$envContent = file_get_contents($envFile);

// Generate a new APP_KEY if it's empty
if (strpos($envContent, 'APP_KEY=') !== false) {
    // Generate a new key using Laravel's method
    $key = 'base64:' . base64_encode(random_bytes(32));
    
    // Replace the empty APP_KEY
    $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $envContent);
    
    // Write back to file
    if (file_put_contents($envFile, $envContent) !== false) {
        // Clear config cache
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        
        respond(true, 'APP_KEY updated successfully. Key: ' . $key);
    } else {
        respond(false, 'Failed to write to .env file');
    }
} else {
    respond(false, 'APP_KEY not found in .env file');
}
?>
