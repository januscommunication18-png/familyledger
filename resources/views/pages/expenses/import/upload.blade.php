@extends('layouts.dashboard')

@section('page-name', 'Import Transactions')

@section('content')
<div class="p-4 lg:p-6 max-w-2xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Import Transactions</h1>
        <p class="text-sm text-slate-500">Import transactions from a CSV file exported from your bank</p>
    </div>

    {{-- Upload Form --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <form action="{{ route('expenses.import.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="border-2 border-dashed border-slate-300 rounded-xl p-8 text-center mb-6" id="dropZone">
                    <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="rgb(100 116 139)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                    </div>
                    <p class="text-slate-600 mb-2">Drag and drop your CSV file here, or</p>
                    <label class="btn btn-primary btn-sm">
                        Choose File
                        <input type="file" name="csv_file" accept=".csv,.txt" class="hidden" onchange="fileSelected(this)" required>
                    </label>
                    <p id="fileName" class="text-sm text-slate-500 mt-2"></p>
                    <p class="text-xs text-slate-400 mt-2">Maximum file size: 5MB</p>
                </div>

                {{-- Instructions --}}
                <div class="bg-base-200 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-slate-700 mb-2">CSV Format Tips</h3>
                    <ul class="text-sm text-slate-600 space-y-1">
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            The first row should contain column headers
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            Required columns: Date, Description, Amount
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            Dates should be in a recognizable format (MM/DD/YYYY, YYYY-MM-DD, etc.)
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            Duplicate transactions will be automatically detected and skipped
                        </li>
                    </ul>
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    Upload and Continue
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function fileSelected(input) {
    const fileName = input.files[0]?.name || '';
    document.getElementById('fileName').textContent = fileName ? 'Selected: ' + fileName : '';
}

// Drag and drop
const dropZone = document.getElementById('dropZone');

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-primary', 'bg-primary/5');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-primary', 'bg-primary/5');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-primary', 'bg-primary/5');
    const file = e.dataTransfer.files[0];
    if (file) {
        const input = document.querySelector('input[name="csv_file"]');
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        document.getElementById('fileName').textContent = 'Selected: ' + file.name;
    }
});
</script>
@endsection
