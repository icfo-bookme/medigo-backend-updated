<?php

namespace App\Http\Controllers;

use App\Events\PusherBroadcast;
use Illuminate\Http\Request;

class PusherController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function broadcast(Request $request)
    {
        $message = $request->message ?? 'Hello How are you ?';
        broadcast(new PusherBroadcast($message))->toOthers();
    }

    public function receive(Request $request)
    {
        return view('receive', ['message' => $request->get('message')]);
    }

    public function pusher()
    {
        return view('pusher');
    }
}
