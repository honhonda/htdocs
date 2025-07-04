<?php
session_start();

// ★ テスト用ログイン（初回アクセス時のみ）
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // 自分のuser_id（usersテーブルに存在するIDにすること）
    $_SESSION['username'] = 'h.masahiro'; // 自分のユーザー名
}

$self_id = $_SESSION['user_id'];
$self_name = $_SESSION['username'];

$partner_name = $_GET['partner'] ?? '';
if ($partner_name === '') {
    die('相手ユーザーが指定されていません');
}

// DB接続情報
$host = 'localhost';
$dbname = 'mydb';
$db_user = 'testuser';
$db_pass = 'pass';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 相手のユーザー情報取得
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$partner_name]);
    $partner = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$partner) {
        die('相手ユーザーが見つかりません');
    }

    $partner_id = $partner['id'];

    // メッセージ取得（両方向）
    $stmt = $pdo->prepare("
        SELECT sender_id, message, created_at
        FROM messages
        WHERE (sender_id = :me AND receiver_id = :you)
           OR (sender_id = :you AND receiver_id = :me)
        ORDER BY created_at ASC
    ");
    $stmt->execute([
        ':me' => $self_id,
        ':you' => $partner_id
    ]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("DBエラー: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($partner_name) ?> さんとのチャット</title>
  <style>
    body {
      font-family: sans-serif;
      background: #e5ddd5;
      margin: 0;
      padding: 0;
    }
    .chat-container {
      max-width: 600px;
      margin: 50px auto;
      background: #fff;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 0 5px rgba(0,0,0,0.1);
      min-height: 500px;
    }
    .message {
      display: flex;
      margin: 10px 0;
    }
    .message.self {
      justify-content: flex-end;
    }
    .message.other {
      justify-content: flex-start;
    }
    .bubble {
      padding: 10px 15px;
      border-radius: 18px;
      max-width: 70%;
      word-wrap: break-word;
    }
    .self .bubble {
      background: #dcf8c6;
      border-bottom-right-radius: 2px;
    }
    .other .bubble {
      background: #eee;
      border-bottom-left-radius: 2px;
    }
    .input-area {
      display: flex;
      justify-content: space-between;
      margin-top: 20px;
    }
    textarea {
      flex: 1;
      resize: none;
      padding: 10px;
      font-size: 1rem;
      border-radius: 6px;
      border: 1px solid #ccc;
    }
    button {
      margin-left: 10px;
      padding: 10px 20px;
      font-size: 1rem;
      background: #1e90ff;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <div class="chat-container">
    <h2><?= htmlspecialchars($partner_name) ?> さんとのチャット</h2>

    <?php foreach ($messages as $msg): ?>
      <div class="message <?= $msg['sender_id'] == $self_id ? 'self' : 'other' ?>">
        <div class="bubble"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
      </div>
    <?php endforeach; ?>

    <form class="input-area" action="send_message.php" method="POST">
      <textarea name="message" rows="2" required></textarea>
      <input type="hidden" name="receiver" value="<?= htmlspecialchars($partner_name) ?>">
      <button type="submit">送信</button>
    </form>
  </div>
</body>
</html>
