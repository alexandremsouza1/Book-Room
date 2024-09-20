<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\Room;
use App\Services\EventService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class ProcessBook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;
    public $auth;

    /**
     * Create a new job instance.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->auth = auth();
    }

    /**
     * Execute the job.
     */
    public function handle(EventService $eventService): void
    {
        $this->data['user_id'] = $this->auth->id();

        $validator = validator($this->data, [
            'title'   => 'required',
            'room_id' => 'required',
        ]);

        if ($validator->fails()) {
            return;
        }

        $room = Room::findOrFail($this->data['room_id']);

        if ($eventService->isRoomTaken($this->data)) {
            return;
        }

        if (!$this->auth->user()->is_admin && !$eventService->chargeHourlyRate($this->data, $room)) {
            return;
        }

        $event = Event::create($this->data);

        if (isset($this->data['recurring_until'])) {
            $eventService->createRecurringEvents($this->data);
        }
    }
}
