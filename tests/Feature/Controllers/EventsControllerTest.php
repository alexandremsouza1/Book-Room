<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\EventsController;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Tests\TestCase;
use Illuminate\Support\Str;


class EventsControllerTest extends TestCase
{
    protected $controller;

    protected function setUp(): void
    {
        $this->controller = $this->mockController();
        parent::setUp();
        $this->artisan('migrate:fresh');
        $this->artisan('db:seed');
        $this->authMockUser();
    }

    public function mockController()
    {
        return Mockery::mock(EventsController::class)->makePartial();
    }

    public function authMockUser(): void
    {
        $user = User::find(1);
        $this->assertNotNull($user, 'Usuário não encontrado');
        $this->actingAs($user);
    }


    public function testListAllEvents()
    {
        $response = $this->get('/admin/events');
        $response->assertStatus(200);
    }

    public function testCreateEvent()
    {
        $now = Carbon::now();
        $now = Carbon::now();
        $startTime = $now->format('Y-m-d');
        $endTime = $now->addHour()->format('Y-m-d');
        $recurring_until = $now->addHour()->format('Y-m-d H:i:s');

        $token = Str::random(40);

        $params = [
            '_method' => 'POST', 
            '_token' => $token ,
            'room_id' => 1,
            'user_id' => 1,
            'title' => 'new event',
            'start_time' =>  $startTime,
            'end_time' => $endTime, 
            'description' => 'new event',
            'recurring_until' => $recurring_until, 
        ];

        $response = $this->post('/admin/events', $params);

        $response->assertStatus(302);
    }

    protected function createEvent()
    {
        return Event::create([
            'room_id' => 1,
            'user_id' => 1,
            'title' => 'book test',
            'start_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'end_time' => Carbon::now()->addHour()->format('Y-m-d H:i:s'),
            'description' => 'description test',
        ]);
    }
    
    public function testEditEvent()
    {
        $event = $this->createEvent(); 

        $now = Carbon::now();
        $startTime = $now->format('Y-m-d');
        $endTime = $now->addHour()->format('Y-m-d');
        $recurring_until = $now->addHour()->format('Y-m-d H:i:s');

        $token = Str::random(40);

        $params = [
            '_method' => 'PUT', 
            '_token' => $token,
            'room_id' => 1,
            'user_id' => 1,
            'title' => 'new edit event',
            'start_time' => $startTime,
            'end_time' => $endTime,
            'description' => '',
            'recurring_until' => $recurring_until,
        ];
        $response = $this->put("/admin/events/{$event->id}", $params);

        $response->assertStatus(302);
    }

    public function testDeleteEvent()
    {
        $event = $this->createEvent(); 

        $token = Str::random(40);

        $params = [
            '_method' => 'DELETE', 
            '_token' => $token,
        ];
        $response = $this->post("/admin/events/{$event->id}", $params);

        $response->assertStatus(302);
    }

    public function testCreateWithPermission()
    {
        $response = $this->get(route('admin.events.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.events.create');
        $response->assertViewHas(['rooms', 'users']);
    }

    public function testCreateView()
    {
        $response = $this->get(route('admin.events.create'));
        $response->assertStatus(200);
    }

    public function testEditView()
    {
        $event = $this->createEvent();

        $response = $this->get(route('admin.events.edit', $event->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.events.edit');
        $response->assertViewHas(['rooms', 'users', 'event']);
    }


    public function testShowView()
    {
        $event = $this->createEvent();

        $response = $this->get(route('admin.events.show', $event->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.events.show');
        $response->assertViewHas('event');
    }
}
