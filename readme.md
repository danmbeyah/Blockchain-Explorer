
### Blockchain Explorer App
Implements the following:
1. Queries blockchain for crypto data
1. Creates a crypto wallet and transacts
1. PHPUnit tests
1. JWT authentication
1. Laravel JSON API (LJA)
1. MYSQL DB

## Outcome
1. A wallet used to hold bitcoins and carry out transactions.

## Set up
1. Clone repo and run composer install
1. php artisan migrate to run migrations
1. php artisan serve to start the app
1. Run wallet service locally (https://github.com/blockchain/service-my-wallet-v3 and expose service url)

## Available endpoints
1. POST: http://localhost:8000/api/register  Body:{name,email,password}
1. POST: http://localhost:8000/api/login Body:{email,password}
1. GET: http://localhost:8000/api/coins [fetch list of crypto currencies from Coin Gecko]
1. GET: http://localhost:8000/api/coins/ethereum  [Get coin data and ticker data]
1. POST: http://localhost:8000/api/wallet  Body:{email,label,password,private_key}
1. GET: http://localhost:8000/api/wallet/{wallet_id}
1. GET: http://localhost:8000/api/wallet/{wallet_id}/address/{address}/balance

## Tests
1. vendor/bin/phpunit

## To Do
1. Add custom make file for easy terminal commands
1. Add JSON-specific exception handling
1. Fix Deprecation warning: require.Blockchain/Blockchain is invalid.
1. Add JSON API spec
