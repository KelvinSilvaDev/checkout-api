<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PagamentoSimuladoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/checkout', [CheckoutController::class, 'processCheckout']);
Route::get('/address/{cep}', [CheckoutController::class, 'getAddressByCep']);



// Rota para pagamento com cartão de crédito
Route::post('/pagamento/cartao-credito', [PagamentoSimuladoController::class, 'pagamentoCartaoCredito']);

// Rota para pagamento via PIX
Route::post('/pagamento/pix', [PagamentoSimuladoController::class, 'pagamentoPIX']);

// Rota para geração de boleto
Route::post('/pagamento/boleto', [PagamentoSimuladoController::class, 'gerarBoleto']);


// Route::post('/register', [RegisteredUserController::class, 'store'])
//     ->middleware('guest')
//     ->name('register');
