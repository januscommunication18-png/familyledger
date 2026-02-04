<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Approved - Family Ledger</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-lg w-full">
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-t-xl p-6 text-center">
            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white">Access Approved</h1>
        </div>

        <div class="bg-white rounded-b-xl shadow-lg p-6 text-center">
            <p class="text-gray-700 mb-4">
                You have successfully approved the data access request.
            </p>

            <div class="bg-green-50 border border-green-200 p-4 rounded mb-6">
                <p class="text-sm text-green-800">
                    <strong>{{ $request->admin->name }}</strong> now has access to view your account data until <strong>{{ $request->access_expires_at->format('M j, Y g:i A') }}</strong>.
                </p>
            </div>

            <div class="bg-gray-50 p-4 rounded mb-6">
                <p class="text-sm text-gray-600">
                    The administrator will only be able to view your account information to assist with your support request.
                    Access will automatically expire after the specified duration.
                </p>
            </div>

            <p class="text-sm text-gray-500">
                If you have any concerns, please contact our support team immediately.
            </p>

            <div class="mt-6 pt-4 border-t text-sm text-gray-500">
                <p>Approved by: {{ $request->approved_by_email }}</p>
                <p>Request ID: #{{ $request->id }}</p>
            </div>
        </div>
    </div>
</body>
</html>
