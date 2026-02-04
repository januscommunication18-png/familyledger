<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Expired - Family Ledger</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-lg w-full">
        <div class="bg-gradient-to-r from-gray-500 to-gray-600 rounded-t-xl p-6 text-center">
            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white">Request Expired</h1>
        </div>

        <div class="bg-white rounded-b-xl shadow-lg p-6 text-center">
            <p class="text-gray-700 mb-4">
                This data access request has expired.
            </p>

            <div class="bg-gray-50 border border-gray-200 p-4 rounded mb-6">
                <p class="text-sm text-gray-600">
                    The request was valid until <strong>{{ $request->expires_at->format('M j, Y g:i A') }}</strong> but was not actioned in time.
                </p>
            </div>

            <p class="text-sm text-gray-500">
                If you still need support assistance, please contact our support team and a new request can be submitted.
            </p>

            <div class="mt-6 pt-4 border-t text-sm text-gray-500">
                <p>Request ID: #{{ $request->id }}</p>
            </div>
        </div>
    </div>
</body>
</html>
