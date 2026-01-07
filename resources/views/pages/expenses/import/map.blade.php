@extends('layouts.dashboard')

@section('page-name', 'Map Columns')

@section('content')
<div class="p-4 lg:p-6 max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Map CSV Columns</h1>
        <p class="text-sm text-slate-500">Match your CSV columns to the required fields</p>
    </div>

    {{-- Preview Table --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="font-semibold text-slate-800 mb-3">Preview (first {{ count($rows) }} rows)</h3>
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            @foreach($headers as $index => $header)
                            <th class="text-xs">Col {{ $index }}: {{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                        <tr>
                            @foreach($row as $cell)
                            <td class="text-xs text-slate-600">{{ Str::limit($cell, 30) }}</td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Mapping Form --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h3 class="font-semibold text-slate-800 mb-4">Column Mapping</h3>

            <form action="{{ route('expenses.import.map') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Date Column <span class="text-error">*</span></span>
                        </label>
                        <select name="date_column" class="select select-bordered" required>
                            @foreach($headers as $index => $header)
                            <option value="{{ $index }}">{{ $header }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Description Column <span class="text-error">*</span></span>
                        </label>
                        <select name="description_column" class="select select-bordered" required>
                            @foreach($headers as $index => $header)
                            <option value="{{ $index }}" {{ $index == 1 ? 'selected' : '' }}>{{ $header }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Amount Column <span class="text-error">*</span></span>
                        </label>
                        <select name="amount_column" class="select select-bordered" required>
                            @foreach($headers as $index => $header)
                            <option value="{{ $index }}" {{ $index == 2 ? 'selected' : '' }}>{{ $header }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Default Category</span>
                        </label>
                        <select name="default_category_id" class="select select-bordered">
                            <option value="">Uncategorized</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->display_icon }} {{ $category->name }}</option>
                            @endforeach
                        </select>
                        <label class="label">
                            <span class="label-text-alt text-slate-500">All imported transactions will use this category</span>
                        </label>
                    </div>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('expenses.import') }}" class="btn btn-ghost">Back</a>
                    <button type="submit" class="btn btn-primary flex-1">Preview Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
