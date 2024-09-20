<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use \DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Billable;

    public $table = 'users';

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $dates = [
        'updated_at',
        'created_at',
        'email_verified_at',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'created_at',
        'updated_at',
        'remember_token',
        'email_verified_at',
        'credits',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');

    }

    public function getIsAdminAttribute()
    {
        return $this->roles()->where('id', 1)->exists();

    }

    public function getEmailVerifiedAtAttribute($value)
    {
        return $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value)->format(config('panel.date_format') . ' ' . config('panel.time_format')) : null;

    }

    public function setEmailVerifiedAtAttribute($value)
    {
        if ($value) {
            try {
                $date = Carbon::parse($value);
                $this->attributes['email_verified_at'] = $date->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                throw new \InvalidArgumentException("Data inválida fornecida para email_verified_at: {$value}");
            }
        } else {
            $this->attributes['email_verified_at'] = null;
        }
    }

    public function setPasswordAttribute($input)
    {
        if ($input) {
            $this->attributes['password'] = app('hash')->needsRehash($input) ? Hash::make($input) : $input;
        }

    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));

    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);

    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function chargeCredits($hours, Room $room)
    {
        $amount = $hours * (int) $room->hourly_rate * 100;

        if ($this->credits < $amount) {
            return false;
        }

        $this->credits -= $amount;
        $this->save();

        Transaction::create([
            'user_id'      => $this->id,
            'room_id'      => $room->id,
            'paid_amount'  => $amount,
            'booking_time' => $hours,
        ]);

        return true;
    }

}
