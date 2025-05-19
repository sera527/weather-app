<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CurrentWeatherController extends Controller
{
    public function index()
    {
        return view('current-weather');
    }
}
