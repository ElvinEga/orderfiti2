<?php

namespace App\Http\PaymentGateways\Gateways;

use Exception;
use App\Enums\Activity;
use App\Models\Currency;
use App\Models\PaymentGateway;
use App\Services\PaymentService;
use App\Services\PaymentAbstract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Smartisan\Settings\Facades\Settings;
use Illuminate\Support\Facades\Http;

class Mpesa extends PaymentAbstract
{
    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $paymentService = new PaymentService();
        parent::__construct($paymentService);
        $this->paymentGateway = PaymentGateway::with('gatewayOptions')->where(['slug' => 'mpesa'])->first();
        if (!blank($this->paymentGateway)) {
            $this->paymentGatewayOption = $this->paymentGateway->gatewayOptions->pluck('value', 'option');
            Config::set([
                'mpesa' => [
                    'consumer_key' => $this->paymentGatewayOption['mpesa_consumer_key'],
                    'consumer_secret' => $this->paymentGatewayOption['mpesa_consumer_secret'],
                    'short_code' => $this->paymentGatewayOption['mpesa_short_code'],
                    'passkey' => $this->paymentGatewayOption['mpesa_passkey'],
                    'callback_url' => $this->paymentGatewayOption['mpesa_callback_url'],
                ]
            ]);
        }
    }

    public function payment($order, $request)
    {
        try {
            $currencyCode = 'KES';
            $currencyId = Settings::group('site')->get('site_default_currency');
            if (!blank($currencyId)) {
                $currency = Currency::find($currencyId);
                if ($currency) {
                    $currencyCode = $currency->code;
                }
            }

            // Generate Access Token
//            $response = Http::withBasicAuth(
//                Config::get('mpesa.consumer_key'),
//                Config::get('mpesa.consumer_secret')
//            )->post('https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');

            $response = Http::withBasicAuth(
                Config::get('mpesa.consumer_key'),
                Config::get('mpesa.consumer_secret'))
                ->withHeaders(['Content-Type' => 'application/json; charset=utf8'])
                ->get('https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');



            if ($response->failed()) {
                throw new Exception('Failed to generate access token');
            }

            $accessToken = $response->json()['access_token'];

            // Make Payment Request
            $timestamp = now()->format('YmdHis');
            $password = base64_encode(Config::get('mpesa.short_code') . Config::get('mpesa.passkey') . $timestamp);

            $data = [
                "BusinessShortCode" => Config::get('mpesa.short_code'),
                "Password" => $password,
                "Timestamp" => $timestamp,
                "TransactionType" => "CustomerPayBillOnline",
                "Amount" => number_format($order->total),
                "PartyA" => $request->phone_number,
                "PartyB" => Config::get('mpesa.short_code'),
                "PhoneNumber" => $request->phone_number,
//                "CallBackURL" => Config::get('mpesa.callback_url'),
                "CallBackURL" => route('payment.success', ['order' => $order, 'paymentGateway' => 'mpesa']),
                "AccountReference" => $order->order_serial_no,
                "TransactionDesc" => "Payment for Order #{$order->order_serial_no}"
            ];

            $paymentResponse = Http::withToken($accessToken)->post('https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest', $data);

            if ($paymentResponse->failed()) {
                throw new Exception('Payment request failed: ' . $paymentResponse->body());
            }

            return redirect()->route('payment.index', ['order' => $order, 'paymentGateway' => 'mpesa'])
                ->with('success', 'Payment request sent. Complete payment on your phone.');
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return redirect()->route('payment.index', [
                'order' => $order,
                'paymentGateway' => 'mpesa'
            ])->with('error', $e->getMessage());
        }
    }

    public function status(): bool
    {
        $paymentGateways = PaymentGateway::where(['slug' => 'mpesa', 'status' => Activity::ENABLE])->first();
        if ($paymentGateways) {
            return true;
        }
        return false;
    }

    public function success($order, $request): \Illuminate\Http\RedirectResponse
    {
        try {
            // Handle M-Pesa callback here.
            $response = $request->all();

            if (isset($response['Body']['stkCallback']['ResultCode']) && $response['Body']['stkCallback']['ResultCode'] == 0) {
                $this->paymentService->payment($order, 'mpesa', $response['Body']['stkCallback']['CheckoutRequestID']);
                return redirect()->route('payment.successful', ['order' => $order])->with('success', trans('all.message.payment_successful'));
            } else {
                return redirect()->route('payment.fail', [
                    'order' => $order,
                    'paymentGateway' => 'mpesa'
                ])->with('error', trans('all.message.something_wrong'));
            }
        } catch (Exception $e) {
            Log::info($e->getMessage());
            DB::rollBack();
            return redirect()->route('payment.fail', [
                'order' => $order,
                'paymentGateway' => 'mpesa'
            ])->with('error', $e->getMessage());
        }
    }

    public function fail($order, $request): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('payment.index', ['order' => $order])->with('error', trans('all.message.something_wrong'));
    }

    public function cancel($order, $request): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('home')->with('error', trans('all.message.payment_canceled'));
    }
}
