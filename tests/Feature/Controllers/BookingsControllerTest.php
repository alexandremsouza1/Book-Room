<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\BookingsController;
use App\Models\User;
use Carbon\Carbon;
use Mockery;
use Tests\TestCase;
use Illuminate\Support\Str;


class BookingsControllerTest extends TestCase
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
    return Mockery::mock(BookingsController::class)->makePartial();
  }

  public function authMockUser() : void
  {
    $user = User::find(1);
    $this->assertNotNull($user, 'Usuário não encontrado'); 
    $this->actingAs($user);
  }


  public function testSuccessSearchRoom()
  {
    $response = $this->get('/admin/search-room?start_time=2024-09-20&end_time=2024-09-20&capacity=1');
    $response->assertStatus(200);
  }

  public function testSuccessBookRoom()
  {
      $now = Carbon::now();
      $startTime = $now->format('Y-m-d H:i:s');
      $endTime = $now->addHour()->format('Y-m-d H:i:s');
  
      $token = Str::random(40);
  
      $params = [
          '_token' => $token,
          'room_id' => 1,
          'start_time' => $startTime,
          'end_time' => $endTime,
          'title' => 'book room',
          'description' => 'test book room',
          'recurring_until' => $now->format('Y-m-d H:i:s')
      ];
  
      $response = $this->post('/admin/book-room', $params);
  
   
      $response->assertStatus(302);
    }
}