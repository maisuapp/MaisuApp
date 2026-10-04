{{--orders--}}
<div class="col-md-12 bg-white">
<!--    <div class="title">
        <h2>{{ __('messages.194') }}</h2>
    </div>-->
    <div class="row">
        <div class="col-12  col-xs-12">
            <div class="grid invoice">
                <div class="grid-body">
                    <div class="invoice-title">
                        <div class="row">
                            <div class="col-12 col-xs-12">
                                <h2><span class="small">{{ __('messages.195') }} #{{ isset($order_details->order_no)?$order_details->order_no:"" }}</span>
                                    <span class="small order-status float-right">
                                         <?php

                                        if ($order_details['status'] == 1) {
                                            $bg_status_class = "pending";
                                        } elseif (in_array($order_details['status'], [ 2 , 3])) {
                                            $bg_status_class = "approved";
                                        } elseif (in_array($order_details['status'], [4,5, 10])) {
                                            $bg_status_class = "cancelled";
                                        } elseif (in_array($order_details['status'], [6, 7])) {
                                            $bg_status_class = "processing";
                                        } elseif ($order_details['status'] == 8) {
                                            $bg_status_class = "ongoing";
                                        } elseif ($order_details['status'] == 9) {
                                            $bg_status_class = "completed";
                                        } else {
                                            $bg_status_class = "";
                                        }
                                        ?>
                                        <span class="{{$bg_status_class}}">
                                             {{ str_replace('-',' ',ucwords(trim($bg_status_class))) }}
                                        </span>
                                    </span>
                                </h2>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6 col-xs-6">
                            <address>
                                <strong>Customer Name :</strong><br>
                                {{ isset($order_details->user_name)?$order_details->user_name:"" }}
                            </address>
                        </div>
                        <div class="col-6 col-xs-6 ">
                            <address>
                                <strong>Customer Address :</strong><br>
                                {{ isset($order_details->delivery_address)?$order_details->delivery_address:"N/A" }}
                            </address>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 col-xs-6">
                            <address>
                                <strong>{{ __('messages.198') }}</strong><br>
                                {{ isset($order_details) ? ($order_details->payment_type == 1 ? "cash" : ($order_details->payment_type == 2 ? "card" : ($order_details->payment_type == 3 ? "wallet" : ''))) : '' }} <br/>
                            </address>
                        </div>
                        <div class="col-6 col-xs-6 ">
                            <div class="row">
                                <div class="col-6">
                                    <address>
                                        <strong>Provider Name:</strong><br>
                                        {{ (isset($order_details->provider_name) )?$order_details->provider_name:"---" }}
                                    </address>
                                </div>
                                <div class="col-6">
                                <address>
                                    <strong>{{ __('messages.183') }}:</strong><br>
                                    {{--{{ isset($order_details['service_date_time'])?date("F j, Y, g:i a",strtotime($order_details->service_date_time)):"" }}--}}
                                    {{ isset($order_details['service_date_time'])?date("F j, Y, g:i a",strtotime(\App\Classes\UserClassApi::dateConvertTimezonewise($order_details->service_date_time))):"" }}
                                </address>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 col-xs-6">
                            <address>
                                <strong>Payment Status:</strong><br>
                                <span> {{ ($order_details->payment_status  == 1)? "Completed" : "Pending"}}</span><br>
                            </address>
                        </div>
                        <div class="col-6 col-xs-6 ">
                            <address>
                                <strong>Order Type:</strong><br>
                                <span>{{ ($order_details->order_type == 1)? "Schedule" : "Book Now" }}</span><br>

                            </address>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-12">
                            <h3>
                                {{ __('messages.203') }}
                                @if(isset($order_details))
                                    @if(in_array($order_details['status'], [1,2,3] ))
                                        <a href="#" orderid="{{$order_details->id}}" updatestatus="5" class="btn btn-outline-secondary btnColor btn-sm float-right order_cancel">Cancel</a>
                                    @endif
                                @endif
                            </h3>
                            <table class="table table-striped">
                                <thead>
                                <tr class="line">
                                    <td><strong>#</strong></td>
                                    <td class=""><strong>{{ __('messages.204') }}</strong></td>
                                    <td class=""><strong>{{ __('messages.205') }}</strong></td>
                                    <td class="text-center"><strong>{{ __('messages.206') }}</strong></td>
                                    <td class="text-right"><strong>{{ __('messages.207') }}</strong></td>
                                    <td class="text-right"><strong>{{ __('messages.135') }}</strong></td>
                                </tr>
                                </thead>
                                <tbody>
                                @if(isset($package_list))
                                    @foreach($package_list as $key => $package)
                                        {{--@foreach($products as $no => $product)--}}
                                            <tr>

                                                <td>{{ $key+1 }}</td>
                                                <td>
                                                    {{ $package->sub_category_name }}
                                                </td>
                                                <td>
                                                    {{ $package->package_name }}
                                                </td>
                                                <td class="currency">
                                                    {{ $package->price_for_one }}
                                                </td>
                                                <td class="text-right">
                                                    <span class=""></span> {{ $package->num_of_items }}
                                                </td>
                                                <td class="text-right">
                                                    <span class="currency "></span> {{ round($package->price_for_one * $package->num_of_items,2) }}
                                                </td>
                                            </tr>
                                        {{--@endforeach--}}
                                    @endforeach
                                @endif
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-right"><strong>{{ __('messages.144') }}</strong></td>
                                    <td class="currency text-right"><strong>{{ $order_details->total_item_cost }}</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-right"><strong>{{ __('messages.145') }}</strong></td>
                                    <td class="currency text-right"><strong>{{ isset($order_details->discount_cost) ? $order_details->discount_cost : 0 }}</strong></td>
                                </tr>
