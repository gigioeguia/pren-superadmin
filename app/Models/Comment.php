<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'email', 'comment', 'blog_id'];

    public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class);
    }

    protected $casts = [
        'blog_id' => 'integer',
        'comment_id' => 'integer',
        'status' => 'integer'
    ];
}
