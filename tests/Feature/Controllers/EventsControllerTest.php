<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\EventsController;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
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
        $startTime = $now->format('Y-m-d H:i:s');
        $endTime = $now->addHour()->format('Y-m-d H:i:s');

        $token = Str::random(40);

        $params = [
            '_method' => 'POST',
            '_token' => $token,
            'room_id' => 1,
            'user_id' => 1,
            'title' => 'book test',
            'start_time' => $startTime,
            'end_time' => $endTime,
            'description' => 'description test'
        ];

        // Faz a requisição POST
        $response = $this->post('/admin/events', $params);

        // Verifica se a resposta é 302 (Redirecionamento)
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
        $startTime = $now->format('Y-m-d H:i:s');
        $endTime = $now->addHour()->format('Y-m-d H:i:s');

        $token = Str::random(40);

        $params = [
            '_method' => 'PUT',
            '_token' => $token,
            'room_id' => 1,
            'user_id' => 1,
            'title' => 'edit book test',
            'start_time' => $startTime,
            'end_time' => $endTime,
            'description' => 'edit description test'
        ];
        $response = $this->put("/admin/events/{$event->id}", $params);

        $response->assertStatus(302);
    }
}
