<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    @foreach($headers as $header)
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($items as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        @if($rowTemplate === 'users')
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center">
                                        <span class="text-primary-700 dark:text-primary-300 font-medium">
                                            {{ strtoupper(substr($item->name ?? 'U', 0, 1)) }}
                                        </span>
                                    </div>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $item->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->email }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->phone ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->last_login_at?->diffForHumans() ?? 'Never' }}</td>

                        @elseif($rowTemplate === 'family')
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                        <span class="text-purple-700 dark:text-purple-300 font-medium">
                                            {{ strtoupper(substr($item->first_name ?? 'M', 0, 1)) }}
                                        </span>
                                    </div>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $item->first_name }} {{ $item->last_name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->relationship ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->date_of_birth?->format('M d, Y') ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ ucfirst($item->gender ?? 'N/A') }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->created_at->format('M d, Y') }}</td>

                        @elseif($rowTemplate === 'pets')
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                                        <span class="text-green-700 dark:text-green-300 font-medium">
                                            {{ strtoupper(substr($item->name ?? 'P', 0, 1)) }}
                                        </span>
                                    </div>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $item->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ ucfirst($item->species ?? 'N/A') }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->breed ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->date_of_birth?->format('M d, Y') ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->created_at->format('M d, Y') }}</td>

                        @elseif($rowTemplate === 'assets')
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $item->name }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $item->type ?? 'N/A')) }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">${{ number_format($item->value ?? 0, 2) }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->location ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->created_at->format('M d, Y') }}</td>

                        @elseif($rowTemplate === 'insurance')
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $item->name ?? $item->policy_number }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $item->type ?? 'N/A')) }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->provider ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">${{ number_format($item->premium ?? 0, 2) }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->expiry_date?->format('M d, Y') ?? 'N/A' }}</td>

                        @elseif($rowTemplate === 'legal')
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $item->name ?? $item->title }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $item->type ?? 'N/A')) }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $item->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400' }}">
                                    {{ ucfirst($item->status ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->effective_date?->format('M d, Y') ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->created_at->format('M d, Y') }}</td>

                        @elseif($rowTemplate === 'tax')
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $item->tax_year ?? $item->year }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $item->status === 'filed' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' }}">
                                    {{ ucfirst($item->status ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->filing_date?->format('M d, Y') ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                @if($item->refund_amount)
                                    <span class="text-green-600 dark:text-green-400">+${{ number_format($item->refund_amount, 2) }}</span>
                                @elseif($item->amount_owed)
                                    <span class="text-red-600 dark:text-red-400">-${{ number_format($item->amount_owed, 2) }}</span>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->created_at->format('M d, Y') }}</td>

                        @elseif($rowTemplate === 'budgets')
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $item->name }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ ucfirst($item->period ?? 'monthly') }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">${{ number_format($item->amount ?? 0, 2) }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">${{ number_format($item->spent ?? 0, 2) }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->created_at->format('M d, Y') }}</td>

                        @elseif($rowTemplate === 'goals')
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $item->title ?? $item->name }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ ucfirst($item->category ?? 'N/A') }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->target_value ?? 'N/A' }}</td>
                            <td class="px-6 py-4">
                                @php $progress = $item->progress ?? 0; @endphp
                                <div class="flex items-center gap-2">
                                    <div class="w-24 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-primary-600 h-2 rounded-full" style="width: {{ min($progress, 100) }}%"></div>
                                    </div>
                                    <span class="text-sm text-gray-500">{{ $progress }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->due_date?->format('M d, Y') ?? 'N/A' }}</td>

                        @elseif($rowTemplate === 'contacts')
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                        <span class="text-blue-700 dark:text-blue-300 font-medium">
                                            {{ strtoupper(substr($item->first_name ?? $item->name ?? 'C', 0, 1)) }}
                                        </span>
                                    </div>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $item->first_name ?? '' }} {{ $item->last_name ?? $item->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ ucfirst($item->type ?? 'N/A') }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->company ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->email ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->phone ?? 'N/A' }}</td>

                        @elseif($rowTemplate === 'journal')
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ Str::limit($item->title ?? 'Untitled', 40) }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ ucfirst($item->type ?? 'entry') }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->mood ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->attachments_count ?? $item->attachments?->count() ?? 0 }}</td>

                        @elseif($rowTemplate === 'invoices')
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $item->invoice_number ?? '#' . $item->id }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">${{ number_format($item->amount ?? $item->total ?? 0, 2) }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    {{ $item->status === 'paid' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' :
                                       ($item->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' :
                                       'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400') }}">
                                    {{ ucfirst($item->status ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->due_date?->format('M d, Y') ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $item->paid_at?->format('M d, Y') ?? '-' }}</td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($headers) }}" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            {{ $emptyMessage }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
