<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Access Request - Family Ledger</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-lg w-full">
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-t-xl p-6 text-center">
            <h1 class="text-2xl font-bold text-white">Data Access Request</h1>
        </div>

        <div class="bg-white rounded-b-xl shadow-lg p-6">
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <p class="text-gray-700 mb-4">
                A Family Ledger support administrator is requesting access to view your account data.
            </p>

            <div class="bg-gray-50 border-l-4 border-indigo-500 p-4 rounded mb-6">
                <p class="mb-2"><strong>Administrator:</strong> {{ $request->admin->name }}</p>
                <p class="mb-2"><strong>Email:</strong> {{ $request->admin->email }}</p>
                @if($request->reason)
                    <p><strong>Reason:</strong> {{ $request->reason }}</p>
                @endif
            </div>

            <div class="bg-blue-50 border border-blue-200 p-4 rounded mb-6">
                <p class="text-sm text-blue-800">
                    <strong>What this means:</strong> If you approve, the administrator will be able to view your account information for a limited time to assist with your support request.
                </p>
            </div>

            <div class="border-t pt-6">
                <h3 class="font-semibold text-lg mb-4">Approve Access</h3>
                <form action="{{ route('data-access.approve', $request->token) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Your Email Address</label>
                        <input type="email" name="email" id="email" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Enter your email to confirm">
                        @if($maskedEmail)
                            <p class="text-gray-500 text-sm mt-1">Enter the email associated with this account: <strong>{{ $maskedEmail }}</strong></p>
                        @endif
                        @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="access_hours" class="block text-sm font-medium text-gray-700 mb-1">Access Duration</label>
                        <select name="access_hours" id="access_hours" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="1">1 hour</option>
                            <option value="2" selected>2 hours</option>
                            <option value="4">4 hours</option>
                            <option value="8">8 hours</option>
                            <option value="24">24 hours</option>
                        </select>
                        @error('access_hours')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                            class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 text-white py-3 px-4 rounded-md font-semibold hover:from-indigo-600 hover:to-purple-700 transition">
                        Approve Access
                    </button>
                </form>
            </div>

            <div class="border-t mt-6 pt-6">
                <h3 class="font-semibold text-lg mb-4 text-red-600">Deny Access</h3>
                <form action="{{ route('data-access.deny', $request->token) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Reason (optional)</label>
                        <textarea name="reason" id="reason" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500"
                                  placeholder="Optionally provide a reason for denial"></textarea>
                        @error('reason')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                            class="w-full bg-red-500 text-white py-3 px-4 rounded-md font-semibold hover:bg-red-600 transition">
                        Deny Access
                    </button>
                </form>
            </div>

            <div class="mt-6 pt-4 border-t text-center text-sm text-gray-500">
                <p>Request ID: #{{ $request->id }}</p>
                <p>Expires: {{ $request->expires_at->format('M j, Y g:i A') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
