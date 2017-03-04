<?php
$db = require(__DIR__ . '/db.php');
// test database! Important not to run tests on production or development databases
$db['dsn'] = 'mysql:host=localhost;dbname=chat_test';

try {
    $dbh = new PDO($db['dsn'], $db['user'], $db['password']);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}


return $db;