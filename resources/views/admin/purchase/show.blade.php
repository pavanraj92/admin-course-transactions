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
                                    <h5 class="mb-0 text-white font-bold">Purchase Calculation</h5>
                                </div>
                                <div class="card-body">
                                    @php
                                    $discount = $purchase->discount_value ?? 0;
                                    $amountAfterDiscount = $purchase->amount - $discount;

                                    $commissionAmount = 0;
                                    if ($purchase->commission_type === 'percentage') {
                                    $commissionAmount = ($amountAfterDiscount * $purchase->commission_value) / 100;
                                    } elseif ($purchase->commission_type === 'fixed') {
                                    $commissionAmount = $purchase->commission_value;
                                    }

                                    $netRevenue = $amountAfterDiscount - $commissionAmount;
                                    @endphp

                                    <table class="table table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Description</th>
                                                <th>Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Original Amount</td>
                                                <td>{{ config('GET.currency_sign') }}{{ number_format($purchase->amount, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Discount
                                                    @if($purchase->coupon_id)
                                                    (Coupon Applied)
                                                    @endif
                                                </td>
                                                <td>{{ config('GET.currency_sign') }}{{ number_format($discount, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Amount After Discount</td>
                                                <td>{{ config('GET.currency_sign') }}{{ number_format($amountAfterDiscount, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Commission Type</td>
                                                <td>{{ ucfirst($purchase->commission_type) ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <td>Commission Value</td>
                                                <td>
                                                    @if($purchase->commission_type === 'percentage')
                                                    {{ $purchase->commission_value }}%
                                                    @elseif($purchase->commission_type === 'fixed')
                                                    {{ config('GET.currency_sign') }}{{ number_format($purchase->commission_value, 2) }}
                                                    @else
                                                    —
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Commission Amount</td>
                                                <td>{{ config('GET.currency_sign') }}{{ number_format($commissionAmount, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Net Revenue</td>
                                                <td>{{ config('GET.currency_sign') }}{{ number_format($netRevenue, 2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div> <!-- /.row -->

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-primary">
                                    <h5 class="mb-0 text-white font-bold">Course Purchase Summary</h5>
                                </div>
                                <div class="card-body">
                                    @php
                                    $discount = $purchase->discount_value ?? 0;
                                    $amountAfterDiscount = $purchase->amount - $discount;

                                    $commissionAmount = 0;
                                    if ($purchase->commission_type === 'percentage') {
                                    $commissionAmount = ($amountAfterDiscount * $purchase->commission_value) / 100;
                                    } elseif ($purchase->commission_type === 'fixed') {
                                    $commissionAmount = $purchase->commission_value;
                                    }

                                    $netRevenue = $amountAfterDiscount - $commissionAmount;
                                    @endphp

                                    <table class="table table-bordered text-right">
                                        <thead class="thead-light">
                                            <tr class="">
                                                <th class="text-left">Description</th>
                                                <th class="text-right">Amount</th>
                                            </tr>
                                            
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-left"><strong>Course:</strong> {{ $purchase->course->title ?? '—' }}</td>
                                                <td>{{ config('GET.currency_sign') }}{{ number_format($purchase->amount, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-left"><strong>Discount</strong>
                                                    @if($purchase->coupon_id)
                                                    (Coupon Applied)
                                                    @endif
                                                </td>
                                                <td>- {{ config('GET.currency_sign') }}{{ number_format($discount, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-left"><strong>Amount After Discount</strong></td>
                                                <td>= {{ config('GET.currency_sign') }}{{ number_format($amountAfterDiscount, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-left"><strong>Commission ({{ ucfirst($purchase->commission_type) }})</strong></td>
                                                <td>- {{ $purchase->commission_type === 'percentage' ? $purchase->commission_value . '%' : config('GET.currency_sign') . number_format($purchase->commission_value, 2) }}
                                                    <br>({{ config('GET.currency_sign') }}{{ number_format($commissionAmount, 2) }})
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-left"><strong>Net Revenue</strong></td>
                                                <td>= {{ config('GET.currency_sign') }}{{ number_format($netRevenue, 2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>




                </div>
            </div>

        </div>
    </div>
</div>
@endsection