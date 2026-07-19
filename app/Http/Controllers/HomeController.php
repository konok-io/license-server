<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\View\View;

class HomeController extends Controller
{
    /** Public landing page for the license server. */
    public function index(): View
    {
        $s = SiteSetting::all();

        return view('home', ['s' => $s]);
    }
}
