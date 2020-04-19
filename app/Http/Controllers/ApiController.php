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

    protected $gecko_client;

    protected $blockchain;
	 
	public function __construct()
	{
	    $this->user = JWTAuth::parseToken()->authenticate();
        $this->gecko_client = new Client(['base_uri' => env('COINGECKO_BASE_URL')]);
        $this->blockchain = new Blockchain('My_API_Key');
        
        $wallet_service_url = env('WALLET_SERVICE_URL');
        $this->blockchain->setServiceUrl($wallet_service_url);
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
            $response = $this->gecko_client->request('GET', 'coins/list');
        } catch (RequestException $e) {
            return $e->getMessage();
        }

        $data = json_decode($response->getBody()->getContents(), true);

        $i = 1;
        foreach ($data as $key => $value) {
            //Fetch additional coin data for first 10 only. Remove check for all coins.
            if ($i <= 10) {
                try {
                    $coin = $this->getCoin($value['id']);
                    $coinData = $coin->getData();
                } catch (RequestException $e) {
                    //return $e->getMessage();
                    continue;
                }
            }

            //Upsert coins in DB
            $coins = new Coins;
            $coins = $coins->updateOrCreate(
                ['symbol' => $value['symbol']],
                [
                    'name' => $value['name'],
                    'logo_url' => $coinData->data->image->thumb ?? null,
                    'market_cap'=> $coinData->data->market_data->market_cap->usd ?? null,
                    'price'=> $coinData->data->market_data->current_price->usd ?? null
                ]
            );

            $i++;
        }

        return response()->json([
                'data' => $data
            ],
            $response->getStatusCode()
        );
    }

    public function getCoin($id)
    {
        try {
            //Fetch coin and ticker data asynchronously...I promise
            $promises = [
                'coin_data' => $this->gecko_client->getAsync('coins/' . $id),
                'ticker_data'   => $this->gecko_client->getAsync('coins/' . $id . '/tickers')
            ];
        } catch (RequestException $e) {
            return $e->getMessage();
        }

        //Settle made promise
        $responses = Promise\settle($promises)->wait();

        //Assign responses
        $data = null;
        $status_code = 400;
        if (isset($responses['coin_data']) && array_key_exists('value', $responses['coin_data'])) {
            $data = json_decode($responses['coin_data']['value']->getBody()->getContents(), true);
            $status_code = $responses['coin_data']['value']->getStatusCode();
        }

        $ticker_data = null;
        if (isset($responses['ticker_data']) && array_key_exists('value', $responses['ticker_data'])) {
            $ticker_data = json_decode($responses['ticker_data']['value']->getBody()->getContents(), true);
        }

        return response()->json([
                'data' => $data,
                'ticker_data' => $ticker_data
            ],
            $status_code
        );
    }

    public function createWallet(Request $request)
    {
        try {
            if (isset($request->private_key)) {
                $bip39 = new Bip39('en');
                $entropy = $bip39->decode($request->private_key);
                $priv_hex = (string) $entropy;

                $wallet = $this->blockchain->Create->createWithKey($request->password, $priv_hex, $request->email, $request->label);
            } else {
                $wallet = $this->blockchain->Create->create($request->password, $request->email, $request->label);
            }
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
        $bits = (int) $request->bits ?? 128;

        $bip39 = new Bip39('en');

        //Generate 256-bit hexadecimal string
        $hex = Entropy::random($bits);

        $entropy = new Entropy($hex);

        $word_sequence = $bip39->setEntropy($entropy)->encode();

        return response()->json([
                'data' => [
                    'private_key_mnemonic' => $word_sequence
                ]
            ],
            200
        );
    }
 
}