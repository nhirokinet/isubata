<?php

$userId = $_COOKIE['user_id'];

if (!$userId) {
        http_response_code(403);
}

usleep(100000);

$dbh = new PDO(
        "mysql:host=db;port=3306;dbname=isubata;charset=utf8mb4", 'isucon', 'isucon',
                [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]
              );
$dbh->query("SET SESSION sql_mode='TRADITIONAL,NO_AUTO_VALUE_ON_ZERO,ONLY_FULL_GROUP_BY'");

$stmt = $dbh->query('SELECT id FROM channel');
$rows = $stmt->fetchall();
$channelIds = [];
foreach ($rows as $row) {
        $channelIds[] = (int)$row['id'];
}

$res = [];
foreach ($channelIds as $channelId) {
        $stmt = $dbh->prepare(
                        "SELECT message_id ".
                        "FROM haveread ".
                        "WHERE user_id = ? AND channel_id = ?"
                        );
        $stmt->execute([$userId, $channelId]);
        $row = $stmt->fetch();
        if ($row) {
                $lastMessageId = $row['message_id'];
                $stmt = $dbh->prepare(
                                "SELECT COUNT(id) as cnt ".
                                "FROM message ".
                                "WHERE channel_id = ? AND ? < id"
                                );
                $stmt->execute([$channelId, $lastMessageId]);
        } else {
                $stmt = $dbh->prepare(
                                "SELECT COUNT(id) as cnt ".
                                "FROM message ".
                                "WHERE channel_id = ?"
                                );
                $stmt->execute([$channelId]);
        }
        $r = [];
        $r['channel_id'] = $channelId;
        $r['unread'] = (int)$stmt->fetch()['cnt'];
        $res[] = $r;
}

echo json_encode($res);

