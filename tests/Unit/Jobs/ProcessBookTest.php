<?php


namespace Tests\Unit\Jobs;

use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessBook;
use App\Models\Room;
use App\Models\User;
use App\Services\EventService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProcessBookTest extends TestCase
{
    use RefreshDatabase;

    //vendor/bin/phpunit --filter testBookJobDispatchesCorrectly tests/Unit/Jobs/ProcessBookTest.php
    public function testBookJobDispatchesCorrectly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
    
        $auth = auth();

        $userId = $auth->id(); 
        $userInfo = $auth->user(); 
    
        Queue::fake();

        $job = new ProcessBook([
            "_token" => "kSQ3nYouCNNFMcz5ltyJQKp2V4LAKHMrR1BCB48I",
            "room_id" => "2",
            "start_time" => "2024-09-19",
            "end_time" => "2024-09-19",
            "title" => "testes",
            "description" => "tesets",
            "recurring_until" => "2024-09-19T10:10",
        ],$userId,$userInfo);
    

        dispatch($job);
    

        Queue::assertPushed(ProcessBook::class);
    }

    public function testHandleProcessesBookCorrectly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Queue::fake();

        $bookData = [
            "_token" => "kSQ3nYouCNNFMcz5ltyJQKp2V4LAKHMrR1BCB48I",
            "room_id" => "1",
            "start_time" => "2024-09-19",
            "end_time" => "2024-09-19",
            "title" => "room 2",
            "description" => "room 2",
            "recurring_until" => "2024-09-19T10:10",
        ];

        Room::create([
            "name" => $bookData['title'],
            "capacity" => 4,
        ]);

        $this->assertDatabaseHas('rooms', [
            'id' => 1,
            'name' => 'room 2',
        ]);

        $eventService = $this->createMock(EventService::class);

        $eventService->expects($this->once())
            ->method('isRoomTaken')
            ->willReturn(false);


        $job = new ProcessBook($bookData, $user->id, $user);

        $job->handle($eventService);

        // $this->assertDatabaseHas('events', [
        //     'title' => 'room 2',
        // ]);
    }
}
