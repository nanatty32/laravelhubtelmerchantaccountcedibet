<?php

namespace Nanatty32\HubtelMerchantAccount\Helpers;

use GuzzleHttp\Client;
use Nanatty32\HubtelMerchantAccount\MobileMoney\Refund\Request as RefundRequest;
use Nanatty32\HubtelMerchantAccount\MobileMoney\Receive\Request as ReceiveMobileMoneyRequest;
use Nanatty32\HubtelMerchantAccount\OnlineCheckout\Request as OnlineCheckoutRequest;
use Nanatty32\HubtelMerchantAccount\OnlineCheckout\Response as OnlineCheckoutResponse;

class SendsRequests
{
    use FormatsRequests;

    protected $http;

    protected $config;

    private $auth;

    public function __construct($config)
    {
        $this->http = new Client(['base_uri' => 'https://api.hubtel.com']);
        $this->config = $config;
        $this->auth = [$this->config['api_key']['client_id'], $this->config['api_key']['client_secret']];
    }

    /**
     * @param ReceiveMobileMoneyRequest $request
     * @return mixed Actual response body from gateway
     */
    public function sendReceiveMobileMoneyRequest(ReceiveMobileMoneyRequest $request)
    {
        $response = $this->http->request('POST', "/v1/merchantaccount/merchants/{$this->config['account_number']}/receive/mobilemoney", [
            'headers'=>[
                'Content-type' => 'application/json'
            ],
            'body' => $this->toJson($request),
            'auth' => $this->auth
        ]);

        $this->checkResponseStatus($response);

        return $response->getBody();
    }

    /**
     * @param OnlineCheckoutRequest $request
     * @return OnlineCheckoutResponse
     */
    public function sendOnlineCheckoutRequest(OnlineCheckoutRequest $request)
    {
        if (!$request->business->name) {
            $request->business->name = $this->config['business']['name'];
        }

        $response = $this->http->request('POST', "/v1/merchantaccount/onlinecheckout/invoice/create", [
            'json' => json_decode(json_encode($request), true),
            'auth' => $this->auth
        ]);

        $this->checkResponseStatus($response);

        $invoiceResponse = json_decode((string)$response->getBody());

        return $invoiceResponse->response_text;
    }

    /**
     * @param $token
     * @return mixed
     */
    public function sendCheckInvoiceStatusRequest($token)
    {
        $response = $this->http->request('GET', "/v1/merchantaccount/onlinecheckout/invoice/status/{$token}");

        $this->checkResponseStatus($response);

        return json_decode((string)$response->getBody());
    }

    /**
     * @param RefundRequest $request
     * @return mixed Actual gateway response
     */
    public function sendRefundMobileMoneyRequest(RefundRequest $request)
    {
        $response = $this->http->request('POST', "/v1/merchantaccount/merchants/{$this->config['account_number']}/transactions/refund", [
            'headers'=>[
                'Content-type' => 'application/json'
            ],
            'body' => $this->toJson($request),
            'auth' => $this->auth
        ]);
        $this->checkResponseStatus($response);
        return $response->getBody();
    }

    private function checkResponseStatus($response)
    {
        if ($response->getStatusCode() !== 200) {
            throw new \Exception((string)$response->getBody());
        }
        return $this;
    }
}
