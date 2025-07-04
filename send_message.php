<?php
session_start();

$sender_id = $_SESSION['user_id'] ?? null;
$receiver_name = $_POST['receiver'] ?? '';
$message = $_POST['message'] ?? '';

if (!$sender_id || !$receiver_name || trim($message) === '') {
    die('必要な情報が不足しています');
}




// DB接続
$host = 'localhost';
$dbname = 'mydb';
$user = 'testuser';
$pass = 'pass';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // receiver の id を取得
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$receiver_name]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        die('相手ユーザーが存在しません');
    }

    $receiver_id = $row['id'];

    // メッセージ挿入
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message, created_at)
                           VALUES (?, ?, ?, NOW())");
    $stmt->execute([$sender_id, $receiver_id, $message]);

    header("Location: chat.php?partner=" . urlencode($receiver_name));
    exit;

} catch (PDOException $e) {
    die("DBエラー: " . htmlspecialchars($e->getMessage()));
}
