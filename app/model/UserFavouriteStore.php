<?php

namespace App\model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFavouriteStore extends Model
{
    use HasFactory;
    protected $table = 'user_favourite_store';
}
