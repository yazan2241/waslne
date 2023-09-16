<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{

    public function login(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'username' => ['required', 'string', 'max:255'],
                'password' => 'required',
                'deviceToken' => ['required'],
            ]);


            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validation->errors()
                ], 401);
            }

            if (!Auth::attempt($request->only(['username', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'username or Password wrong'
                ], 401);
            }

            $user = User::where('username', $request->username)->first();

            $user->deviceToken = $request->deviceToken;

            $user->update();

            return response()->json([
                'status' => true,
                'message' => 'User loged in successfully',
                'token' => $user->createToken("TOKEN API")->plainTextToken
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    public function register(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'fName' => ['required', 'string', 'max:255'],
                'lName' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:' . User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'deviceToken' => ['required'],
                
            ]);


            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validation->errors()
                ], 401);
            }

            $imageName = '';

            if ($file = $request->file('image')) {

                $imageName = time() . $file->getClientOriginalName();
                $file->move(public_path('images'), $imageName);
            }


            $user = User::create([
                'fName' => $request->fName,
                'lName' => $request->lName,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'image' => $imageName,
                'deviceToken' => $request->deviceToken,
            ]);


            Auth::login($user);

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                'token' => $user->createToken("TOKEN API")->plainTextToken
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    public function left(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'id' => ['required'],
        ]);


        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validation->errors()
            ], 401);
        }

        $id = $request->id;

        $driver = $request->user();

        $deviceToken = User::where('id', '=', $id);


        pushNotification($deviceToken, $driver , "pending" , "سيارة بالقرب منك" , "هل تريد الذهاب مع السائق " . $driver->fName . " " . $driver->lName);
    }

    public function acceptLeft(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'id' => ['required'],
        ]);


        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validation->errors()
            ], 401);
        }

        $id = $request->id;

        $person = $request->user();

        $deviceToken = User::where('id', '=', $id);


        pushNotification($deviceToken, $person , "Accepted" , "تمت الموافقة على الطلب" , "يرغب " . $person->fName . " " . $person->lName . " بالذهاب برفقتك");
    }

    public function rejectLeft(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'id' => ['required'],
        ]);


        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validation->errors()
            ], 401);
        }

        $id = $request->id;

        $person = $request->user();

        $deviceToken = User::where('id', '=', $id);

        pushNotification($deviceToken, $person , "Rejected" , "تم رفض الطلب" , "العميل غير متاح حاليا");
    }

    public function edit(Request $request){
        $user = $request->user();

        if($request->has('fName'))$user->fName = $request->fName;
        if($request->has('lName'))$user->fName = $request->lName;
        if($request->has('password'))$user->fName = $request->password;
        $imageName = $request->image;
        if ($file = $request->file('image')) {

            $imageName = time() . $file->getClientOriginalName();
            $file->move(public_path('images'), $imageName);
        }

        $user->image = $imageName;

        if($user->update()){
            return Response::json(
                'User updated successfuly',
                200
            );
        } else {
            return Response::json(
                'error updating into',
                401
            );
        }

    }
}

function pushNotification($deviceToken, $driver , $status , $title , $body)
{

    $SERVER_API_KEY = 'AAAAKob7ilg:APA91bFxID6bZKbubIfJ4pJjaNlYR2t1boHi_f37jJ6YL3T7LwiR_i41x4PDmo5PjE0LNQogz4mlt_y2roezphDqzK4GriB67mUhpP0rMNICSkm5V5S-UmmAGZ0o61o517CFDQsPZ_K-';


    $token_1 = $deviceToken;

    $data = [

        "registration_ids" => [
            $token_1
        ],

        "notification" => [

            "title" => $title,

            "body" => $body,

            "sound" => "default" // required for sound on ios

        ],

        "dirver" => $driver,
        "status" => $status,

    ];

    $dataString = json_encode($data);

    $headers = [

        'Authorization: key=' . $SERVER_API_KEY,

        'Content-Type: application/json',

    ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');

    curl_setopt($ch, CURLOPT_POST, true);

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

    $response = curl_exec($ch);

    dd($response);
}





