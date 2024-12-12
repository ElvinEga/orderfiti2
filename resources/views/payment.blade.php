<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $company['company_name'] }}</title>
    <link rel="icon" href="{{ $faviconLogo->faviconLogo }}">
    <link rel="stylesheet" href="{{ asset('themes/default/css/style.css') }}">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700;800;900&display=swap">
    <link rel="stylesheet" href="{{ asset('themes/default/fonts/lab/lab.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/default/css/custom.css') }}">
</head>

<body>
    <div class="py-14 px-4 w-full max-w-3xl mx-auto">
        <a href="{{ route('home') }}" class="block mx-auto w-36 mb-8">
            <img class="w-full" src="{{ $logo->logo }}" alt="logo">
        </a>
        <h3 class="text-[22px] text-center font-medium leading-[34px] mb-6">
            {{ __('all.message.select_your_payment_method') }}</h3>

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mb-5 rounded relative"
                    role="alert">
                    <span class="block sm:inline">{{ $error }}</span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer close-button">
                        <i class="lab lab-close-circle-line margin-top-5-px"></i>
                    </span>
                </div>
            @endforeach
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mb-5 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer close-button">
                    <i class="lab lab-close-circle-line margin-top-5-px"></i>
                </span>
            </div>
        @endif

        <form id="paymentForm" method="POST" action="{{ route('payment.store', ['order' => $order]) }}">
            @csrf
            <fieldset class="payment-fieldset">
                @if (!blank($paymentGateways))
                    @foreach ($paymentGateways as $paymentGateway)
                        @if (!$credit && $paymentGateway->slug === 'credit')
                            @continue
                        @endif
                        <label class="payment-label" for="{{ $paymentGateway->slug }}">
                            <input class="paymentMethod" id="{{ $paymentGateway->slug }}" type="radio"
                                name="paymentMethod" value="{{ $paymentGateway->slug }}"
                                @if (old('paymentMethod') == $paymentGateway->slug) checked @endif>
                            <img src="{{ $paymentGateway->image }}" alt="payment">
                            <span>{{ $paymentGateway->name }}</span>
                            @if ($paymentGateway->slug === 'credit')
                                <span>
                                    {{ $creditAmount }}
                                </span>
                            @endif
                        </label>
                    @endforeach
                @endif
            </fieldset>

            @if (!blank($paymentGateways))
                @foreach ($paymentGateways as $paymentGateway)
                    @if ($paymentGateway->misc !== null)
                        @if (!blank(json_decode($paymentGateway->misc)))
                            @if (!blank(json_decode($paymentGateway->misc)->input))
                                @foreach (json_decode($paymentGateway->misc)->input as $input)
                                    @include('paymentGateways.' . str_replace('.blade.php', '', $input))
                                @endforeach
                            @endif
                        @endif
                    @endif
                @endforeach
            @endif

            <!-- Phone Number Input Field -->
            <div id="phoneNumberField" style="display: none; margin-top: 20px;">
                <h4 style="margin-bottom: 10px; font-size: 18px; font-weight: bold; color: #333;">
                    {{ __('M-Pesa Payment Details') }}
                </h4>
                <p style="margin-bottom: 5px; font-size: 16px; color: #555;">
                    <strong>{{ __('Amount to Pay:') }}</strong> KES {{ number_format($order->amount, 2) }}
                </p>
                <label for="phone_number">{{ __('Enter Phone Number') }}</label>
                <input
                    type="text"
                    id="phone_number"
                    name="phone_number"
                    placeholder="2547XXXXXXXX"
                    pattern="254[0-9]{9}"
                    class="w-full py-2 px-4 border rounded"
                />
                <p style="margin-bottom: 10px; font-size: 14px; color: #777;">
                    {{ __('To complete your payment, follow these steps:') }}
                </p>
                <ul style="padding-left: 20px; font-size: 14px; color: #777; list-style: disc;">
                    <li>{{ __('Ensure your phone is switched on and has enough charge.') }}</li>
                    <li>{{ __('An M-Pesa STK Push will be sent to the phone number you provide.') }}</li>
                    <li>{{ __('Check your phone for an M-Pesa prompt and enter your M-Pesa PIN to confirm the payment.') }}</li>
                    <li>{{ __('Once confirmed, the payment will automatically be processed.') }}</li>
                </ul>
                <p style="margin-top: 10px; font-size: 14px; font-weight: bold; color: #555;">
                    {{ __('If you do not receive the STK prompt, please ensure your phone number is correct or try again.') }}
                </p>
            </div>
            @if (!blank($paymentGateways))
                <button type="submit"
                    class="py-3 w-full rounded-3xl text-center text-base font-medium bg-primary text-white"
                    id="confirmBtn">
                    {{ __('all.label.confirm') }}
                </button>
            @endif

            <div class="py-5 px-4 w-full max-w-3xl mx-auto flex flex-col items-center justify-center">
                <a class="text-primary" href="{{ route('home') }}">{{ __('all.label.back_to_home') }}</a>
            </div>
        </form>

    </div>

    @php
        $jsGateway = [];
        $submitGateway = [];
    @endphp
    @if (!blank($paymentGateways))
        @foreach ($paymentGateways as $paymentGateway)
            @if ($paymentGateway->misc != null)
                @if (!blank(json_decode($paymentGateway->misc)))
                    @if (!blank(json_decode($paymentGateway->misc)->js))
                        @foreach (json_decode($paymentGateway->misc)->js as $js)
                            @include('paymentGateways.' . str_replace('.blade.php', '', $js))
                        @endforeach
                    @endif
                    @if (!blank(json_decode($paymentGateway->misc)->input))
                        @if (isset(json_decode($paymentGateway->misc)->input[0]))
                            @php $jsGateway[$paymentGateway->slug] = isset(json_decode($paymentGateway->misc)->input[0]); @endphp
                        @endif
                    @endif
                    @if (!blank(json_decode($paymentGateway->misc)->submit))
                        @if (isset(json_decode($paymentGateway->misc)->submit) && json_decode($paymentGateway->misc)->submit)
                            @php $submitGateway[$paymentGateway->slug] = json_decode($paymentGateway->misc)->submit; @endphp
                        @endif
                    @endif
                @endif
            @endif
        @endforeach
    @endif
    @php
        $jsGateway = json_encode($jsGateway);
        $submitGateway = json_encode($submitGateway);
    @endphp

    <script src="{{ asset('lib/jquery-v3.2.1.min.js') }}"></script>
    <script>
        const gateway = <?= $jsGateway ?>;
        const submitGateway = <?= $submitGateway ?>;
    </script>
    <script src="{{ asset('paymentGateways/payment.js') }}"></script>
    <script>
        // JavaScript to toggle the phone number input field
        document.querySelectorAll('.paymentMethod').forEach((input) => {
            input.addEventListener('change', function () {
                const phoneNumberField = document.getElementById('phoneNumberField');
                if (this.value === 'mpesa') { // Assuming 'mpesa' is the slug for M-Pesa
                    phoneNumberField.style.display = 'block';
                } else {
                    phoneNumberField.style.display = 'none';
                }
            });
        });

        // Initialize field based on pre-selected payment method
        window.addEventListener('DOMContentLoaded', () => {
            const selectedPaymentMethod = document.querySelector('.paymentMethod:checked');
            if (selectedPaymentMethod && selectedPaymentMethod.value === 'mpesa') {
                document.getElementById('phoneNumberField').style.display = 'block';
            }
        });
    </script>
</body>

</html>
