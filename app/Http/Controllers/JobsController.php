<?php

namespace App\Http\Controllers;

use App\Position;

class JobsController extends Controller
{
    public function index()
    {
        $positions = Position::active()->orderBy('title')->get();
        return view('public.jobs', compact('positions'));
    }
}
