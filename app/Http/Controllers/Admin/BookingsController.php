<?php

namespace App\Http\Controllers\Admin;

use App\Models\Event;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Jobs\ProcessBook;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BookingsController extends Controller
{
    const CACHE_TTL = 300;
    /**
     * @OA\Get(
     *     path="/admin/search-room",
     *     summary="Buscar sala disponível",
     *     description="Busca por salas disponíveis no sistema.",
     *     operationId="searchRoom",
     *     tags={"Admin Bookings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date"),
     *         description="Data para buscar disponibilidade"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Salas disponíveis retornadas"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function searchRoom(Request $request)
    {
        $rooms = null;
        $cache_search_key = md5(json_encode($request->only('start_time', 'end_time', 'capacity')));
        if ($request->filled(['start_time', 'end_time', 'capacity'])) {
            $rooms = Cache::remember($cache_search_key, self::CACHE_TTL, function () use ($request) {
                $times = [
                    Carbon::parse($request->input('start_time')),
                    Carbon::parse($request->input('end_time')),
                ];
    
                return Room::where('capacity', '>=', $request->input('capacity'))
                    ->whereDoesntHave('events', function ($query) use ($times) {
                        $query->whereBetween('start_time', $times)
                            ->orWhereBetween('end_time', $times)
                            ->orWhere(function ($query) use ($times) {
                                $query->where('start_time', '<', $times[0])
                                    ->where('end_time', '>', $times[1]);
                            });
                    })
                    ->get();
            });
        }
    
        return view('admin.bookings.search', compact('rooms'));
    }
    /**
     * @OA\Post(
     *     path="/admin/book-room",
     *     summary="Reservar sala",
     *     description="Permite reservar uma sala para uma data específica.",
     *     operationId="bookRoom",
     *     tags={"Admin Bookings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="room_id", type="integer"),
     *             @OA\Property(property="date", type="string", format="date"),
     *             example={"room_id": 1, "date": "2024-10-10"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Sala reservada com sucesso"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function bookRoom(Request $request)
    {
        $auth = auth();
        $userId = $auth->id(); 
        $user = $auth->user(); 
        $params = $request->all();
        ProcessBook::dispatch($params,$userId,$user);
    
        return redirect()->route('admin.systemCalendar')->withStatus('Your booking is being processed.');
    }
}
