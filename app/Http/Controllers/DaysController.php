<?php

namespace App\Http\Controllers;

use App\Models\Day;
use Illuminate\Http\Request;
use App\Http\Resources\Day as DayResource;
use App\Http\Resources\DayCollection;

class DaysController extends Controller
{
    public function index()
    {
        return response()->json([
            "status" => true,
            "days" => new DayCollection(
                Day::all()
            )
        ]);
    }
}
