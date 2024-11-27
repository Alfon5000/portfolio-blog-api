<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory;

    protected $fillable = [
        'author_id',
        'title',
        'body',
        'image',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id', 'id', 'users');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id', 'id', 'comments');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tags', 'post_id', 'tag_id');
    }

    public function likers()
    {
        return $this->belongsToMany(User::class, 'post_likes', 'post_id', 'liker_id');
    }
}
