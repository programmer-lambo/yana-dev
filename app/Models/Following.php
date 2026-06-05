<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Following extends Model
{
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'author_id');
    }

    public function followings(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followings', 'follower_id', 'following_id')->withTimestamps();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followings', 'following_id', 'follower_id')->withTimestamps();
    }
}
