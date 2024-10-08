<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SystemCalendarController extends Controller
{
    public $sources = [
        [
            'model'      => '\\App\\Models\\Event',
            'date_field' => 'start_time',
            'field'      => 'title',
            'prefix'     => '',
            'suffix'     => '',
            'route'      => 'admin.events.edit',
        ],
    ];
    /**
     * @OA\Get(
     *     path="/admin/system-calendar",
     *     summary="Visualizar o calendário do sistema",
     *     description="Exibe os eventos no calendário do sistema.",
     *     operationId="getSystemCalendar",
     *     tags={"Admin System"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Calendário exibido com sucesso"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $events = [];
        $rooms = Room::all()->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $users = User::all()->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        foreach ($this->sources as $source) {
            $models = $source['model']::when($request->input('room_id'), function ($query) use ($request) {
                    $query->where('room_id', $request->input('room_id'));
                })
                ->when($request->input('user_id'), function ($query) use ($request) {
                    $query->where('user_id', $request->input('user_id'));
                })
                ->get();
            foreach ($models as $model) {
                $crudFieldValue = $model->getOriginal($source['date_field']);

                if (!$crudFieldValue) {
                    continue;
                }

                $events[] = [
                    'title' => trim($source['prefix'] . " " . $model->{$source['field']}
                        . " " . $source['suffix']),
                    'start' => $crudFieldValue,
                    'url'   => route($source['route'], $model->id),
                ];
            }

        }

        return view('admin.calendar.calendar', compact('events', 'rooms', 'users'));

    }

}
