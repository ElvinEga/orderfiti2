<?php

namespace App\Http\PaymentGateways\Requests;

use App\Enums\Activity;
use Illuminate\Foundation\Http\FormRequest;

class Mpesa extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        if (request()->mpesa_status == Activity::ENABLE) {
            return [
                'mpesa_consumer_key'  => ['required', 'string'],
                'mpesa_consumer_secret'  => ['required', 'string'],
                'mpesa_short_code' => ['required', 'string'],
                'mpesa_passkey'        => ['required', 'string'],
                'mpesa_callback_url' => ['required', 'string'],
                'mpesa_status'      => ['nullable', 'numeric'],
            ];
        } else {
            return [
                'mpesa_consumer_key'  => ['nullable', 'string'],
                'mpesa_consumer_secret'  => ['nullable', 'string'],
                'mpesa_short_code' => ['nullable', 'string'],
                'mpesa_passkey'        => ['nullable', 'string'],
                'mpesa_callback_url' => ['nullable', 'string'],
                'mpesa_status'      => ['nullable', 'numeric'],
            ];
        }
    }
}
