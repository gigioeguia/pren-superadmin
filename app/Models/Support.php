<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Support extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'parent_id',
        'message',
        'file',
        'file_info'
    ];

    public function sender() {

        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver() {

        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function replies()
    {
        return $this->hasMany(Support::class, 'parent_id');
    }

    public function messages()
    {
        return $this->hasMany(Support::class, 'parent_id');
    }
    protected $casts = [
        'parent_id' => 'integer',
        'file_info' => 'json',
    ];

}
