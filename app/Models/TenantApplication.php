<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantApplication extends Model
{
    use HasFactory;
    protected $fillable = ['tenant_id','property_id','document_file','is_approve','rejected_cause','status'] ;

    protected $casts = [
        'is_approve' => 'integer',
        'status' => 'integer',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
