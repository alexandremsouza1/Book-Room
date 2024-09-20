<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\BookingsController;
use App\Models\User;
use Mockery;
use Tests\TestCase;


class BookingsControllerTest extends TestCase
{
  protected $controller;

  protected function setUp(): void
  {
    $this->controller = $this->mockRulesItemService();
    parent::setUp();
  }

  public function mockRulesItemService()
  {
    return Mockery::mock(BookingsController::class)->makePartial();
  }


  public function testSuccessSearchRoom()
  {
    $user = User::find(1);
    $this->assertNotNull($user, 'Usuário não encontrado'); 

    $this->actingAs($user);
    $response = $this->get('/admin/search-room?start_time=2024-09-20&end_time=2024-09-20&capacity=1');
    $response->assertStatus(200);
  }

}