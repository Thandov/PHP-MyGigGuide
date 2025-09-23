<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Artist;
use App\Models\Venue;

class HomeController extends Controller
{
    /**
     * Display the home page.
     */
    public function index()
    {
        $events = Event::with(['venue', 'artists', 'owner'])
            ->where('status', 'scheduled')
            ->orderBy('date', 'asc')
            ->limit(6)
            ->get();

        $artists = Artist::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        $venues = Venue::with('owner')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return view('home', compact('events', 'artists', 'venues'));
    }

    /**
     * Display the map page.
     */
    public function map()
    {
        $events = Event::with(['venue', 'artists', 'owner'])
            ->where('status', 'scheduled')
            ->orderBy('date', 'asc')
            ->get();

        $venues = Venue::with('owner')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('map', compact('events', 'venues'));
    }

    /**
     * Display the test map page.
     */
    public function testMap()
    {
        $events = Event::with(['venue', 'artists', 'owner'])
            ->where('status', 'scheduled')
            ->orderBy('date', 'asc')
            ->get();

        return view('test-map', compact('events'));
    }
}
