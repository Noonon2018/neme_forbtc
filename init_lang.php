<?php
// Start the session to remember the user's language choice
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define available languages
$available_langs = ['en', 'th'];
$default_lang = 'en';

// Handle language change from URL
if (isset($_GET['lang']) && in_array($_GET['lang'], $available_langs)) {
    $_SESSION['lang'] = $_GET['lang']; // Set the new language in the session
    
    // Redirect to remove the ?lang=... from the URL for a cleaner look
    // This requires parsing the current URL and rebuilding it.
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $uri = strtok($_SERVER['REQUEST_URI'], '?');
    header("Location: $protocol://$host$uri");
    exit;
}

// Set the current language
$current_lang = isset($_SESSION['lang']) && in_array($_SESSION['lang'], $available_langs) ? $_SESSION['lang'] : $default_lang;

// Include the language file
include_once __DIR__ . '/lang/' . $current_lang . '.php';
?> 