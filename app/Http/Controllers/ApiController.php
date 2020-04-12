<?php
 
namespace App\Http\Controllers;
 
use App\Http\Requests\RegisterAuthRequest;
use App\User;
use App\Coins;

use JWTAuth;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;

use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
 
class ApiController extends Controller
{
	protected $user;

    protected $client;
	 
	public function __construct()
	{
	    $this->user = JWTAuth::parseToken()->authenticate();
        $this->client = new Client(['base_uri' => 'https://api.coingecko.com/api/v3/']);
	}

    public function getCoins(Request $request)
    {
        try {
            $response = $this->client->request('GET', 'coins/list');
        } catch (RequestException $e) {
            return $e->getMessage();
        }

        return response()->json([
                'data' => $response->getBody()->getContents()
            ],
            $response->getStatusCode()
        );
    }

    public function getCoin($id)
    {
        try {
            $promises = [
                'coin_data' => $this->client->getAsync('coins/' . $id),
                'ticker_data'   => $this->client->getAsync('coins/' . $id . '/tickers')
            ];
        } catch (RequestException $e) {
            return $e->getMessage();
        }

        $responses = Promise\settle($promises)->wait();

        return response()->json([
                'data' => [
                    'coin_data' => $responses['coin_data']['value']->getBody()->getContents(),
                    'ticker_data' => $responses['ticker_data']['value']->getBody()->getContents()
                ]
            ],
            $responses['coin_data']['value']->getStatusCode()
        );
    }
 
}