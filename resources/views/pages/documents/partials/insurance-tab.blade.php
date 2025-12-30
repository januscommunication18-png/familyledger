<!-- Insurance Tab -->
<div class="flex justify-between items-center mb-6">
    <h2 class="text-lg font-semibold">Insurance Policies</h2>
    <a href="{{ route('documents.insurance.create') }}" class="btn btn-primary btn-sm gap-2">
        <span class="icon-[tabler--plus] size-4"></span>
        Add Insurance
    </a>
</div>

@if($insurancePolicies->count() > 0)
    <div class="grid gap-4">
        @foreach($insurancePolicies as $policy)
            <div class="border border-base-200 rounded-xl p-4 hover:border-primary/30 transition-colors">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-4">
                        @if($policy->card_front_image)
                            <div class="w-16 h-12 rounded-lg overflow-hidden flex-shrink-0 border border-base-200">
                                <img src="{{ route('documents.insurance.card', [$policy, 'front']) }}" alt="Card" class="w-full h-full object-cover" />
                            </div>
                        @else
                            <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                                <span class="{{ $policy->getTypeIcon() }} size-6 text-primary"></span>
                            </div>
                        @endif
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-semibold text-base-content">{{ $policy->provider_name }}</h3>
                                <span class="badge badge-sm badge-{{ $policy->getStatusColor() }}">{{ $insuranceStatuses[$policy->status] ?? $policy->status }}</span>
                            </div>
                            <p class="text-sm text-base-content/60">{{ $insuranceTypes[$policy->insurance_type] ?? $policy->insurance_type }}</p>
                            @if($policy->policy_number)
                                <p class="text-sm text-base-content/60 mt-1">Policy #: {{ $policy->policy_number }}</p>
                            @endif
                            @if($policy->policyholders->count() > 0)
                                <p class="text-sm text-base-content/60">
                                    Policyholder: {{ $policy->policyholders->map(fn($p) => $p->first_name . ' ' . $p->last_name)->join(', ') }}
                                </p>
                            @endif
                            @if($policy->expiration_date)
                                <p class="text-sm {{ $policy->isExpiringSoon() ? 'text-warning' : 'text-base-content/60' }} mt-1">
                                    @if($policy->isExpiringSoon())
                                        <span class="icon-[tabler--alert-triangle] size-4 inline-block align-middle mr-1"></span>
                                    @endif
                                    Expires: {{ $policy->expiration_date->format('M d, Y') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('documents.insurance.edit', $policy) }}" class="btn btn-ghost btn-sm btn-square" title="Edit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                        </a>
                        <button type="button" onclick="confirmDelete('{{ route('documents.insurance.destroy', $policy) }}')" class="btn btn-ghost btn-sm btn-square text-error" title="Delete">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                        </button>
                    </div>
                </div>

                @if($policy->premium_amount)
                    <div class="mt-3 pt-3 border-t border-base-200">
                        <span class="text-sm text-base-content/60">Premium: </span>
                        <span class="font-medium">${{ number_format($policy->premium_amount, 2) }}</span>
                        @if($policy->payment_frequency)
                            <span class="text-sm text-base-content/60">/ {{ $paymentFrequencies[$policy->payment_frequency] ?? $policy->payment_frequency }}</span>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@else
    <div class="text-center py-12 text-base-content/60">
        <span class="icon-[tabler--shield-off] size-16 opacity-30"></span>
        <p class="mt-4 text-lg font-medium">No insurance policies</p>
        <p class="text-sm">Add your first insurance policy to keep track of coverage</p>
        <a href="{{ route('documents.insurance.create') }}" class="btn btn-primary mt-4">
            <span class="icon-[tabler--plus] size-4"></span>
            Add Insurance Policy
        </a>
    </div>
@endif
