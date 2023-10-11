<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


class CheckoutController extends Controller
{
    public function processCheckout(Request $request)
    {
        // Defina as regras de validação em um array
        $rules = [
            'nome_completo' => 'required|string|max:255',
            'email' => 'required|email',
            'telefone' => 'required|string|max:20',
            'cep' => 'required|string|max:10',
            'endereco' => 'required|string|max:255',
            'numero' => 'required|string|max:20',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'estado' => 'required|string|max:2',
            'metodo_pagamento' => 'required|in:cartao_credito,pix,boleto',
        ];

        // Verifique o método de pagamento selecionado no campo "metodo_pagamento" do pedido.
        $metodoPagamento = $request->input('metodo_pagamento');
        $mensagemPagamento = '';

        if ($metodoPagamento === 'cartao_credito') {
            // Simule o processamento de cartão de crédito
            $mensagemPagamento = 'Pagamento com cartão de crédito processado com sucesso';
        } elseif ($metodoPagamento === 'pix') {
            // Simule o processamento de PIX
            $mensagemPagamento = 'Pagamento via PIX processado com sucesso';
        } elseif ($metodoPagamento === 'boleto') {
            // Simule o processamento de boleto
            $mensagemPagamento = 'Boleto gerado com sucesso';
        }

        // Registre o evento de pagamento
        $this->logPaymentEvent($metodoPagamento);


        // Adicione regras condicionais para 'numero_cartao' e 'cpf' apenas se 'metodo_pagamento' for 'cartao_credito' ou 'pix'
        if (in_array($metodoPagamento, ['cartao_credito', 'pix'])) {
            $rules['numero_cartao'] = 'required_if:metodo_pagamento,cartao_credito';
            $rules['cpf'] = 'required_if:metodo_pagamento,pix';
        }

        // Valide os dados do formulário usando as regras definidas
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            // Se a validação falhar, retorne os erros em formato JSON
            return response()->json(['errors' => $validator->errors()], 400);
        }



        // Verifique o método de pagamento selecionado no campo "metodo_pagamento" do pedido.
        $metodoPagamento = $request->input('metodo_pagamento');

        if ($metodoPagamento === 'cartao_credito') {
            // Se o método de pagamento for "cartao_credito", você pode realizar verificações adicionais aqui
            // Por exemplo, verifique se o número do cartão de crédito é válido
            $numeroCartao = $request->input('numero_cartao');

            // Implemente sua lógica de validação para números de cartão de crédito aqui

            if (!$this->isValidCreditCardNumber($numeroCartao)) {
                return response()->json(['error' => 'Número de cartão de crédito inválido'], 400);
            }
        } elseif ($metodoPagamento === 'pix') {
            // Se o método de pagamento for "pix", você pode realizar verificações adicionais aqui
            // Por exemplo, verifique se o CPF é válido
            $cpf = $request->input('cpf');

            // Implemente sua lógica de validação para CPF aqui

            if (!$this->isValidCPF($cpf)) {
                return response()->json(['error' => 'CPF inválido'], 400);
            }
        }

        // Se o método de pagamento for "boleto", não é necessário fazer nenhuma verificação

        // Agora você pode prosseguir com o processamento do checkout, chamadas à API do ViaCEP, processamento de pagamento, etc.

        // Retorne uma resposta JSON com o resultado do checkout
        return response()->json(['message' => $mensagemPagamento]);
    }
    private function logPaymentEvent($metodoPagamento)
    {
        // Registre o evento de pagamento no log ou console
        $mensagem = "Evento de pagamento: {$metodoPagamento} - Checkout processado com sucesso";
        Log::info($mensagem);
    }



    public function getAddressByCep($cep)
    {
        $url = "https://viacep.com.br/ws/{$cep}/json/";

        $client = new \GuzzleHttp\Client([
            'verify' => false,
        ]);
        $response = $client->get($url);
        $data = json_decode($response->getBody());

        return response()->json($data);
    }


    private function isValidCreditCardNumber($number)
    {
        // Remova espaços em branco e caracteres não numéricos do número do cartão
        $number = preg_replace('/\D/', '', $number);

        // Verifique se o número do cartão passa no algoritmo de Luhn
        if (!preg_match('/^[0-9]{13,19}$/', $number)) {
            return false;
        }

        $sum = 0;
        $length = strlen($number);

        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = (int)$number[$i];

            if ($i % 2 === $length % 2) {
                $digit *= 2;

                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return $sum % 10 === 0;
    }


    private function isValidCPF($cpf)
    {
        // Implemente a lógica de validação do CPF aqui
        // Você pode usar bibliotecas de terceiros para essa validação ou criar sua própria lógica
        // Por enquanto, retornaremos true para fins de exemplo
        return true;
    }

    private function getCreditCardBrand($number)
    {
        if (preg_match('/^4[0-9]{15}$/', $number)) {
            return 'Visa';
        } elseif (preg_match('/^5[1-5][0-9]{14}$/', $number)) {
            return 'Mastercard';
        } elseif (preg_match('/^3[47][0-9]{13}$/', $number)) {
            return 'Amex';
        } elseif (preg_match('/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/', $number)) {
            return 'Diners';
        } elseif (preg_match('/^(50|5[6-9]|6007|6220|6304|6703|6708|6759|676[1-3])/', $number)) {
            return 'Mastercard';
        } elseif (preg_match('/^606282|^3841(?:[0|4|6]{1})0/', $number)) {
            return 'Hipercard';
        } elseif (preg_match('/^4011(78|79)|^43(1274|8935)|^45(1416|7393|763(1|2))|^50(4175|6699|67[0-6][0-9]|677[0-8]|9[0-8][0-9]{2}|99[0-8][0-9]|999[0-9])|^627780|^63(6297|6368|6369)|^65(0(0(3([1-3]|[5-9])|4([0-9])|5[0-1])|4(0[5-9]|[1-3][0-9]|8[5-9]|9[0-9])|5([0-2][0-9]|3[0-8]|4[1-9]|[5-8][0-9]|9[0-8])|7(0[0-9]|1[0-8]|2[0-7])|9(0[1-9]|[1-6][0-9]|7[0-8]))|16(5[2-9]|[6-7][0-9])|50(0[0-9]|1[0-9]|2[1-9]|[3-4][0-9]|5[0-8]))/', $number)) {
            return 'Elo';
        } else {
            return 'Desconhecida';
        }
    }
}
