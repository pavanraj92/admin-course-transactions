@extends('admin::admin.layouts.master')

@section('title', 'View Purchase - #' . $purchase->id)
@section('page-title', 'Purchase Details')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.course-purchases.index') }}">Course Purchases</a></li>
<li class="breadcrumb-item active" aria-current="page">Purchase Details</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">

            <div class="card">
                <div class="card-body">

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="card-title mb-0">
                            Purchase #{{ $purchase->id }}
                        </h4>
                        <a href="{{ route('admin.course-purchases.index') }}" class="btn btn-secondary ml-2">Back
                        </a>
                    </div>

                    <div class="row">
                        <!-- Left Column: Purchase Information -->
                        <div class="col-md-8">
                            <div class="card mb-3">
                                <div class="card-header bg-primary">
                                    <h5 class="mb-0 text-white font-bold">Purchase Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">User:</label>
                                                <p>
                                                    {{ $purchase->user->name ?? 'N/A' }}<br>
                                                    <small class="text-muted">{{ $purchase->user->email ?? '' }}</small>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Course:</label>
                                                <p>{{ $purchase->course->title ?? '—' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Amount:</label>
                                                <p>{{ config('GET.currency_sign') }}{{ number_format($purchase->amount, 2) }} {{ $purchase->currency }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Status:</label>
                                                @php
                                                $statusColors = [
                                                'pending' => 'badge-warning',
                                                'completed' => 'badge-success',
                                                'cancelled' => 'badge-danger',
                                                ];
                                                $color = $statusColors[$purchase->status] ?? 'badge-secondary';
                                                @endphp
                                                <p><span class="badge {{ $color }}">{{ ucfirst($purchase->status) }}</span></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Purchased On:</label>
                                                <p>{{ $purchase->created_at ? $purchase->created_at->format(config('GET.admin_date_time_format') ?? 'Y-m-d H:i:s') : '—' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Transaction Reference:</label>
                                                <p>{{ $purchase->transaction->transaction_reference ?? '—' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Metadata -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-primary">
                                    <h5 class="mb-0 text-white font-bold">Metadata</h5>
                                </div>
                                <div class="card-body">
                                    @if (!empty($purchase->metadata) && is_array($purchase->metadata))
                                    <ul class="list-group">
                                        @foreach ($purchase->metadata as $key => $value)
                                        <li class="list-group-item">
                                            <strong>{{ ucfirst($key) }}:</strong>
                                            {{ is_array($value) ? json_encode($value) : $value }}
                                        </li>
                                        @endforeach
                                    </ul>
                                    @else
                                    <p class="text-muted">No additional metadata</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div> <!-- /.row -->

                </div>
            </div>

        </div>
    </div>
</div>
@endsection