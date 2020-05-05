<?php
    /**
     * The logic should be in a separate file the whole "log in" should be tied
     * to vue.js anyway. So for now this has to do.
     */
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
                header('Location: http://chat.local/chat.html', TRUE);
                die();
            }
        } else {
            // Return errors
            if (session_status() != PHP_SESSION_ACTIVE) {
                $form_error[] = 'Deine Session konnte nicht aktiviert werden.';
            }
        }
    }
?>
<!doctype html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <title>St. Franziskus Bochum - Chat zum Livestream</title>
        <link rel="apple-touch-icon" sizes="57x57" href="assets/grafix/favicon/apple-icon-57x57.png">
        <link rel="apple-touch-icon" sizes="60x60" href="assets/grafix/favicon/apple-icon-60x60.png">
        <link rel="apple-touch-icon" sizes="72x72" href="assets/grafix/favicon/apple-icon-72x72.png">
        <link rel="apple-touch-icon" sizes="76x76" href="assets/grafix/favicon/apple-icon-76x76.png">
        <link rel="apple-touch-icon" sizes="114x114" href="assets/grafix/favicon/apple-icon-114x114.png">
        <link rel="apple-touch-icon" sizes="120x120" href="assets/grafix/favicon/apple-icon-120x120.png">
        <link rel="apple-touch-icon" sizes="144x144" href="assets/grafix/favicon/apple-icon-144x144.png">
        <link rel="apple-touch-icon" sizes="152x152" href="assets/grafix/favicon/apple-icon-152x152.png">
        <link rel="apple-touch-icon" sizes="180x180" href="assets/grafix/favicon/apple-icon-180x180.png">
        <link rel="icon" type="image/png" sizes="192x192"  href="assets/grafix/favicon/android-icon-192x192.png">
        <link rel="icon" type="image/png" sizes="32x32" href="assets/grafix/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="96x96" href="assets/grafix/favicon/favicon-96x96.png">
        <link rel="icon" type="image/png" sizes="16x16" href="assets/grafix/favicon/favicon-16x16.png">
        <link rel="manifest" href="assets/grafix/favicon/manifest.json">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
        <meta name="theme-color" content="#ffffff">
        <link rel="stylesheet" href="assets/css/style.css" type="text/css">
        <script src="node_modules/vue/dist/vue.js"></script>
    </head>
    <body>
        <div class="login_wrapper__body">
            <div class="login_form__login_wrapper">
                <h1>Anmeldung</h1>
                <p>
                    Der Name dient dazu Dich im Chat zu identifizieren. Es kann sowohl
                    der Klarname, als auch ein Pseudonym gewählt werden.
                </p>
                <form method="POST" action="index.php">
                    <label for="username">Name:</label><input type="text" name="username" size="50">
                    <?php if (sizeof($form_error) > 0): ?>
                        <div class="error_login_wrapper">
                            <?php foreach ($form_error as $error): ?>
                                <p class="error"><?php print($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <input type="submit" value="absenden">
                </form>
            </div>
        </div>
    </body>
</html>
