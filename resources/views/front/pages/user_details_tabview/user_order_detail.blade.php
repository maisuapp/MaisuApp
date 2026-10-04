{{--orders--}}
<div class="col-md-12">
<!--    <div class="title">
        <h2>{{ __('messages.194') }}</h2>
    </div>-->
    <div class="row bg-white" >
        <div class="col-12  col-xs-12">
            <div class="grid invoice">
                <div class="grid-body">
                    <div class="invoice-title">
                        <div class="row">
                            <div class="col-12 col-xs-12">
                                <h2><span class="small">{{ __('messages.195') }} #{{ isset($order_details->order_no)?$order_details->order_no:"" }}</span>
                                    <span class="small order-status float-right">
                                         <?php
                                        if ($order_details->status == 1) {
                                            $bg_status_class = "pending";
                                        } elseif ($order_details->status == 2) {
                                            $bg_status_class = "approved";
                                        } elseif (in_array($order_details->status, [ 4, 10])) {
                                            $bg_status_class = "cancelled";
                                        } elseif (in_array($order_details->status, [3 ,5, 6, 7])) {
                                            $bg_status_class = "processing";
                                        } elseif ($order_details->status == 8) {
                                            $bg_status_class = "ongoing";
                                        } elseif ($order_details->status == 9) {
                                            $bg_status_class = "completed";
                                        } else {
                                            $bg_status_class = "";
                                        }
                                        ?>
                                        <span class="{{$bg_status_class}}">
                                             {{ str_replace('-',' ',ucwords(trim($order_status[$order_details->status]))) }}
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
                                <strong>{{ __('messages.196') }}</strong><br>
                                {{ isset($store_details->address)?$store_details->address:"" }}
                            </address>
                        </div>
                        <div class="col-6 col-xs-6 ">
                            <address>
                                <strong>{{ __('messages.197') }}</strong><br>
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
                                        <strong>Delivery Type:</strong><br>
                                        {{ (isset($order_details['taken_away_type']) && $order_details['taken_away_type'] == 1 )?"Delivery Order":"Pickup Order" }}
                                    </address>
                                </div>
                                <div class="col-6">
                                <address>
                                    <strong>{{ __('messages.183') }}:</strong><br>
                                    {{ isset($order_details['order_date_time'])?date("F j, Y, g:i a",strtotime(\App\Classes\UserClassApi::dateConvertTimezonewise($order_details['order_date_time']))):"" }}
                                </address>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 col-xs-6">
                            <address>
                                <strong>{{ __('messages.188') }}:</strong><br>
                                <span>{{ __('messages.188') }} :</span>  <span> {{ isset($order_details->store_name)?$order_details->store_name:"" }}</span><br>
                                <span>{{ __('messages.200') }} :</span>  <span>{{ isset($store_details->contact_number)?$store_details->country_code.$store_details->contact_number:"" }}</span><br>
                            </address>
                        </div>
                        <div class="col-6 col-xs-6 ">
                            <address>
                                <strong>{{ __('messages.199') }}:</strong><br>
                                <span>{{ __('messages.201') }} :</span>  <span>{{ (isset($driver_details->driver_name) && $driver_details->driver_name !="" )?$driver_details->driver_name:"----" }}</span><br>
                                <span>{{ __('messages.202') }} :</span>  <span>{{ (isset($driver_details->contact_number) && $driver_details->contact_number !="")?$driver_details->country_code.$driver_details->contact_number:"----" }}</span><br>
                            </address>
                        </div>
                    </div>

                    <div class="row " >
                        <div class="col-12 col-md-12">
                            <h3>
                                    {{ __('messages.203') }}
                                    @if(isset($order_details))
                                        @if(in_array($order_details['status'], [1,2] ))
                                            <a href="#" orderid="{{$order_details->id}}" updatestatus="4"  class="btn btn-outline-secondary btnColor btn-sm float-right order_reject_cancel">Cancel</a>
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
                                @if(isset($product_details))
                                    @foreach($product_details as $key => $products)
                                        @foreach($products as $no => $product)
                                            <tr>
                                                <td>{{ $no+1 }}</td>
                                                <td>{{ $key }}</td>
                                                <td>{{ $product->product_name }}
                                                    @if(isset($product_option_list))
                                                        @if(array_key_exists($product->id,$product_option_list))
                                                            @if( $product_option_list[$product->id] != Null)
                                                                ({{ $product_option_list[$product->id] }})
                                                            @endif
                                                        @endif
                                                    @endif
                                                </td>
                                                <td class="currency text-center">{{ $product->price_for_one }}</td>
                                                <td class="text-right">{{ $product->num_of_items }}</td>
                                                <td class="currency text-right">{{ round(($product->price_for_one * $product->num_of_items),2) }}</td>
                                            </tr>
                                        @endforeach
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
                                    <td class="currency text-right"><strong>{{ $order_details->discount_cost }}</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-right"><strong>Packaging Charge</strong></td>
                                    <td class="currency text-right"><strong> {{ (isset($order_details->packaging_cost)? $order_details->packaging_cost : 0) }}</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-right"><strong>{{ __('messages.146') }}</strong></td>
                                    <td class="currency text-right"><strong> {{ (isset($order_details->tax_cost)? $order_details->tax_cost : 0) }}</strong></td>
                                </tr>

                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-right"><strong>{{ __('messages.147') }} </strong></td>
                                    <td class="currency text-right"><strong>{{ $order_details->delivery_cost + (isset($order_details->maintenance_charge)? $order_details->maintenance_charge : 0) + (isset($order_details->base_fee)? $order_details->base_fee : 0) }}</strong></td>
                                </tr>

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
        $(document).on('click', '.order_reject_cancel', function (e) {
            e.preventDefault();
            var id = $(this).attr('orderid');
            var update_status = $(this).attr('updatestatus');
            var checkstatus = $(this).attr('checkstatus');
            var txt, title, status, url;
            if (update_status == 3) {
                status = "Reject";
            } else {
                status = "Cancel";
            }
            url = '{{ route("post:order_status") }}';

            title = status + " Order?";
            @if(isset($cancel_charge) && $cancel_charge > 0)
                txt = "There will be a cancellation charge of ${{ $cancel_charge }}. Do you want to continue? ";
            @else
                txt = "if press yes then " + status + " order!";
            @endif
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
                                    data: {orderid: id, order_status: update_status, cancel_reason: inputValue},
                                    success: function (result) {
                                        console.log(result);
                                        if (result.success == true) {

                                            swal("success!", "You " + status + " order reason is: " + inputValue, "success");
                                            location.reload();
                                        } else {
                                            location.reload();
                                        }
                                    }
                                })
                            });
                    } else {
                        swal("Cancelled", "Reject Order", "error");
                    }
                });
        });
    </script>

@endif
