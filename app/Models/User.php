<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'landlord_id',
        'plan_subscribe_id',
        'unique_id',
        'name',
        'role',
        'email',
        'image',
        'phone',
        'password',
        'is_setupped',
        'balance',
        'profile_type',
        'subscriptionDate',
        'will_expire',
        'email_verified_at',
        'remember_token'
    ];

    public function supportMessages()
    {
        return $this->hasMany(Support::class, 'sender_id');
    }

    public function allSupportMessagesWith($adminId)
    {
        return Support::where(function ($query) use ($adminId) {
            $query->where('sender_id', $this->id)->where('receiver_id', $adminId);
        })->orWhere(function ($query) use ($adminId) {
            $query->where('receiver_id', $this->id)->where('sender_id', $adminId);
        });
    }

    public function user_details()
    {
        return $this->hasOne(UserDetail::class);
    }

    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    public function tenants()
    {
        return $this->hasMany(User::class);
    }

    public function rents()
    {
        return $this->hasMany(Rent::class, 'tenant_id');
    }

    public function maintenances()
    {
        return $this->hasManyThrough(Maintenance::class, Property::class, 'user_id', 'property_id');
    }

    public function landlord()
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function plan_subscribe()
    {
        return $this->belongsTo(PlanSubscribe::class, 'plan_subscribe_id');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $id = User::max('id') + 1;
            $model->unique_id = str_pad($id, 6, '0', STR_PAD_LEFT);
        });
    }

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone' => 'json',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
