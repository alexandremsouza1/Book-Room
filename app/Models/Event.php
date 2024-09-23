<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \DateTimeInterface;
/**
 * @OA\Schema(
 *     schema="Event",
 *     type="object",
 *     required={"title", "room_id", "user_id", "start_time", "end_time"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Reunião de Teste"),
 *     @OA\Property(property="room_id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="start_time", type="string", format="date-time", example="2024-10-10 10:00:00"),
 *     @OA\Property(property="end_time", type="string", format="date-time", example="2024-10-10 11:00:00"),
 *     @OA\Property(property="description", type="string", example="Descrição do evento"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-20 10:00:00"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-20 10:00:00"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", example="2024-09-20 10:00:00"),
 * )
 */
class Event extends Model
{
    use SoftDeletes;

    public $table = 'events';

    protected $dates = [
        'end_time',
        'start_time',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'title',
        'room_id',
        'user_id',
        'end_time',
        'start_time',
        'created_at',
        'updated_at',
        'deleted_at',
        'description',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');

    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');

    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');

    }

    public function getStartTimeAttribute($value)
    {
        $date = Carbon::parse($value);
        return $date->format('Y-m-d H:i:s');

    }

    public function setStartTimeAttribute($value)
    {
        if ($value) {
            try {
                $date = Carbon::parse($value);
                $this->attributes['start_time'] = $date->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                throw new \InvalidArgumentException("Data inválida fornecida para start_time: {$value}");
            }
        } else {
            $this->attributes['start_time'] = null;
        }
    }
    public function getEndTimeAttribute($value)
    {
        $date = Carbon::parse($value);
        return $date->format('Y-m-d H:i:s');

    }

    public function setEndTimeAttribute($value)
    {
        if ($value) {
            try {
                $date = Carbon::parse($value);
                $this->attributes['end_time'] = $date->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                throw new \InvalidArgumentException("Data inválida fornecida para end_time: {$value}");
            }
        } else {
            $this->attributes['end_time'] = null;
        }
    }
}