<!--                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-right"><strong>Packaging Charge</strong></td>
                                    <td class="currency text-right"><strong> {{ (isset($order_details->packaging_cost)? $order_details->packaging_cost : 0) }}</strong></td>
                                </tr>-->
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-right"><strong>{{ __('messages.146') }}</strong></td>
                                    <td class="currency text-right"><strong> {{ $order_details->tax }}</strong></td>
                                </tr>


{{--                                <tr>--}}
{{--                                    <td colspan="4"></td>--}}
{{--                                    <td class="text-right"><strong>{{ __('messages.147') }}</strong></td>--}}
{{--                                    <td class="currency text-right"><strong> {{ $order_details->delivery_cost + (isset($order_details->maintenance_charge)? $order_details->maintenance_charge : 0) + (isset($order_details->base_fee)? $order_details->base_fee : 0) }}</strong></td>--}}
{{--                                </tr>--}}

                                <tr>
                                    <td colspan="4">
                                    </td><td class="text-right"><strong>{{ __('messages.132') }}</strong></td>
                                    <td class="text-right"><h3 class="currency"> {{ $order_details->total_pay }}</h3></td>
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
@if (Illuminate\Support\Facades\Auth::guard("user")->check())
<script type="text/javascript">
    $(document).on('click', '.order_cancel', function (e) {
        e.preventDefault();
        var id = $(this).attr('orderid');
        var update_status = $(this).attr('updatestatus');
        var txt, title, status, url;
        status = "Cancel";

        url = '{{ route("get:update_other_service_order_status") }}';
        title = status + " Order?";
        txt = "if press yes then " + status + " order!";
        swal({
                title: title,
                text: txt,
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes",
                cancelButtonText: "No",
                closeOnConfirm: false,
                closeOnCancel: false
            },
            function (isConfirm) {
                if (isConfirm) {
                    swal({
                            title: status + " Order!",
                            text: "if " + status + " order then Write " + status + " reason:",
                            type: "input",
                            showCancelButton: true,
                            closeOnConfirm: false,
                            animation: "slide-from-top",
                            inputPlaceholder: status + " Reason"
                        },
                        function (inputValue) {
                            if (inputValue === false) return false;
                            if (inputValue === "") {
                                swal.showInputError("You need to write " + status + " order!");
                                return false
                            }
                            $.ajax({
                                type: 'get',
                                url: url,
                                data: {id: id, update_status: update_status, reason: inputValue},
                                success: function (result) {
                                    if (result.success == true) {

                                        swal("success!", "You " + status + " order reason is: " + inputValue, "success");
                                        location.reload();

                                    } else {
                                        console.log(result);
                                    }
                                }
                            })
                        });
                } else {
                    swal("Cancelled", "Cancel Order", "error");
                }
            });
    });
</script>
@endif
