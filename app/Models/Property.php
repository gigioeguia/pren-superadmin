<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Property extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'landlord_id', 'house_type_id',  'room_info', 'building_name', 'utility_deposit', 'security_deposit', 'sale_amount', 'late_fee', 'late_fee_after_days', 'cover_image', 'status', 'type', 'rent_info', 'address_info', 'meta'];

    protected function rentInfo(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $data = json_decode($value, true);

                if (isset($data['monthly_rent'])) {
                    $data['monthly_rent'] = (int) $data['monthly_rent'];
                }

                return $data;
            },
            set: fn ($value) => json_encode($value),
        );
    }

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function propertyDetail(): HasOne
    {
        return $this->hasOne(PropertyDetail::class, 'property_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class, 'property_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function house_type(): BelongsTo
    {
        return $this->belongsTo(HouseType::class, 'house_type_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function favorite(): HasOne
    {
        return $this->hasOne(Favorite::class);
    }

    public function rent(): HasOne
    {
        return $this->hasOne(Rent::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $id = Property::max('id') + 1;
            $model->puid = str_pad($id, 6, '0', STR_PAD_LEFT);
        });
    }

    protected $casts = [
        'category_id' => 'integer',
        'landlord_id' => 'integer',
        'house_type_id' => 'integer',
        'user_id' => 'integer',
        'status' => 'integer',
        'utility_deposit' => 'double',
        'security_deposit' => 'double',
        'sale_amount' => 'double',
        'late_fee' => 'double',
        'late_fee_after_days' => 'integer',
        'cover_image' => 'json',
        'room_info' => 'json',
        'rent_info' => 'json',
        'address_info' => 'json',
        'property_info' => 'json',
        'meta' => 'json',
    ];
}
