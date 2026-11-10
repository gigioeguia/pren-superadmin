<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'tenant_id',
        'status',
   ];


   public function tenant() {

    return $this->belongsTo(User::class);
   }

   public function property() {

    return $this->belongsTo(Property::class);
   }
}
