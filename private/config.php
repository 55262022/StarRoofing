<?php
// Load environment variables from .env file
function load_env($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
    return true;
}

// Load .env file
load_env(__DIR__ . '../.env');

// Function to get environment variable with default
function env($key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }
    return $value;
}
?>