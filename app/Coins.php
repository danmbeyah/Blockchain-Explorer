<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coins extends Model
{
    //Allowed mass assignable attributes
	protected $fillable = [
		'symbol', 'name', 'price', 'logo_url', 'market_cap'
	];

	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [];
}
