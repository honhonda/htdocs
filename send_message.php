<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 2; // usersテーブルに存在するIDに書き換えてください
    $_SESSION['username'] = 'tanaka'; // 対応するユーザー名
}


$self_id = $_SESSION['user_id'];
$message = trim($_POST['message']);
$partner_name = $_POST['receiver'];

if ($message === '') {
    die('メッセージが空です');
}

$host = 'localhost';
$dbname = 'mydb';
$db_user = 'testuser';
$db_pass = 'pass';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 相手ユーザーID取得
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$partner_name]);
    $partner = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$partner) {
        die('相手ユーザーが見つかりません');
    }
    $partner_id = $partner['id'];

    // メッセージをDBに挿入
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$self_id, $partner_id, $message]);

    // チャット画面にリダイレクト
    header('Location: chat.php?partner=' . urlencode($partner_name));
    exit;

} catch (PDOException $e) {
    die('DBエラー: ' . htmlspecialchars($e->getMessage()));
}
?>