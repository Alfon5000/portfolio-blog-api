<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    /** @use HasFactory<\Database\Factories\CommentFactory> */
    use HasFactory;

    protected $fillable = [
        'commenter_id',
        'post_id',
        'body',
    ];

    public function scopeCommenterId($query, $commenterId)
    {
        return $query->where('commenter_id', $commenterId);
    }

    public function scopePostId($query, $postId)
    {
        return $query->where('post_id', $postId);
    }

    public function commenter()
    {
        return $this->belongsTo(User::class, 'commenter_id', 'id', 'users');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id', 'id', 'posts');
    }
}
