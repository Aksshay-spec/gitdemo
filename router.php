<?php
// Get the current request URI
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Basic routing setup
switch ($request) {
    case '/':
    case '/index.php':
        require __DIR__ . '/index.php';  // Loads the main content file
        break;

    // Future pages:
    // case '/about':
    //     require __DIR__ . '/about.php';
    //     break;
    // case '/contact':
    //     require __DIR__ . '/contact.php';
    //     break; pagessssss

    default:
        http_response_code(404);
        echo "<h1>404 - Page Not Found</h1>";
        echo "<p>The page you requested was not found.</p>";
        break;
}
?>
