{{--orders--}}


<div class="content-container">
    <div class="container-fluid">
        <div class="col-md-12 col-xs-12">
            <div class="title">
                <h2>Order List</h2>
            </div>
            @foreach($get_orders as $key => $order)
                <div class="col-md-12 col-sm-12 col-xs-12 order_list row">
                    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12 order_list_left">
                        @if(file_exists(public_path('/assets/images/store-images/'. $order['image'] )) )
                        <img src="{{ asset('/assets/images/store-images/'. $order['image'] ) }}" width="100%">
                        @else
                            <img src="{{ asset('/assets/images/store-images/no_store_img.png') }}" width="100%">
                        @endif
                    </div>
                    <div class="col-lg-10 col-md-9 col-sm-8 col-xs-12 order_list_right">
                        <h4>{{ $order['name'] }}</h4>
                        <p>{{ $order['address'] }}</p>
                        <p>
                            <b>ORDER :</b> <a href="{{ route('get:store_order_details',[$order['id']]) }}" target="_blank" title="view detail"> #{{ $order['order_no'] }} </a> |
                            {{ /*$order['time_elapsed_string']*/($order['created_at']) }} |
                            <span style="color: #4bb1b1; text-transform: uppercase">{{ $order['payment_type'] == 1 ? "Cash" : ($order['payment_type'] == 2 ? "Card" : ($order['payment_type'] == 3 ? "Wallet" : "")) }}</span> |
                            <span style="color: #ff4500"><b>Order Status :</b>
                                @if(in_array($order['status'],[1]))
                                    New
                                @elseif(in_array($order['status'],[2]))
                                    Approved
                                @elseif(in_array($order['status'],[5,7]))
                                    Processing
                                @elseif(in_array($order['status'],[8]))
                                    Ongoing
                                @elseif(in_array($order['status'],[9]))
                                    Completed
                                @elseif(in_array($order['status'],[3,4]))
                                    Cancelled
                                @endif
                    </span>
                        </p>
                        @if(\Carbon\Carbon::parse($order['created_at'])->format('d/m/Y') == \Carbon\Carbon::now()->format('d/m/Y') && $order['status'] == 1 || $order['status'] == 2)
                            {{--                @if($order['status'] == 1 || $order['status'] == 2)--}}
                            {{--                    <button  class="md-trigger text-c-orenge"--}}
                            {{--                             data-modal="modal-1" orderno="{{ $order['order_no'] }}" orderid="{{ $order['id'] }}" order_status="4">Cancel Order</button>--}}
                            <button class="cancel" orderno="{{ $order['order_no'] }}" orderid="{{ $order['id'] }}" order_status="4">Cancel Order</button>
                        @endif
                        <div class="order_list_bottom">
                            <div class="col-md-9 col-sm-8 col-xs-12 order_list_left">
                                <p>
                                    {!! $order['order_product_list'] !!}

                                </p>
                            </div>
                            <div class="col-md-3 col-sm-4 col-xs-12 order_list_right">
                                <p>
                                    To Pay: <span> {{ $order['total_cost'] }} </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="float-right">
                {{ $get_orders_data->links() }}
            </div>

            <div class="modal fade" id="myModal" role="dialog">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Cancel Order Reason</h4>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="order_no">
                            <label for="address">Cancel Reason <span class="error">*</span></label>
                            <input id="cancel_reason" placeholder="Cancel Reason"
                                   class="materialize-textarea" required>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-default doc-save buttonid" id="cancel_order">
                                Cancel Order
                            </button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


<script src="{{ asset('assets/js/classie.js')}}" type="text/javascript"></script>
<script>

    // $(document).on('click', '.cancel', function (e) {
    $('.cancel').on('click', function (e) {
        e.preventDefault();
        var slug = $(this).attr('orderno');
        var orderid = $(this).attr('orderid');
        var order_status = $(this).attr('order_status');
        var txt, title, status, url;
        status = "Cancel";

        url = '{{ route("post:order_status") }}';
        title = status + " Order?";
        txt = "if press yes then Order " + status + " !";
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
                        text: "if " + status + " Order then Write " + status + " reason:",
                        type: "input",
                        showCancelButton: true,
                        closeOnConfirm: false,
                        animation: "slide-from-top",
                        inputPlaceholder: status + " Reason"
                    },
                    function (inputValue) {
                        if (inputValue === false) return false;
                        if (inputValue === "") {
                            swal.showInputError("You need to write " + status + " Order!");
                            return false
                        }
                        $.ajax({
                            url: url,
                            type: 'get',
                            data: {orderid: orderid, slug: slug, cancel_reason: inputValue, order_status: order_status},
                            success: function (result) {
                                if (result.success == true) {
                                    swal("success!", "You " + status + " Order reason is: " + inputValue, "success");
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
