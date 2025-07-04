<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['partner'])) {
    die('アクセスが不正です');
}
$_SESSION['user_id'] = 2;  // h.masahiro の ID
$_SESSION['username'] = 'tanaka';

$self_id = $_SESSION['user_id'];
$self_name = $_SESSION['username'];
$partner_name = $_GET['partner'];

$host = 'localhost';
$dbname = 'mydb';
$db_user = 'testuser';
$db_pass = 'pass';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 相手ユーザーIDを取得
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$partner_name]);
    $partner = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$partner) {
        die('相手ユーザーが見つかりません');
    }
    $partner_id = $partner['id'];

    // チャット履歴を取得（自分⇔相手のメッセージ全部）
    $stmt = $pdo->prepare("SELECT sender_id, message, created_at FROM messages
                           WHERE (sender_id = ? AND receiver_id = ?)
                              OR (sender_id = ? AND receiver_id = ?)
                           ORDER BY created_at ASC");
    $stmt->execute([$self_id, $partner_id, $partner_id, $self_id]);
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
   font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
   background-color: #e5ddd5;
   margin: 0;
   padding: 0;
   height: 100vh;
   display: flex;
   justify-content: center;
   align-items: center;
   }

   .chat-container {
   background-color: #fff;
   width: 400px;
   max-width: 95vw;
   height: 600px;
   box-shadow: 0 4px 10px rgba(0,0,0,0.1);
   border-radius: 10px;
   display: flex;
   flex-direction: column;
   overflow: hidden;
   }

   .chat-header {
   background-color: #075e54;
   color: white;
   padding: 15px 20px;
   font-weight: bold;
   font-size: 1.2rem;
   }

   .chat-messages {
   flex: 1;
   padding: 15px 20px;
   overflow-y: auto;
   background-image: url("https://www.transparenttextures.com/patterns/diamond-upholstery.png");
   background-repeat: repeat;
   background-size: 50px 50px;
   }

   .message {
   display: flex;
   margin-bottom: 12px;
   }

   .message.self {
   justify-content: flex-end;
   }

   .message.other {
   justify-content: flex-start;
   }

   .bubble {
    max-width: 70%;
    padding: 10px 15px;
    border-radius: 20px;
    font-size: 1rem;
    word-wrap: break-word;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1); 
   }

   .message.self .bubble {
    background-color: #dcf8c6;
    border-bottom-right-radius: 5px;
   }

   .message.other .bubble {
    background-color: #fff;
    border: 1px solid #ccc;
    border-bottom-left-radius: 5px;
   }

   .input-area {
    display: flex;
    padding: 10px 15px;
    border-top: 1px solid #ddd;
    background-color: #f7f7f7;
   }

   textarea {
    flex: 1;
    resize: none;
    padding: 10px;
    border-radius: 18px;
    border: 1px solid #ccc;
    font-size: 1rem;
    height: 50px;
    outline: none;
    transition: border-color 0.2s ease;
   }

   textarea:focus {
    border-color: #075e54;
   }

   button {
    background-color: #075e54;
    color: white;
    border: none;
    border-radius: 18px;
    padding: 0 20px;
    margin-left: 10px;
    cursor: pointer;
    font-weight: bold;
    font-size: 1rem;
    transition: background-color 0.2s ease;
   }

   button:hover {
    background-color: #0a7f68;
   }

/* スクロールバーのスタイル (Webkit系ブラウザ) */
   .chat-messages::-webkit-scrollbar {
    width: 8px;
   }

   .chat-messages::-webkit-scrollbar-thumb {
    background-color: rgba(0,0,0,0.2);
    border-radius: 4px;
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
