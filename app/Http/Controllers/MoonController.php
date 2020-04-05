<?php

namespace App\Http\Controllers;

use App\Models\Moon;

class MoonController extends Controller
{

    public function index()
    {
        $moons = Moon::with(['region', 'system', 'renter'])
            ->orderBy('region_id')
            ->orderBy('solar_system_id')
            ->orderBy('planet')
            ->orderBy('moon')
            ->get();

        // We want to display information differently to administrators and prospective renters.

        return view('moons.public', ['moons' => $moons]);
    }

}
