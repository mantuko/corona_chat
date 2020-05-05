<?php

include_once 'db.php';

class chatBase
{
    public function getPdo() {
        $pdo = new Database();

        if ($pdo === FALSE) {
            return FALSE;  // TODO: Add JSON error message.
        } else {
            return $pdo->getPdo();
        }
    }
}

class Message extends chatBase
{
    private $user;
    private $message;

    public function __construct($user, string $message) {
        $this->user = $user;
        $this->message = $message;
    }

    public function getUser() : string {
        return $this->user;
    }

    public function getMessage() : string {
        return $this->message;
    }

    public function setUser(string $user) {
        $this->user = $user;
    }

    public function setMessage(string $message) {
        $this->message = $message;
    }

    public function getId() {
        $pdo = $this->getPdo();

        $stmt = $pdo->prepare(
            'SELECT id FROM messages WHERE user = :user AND message = :message'
        );
        $stmt->execute([
            ':user' => $this->user->getId(),
            ':message' => $this->message,
        ]);
        $id = $stmt->fetch()['id'];
        // TODO: Fehler behandlung if nothing is returned
        if (!id) {
            exit('No rows'); // Was gibt das überhaupt wo aus?
        }
        $stmt = null;

        return $id;
    }

    public function saveMessageToDb($posted) {
        $pdo = $this->getPdo();

        $stmt = $pdo->prepare(
            'INSERT INTO messages (user, posted, message) VALUES (:user, STR_TO_DATE(:posted, "%d.%m.%Y, %T"), :message)'
        );
        $stmt->execute([
            ':user' => $this->user->getId(),
            ':posted' => $posted,
            ':message' => $this->message,
        ]);
        $affectedRows = $stmt->rowCount();
        $stmt = null;

        return $affectedRows;
    }
}

class Chat extends chatBase
{
    public function getUsers() {
        /**
         * Returns users who are online. Online/offline is determined by the
         * updated timestamp. The timestamp is refreshed every poll cycle. If
         * it's older than POLL_INTERVALL * OFFLINE_CYCLES seconds the user is
         * no longer returned.
         */
        $pdo = $this->getPdo();

        try {
            $stmt = $pdo->prepare('SELECT username FROM users WHERE TIME_TO_SEC(TIMEDIFF(NOW(), updated)) < :timedelta');
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return FALSE;
        }
        $stmt->execute([
            ':timedelta' => POLL_INTERVAL * OFFLINE_CYCLES,
        ]);
        $result = $stmt->fetchAll(PDO::FETCH_NUM);

        if (!$result) {
            return FALSE;  // TODO: Add JSON error message, oder einfach ein assoc array mit error feld?
        } else {
            $stmt = null;
            return $result;  // TODO: Evtl. kümmert sich auch eine andere Funktion um das JSON encoding.
        }
    }

    public function loadMessages($lastId) {
        if ($lastId === FALSE) {
            return FALSE;
        }

        $pdo = $this->getPdo();

        // Get the last n rows
        try {
            $stmt = $pdo->prepare(
                'SELECT id, username, posted, message FROM (' .
                'SELECT messages.id, users.username, messages.posted, messages.message FROM users, messages ' .
                'WHERE users.id = messages.user AND messages.id > :lastId ORDER BY id DESC' .
                ') as chatMessages ORDER BY id ASC'
            );
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return FALSE;
        }
        $stmt->execute([
            ':lastId' => $lastId,
        ]);
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $row;
        }

        if (!$result) {
            return FALSE;  // TODO: Add JSON error message, oder einfach ein assoc array mit error feld?
        } else {
            $stmt = null;
            return $result = $this->formatPosted($result);  // TODO: Evtl. kümmert sich auch eine andere Funktion um das JSON encoding.
        }
    }

    public function getHistory() {
        $pdo = $this->getPdo();

        // Get the last n rows
        try {
            $stmt = $pdo->prepare(
                'SELECT id, username, posted, message FROM (' .
                'SELECT messages.id, users.username, messages.posted, messages.message FROM users, messages ' .
                'WHERE users.id = messages.user ORDER BY id DESC LIMIT :historyCount' .
                ') as chatMessages ORDER BY id ASC'
            );
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return FALSE;
        }
        $stmt->execute([
            ':historyCount' => HISTORY_COUNT,
        ]);
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $row;
        }

        if (!$result) {
            return FALSE;  // TODO: Add JSON error message, oder einfach ein assoc array mit error feld?
        } else {
            $stmt = null;
            return $this->formatPosted($result);  // TODO: Evtl. kümmert sich auch eine andere Funktion um das JSON encoding.
        }
    }

    private function formatPosted($chatHistory) {
        foreach($chatHistory as &$value) {
            $value['posted'] = strftime('%d.%m.%Y, %H:%M:%S', strtotime($value['posted']));
        }

        return $chatHistory;
    }
}

class User extends chatBase
{
    private $username;
    private $sessionId;

    public function __construct(string $sessionId, string $username = '') {
        // TODO: Wenn $sessionId und $username gesetzt sind checken ob das passt
        $this->sessionId = $sessionId;
        if ($username == '') {
            $this->username = $this->getUsernameFromSessionId();
        } else {
            $this->username = $username;
        }
    }

    public function getUsername() : string {
        return $this->username;
    }

    public function getSessionId() : string {
        return $this->sessionId;
    }

    public function setUsername(string $username) {
        $this->username = $username;
    }

    public function setSessionId(string $sessionId) {
        $this->sessionId = $sessionId;
    }

    public function usernameExists() {
        $pdo = $this->getPdo();

        $stmt = $pdo->prepare(
            'SELECT id FROM users WHERE username = :username'
        );
        $stmt->execute([
            ':username' => $this->username,
        ]);
        $count = $stmt->fetch(PDO::FETCH_COLUMN);
        if (!$count) {
            return FALSE;
        }
        $stmt = null;

        return TRUE;
    }

    public function getUsernameFromSessionId() {
        $pdo = $this->getPdo();

        $stmt = $pdo->prepare(
            'SELECT username FROM users WHERE sessionId = :sessionId'
        );
        $stmt->execute([
            ':sessionId' => $this->sessionId,
        ]);
        $username = $stmt->fetch()['username'];
        if (!$username) {
            return FALSE;
        }
        $stmt = null;

        return $username;
    }

    public function getId() {
        $pdo = $this->getPdo();

        $stmt = $pdo->prepare(
            'SELECT id FROM users WHERE sessionId = :sessionId'
        );
        $stmt->execute([
            ':sessionId' => $this->sessionId,
        ]);
        $id = $stmt->fetch()['id'];
        if (!$id) {
            return FALSE;
        }
        $stmt = null;

        return $id;
    }

    public function saveUserToDb() {
        $pdo = $this->getPdo();

        $stmt = $pdo->prepare(
            'INSERT INTO users (username, created, updated, sessionId) VALUES (:username, NOW(), NOW(), :sessionId)'
        );
        $stmt->execute([
            ':username' => $this->username,
            ':sessionId' => $this->sessionId,
        ]);
        $affectedRows = $stmt->rowCount();
        $stmt = null;

        return $affectedRows;
    }

    public function keepAlive() {
        $pdo = $this->getPdo();

        $stmt = $pdo->prepare(
            'UPDATE users SET updated = NOW() WHERE sessionId = :sessionId'
        );
        $stmt->execute([
            ':sessionId' => $this->sessionId,
        ]);
        $affectedRows = $stmt->rowCount();
        $stmt = null;

        return $affectedRows;
    }
}
