<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Already Processed - Family Ledger</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-lg w-full">
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-t-xl p-6 text-center">
            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white">Request Already Processed</h1>
        </div>

        <div class="bg-white rounded-b-xl shadow-lg p-6 text-center">
            <p class="text-gray-700 mb-4">
                This data access request has already been processed.
            </p>

            <div class="bg-blue-50 border border-blue-200 p-4 rounded mb-6">
                @if($request->status === 'approved')
                    <p class="text-sm text-blue-800">
                        <strong>Status:</strong> Approved on {{ $request->approved_at->format('M j, Y g:i A') }}
                    </p>
                    @if($request->hasValidAccess())
                        <p class="text-sm text-green-700 mt-2">
                            Access is valid until {{ $request->access_expires_at->format('M j, Y g:i A') }}
                        </p>
                    @else
                        <p class="text-sm text-gray-600 mt-2">
                            Access has expired.
                        </p>
                    @endif
                @elseif($request->status === 'denied')
                    <p class="text-sm text-red-800">
                        <strong>Status:</strong> Denied on {{ $request->denied_at->format('M j, Y g:i A') }}
                    </p>
                @elseif($request->status === 'expired')
                    <p class="text-sm text-gray-800">
                        <strong>Status:</strong> Expired
                    </p>
                @endif
            </div>

            <p class="text-sm text-gray-500">
                If you need to take a different action, please contact our support team.
            </p>

            <div class="mt-6 pt-4 border-t text-sm text-gray-500">
                <p>Request ID: #{{ $request->id }}</p>
            </div>
        </div>
    </div>
</body>
</html>
