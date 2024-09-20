<?php


namespace Tests\Feature\Jobs;

use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessBook;
use App\Models\User;
use Tests\TestCase;

class ProcessBookTest extends TestCase
{

    //vendor/bin/phpunit --filter testBookJobDispatchesCorrectly tests/Feature/Jobs/ProcessBookTest.php
    public function testBookJobDispatchesCorrectly()
    {
        // Encontre ou crie um usuário para simular a autenticação
        $user = User::find(1);
        $this->actingAs($user);
    
        // Certifique-se de que a fila está sendo fakeada
        $auth = auth();

        $userId = $auth->id(); 
        $userInfo = $auth->user(); 
    
        // Crie o job com os dados necessários
        $job = new ProcessBook([
            "_token" => "kSQ3nYouCNNFMcz5ltyJQKp2V4LAKHMrR1BCB48I",
            "room_id" => "2",
            "start_time" => "2024-09-19",
            "end_time" => "2024-09-19",
            "title" => "testes",
            "description" => "tesets",
            "recurring_until" => "2024-09-19T10:10",
        ],$userId,$userInfo);
    
        // Despache o job
        dispatch($job);
    
        // Verifique se o job foi adicionado à fila
        Queue::assertPushed(ProcessBook::class, function ($job) use ($user) {
            return $job->data['room_id'] === "2" &&
                   $job->data['_token'] === "kSQ3nYouCNNFMcz5ltyJQKp2V4LAKHMrR1BCB48I" &&
                   $job->data['user_id'] === $user->id; // Verifique se o user_id está correto
        });
    }
}
