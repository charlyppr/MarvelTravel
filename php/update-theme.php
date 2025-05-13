<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user']) || !isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get the theme from POST or GET
$theme = isset($_POST['theme']) ? $_POST['theme'] : (isset($_GET['theme']) ? $_GET['theme'] : null);

if ($theme === null) {
    echo json_encode(['success' => false, 'message' => 'No theme specified']);
    exit;
}

// Validate theme value
if (!in_array($theme, ['light', 'dark', 'auto'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid theme value']);
    exit;
}

// Path to users.json file
$json_file = '../json/users.json';

// Check if file exists
if (!file_exists($json_file)) {
    echo json_encode(['success' => false, 'message' => 'Users data file not found']);
    exit;
}

// Read and parse the JSON file
$users_json = file_get_contents($json_file);
if ($users_json === false) {
    echo json_encode(['success' => false, 'message' => 'Failed to read users data']);
    exit;
}

$users = json_decode($users_json, true);
if ($users === null) {
    echo json_encode(['success' => false, 'message' => 'Failed to parse users data']);
    exit;
}

// Find the user by email and update theme
$user_found = false;
foreach ($users as &$user) {
    if ($user['email'] === $_SESSION['email']) {
        $user['theme'] = $theme;
        $user_found = true;
        break;
    }
}

if (!$user_found) {
    echo json_encode(['success' => false, 'message' => 'User not found in database']);
    exit;
}

// Write the updated JSON back to the file
if (file_put_contents($json_file, json_encode($users, JSON_PRETTY_PRINT)) === false) {
    echo json_encode(['success' => false, 'message' => 'Failed to update users data']);
    exit;
}