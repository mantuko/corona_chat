<?php
include_once 'models.php';

session_start();

function getChatHistory() {
    $data = [
        'username' => '',
        'messages' => []
    ];

    $chat = new Chat();
    $data['messages'] = $chat->getHistory();
    $user = new User(session_id());
    $username = $user->getUsername();

    if ($username === FALSE) {
        $data['errors'] = ['Nutzer existiert nicht.'];
    } else {
        $data['username'] = $username;
    }

    return $data;
}

function loadMessages($id) {
    $lastId = filter_var($id, FILTER_SANITIZE_NUMBER_INT);

    $chat = new Chat();
    $result = $chat->loadMessages($lastId);

    if ($result !== FALSE) {
        $data['data'] = $result;
        return $data;
    } else {
        return array();
    }
}

function postMessage($data) {
    $data = filter_var_array( $data, [
        'username' => FILTER_SANITIZE_STRING,
        'posted'   => FILTER_SANITIZE_STRING,
        'message'  => FILTER_SANITIZE_STRING,
    ] );

    if ($data['username'] != '' && $data['posted'] != '' && $data['message'] != '') {
        $user = new User(session_id());
        if ($user->getUsername() == '') {
            return ['errors' => ["Error while saving message. Session expired. User deleted from db."]];
        }

        $msg = new Message($user, $data['message']);
        $encoded_msg = new Message($user, htmlentities($data['message']));
        $affectedRows = $msg->saveMessageToDb($data['posted']);

        if ($affectedRows != 1) {
            return ['errors' => ["Error while saving message to db (affectedRows: $affectedRows)"]];
        }
    } else if ($data['posted'] == '') {
        return ['errors' => ["Error while saving message. Post date not set."]];
    } else if ($data['message'] == '') {
        return ['errors' => ["Error while saving message. Message not set."]];
    }

    return array();
}

function respondToRequest($data) {
    $json = json_encode($data);
    if ($json === false) {
        // Avoid echo of empty string (which is invalid JSON), and
        // JSONify the error message instead:
        $json = json_encode(["jsonError" => json_last_error_msg()]);
        if ($json === false) {
            // This should not happen, but we go all the way now:
            $json = '{"jsonError":"unknown"}';
        }
        // Set HTTP response status code to: 500 - Internal Server Error
        http_response_code(500);
    }
    header('Content-type: application/json');
    echo $json;
}

$data = null;
if ($_SERVER["REQUEST_METHOD"] == 'GET') {
    $chatHistory = getChatHistory();
    if (isset($chatHistory['errors']) && sizeof($chatHistory['errors']) > 0) {
        $data['errors'] = $chatHistory['errors'];
    } else {
        $data['data'] = $chatHistory;
    }
    //$_SESSION['lastUpdate'] = date('d.m.Y, H:i:s');
} elseif ($_SERVER["REQUEST_METHOD"] == 'POST') {
    if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
        $content = trim(file_get_contents("php://input"));
        $data = json_decode($content, true);

        if (!is_array($data)) {
            $data = ['error' => 'Empty or invalid JSON data.'];
        } else {
            $action = filter_var($data['action'], FILTER_SANITIZE_STRING);

            switch($action) {
                case 'postMessage':
                    $data = postMessage($data['data']);
                    break;
                case 'getUsers':
                    $data = array();
                    $user = new User(session_id());
                    $affectedRows = $user->keepAlive();
                    if ($affectedRows != 1) {
                        $data['errors'] = ["keepAlive returned an error (affectedRows: $affectedRows)"];
                    }
                    $chat = new Chat();
                    $chat->invalidateUsers();
                    $data = array('data' => $chat->getUsers());
                    break;
                case 'loadMessages':
                    $data = loadMessages($data['lastId']);
                    break;
                default:
                    $data = [];
            }

        }
    } else {
        $data = ['error' => 'Empty or invalid JSON data.'];
    }
}

respondToRequest($data);
