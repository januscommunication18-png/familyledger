<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - Family Ledger</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-lg w-full">
        <div class="bg-gradient-to-r from-red-500 to-rose-600 rounded-t-xl p-6 text-center">
            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white">Access Denied</h1>
        </div>

        <div class="bg-white rounded-b-xl shadow-lg p-6 text-center">
            <p class="text-gray-700 mb-4">
                You have denied the data access request.
            </p>

            <div class="bg-red-50 border border-red-200 p-4 rounded mb-6">
                <p class="text-sm text-red-800">
                    <strong>{{ $request->admin->name }}</strong> will not have access to view your account data.
                </p>
            </div>

            @if($request->denial_reason)
                <div class="bg-gray-50 p-4 rounded mb-6">
                    <p class="text-sm text-gray-600">
                        <strong>Your reason:</strong> {{ $request->denial_reason }}
                    </p>
                </div>
            @endif

            <p class="text-sm text-gray-500">
                If you need support assistance in the future, a new access request will need to be submitted.
            </p>

            <div class="mt-6 pt-4 border-t text-sm text-gray-500">
                <p>Request ID: #{{ $request->id }}</p>
            </div>
        </div>
    </div>
</body>
</html>
