<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class MoMoController extends Controller
{
    private $baseUrl = "https://sandbox.momodeveloper.mtn.com";

    // Sandbox primary keys
    private $collectionKey = "428df6af085d4fbc97e9eb3903152d0c";
    private $disbursementKey = "5382f31ee1d94a05b6595b2745d2c036";

    private $apiUser;
    private $apiKey;
    private $token;

    /** @var \GuzzleHttp\Client */
    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    // ---------------------------------------------------------
    // STEP 1 — Create API USER (sandbox only)
    // ---------------------------------------------------------
    private function createApiUser()
    {
        $this->apiUser = (string) Str::uuid();

        $url = $this->baseUrl . "/v1_0/apiuser";

        $headers = [
            "X-Reference-Id" => $this->apiUser,
            "Ocp-Apim-Subscription-Key" => $this->collectionKey,
            "Content-Type" => "application/json",
        ];

        $body = [
            "providerCallbackHost" => "webhook.site"
        ];

        $response = $this->client->post($url, [
            'headers' => $headers,
            'json' => $body
        ]);

        if ($response->getStatusCode() !== 201) {
            throw new \Exception("Failed to create API User");
        }

        return $this->apiUser;
    }

    // ---------------------------------------------------------
    // STEP 2 — Create API KEY (sandbox only)
    // ---------------------------------------------------------
    private function createApiKey()
    {
        $url = $this->baseUrl . "/v1_0/apiuser/{$this->apiUser}/apikey";

        $headers = [
            "Ocp-Apim-Subscription-Key" => $this->collectionKey,
        ];

        $response = $this->client->post($url, [
            'headers' => $headers
        ]);

        if ($response->getStatusCode() !== 201) {
            throw new \Exception("Failed to generate API Key");
        }

        $data = json_decode($response->getBody(), true);
        $this->apiKey = $data['apiKey'];

        return $this->apiKey;
    }

    // ---------------------------------------------------------
    // STEP 3 — Get ACCESS TOKEN
    // ---------------------------------------------------------
    private function getToken()
    {
        $url = $this->baseUrl . "/collection/token/";

        $response = $this->client->post($url, [
            'auth' => [$this->apiUser, $this->apiKey],
            'headers' => [
                "Ocp-Apim-Subscription-Key" => $this->collectionKey
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        $this->token = $data['access_token'];

        return $this->token;
    }

    // ---------------------------------------------------------
    // STEP 4 — Request-to-Pay (mobile money debit)
    // ---------------------------------------------------------
    private function requestPayment($msisdn, $amount)
    {
        $referenceId = (string) Str::uuid();

        $url = $this->baseUrl . "/collection/v1_0/requesttopay";

        $body = [
            "amount" => (string)$amount,
            "currency" => "EUR",
            "externalId" => "order-" . uniqid(),
            "payer" => [
                "partyIdType" => "MSISDN",
                "partyId" => $msisdn
            ],
            "payerMessage" => "Payment for ObpPay deposit",
            "payeeNote" => "ObpPay deposit"
        ];

        $headers = [
            "Authorization" => "Bearer {$this->token}",
            "X-Reference-Id" => $referenceId,
            "X-Target-Environment" => "sandbox",
            "Ocp-Apim-Subscription-Key" => $this->collectionKey,
            "Content-Type" => "application/json"
        ];

        $response = $this->client->post($url, [
            'headers' => $headers,
            'json' => $body
        ]);

        if ($response->getStatusCode() !== 202) {
            throw new \Exception("MoMo request failed");
        }

        return $referenceId;
    }

    // ---------------------------------------------------------
    // STEP 5 — Check payment status
    // ---------------------------------------------------------
    private function checkDebitStatus($referenceId)
    {
        $url = $this->baseUrl . "/collection/v1_0/requesttopay/{$referenceId}";

        $headers = [
            "Authorization" => "Bearer {$this->token}",
            "X-Target-Environment" => "sandbox",
            "Ocp-Apim-Subscription-Key" => $this->collectionKey
        ];

        $response = $this->client->get($url, [
            'headers' => $headers
        ]);

        return json_decode($response->getBody(), true);
    }

    // ---------------------------------------------------------
    // FINAL — MOBILE API ENDPOINT
    // ---------------------------------------------------------
    public function mobilePay(Request $request)
    {
        $request->validate([
            'msisdn' => 'required',
            'amount' => 'required|numeric|min:1'
        ]);

        $user = $request->user();
        $wallet = $user->wallet;

        try {
            // 1) Create user
            $apiUser = $this->createApiUser();

            // 2) Create API key
            $apiKey = $this->createApiKey();

            // 3) Token
            $token = $this->getToken();

            // 4) Request to pay
            $referenceId = $this->requestPayment($request->msisdn, $request->amount);

            // 5) Check status (MoMo sandbox delay)
            sleep(2);
            $status = $this->checkDebitStatus($referenceId);

            // Extract clean status
            $statusValue = $status['status'] ?? 'UNKNOWN';

            // -----------------------------------------------------
            // (1) ALWAYS LOG TRANSACTION (Success / Failed / Pending)
            // -----------------------------------------------------
            $transaction = \App\Models\Transactions::create([
                'user_id'     => $user->id,
                'type'        => 'deposit',
                'amount'      => $request->amount,
                'currency'    => $wallet->currency,
                'description' => 'Dépôt MTN MoMo',
                'status'      => strtolower($statusValue),
                'meta'        => [
                    'referenceId' => $referenceId,
                    'msisdn'      => $request->msisdn,
                    'apiUser'     => $apiUser,
                    'apiKey'      => $apiKey,
                    'response'    => $status
                ],
            ]);

            $credited = false;
            $newBalance = $wallet->balance;

            // -----------------------------------------------------
            // (2) CREDIT USER ONLY IF SUCCESSFUL
            // -----------------------------------------------------
            if ($statusValue === "SUCCESSFUL") {
                DB::transaction(function () use ($wallet, $user, $request, &$newBalance) {
                    $wallet->increment('balance', $request->amount);
                    $newBalance = $wallet->fresh()->balance;

                    add_score($user, 3, "deposit");
                });

                $credited = true;
            }

            // -----------------------------------------------------
            // (3) Return full structured JSON for Flutter
            // -----------------------------------------------------
            return response()->json([
                "success"      => true,
                "credited"     => $credited,
                "referenceId"  => $referenceId,
                "status"       => $statusValue,
                "amount"       => $request->amount,
                "new_balance"  => $newBalance,
                "transaction"  => $transaction,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "error"   => $e->getMessage()
            ], 500);
        }
    }
}
