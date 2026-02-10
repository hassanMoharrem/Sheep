<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class FirebaseService
{
    protected $database;
    protected $auth;
    protected $messaging;

    public function __construct()
    {
        $credentialsPath = base_path(env('FIREBASE_CREDENTIALS_PATH'));
        $databaseUrl = env('FIREBASE_DATABASE_URL');
        $factory = (new Factory)
            ->withServiceAccount($credentialsPath)
            ->withDatabaseUri($databaseUrl);
        $this->database = $factory->createDatabase();
        $this->auth = $factory->createAuth();
        $this->messaging = $factory->createMessaging();
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }

    public function getAuth(): Auth
    {
        return $this->auth;
    }

    public function getMessaging(): Messaging
    {
        return $this->messaging;
    }

    // مثال: إضافة إشعار إلى الفيربيز
    public function pushNotification($data)
    {
        return $this->database->getReference('notifications')->push($data);
    }

    // مثال: جلب جميع الإشعارات
    public function getNotifications()
    {
        return $this->database->getReference('notifications')->getValue();
    }

    // مثال: حذف إشعار
    public function deleteNotification($id)
    {
        return $this->database->getReference('notifications/' . $id)->remove();
    }

    // حذف جميع الإشعارات
    public function deleteAllNotifications()
    {
        return $this->database->getReference('notifications')->remove();
    }

    public function sendToTokens(array $tokens, array $payload = [])
    {
        $tokens = array_values(array_filter($tokens));
        if (count($tokens) === 0) {
            return null;
        }

        $title = $payload['title'] ?? '';
        $body = $payload['body'] ?? '';
        $data = $payload['data'] ?? [];

        $message = CloudMessage::new()
            ->withNotification(FirebaseNotification::create($title, $body))
            ->withData($data);

        return $this->messaging->sendMulticast($message, $tokens);
    }
}
