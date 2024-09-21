<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \DateTimeInterface;

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
        return $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value)->format(config('panel.date_format') . ' ' . config('panel.time_format')) : null;

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
        return $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value)->format(config('panel.date_format') . ' ' . config('panel.time_format')) : null;

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
