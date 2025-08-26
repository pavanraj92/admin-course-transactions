@extends('admin::admin.layouts.master')

@section('title', 'Purchase Manager')
@section('page-title', 'Purchase Manager')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Purchase Manager</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Filter Section -->
    <div class="row">
        <div class="col-12">
            <div class="card card-body">
                <h4 class="card-title">Filter</h4>
                <form action="{{ route('admin.course-purchases.index') }}" method="GET" id="filterForm">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="keyword">User / Course</label>
                                <input type="text" name="keyword" id="keyword" class="form-control"
                                    value="{{ request('keyword') }}" placeholder="Search by user or course">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control select2">
                                    <option value="">All</option>
                                    @foreach ($statuses as $status)
                                    <option value="{{ $status }}"
                                        {{ request('status') == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-auto mt-1 text-right">
                            <div class="form-group">
                                <button type="submit" form="filterForm" class="btn btn-primary mt-4">Filter</button>
                                <a href="{{ route('admin.course-purchases.index') }}" class="btn btn-secondary mt-4">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">S. No.</th>
                                    <th scope="col">@sortablelink('user', 'User', [], ['class' => 'text-dark'])</th>
                                    <th scope="col">@sortablelink('course.title', 'Course', [], ['class' => 'text-dark'])</th>
                                    <th scope="col">@sortablelink('amount', 'Amount', [], ['class' => 'text-dark'])</th>
                                    <th scope="col">Currency</th>
                                    <th scope="col">@sortablelink('status', 'Status', [], ['class' => 'text-dark'])</th>
                                    <th scope="col">@sortablelink('created_at', 'Purchased At', [], ['class' => 'text-dark'])</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (isset($purchases) && $purchases->count() > 0)
                                @php
                                $i = ($purchases->currentPage() - 1) * $purchases->perPage() + 1;
                                @endphp
                                @foreach ($purchases as $purchase)
                                <tr>
                                    <td scope="row">{{ $i }}</td>
                                    <td>
                                        {{ $purchase?->user?->name ?? 'N/A' }}<br>
                                        <small class="text-muted">{{ $purchase?->user?->email }}</small>
                                    </td>
                                    <td>{{ $purchase?->course?->title ?? 'No Course' }}</td>
                                    <td>{{ config('GET.currency_sign') }}{{ number_format($purchase->amount, 2) }}</td>
                                    <td>{{ isset($purchase->currency) ? $purchase->currency : config('GET.default_currency') }}</td>
                                    <td>
                                        @php
                                            $color = config('course.constants.statusBadge.' . $purchase->status, 'badge-secondary');
                                        @endphp
                                        <p><span class="badge {{ $color }}">{{ ucfirst($purchase->status) }}</span></p>                                       
                                    </td>
                                    <td>{{ $purchase->created_at ? $purchase->created_at->format(config('GET.admin_date_time_format') ?? 'Y-m-d H:i:s') : '—' }}</td>
                                    <td>
                                        @admincan('course_purchases_view')
                                        <a href="{{ route('admin.course-purchases.show', $purchase) }}"
                                            data-toggle="tooltip" data-placement="top" title="View this record"
                                            class="btn btn-warning btn-sm mr-1"><i class="mdi mdi-eye"></i></a>
                                        @endadmincan
                                    </td>
                                </tr>
                                @php $i++; @endphp
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="8" class="text-center">No records found.</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>

                        @if ($purchases->count() > 0)
                        {{ $purchases->links('admin::pagination.custom-admin-pagination') }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection