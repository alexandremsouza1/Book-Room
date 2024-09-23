<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\SystemCalendarController;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Mockery;
use Tests\TestCase;
use Illuminate\Support\Str;


class SystemCalendarControllerTest extends TestCase
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
        return Mockery::mock(SystemCalendarController::class)->makePartial();
    }

    public function authMockUser(): void
    {
        $user = User::find(1);
        $this->assertNotNull($user, 'Usuário não encontrado');
        $this->actingAs($user);
    }


    public function testListSystemCalendar()
    {
        $response = $this->get('/admin/system-calendar');
        $response->assertStatus(200);
    }

    public function testListSystemCalendarWithParams()
    {
        $response = $this->get('/admin/system-calendar/?room_id=1&user_id=1');
        $response->assertStatus(200);
    }

}