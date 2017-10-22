<?php


if (isset($_POST['message'])) {
	$userId = $_COOKIE['user_id'];

	$dbh = new PDO(
			"mysql:host=db;port=3306;dbname=isubata;charset=utf8mb4", 'isucon', 'isucon',
			[
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
			]
		      );
	$dbh->query("SET SESSION sql_mode='TRADITIONAL,NO_AUTO_VALUE_ON_ZERO,ONLY_FULL_GROUP_BY'");

	/*
	$stmt = $dbh->prepare("SELECT * FROM user WHERE id = ?");
	$stmt->execute([$userId]);
	$user =  $stmt->fetch();

	*/
	$user = $userId;

	$message = $_POST['message'];
	$channelId = (int)$_POST['channel_id'];
	if (!$user || !$channelId || !$message) {
		http_response_code(403);
		exit;
	}

	$stmt = $dbh->prepare("INSERT INTO message (channel_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
	$stmt->execute([$channelId, $userId, $message]);

	http_response_code(204);
	exit;
}

$userId = $_COOKIE['user_id'];

if (!$userId) {
	http_response_code(403);
	exit;
}

$channelId = $_GET['channel_id'];
$lastMessageId = $_GET['last_message_id'];

$dbh = new PDO(
		"mysql:host=db;port=3306;dbname=isubata;charset=utf8mb4", 'isucon', 'isucon',
		[
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		]
	      );
$dbh->query("SET SESSION sql_mode='TRADITIONAL,NO_AUTO_VALUE_ON_ZERO,ONLY_FULL_GROUP_BY'");
$stmt = $dbh->prepare(
		"SELECT message.id id, message.user_id user_id, message.content content, message.created_at created_at, user.name name, user.display_name display_name, user.avatar_icon avatar_icon ".
		"FROM message, user ".
		"WHERE message.user_id = user.id AND message.id > ? AND channel_id = ? ORDER BY message.id DESC LIMIT 100"
		);
$stmt->execute([$lastMessageId, $channelId]);
$rows = $stmt->fetchall();

$res = [];
$maxMessageId = 0;
foreach ($rows as $row) {
	$r = [];
	$r['id'] = (int)$row['id'];
    $r['user'] = [
        'name' => $row['name'],
        'display_name' => $row['display_name'],
        'avatar_icon' => $row['avatar_icon'],
    ];
	$r['date'] = str_replace('-', '/', $row['created_at']);
	$r['content'] = $row['content'];
	$res[] = $r;

    $maxMessageId = max($maxMessageId, $row['id']);
}
$res = array_reverse($res);

$stmt = $dbh->prepare(
		"INSERT INTO haveread (user_id, channel_id, message_id, updated_at, created_at) ".
		"VALUES (?, ?, ?, NOW(), NOW()) ".
		"ON DUPLICATE KEY UPDATE message_id = ?, updated_at = NOW()"
		);
$stmt->execute([$userId, $channelId, $maxMessageId, $maxMessageId]);
echo json_encode($res);
