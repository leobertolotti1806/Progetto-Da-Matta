<?php
if (!isset($_GET['id'])) {
    header("HTTP/1.1 400 Bad Request");
    exit;
}

$id = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['id']);

$ch = curl_init("https://drive.google.com/uc?export=download&id=$id");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$content = curl_exec($ch);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if (!$content) {
    header("HTTP/1.1 404 Not Found");
    exit;
}

header("Content-Type: $contentType");
echo $content;