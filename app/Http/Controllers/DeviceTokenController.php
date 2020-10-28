<?php

namespace App\Http\Controllers;

use App\DeviceToken;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;


class DeviceTokenController extends Controller
{

    public function show()
    {

        $data = DeviceToken::get();

        $out = [
            'message' => 'success',
            'result' => $data,
            'code' => 200,
        ];

        return response()->json($out, $out['code'], [], JSON_NUMERIC_CHECK);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'deviceId' => 'required',
            'packageName' => 'required',
            'token' => 'required',
            //'user' => 'required'
        ]);

        $deviceId = $request->input('deviceId');
        $packageName = $request->input('packageName');
        $token = $request->input('token');
        $user = $request->input('user');
        $model = $request->input('device_model');

        $devToken = DeviceToken::whereRaw('device_id = ? and package_name = ?', [$deviceId, $packageName])->first();

        try {

            if($devToken){

                if($devToken->token != $token){
                    //Update token
                    $data = [
                        'token' => $token,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    $appRule = DeviceToken::where('device_id', $deviceId)
                            ->where('package_name', $packageName)
                            ->update($data);

                    $out = [
                        'message' => 'Device berhasil diupdate',
                        'code' => 200
                    ];
                } else {
                    $out = [
                        'message' => 'Device dan token sudah ada',
                        'code' => 200
                    ];
                }
    
            } else {
                //insert
                $data = [
                    'device_id' => $deviceId,
                    'package_name' => $packageName,
                    'user' => $user,
                    'device_model' => $model,
                    'token' => $token,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $insert = DeviceToken::insert($data);

                $out = [
                    'message' => 'Device berhasil ditambahkan',
                    'code' => 201,
                ];
            }

        } catch ( QueryException $e ) {
            $out = [
                'message' =>  'Error: [' . $e->errorInfo[1] . '] ' . $e->errorInfo[2],
                'code' => 500,
            ];
        }

        return response()->json($out, $out['code'], [], JSON_NUMERIC_CHECK);
    
    }

    public function sendMessage(Request $request)
    {
        $user = $request->input('user'); // User
        $title = $request->input('title'); // Judul
        $body = $request->input('body'); // Isi
        $image = $request->input('image'); // Opsional
        $content = $request->input('content'); // Pesan 
        //$url = $request->input('url'); // Generate dari web/desktop
        $scope = $request->input('scope');
        $modul = $request->input('modul');
        $notrans = $request->input('notrans');
        $nik = $request->input('nik');
        $encodetrans = $this->base64encode($notrans);
        $encodenik = $this->base64encode($nik);
        $url = 'http://ppbj.paneragroup.com/approval?i=' . $encodenik . '&n=' . $encodetrans;
        //scope
        //modul
        //notrans
        //nik
        //Tambahan jenis transaksi = [ppbj, fin, pdt, purchasing]

        $device_token = DeviceToken::whereRaw('user = ?', [$user])->first();

        $curl = curl_init();
        $data = [
            "notification" => [
                "title" => $title,
                "body" => $body,
                "image" => $image
            ],
            "priority" => "high",
            "data" => [
                "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                "id" => "1",
                "status" => "done",
                "url" => $url,
                "content" => $content
            ],
            "to" => $device_token->token
        ];
        $data = json_encode($data);
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: key=AAAAQC07kYI:APA91bHirsOFVXZA4iz3-40CKE3OX-d22wIy-cK5fxRcCXG0qNTw4sZUyeD-ci0famiHg4d7jmWRNCl535givyUn0ALaMSIruenTU2ONxje2INyuUz1HpPoxBAZbbKHjd6lT5MEedXi2"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        //echo $response; 
        return response()->json($response, 200);
    }

    public function base64encode($string)
    {
        return rtrim(strtr(base64_encode($string), '+/', '-_'), '=');
    }

}
