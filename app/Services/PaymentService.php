<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Balance;
use App\Models\Transaction;
use App\Models\User;

class PaymentService
{
    public function payment($order, $gatewaySlug, $transactionNo)
    {
        $transaction = Transaction::where(['order_id' => $order->id])->first();
        if (!$transaction) {
            $transaction = Transaction::create([
                'order_id'       => $order->id,
                'transaction_no' => $transactionNo,
                'amount'         => $order->total,
                'payment_method' => $gatewaySlug,
                'sign'           => '+',
                'type'           => 'payment'
            ]);
        }

        $user = User::find($order->user_id);
        if ($user) {
            // Check if a balance record exists for the user and branch
            $balance = Balance::where('user_id', $user->id)
                ->where('order_id', $order->id)
                ->first();

            if ($balance) {
                // Update the balance
                $balance->balance = 0;
                $balance->save();
            }


            // Set the user's balance to 0
            $user->balance = 0;
            $user->save();
        }

        $order->payment_status = PaymentStatus::PAID;
        $order->save();


        return $transaction;
    }

    public function cashBack($order, $gatewaySlug, $transactionNo)
    {
        $transaction = Transaction::where(['order_id' => $order->id])->first();
        if ($transaction) {
            $transaction = Transaction::create([
                'order_id'       => $order->id,
                'transaction_no' => $transactionNo,
                'amount'         => $order->total,
                'payment_method' => $gatewaySlug,
                'sign'           => '-',
                'type'           => 'cash_back'
            ]);

            $user = User::find($order->user_id);
            if ($user) {
                // Check if a balance record exists for the user and branch
                $balance = Balance::where('user_id', $user->id)
                    ->where('order_id', $order->id)
                    ->first();

                if ($balance) {
                    // Update the balance
                    $balance->balance += $order->total;
                    $balance->save();
                }



                // Optionally, update the user's balance if needed
                $user->balance = ($user->balance + $order->total);
                $user->save();
            }
        }

        return $transaction;
    }
}
