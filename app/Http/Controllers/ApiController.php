<?php
 
namespace App\Http\Controllers;
 
use App\Http\Requests\RegisterAuthRequest;
use App\User;
use App\Coins;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
 
class ApiController extends Controller
{
	protected $user;
	 
	public function __construct()
	{
	    $this->user = JWTAuth::parseToken()->authenticate();
	}
 
    public function test(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->user
        ], 200);
    }
}