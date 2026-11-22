<?php

namespace App\Models;

use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[ObservedBy(UserObserver::class)]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if (empty($user->role)) {
                $user->role = 'landlord';
            }
        });
    }

    // Contrato 5.11 (DT-B3): allowlist mínima de perfil. El resto de
    // columnas (tenancy, saldo, suscripción, verificación, OTP) se
    // escribe solo server-side vía forceFill explícito.
    protected $fillable = [
        'name',
        'email',
        'image',
        'phone',
        'password',
        'profile_type',
        'is_setupped',
    ];

    /**
     * Cuentas landlord de demo (flag is_demo, Fase 3.5 / spec 4.10).
     */
    public static function demoLandlords(): Collection
    {
        return static::query()->where('is_demo', true)->where('role', 'landlord')->get();
    }

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

    public function user_details(): HasOne
    {
        return $this->hasOne(UserDetail::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function rents(): HasMany
    {
        return $this->hasMany(Rent::class, 'tenant_id');
    }

    public function maintenances(): HasManyThrough
    {
        return $this->hasManyThrough(Maintenance::class, Property::class, 'user_id', 'property_id');
    }

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function plan_subscribe(): BelongsTo
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
        'is_demo' => 'boolean',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
