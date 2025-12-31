<!-- Tax Returns Tab -->
<div class="flex justify-between items-center mb-6">
    <h2 class="text-lg font-semibold">Tax Returns</h2>
    <a href="{{ route('documents.tax-returns.create') }}" class="btn btn-primary btn-sm gap-2">
        <span class="icon-[tabler--plus] size-4"></span>
        Add Tax Return
    </a>
</div>

@if($taxReturns->count() > 0)
    <div class="overflow-x-auto">
        <table class="table table-zebra">
            <thead>
                <tr>
                    <th>Tax Year</th>
                    <th>Taxpayer</th>
                    <th>Filing Status</th>
                    <th>Status</th>
                    <th>Jurisdiction</th>
                    <th>Files</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($taxReturns as $return)
                    <tr>
                        <td>
                            <span class="font-semibold">{{ $return->tax_year }}</span>
                        </td>
                        <td>
                            @if($return->taxpayers->count() > 0)
                                <div class="flex items-center gap-2">
                                    <div class="avatar placeholder">
                                        <div class="w-8 rounded-full bg-primary/10 text-primary">
                                            <span class="text-xs">{{ substr($return->taxpayers->first()->first_name, 0, 1) }}{{ substr($return->taxpayers->first()->last_name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <span>{{ $return->taxpayers->map(fn($t) => $t->first_name . ' ' . $t->last_name)->join(', ') }}</span>
                                </div>
                            @else
                                <span class="text-base-content/40">-</span>
                            @endif
                        </td>
                        <td>
                            @if($return->filing_status)
                                {{ $filingStatuses[$return->filing_status] ?? $return->filing_status }}
                            @else
                                <span class="text-base-content/40">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-sm badge-{{ $return->getStatusColor() }}">
                                {{ $taxStatuses[$return->status] ?? $return->status }}
                            </span>
                        </td>
                        <td>
                            {{ $jurisdictions[$return->tax_jurisdiction] ?? $return->tax_jurisdiction }}
                            @if($return->state_jurisdiction)
                                ({{ $return->state_jurisdiction }})
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-neutral badge-sm">{{ $return->getFilesCount() }} files</span>
                        </td>
                        <td>
                            <div class="flex items-center gap-1">
                                <a href="{{ route('documents.tax-returns.show', $return) }}" class="btn btn-ghost btn-sm btn-square" title="View">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                <a href="{{ route('documents.tax-returns.edit', $return) }}" class="btn btn-ghost btn-sm btn-square" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                </a>
                                <button type="button" onclick="confirmDelete('{{ route('documents.tax-returns.destroy', $return) }}')" class="btn btn-ghost btn-sm btn-square text-error" title="Delete">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center py-12 text-base-content/60">
        <span class="icon-[tabler--file-invoice] size-16 opacity-30"></span>
        <p class="mt-4 text-lg font-medium">No tax returns</p>
        <p class="text-sm">Add your first tax return to keep track of filings</p>
        <a href="{{ route('documents.tax-returns.create') }}" class="btn btn-primary mt-4">
            <span class="icon-[tabler--plus] size-4"></span>
            Add Tax Return
        </a>
    </div>
@endif
