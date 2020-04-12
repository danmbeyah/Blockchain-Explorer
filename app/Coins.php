<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coins extends Model
{
    //
	protected $fillable = [
		'coin_name', 'coin_price', 'coin_url'
	];
}
