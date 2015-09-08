<?php

$server = 'localhost';
$usr = 'stigma';
$db = &$usr;
$pwd = base64_decode('hemmelig passord her');

$query =
'CREATE TABLE IF NOT EXISTS eimf_comments ( 
    id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nickname VARCHAR(128) NOT NULL,
    post     VARCHAR(64) NOT NULL,
    comment  VARCHAR(512) NOT NULL,
    poster   VARCHAR(128) NOT NULL,
    reg_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)';

$mysqli = new mysqli($server, $usr, $pwd, $db);

if ($mysqli->connect_errno) {
    echo('Connection failed: '.$mysqli->connect_error."\n");
    exit();
}

if ($mysqli->query($query) === TRUE) {
   echo('Great success!'."\n");
} else {
   echo('Failed to execute query '.$mysqli->error."\n");
}

$mysqli->close();

?>