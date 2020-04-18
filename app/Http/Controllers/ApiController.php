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

use Blockchain\Blockchain;

use Blocker\Bip39\Bip39;
use Blocker\Bip39\Util\Entropy;

use Exception;
 
class ApiController extends Controller
{
	protected $user;

    protected $client;

    protected $blockchain;
	 
	public function __construct()
	{
	    $this->user = JWTAuth::parseToken()->authenticate();
        $this->client = new Client(['base_uri' => env('COINGECKO_BASE_URL')]);
        $this->blockchain = new Blockchain('My_API_Key');
        
        $walletServiceUrl = env('WALLET_SERVICE_URL');
        $this->blockchain->setServiceUrl($walletServiceUrl);
	}

    private function validateAccess($wallet_guid = null, $wallet_pass_phrase = null)
    {
        $wallet_guid = $wallet_guid ?? env('WALLET_GUID');
        $wallet_pass_phrase = $wallet_pass_phrase ?? env('WALLET_PASS_PHRASE');

        if(is_null($wallet_guid) || is_null($wallet_pass_phrase)) {
                return response()->json([
                    'error' => 'Please enter a wallet ID and pass phrase'
                ],
                400
            );
        }

        //Set wallet credentials
        try {
            $this->blockchain->Wallet->credentials($wallet_guid, $wallet_pass_phrase);
        } catch (Blockchain_ApiError $e) {
            return $e->getMessage();
        }

        return $this;
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

    public function createWallet(Request $request)
    {
        //ToDo: Introduce entropy to improve passphrase security
        $pass_phrase = $request->pass_phrase;

        try {
            $wallet = $this->blockchain->Create->create($pass_phrase, $request->email, $request->label);
        } catch (Blockchain_ApiError $e) {
            return $e->getMessage();
        }

        return response()->json([
                'data' => $wallet
            ],
            200
        );
    }

    public function getWallet(Request $request)
    {
        $wallet_guid = $request->wallet_guid ?? null;
        $wallet_pass_phrase = $request->pass_phrase ?? null;

        $this->validateAccess($wallet_guid, $wallet_pass_phrase);

        //Get wallet addresses
        try {
            $addresses = $this->blockchain->Wallet->getAddresses();
        } catch (Blockchain_ApiError $e) {
            return $e->getMessage();
        }
        
        //Sum wallet addresses balances 
        $total_balance = 0;
        foreach ($addresses as $addresses) {
            $total_balance = bcadd($total_balance, $addresses->balance, 8);
        }

        return response()->json([
                'data' => [
                    'addresses' => $addresses,
                    'total_balance' => $total_balance
                ]
            ],
            200
        );
    }

    public function getAddressBalance($address, Request $request)
    {
        $wallet_guid = $request->wallet_guid ?? null;
        $wallet_pass_phrase = $request->pass_phrase ?? null;

        $this->validateAccess($wallet_guid, $wallet_pass_phrase);

        try {
            $balance = $this->blockchain->Wallet->getAddressBalance($address);
        } catch (Blockchain_ApiError $e) {
            return $e->getMessage();
        }

        return response()->json([
                'data' => $balance
            ],
            200
        );
    }

    public function generatePrivateKey(Request $request)
    {
        $bip39 = new Bip39('en');

        if (true) {
            //Generate 256-bit hexadecimal string
            $hex = Entropy::random(128);

            $entropy = new Entropy($hex);

            $wordSequence = $bip39->setEntropy($entropy)->encode();
        }

        return response()->json([
                'data' => [
                    'private_key_hex' => (string) $bip39->decode($wordSequence),
                    'private_key_sequence' => $wordSequence
                ]
            ],
            200
        );
    }
 
}