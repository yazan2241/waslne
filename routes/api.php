<?php

use App\Http\Controllers\CarController;
use App\Http\Controllers\FirebaseController;
use App\Http\Controllers\User\UserController;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->post('/user', function (Request $request) {

    $user = $request->user();

    
        $car = Car::where('owner' , '=' , $user->id)->first();
        if($car)
            $user->car = $car;
    


    return Response::json(
        $user,
        200
    );
});

Route::post('/auth/register' , [UserController::class , 'register']);
Route::post('/auth/login' , [UserController::class , 'login']);

Route::middleware('auth:sanctum')->get('/user/revoke' , function(Request $request){
    $user = $request->user();
    $user->tokens->delete();
    return 'token are deleted';
});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/send' , [FirebaseController::class , 'index']);
    Route::post('/addCar' , [CarController::class , 'store']);
    Route::post('/leftUser' , [UserController::class , 'left']);
    Route::post('/acceptLeft' , [UserController::class , 'acceptLeft']);
    Route::post('/rejectLeft' , [UserController::class , 'rejectLeft']);
    
});






// push notification

Route::get('/' , function(){
    $SERVER_API_KEY = 'AAAAU8YvtDg:APA91bGRxYtwsFq0J9NFqkMHvC2QcKgukO_r3cWm6TCEBau-ngivS8S1mEpCziKbTEpTic_57TB4YGkL3VNTCPvEKW_oLdtjeFeJKJ04Y-fIgZcB7EWyhH6eu3HGLnua1UnG_bIjb08K';


    $token_1 = 'Test Token';

    $data = [

        "registration_ids" => [
            $token_1
        ],

        "notification" => [

            "title" => 'مصطفى',

            "body" => 'بدك اشعارات؟',

            "sound"=> "default" // required for sound on ios

        ],

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

});


