<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validation = Validator::make($request->all(), [
            'number' => ['required', 'string'],
            'passengers' => ['required', 'integer'],
            'image' => ['required', 'file'],
        ]);


        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validation->errors()
            ], 401);
        }

        $number = $request->number;
        $passengers = $request->passengers;
        
        $user = $request->user();

        $owner = $user->id;

        $car = new Car();
        $car->owner = $owner;
        $car->number = $number;
        $car->passengers = $passengers;

       

        
        $car->image = '';

        if ($file = $request->file('image')) {
            
            $car->image = time() . $file->getClientOriginalName();
            $file->move(public_path('images'), $car->image);
            
        }



        if($car->save()){
            return Response::json(
                $car,
                200
            );
        }else {
            return Response::json(
                'Car not stored',
                401
            );


        }

    }

    /**
     * Display the specified resource.
     */
    public function show(Car $car)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Car $car)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Car $car)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Car $car)
    {
        //
    }
}
