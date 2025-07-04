<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die('アクセスが不正です');
}

$user_id = $_SESSION['user_id'];
$title = $_POST['title'];
$message = trim($_POST['message']);

if ($message === '') {
    die('メッセージが空です');
}

$host = 'localhost';
$dbname = 'mydb';
$user = 'testuser';
$pass = 'pass';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("INSERT INTO messages (sender, title, message, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$user_id, $title, $message]);

    header("Location: chat.php?title=" . urlencode($title));
    exit;

} catch (PDOException $e) {
    die("DBエラー: " . htmlspecialchars($e->getMessage()));
}
