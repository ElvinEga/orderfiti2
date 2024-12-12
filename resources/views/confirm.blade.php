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

<body class="flex items-center justify-center min-h-screen">
<div class="bg-white shadow-lg rounded-lg p-8 max-w-lg w-full">
    <div class="text-center mb-8">
        <img src="{{ $logo->logo }}" alt="Company Logo" class="w-24 mx-auto mb-4">
    </div>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 border border-red-400 rounded p-4 mb-4">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 text-red-700 border border-red-400 rounded p-4 mb-4">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="container mx-auto mt-16 p-6 max-w-lg bg-white shadow-lg rounded-lg">
        <div class="loader mx-auto w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
        <h1 class="text-center text-2xl font-semibold text-gray-800 mt-6">Waiting for Payment Confirmation</h1>
        <p class="text-center text-gray-600 mt-4">Please complete the payment on your M-Pesa app.</p>
        <p class="text-center text-gray-600 mt-2">We are processing your transaction. This might take a few moments.</p>
        <div class="tips bg-blue-50 p-4 mt-6 rounded-lg">
            <strong class="block text-gray-700 mb-2">Tips:</strong>
            <ul class="list-disc list-inside text-gray-600">
                <li>Ensure your phone is unlocked.</li>
                <li>Follow the M-Pesa prompt to complete the payment.</li>
                <li>If you do not see a prompt, check your M-Pesa messages or try again.</li>
            </ul>
        </div>
    </div>

    <h1 class="text-2xl font-semibold text-gray-800">Pay Manually </h1>
    <p class="text-gray-600 mt-2 mb-6">Enter the verification code sent to your M-Pesa number</p>

    <form id="confirmPaymentForm" method="POST" action="{{ route('payment.confirm', ['order' => $order]) }}">
        @csrf
        <div class="mb-6">
            <label for="verification_code" class="block text-gray-700 font-medium mb-2">Verification Code</label>
            <input type="text" id="verification_code" name="verification_code" placeholder="Enter verification code"
                   class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            <p class="text-sm text-gray-500 mt-2">
                Check your M-Pesa prompt for the verification code.
            </p>
        </div>

        <div class="mb-6">
            <p class="text-gray-700 font-medium">Amount to Pay: <span class="font-bold">KES {{ number_format($order->total) }}</span></p>
        </div>

        <button type="submit"
                class="w-full py-3 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
            Confirm Payment
        </button>
    </form>

    <div class="text-center mt-6">
        <a href="{{ route('home') }}" class="text-blue-500 hover:underline">Payment Done</a>
    </div>
</div>
</body>

</html>
