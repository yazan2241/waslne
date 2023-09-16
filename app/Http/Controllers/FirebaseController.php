<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Factory;

use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Laravel\Firebase\Facades\ServiceAccount;


class FirebaseController extends Controller
{
    
    //
    public function index(Request $request)
    {
        $user = $request->user();
        
        $longStart = $request->longStart;
        $latStart = $request->latStart;
        $longEnd = $request->longEnd;
        $latEnd = $request->latEnd;

        $startText = $request->startText;
        $endText = $request->endText;
        
        $dateTime = $request->dateTime;

        $days = $request->days;

        $haveCar = $request->car;

        $car = Car::where('owner' , '=' , $user->id)->first();
        if(($haveCar==1) && $car)
            $user->car = $car;

        $factory = (new Factory)
            ->withServiceAccount('E:\MyProjects\Web\Laravel\waslne\app\Http\Controllers\waqfli-firebase-adminsdk-4ydt7-3d1844025c.json')
            ->withDatabaseUri('https://waqfli-default-rtdb.asia-southeast1.firebasedatabase.app');

        $realtimeDatabase = $factory->createDatabase();
        
        $object = [
            "profile" => $user,
            "order" => [
                'longStart' => $longStart,
                'latStart' => $latStart,
                'longEnd' => $longEnd,
                'latEnd' => $latEnd,
                'startText' => $startText,
                'endText' => $endText,
                'dateTime' => $dateTime,
                'days' => $days,
            ],
            ];

        $realtimeDatabase->getReference()->push($object);
                
        
    }
}
