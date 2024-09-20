<?php

namespace App\Http\Controllers\Admin;

use App\Models\Event;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Jobs\ProcessBook;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingsController extends Controller
{
    public function searchRoom(Request $request)
    {
        $rooms = null;
        if($request->filled(['start_time', 'end_time', 'capacity'])) {
            $times = [
                Carbon::parse($request->input('start_time')),
                Carbon::parse($request->input('end_time')),
            ];

            $rooms = Room::where('capacity', '>=', $request->input('capacity'))
                ->whereDoesntHave('events', function ($query) use ($times) {
                    $query->whereBetween('start_time', $times)
                        ->orWhereBetween('end_time', $times)
                        ->orWhere(function ($query) use ($times) {
                            $query->where('start_time', '<', $times[0])
                                ->where('end_time', '>', $times[1]);
                        });
                })
                ->get();
        }

        return view('admin.bookings.search', compact('rooms'));
    }

    public function bookRoom(Request $request)
    {
        $params = $request->only(['room_id', 'user_id']);
        ProcessBook::dispatch($params);
    
        return redirect()->route('admin.systemCalendar')->withStatus('Your booking is being processed.');
    }
}
