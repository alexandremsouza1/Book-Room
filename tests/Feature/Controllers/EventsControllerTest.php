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
        $startTime = Carbon::createFromFormat('d/m/Y', '01/09/2024')->format('Y-m-d H:i');
        $endTime = Carbon::createFromFormat('d/m/Y', '01/09/2024')->format('Y-m-d H:i');
        $recurring_until = $now->addHour()->format('Y-m-d');

        $token = Str::random(40);

        $params = [
            '_method' => 'POST', 
            '_token' => $token , // Obtém o token CSRF
            'room_id' => 1,
            'user_id' => 1,
            'title' => 'sfdasd',
            'start_time' =>  $startTime, // Formato correto para o Laravel
            'end_time' => $endTime,   // Formato correto para o Laravel
            'description' => 'dsafsd',
            'recurring_until' => '2024-01-09', 
        ];

        // Faz a requisição POST
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
        $startTime = $now->format('Y-m-d H:i');
        $endTime = $now->addHour()->format('Y-m-d H:i');
        $recurring_until = $now->addHour()->format('Y-m-d');

        $token = Str::random(40);

        $params = [
            '_method' => 'PUT', 
            '_token' => $token,
            'room_id' => 1,
            'user_id' => 1,
            'title' => 'afsdfasdfasasdfasd',
            'start_time' => $startTime,
            'end_time' => $endTime,
            'description' => '',
            'recurring_until' => $recurring_until,
        ];
        $response = $this->put("/admin/events/{$event->id}", $params);

        $response->assertStatus(302);
    }
}
