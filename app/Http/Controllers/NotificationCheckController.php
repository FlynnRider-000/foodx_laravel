<?php

namespace App\Http\Controllers;


use Auth;
use App\Models\User;

class NotificationCheckController extends Controller
{
    
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $user = User::find(Auth::id());
        return $user->notification_exist;
    }

    public function read()
    {
        $user = User::find(Auth::id());
        $user->notification_exist = 0;
        $user->save();
        return 1;
    }
}
