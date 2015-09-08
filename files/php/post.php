<?php

header('Content-Type: application/json');

$server = 'localhost';
$usr = 'stigma';
$db = &$usr;
$table = 'eimf_comments';
$salt = 'q@w%12AsK*)';
$pwd = base64_decode('cG5vZHQyMjI=');

$result = array();
$_POST = @file_get_contents('php://input');

if (!$_POST || !isset($_POST['name']) || !isset($_POST['comment']) || !isset($_POST['id'])) {
    $result['error'] = 'Invalid data';
    echo(json_encode($result));
    exit();
}

if (!isset($_SERVER['REMOTE_ADDR'])) {
    $result['error'] = 'IP hide detected, this is required by flood prevention.';
    echo(json_encode($result));
    exit();
}

$name = trim(strip_tags($_POST['name']));
$comment = trim(strip_tags($_POST['comment']));
$id = strip_tags(trim($_POST['id']));
$ip = hash('sha512', $salt.(strip_tags(trim($_SERVER['REMOTE_ADDR']))));

if ((strlen($name) < 3) || (strlen($comment) < 5) || (strlen($id) < 2) ||
    (strlen($name) > 128) || (strlen($comment) > 512) || (strlen($id) > 64) || (strlen($ip) > 128)
   ) {
    $result['error'] = 'Invalid data length';
    echo(json_encode($result));
    exit();
}

$mysqli = new mysqli($server, $usr, $pwd, $db);

if ($mysqli->connect_errno) {
    $result['error'] = 'Connection failed: '.$mysqli->connect_error;
    echo(json_encode($result));
    exit();
}

$name = $mysqli->real_escape_string($name);
$comment = $mysqli->real_escape_string($comment);
$id = $mysqli->real_escape_string($id);
$ip = $mysqli->real_escape_string($ip);

if (!file_exists('../articles/'.$id.'.html')) {
    $result['error'] = 'Invalid article';
    $mysqli->close();
    echo(json_encode($result));
    exit();
}

// Get time since last post, min 5 min.

if (!($stmt = $mysqli->prepare('INSERT INTO '.$table.'(nickname, post, comment, poster) VALUES (?, ?, ?, ?)'))) {
    $result['error'] = 'Database failure: '.$mysqli->error;
    $mysqli->close();
    echo(json_encode($result));
    exit();
}

if (!$stmt->bind_param('ssss', $name, $id, $comment, $ip) || !$stmt->execute()) {
    $result['error'] = 'Database failure: '.$stmt->error;
    $stmt->close();
    $mysqli->close();
    echo(json_encode($result));
    exit();
}

$stmt->close();
$mysqli->close();

$result['success']  = 'y';
$result['feedback'] = 'Comment is submitted for review';
echo(json_encode($result));
?>