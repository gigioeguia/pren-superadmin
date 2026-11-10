<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

   protected $fillable = [
        'property_id',
        'tenant_id',
        'rent_id',
        'review',
        'comment',
        'status',
   ];


   public function tenant() {

    return $this->belongsTo(User::class);
   }

   public function property() {

    return $this->belongsTo(Property::class);
   }

   public function rents() {

    return $this->hasMany(Rent::class);
   }

   protected $casts = [
        'property_id' => 'integer',
        'review' => 'double',
   ];
}
