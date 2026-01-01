@props([
    'name' => 'date',
    'label' => 'Date',
    'required' => false,
    'value' => null,
    'monthName' => null,
    'dayName' => null,
    'yearName' => null,
])

@php
    $monthName = $monthName ?? $name . '_month';
    $dayName = $dayName ?? $name . '_day';
    $yearName = $yearName ?? $name . '_year';

    $monthValue = null;
    $dayValue = null;
    $yearValue = null;

    if ($value) {
        if ($value instanceof \Carbon\Carbon) {
            $monthValue = $value->format('m');
            $dayValue = $value->format('d');
            $yearValue = $value->format('Y');
        } elseif (is_string($value) && strpos($value, '/') !== false) {
            $parts = explode('/', $value);
            if (count($parts) === 3) {
                $monthValue = $parts[0];
                $dayValue = $parts[1];
                $yearValue = $parts[2];
            }
        } elseif (is_string($value) && strpos($value, '-') !== false) {
            $parts = explode('-', $value);
            if (count($parts) === 3) {
                $yearValue = $parts[0];
                $monthValue = $parts[1];
                $dayValue = $parts[2];
            }
        }
    }
@endphp

<div>
    <label class="block text-sm font-medium text-slate-700 mb-2">
        {{ $label }} @if($required)<span class="text-rose-500">*</span>@endif
    </label>
    <div class="flex gap-2">
        <!-- Month -->
        <div class="flex-1">
            <select name="{{ $monthName }}" id="{{ $monthName }}" {{ $required ? 'required' : '' }} data-select='{
                "placeholder": "Month",
                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                "toggleClasses": "advance-select-toggle w-full",
                "hasSearch": true,
                "searchPlaceholder": "Search...",
                "dropdownClasses": "advance-select-menu",
                "optionClasses": "advance-select-option selected:select-active",
                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
            }' class="hidden">
                <option value="">Month</option>
                <option value="01" {{ old($monthName, $monthValue) == '01' ? 'selected' : '' }}>January</option>
                <option value="02" {{ old($monthName, $monthValue) == '02' ? 'selected' : '' }}>February</option>
                <option value="03" {{ old($monthName, $monthValue) == '03' ? 'selected' : '' }}>March</option>
                <option value="04" {{ old($monthName, $monthValue) == '04' ? 'selected' : '' }}>April</option>
                <option value="05" {{ old($monthName, $monthValue) == '05' ? 'selected' : '' }}>May</option>
                <option value="06" {{ old($monthName, $monthValue) == '06' ? 'selected' : '' }}>June</option>
                <option value="07" {{ old($monthName, $monthValue) == '07' ? 'selected' : '' }}>July</option>
                <option value="08" {{ old($monthName, $monthValue) == '08' ? 'selected' : '' }}>August</option>
                <option value="09" {{ old($monthName, $monthValue) == '09' ? 'selected' : '' }}>September</option>
                <option value="10" {{ old($monthName, $monthValue) == '10' ? 'selected' : '' }}>October</option>
                <option value="11" {{ old($monthName, $monthValue) == '11' ? 'selected' : '' }}>November</option>
                <option value="12" {{ old($monthName, $monthValue) == '12' ? 'selected' : '' }}>December</option>
            </select>
        </div>
        <!-- Day -->
        <div class="flex-1">
            <select name="{{ $dayName }}" id="{{ $dayName }}" {{ $required ? 'required' : '' }} data-select='{
                "placeholder": "Day",
                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                "toggleClasses": "advance-select-toggle w-full",
                "hasSearch": true,
                "searchPlaceholder": "Search...",
                "dropdownClasses": "advance-select-menu",
                "optionClasses": "advance-select-option selected:select-active",
                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
            }' class="hidden">
                <option value="">Day</option>
                @for($i = 1; $i <= 31; $i++)
                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" {{ old($dayName, $dayValue) == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>
        <!-- Year -->
        <div class="flex-1">
            <select name="{{ $yearName }}" id="{{ $yearName }}" {{ $required ? 'required' : '' }} data-select='{
                "placeholder": "Year",
                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                "toggleClasses": "advance-select-toggle w-full",
                "hasSearch": true,
                "searchPlaceholder": "Search...",
                "dropdownClasses": "advance-select-menu",
                "optionClasses": "advance-select-option selected:select-active",
                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
            }' class="hidden">
                <option value="">Year</option>
                @for($year = date('Y') + 20; $year >= 1900; $year--)
                    <option value="{{ $year }}" {{ old($yearName, $yearValue) == $year ? 'selected' : '' }}>{{ $year }}</option>
                @endfor
            </select>
        </div>
    </div>
    <input type="hidden" name="{{ $name }}" id="{{ $name }}" {{ $required ? 'required' : '' }}>
    @error($name)
        <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
    @enderror
</div>

@pushOnce('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle all date-select components
    document.querySelectorAll('[id$="_month"]').forEach(function(monthSelect) {
        const baseName = monthSelect.id.replace('_month', '');
        const daySelect = document.getElementById(baseName + '_day');
        const yearSelect = document.getElementById(baseName + '_year');
        const hiddenInput = document.getElementById(baseName);

        if (!daySelect || !yearSelect || !hiddenInput) return;

        function updateHiddenDate() {
            const month = monthSelect.value;
            const day = daySelect.value;
            const year = yearSelect.value;

            if (month && day && year) {
                hiddenInput.value = `${month}/${day}/${year}`;
            } else {
                hiddenInput.value = '';
            }
        }

        monthSelect.addEventListener('change', updateHiddenDate);
        daySelect.addEventListener('change', updateHiddenDate);
        yearSelect.addEventListener('change', updateHiddenDate);

        // Initial update
        updateHiddenDate();
    });
});
</script>
@endPushOnce
