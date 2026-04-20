<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Papp\Points\Webhooks\WebhookHandler;

$secret = getenv('POINTS_WEBHOOK_SECRET') ?: 'replace-with-webhook-secret';
$handler = new WebhookHandler($secret);

$payload = file_get_contents('php://input') ?: '{}';
$secretHeader = $_SERVER['HTTP_X_WEBHOOK_SECRET'] ?? '';
$eventHeader = $_SERVER['HTTP_X_WEBHOOK_EVENT'] ?? null;

$event = $handler->parse($payload, $secretHeader, $eventHeader);

header('Content-Type: application/json');
echo json_encode([
    'event' => $event->event()->value,
    'accepted' => true,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
