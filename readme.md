
### Blockchain Explorer App
Implements the following:
1. Queries blockchain for crypto data
1. Creates a crypto wallet and transacts
1. PHPUnit tests
1. JWT authentication
1. Laravel JSON API (LJA)
1. MYSQL DB

## Outcome
1. I created a wallet and used the pass phrase and wallet ID to login on Blockchain.com.

## Set up
1. Clone repo and run composer install
1. php artisan migrate to run migrations
1. php artisan serve to start the app
1. Run wallet service locally (https://github.com/blockchain/service-my-wallet-v3 and expose service url)

## Available endpoints
1. POST: http://localhost:8000/api/register  [name,email,password]
1. POST: http://localhost:8000/api/login [email,password]
1. GET: http://localhost:8000/api/coins [fetch list of crypto currencies from Coin Gecko]
1. GET: http://localhost:8000/api/coins/ethereum  [Get coin data and ticker data]
1. POST: http://localhost:8000/api/wallet  [email,label,pass_phrase]
1. GET: http://localhost:8000/api/wallet/39997efa-c2ab-4e55-85ba-6a190a77wxyz  [39997efa-c2ab-4e55-85ba-6a190a77wxyz is the wallet ID]
1. GET: http://localhost:8000/api/address/1Ct1qTc3eUhLJSC7arRbG7Aq6qqXRCKCFN/balance

## Tests
1. vendor/bin/phpunit

## To Do
1. Add custom make file for easy terminal commands
1. Add JSON-specific exception handling
1. Fix Deprecation warning: require.Blockchain/Blockchain is invalid.
1. Add JSON API spec
