<?php
include_once 'models.php';

session_start();
$form_error = array();
// Get and sanitize username
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['username'])) {
        $form_error[] = 'Du hast keinen Namen eingegeben.';
    } else {
        $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
        $username = trim($username);
        $username = stripslashes($username);
        $username = htmlspecialchars($username);
    }
} else {
    $form_error[] = "Bitte melde Dich bei uns, wenn du diese Fehlermeldung erhältst.";
}

// Add user to db
if (sizeof($form_error) == 0 && session_status() == PHP_SESSION_ACTIVE) {
    $user = new User(session_id(), $username);  // Create user object
    $userId = $user->getId();  // Look if user exists in database
    if (!$userId) {  // If user is not in database
        if ($user->usernameExists()) {  // Check if username is already taken
            $form_error[] = 'Dieser Name existiert bereits.';
        } else {  // I username is not taken write user to db
            $user->saveUserToDb();
        }
    }
    if (sizeof($form_error) == 0) {  // If no error redirect
        $_SESSION['username'] = $user->getUsername();
        header('Location: http://chat.local/chat.html');
        die();
    }
} else {
    // Return errors
    if (session_status() != PHP_SESSION_ACTIVE) {
        $form_error[] = 'Deine Session konnte nicht aktiviert werden.';
    }
    $serialized_error = '';
    foreach ($form_error as $value) {
        $serialized_error .= $value . ', ';
    }
    $serialized_error = rtrim($serialized_error, ', ');
    header("Location: http://chat.local/index.php?e=$serialized_error");
die();
}
