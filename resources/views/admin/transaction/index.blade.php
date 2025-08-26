@extends('admin::admin.layouts.master')

@section('title', 'Transactions Management')

@section('page-title', 'Transaction Manager')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Transaction Manager</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Start Transaction Content -->
    <div class="row">
        <div class="col-12">
            <div class="card card-body">
                <h4 class="card-title">Filter</h4>
                <form action="{{ route('admin.transactions.index') }}" method="GET" id="filterForm">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="keyword">Transaction Reference</label>
                                <input type="text" name="keyword" id="keyword" class="form-control"
                                    value="{{ app('request')->query('keyword') }}" placeholder="Enter transaction reference">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control select2">
                                    <option value="">All</option>
                                    @foreach ($statuses as $status)
                                    <option value="{{ $status }}"
                                        {{ app('request')->query('status') == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-auto mt-1 text-right">
                            <div class="form-group">
                                <button type="submit" form="filterForm" class="btn btn-primary mt-4">Filter</button>
                                <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary mt-4">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">S. No.</th>
                                    <th scope="col">@sortablelink('transaction_reference', 'Reference', [], ['class' => 'text-dark'])</th>
                                    <th scope="col">User</th>
                                    <th scope="col">Payment Gateway</th>
                                    <th scope="col">@sortablelink('amount', 'Amount', [], ['class' => 'text-dark'])</th>
                                    <th scope="col">Currency</th>
                                    <th scope="col">@sortablelink('status', 'Status', [], ['class' => 'text-dark'])</th>
                                    <th scope="col">@sortablelink('created_at', 'Created At', [], ['class' => 'text-dark'])</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (isset($transactions) && $transactions->count() > 0)
                                @php
                                $i = ($transactions->currentPage() - 1) * $transactions->perPage() + 1;
                                @endphp
                                @foreach ($transactions as $transaction)
                                <tr>
                                    <td scope="row">{{ $i }}</td>
                                    <td>{{ $transaction->transaction_reference }}</td>
                                    <td>
                                        @if (class_exists(\admin\users\Models\User::class))
                                        {{ $transaction?->user?->name ?? 'N/A' }}
                                        @else
                                        N/A
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($transaction->payment_gateway) }}</td>
                                    <td>{{ $transaction->amount }}</td>
                                    <td>{{ $transaction->currency }}</td>
                                    <td>
                                        @if ($transaction->status == 'success')
                                        <span class="badge badge-success">Success</span>
                                        @elseif ($transaction->status == 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                        @else
                                        <span class="badge badge-secondary">Failed</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $transaction->created_at ? $transaction->created_at->format(config('GET.admin_date_time_format') ?? 'Y-m-d H:i:s') : '—' }}
                                    </td>
                                    <td>
                                        @admincan('transactions_manager_view')
                                        <a href="{{ route('admin.transactions.show', $transaction) }}"
                                            data-toggle="tooltip" data-placement="top"
                                            title="View this record" class="btn btn-warning btn-sm mr-1"><i
                                                class="mdi mdi-eye"></i></a>
                                        @endadmincan
                                    </td>
                                </tr>
                                @php
                                $i++;
                                @endphp
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="9" class="text-center">No records found.</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>

                        @if ($transactions->count() > 0)
                        {{ $transactions->links('admin::pagination.custom-admin-pagination') }}
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Transaction Content -->
</div>
@endsection