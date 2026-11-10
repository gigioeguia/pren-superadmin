<?php
namespace App\Traits ;

use App\Models\UserPermission;

trait HasPermission
{
    public function permissions()
    {
        return $this->belongsToMany(UserPermission::class,'user_id');
    }
}
