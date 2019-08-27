<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Movies extends Model
{
    protected $fillable = ['title', 'overview', 'release_date', 'image', 'movie_id', 'user_id'];
    public $timestamps = false;
    public $table = 'watchlist';

    protected $casts = [
        'user_id'  => 'integer',
        'movie_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
