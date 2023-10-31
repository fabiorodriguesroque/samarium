<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VacancyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('isAdmin');
    }

    /**
     * Show the dashboard vacancy page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('dashboard.vacancy');
    }
}
