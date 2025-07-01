<?php
// セッション開始（必要であれば）
session_start();

$username = $_GET['user'] ?? '';
$reviews = [];

if (empty($username)) {
    die('ユーザーが指定されていません。');
}

// DB接続情報
$host = 'localhost';
$dbname = 'mydb';
$user = 'testuser';
$pass = 'pass';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ユーザー名からユーザーID取得
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    

    $stmt->execute([$username]);
    $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userRow) {
        die('ユーザーが存在しません。');
    }

    $user_id = $userRow['id'];

    // そのユーザーのレビュー一覧を取得
    $stmt = $pdo->prepare("SELECT title, content, rating FROM reviews WHERE user_id = ? ORDER BY id DESC");

    $stmt->execute([$user_id]);

    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("DBエラー: " . htmlspecialchars($e->getMessage()));
}

// 星表示関数
function printStars($count) {
    $stars = '';
    for ($i = 0; $i < 5; $i++) {
        $stars .= $i < $count ? '★' : '☆';
    }
    return $stars;
}
?>
