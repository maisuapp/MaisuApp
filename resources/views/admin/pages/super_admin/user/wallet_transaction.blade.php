@extends('admin.layout.super_admin')
@section('title')
    Customer Wallet Transaction
@endsection
@section('page-css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive/responsive.bootstrap4.min.css')}}">
@endsection
@section('page-content')

    <div class="pcoded-content">
        <!-- [ breadcrumb ] start -->
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="page-header-title ">
                                <i class="feather icon-list bg-c-blue"></i>
                                <div class="d-inline">
                                    <h5> Customer Wallet Transaction</h5>
                                    <span>All Wallet Transaction</span>
                                </div>
                            </div>
                        </div>
                        {{--<div class="col-lg-4">--}}
                        {{--<a href="{{ route('get:admin:user_list') }}"--}}
                        {{--class="btn btn-primary m-b-0 btn-right render_link">Back</a>--}}
                        {{--</div>--}}
                    </div>
                </div>
            </div>
        </div>
        <!-- [ breadcrumb ] end -->

        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <!-- Page body start -->
                    <div class="page-body">
                        <div class="card">
                            <div class="card-header">
                                <h5>Transaction List</h5>
                            </div>
                            <div class="card-block">
                                <div class="dt-responsive table-responsive">
                                    <table id="new-cons" class="table table-striped table-bordered nowrap"
                                           style="width:100%">
                                        <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Transaction Type</th>
                                            <th>Amount</th>
                                            <th>Remaining Balance</th>
                                            <th>Date Time</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(isset($wallet_transaction_list))
                                            @foreach($wallet_transaction_list as $key => $wallet_transaction)
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    <td>
                                                        {{ $wallet_transaction->transaction_type }}
                                                        {{--@if($wallet_transaction->transaction_type == 1)--}}
                                                        {{--Credit--}}
                                                        {{--@elseif($wallet_transaction->transaction_type == 2)--}}
                                                        {{--Debit--}}
                                                        {{--@endif--}}
                                                    </td>
                                                    <td class=""><span class="currency"></span> {{ $wallet_transaction->amount }}</td>
                                                    <td class=""> <span class="currency"></span> {{ $wallet_transaction->remaining_balance }}</td>
                                                    <td>
                                                        {{ date('d M,Y h:i A',strtotime($wallet_transaction->created_at)) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Page body end -->
            </div>
        </div>
    </div>

@endsection
@section('page-js')
    <script src="{{ asset('assets/js/responsive/jquery.dataTables.min.js')}}"></script>
    <script src="{{ asset('assets/js/responsive/dataTables.bootstrap4.min.js')}}"></script>
    <script src="{{ asset('assets/js/responsive/dataTables.responsive.min.js')}}"></script>
    <script src="{{ asset('assets/js/responsive/responsive-custom.js')}}"></script>
@endsection

