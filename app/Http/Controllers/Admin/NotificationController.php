<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Services\FirebaseService;


class NotificationController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    // عرض جميع الإشعارات
    public function index()
    {
        $notifications = $this->firebase->getNotifications();
        return response()->json($notifications);
    }
    

    // حذف إشعار واحد
    public function destroy($id)
    {
        $this->firebase->deleteNotification($id);
        return response()->json(['message' => 'تم حذف الإشعار من Firebase']);
    }

    // حذف جميع الإشعارات
    public function destroyAll()
    {
        $this->firebase->deleteAllNotifications();
        return response()->json(['message' => 'تم حذف جميع الإشعارات من Firebase']);
    }
}
