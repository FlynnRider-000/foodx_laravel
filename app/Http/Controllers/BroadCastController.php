<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class BroadCastController extends Controller
{
    /** @var  UserRepository */
    private $userRepository;

    /**
     * @var Client
     */
    private $client;

    public function __construct(UserRepository $userRepo, Client $client)
    {
        parent::__construct();
        $this->userRepository = $userRepo;
        $this->client = $client;
    }

    public function index()
    {
        return view('broadcast.index');
    }

    public function send(Request $request)
    {
        $message = $request->input('message');
        if ($message != '') {
            $groupName = "DeliapDeviceGroup";
            $tokens = explode(',', setting('firebase_device_tokens'));
            $tokenCnt = ceil(count($tokens) / 100);
            $notifKeys = explode(',', setting('firebase_notification_group_key'));
            if($tokenCnt != 1 || $tokens[0] != '') {
                for($i = 0; $i < $tokenCnt; $i++) {
                    $tempTokens = array_slice($tokens, $i * 100, 100);
                    $tmpTokens = array_values(array_filter($tempTokens, function($el) {
                        return $el != '';
                    }));

                    $tempGroupName = $groupName . $i;
                    $response = $this->client->post('https://fcm.googleapis.com/fcm/notification', [
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Authorization' => 'key=' . config('fcm.server.key'),
                            'project_id' => config('fcm.sender.id')
                        ],
                        'body' => json_encode([
                            'operation' => 'remove',
                            'notification_key_name' => $tempGroupName,
                            'notification_key' => $notifKeys[$i],
                            'registration_ids' => $tmpTokens
                        ])
                    ]);
                }
            }

            $newTokens = $this->userRepository->all()->pluck('device_token')->toArray();
            $newToken_str = implode(',', $newTokens);
            $newTokenCnt = ceil(count($newTokens) / 100);
            $newNotifKeys = [];
            for($i = 0 ; $i < $newTokenCnt; $i++) {
                $tempTokens = array_slice($newTokens, $i * 100, 100);
                $tmpTokens = array_values(array_filter($tempTokens, function($el) {
                    return $el != '';
                }));
                $tempGroupName = $groupName . $i;
                $response = $this->client->post('https://fcm.googleapis.com/fcm/notification', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'key=' . config('fcm.server.key'),
                        'project_id' => config('fcm.sender.id')
                    ],
                    'body' => json_encode([
                        'operation' => 'create',
                        'notification_key_name' => $tempGroupName,
                        'registration_ids' => $tmpTokens
                    ])
                ]);

                $resObj = json_decode($response->getBody());
                $newNotifKeys[$i] = $resObj->notification_key;

                $ttt = $resObj->notification_key;
                print($ttt);
                $response = $this->client->post('https://fcm.googleapis.com/fcm/send', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'key=' . config('fcm.server.key')
                    ],
                    'body' => json_encode([
                        "to" => $ttt,
                        "notification" => [
                            "title" => $message,
                            "text" => "",
                            "sound" => "default"
                        ],
                        "priority" => "high"
                    ])
                ]);
            }
            $notifKeys_str = implode(',', $newNotifKeys);
            $input['firebase_device_tokens'] = $newToken_str;
            $input['firebase_notification_group_key'] = $notifKeys_str;
            setting($input)->save();
        }
        return redirect()->route('broadcastMessage.index');
    }
}
