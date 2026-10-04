@if(isset($payments) && count($payments) > 0)
<div class="card"  id="hideManageCard">
    <h5 class="card-header ">Manage Card</h5>
    <div class="card-body addNewCardCls">

        @foreach($payments as $payment)
            <div class="form-row fieldRow mb-2 singleCard_{{ $payment->id }}">
                <div class="form-group col-12 pl-3 pr-3 mb-0 ">
                    <span><i class="fa fa-credit-card cardColor"></i></span> <label class="card-label cardLabel pl-3" for="firstname">{{ ($payment->holder_name!="")?$payment->holder_name:"--" }}</label>
                </div>
                <div class="form-group col-12 pl-5 pr-3">
                    <label class="card-label cardLbl">{{ "XXXX XXXX XXXX ". substr($payment->card_number,12,4) }}</label>
                    <div class="pull-right"><a href="javascript:void(0)" class="removeCls btn-delete-card"  card_id="{{ $payment->id }}" >Remove <span class="pl-2"><i class="fa fa-close"></i></span></a></div>
                </div>
            </div>
        @endforeach
    </div>
</div>
<div class="clearfix pt-3 pb-3"></div>
@endif

<div class="card">
    <h5 class="card-header ">Add New Card</h5>
    <div class="card-body addNewCardCls">
        <form action="{{ route('post:add_user_card') }}" id="add_card_frm" name="add_card_frm" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-row fieldRow mb-2">
                <div class="form-group col-12 pl-3 pr-3">
                    <label class="card-label" for="firstname">Card Holder Name:</label>
                    <input type="text" class="form-control textBox" id="holder_name" name="holder_name" autocomplete="off" placeholder="Enter Card Holder Name">
                </div>
            </div>
            <div class="form-row fieldRow mb-2">
                <div class="form-group col-12 pl-3 pr-3">
                    <label class="card-label" for="firstname">Your Card Number:</label>
                    <input type="text" class="form-control textBox" id="card_number" name="card_number" autocomplete="off" placeholder="Enter Your Card Number">
                </div>
            </div>
            <div class="form-row mb-2 fieldRow">
                <div class="form-group col-lg-6 col-md-6 col-sm-12 col-xs-12 pl-3 pr-3">
                    <label class="card-label" for="firstname">Expiry Date:</label>
                    <div class="input-group date" id="datetimepicker1" >
                        <input type="text" name="expire_date" id="datepicker1" class="form-control form-font-cls datetimepicker-input textBox" placeholder="Enter Card Expiry Date" data-target="#datetimepicker1" autocomplete="off" />
<!--                        <div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>-->
                    </div>
                </div>
                <div class="form-group col-lg-6 col-md-6 col-sm-12 col-xs-12 pl-3 pr-3">
                    <label class="card-label" for="firstname">CVV:</label>
                    <input type="password" class="form-control textBox" autocomplete="off" id="cvv" name="cvv" placeholder="Enter Card CVV">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-6 mx-auto">
                    <button type="submit" class="btn btn-block btn-outline-secondary btnColor  ">Add Credit / Debit Card </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function (){

        $('#datepicker1').datetimepicker({
            format: "yyyy-mm",
            autoclose: true,
            minView: 'month',
            pickTime: false,
            minDate:new Date(),
            fontAwesome: true,
            startDate: new Date(),
            icons: {

                leftArrow: "fa fa-caret-left",
                rightArrow: "fa fa-caret-right",

            }

        });

        $("#add_card_frm").validate({
            rules: {
                holder_name: {
                    required : true,
                },
                card_number: {
                    required : true,
                    number:true,
                    maxlength : 16,
                    minlength : 14,
                },
                expire_date: {
                    required : true,
                },
                cvv: {
                    required : true,
                    number : true,
                    maxlength : 3,
                    minlength : 3,

                },


            },
            messages: {
                holder_name: {
                    required :"Please enter card holder name",

                },
                card_number: {
                    required :"Please enter card number",
                    number :"Please enter valid card number",
                    maxlength :"Please enter card number between 14 to 16 digit",
                    minlength :"Please enter card number between 14 to 16 digit",
                },
                expire_date: {
                    required :"please enter expire date",
                },
                cvv: {
                    required :"Please enter cvv number",
                    number :"Please enter valid cvv number",
                    maxlength :"Please enter only 3 digit",
                    minlength :"Please enter only 3 digit",

                },

            },
            errorPlacement: function(error, element) {
                if(element.attr("name") == "expire_date") {
                    error.insertAfter( element.parent("div"));
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler: function(form) {
                form.submit();
            }
        });

        //delete card
        $(document).on('click', '.btn-delete-card', function (e) {
            e.preventDefault();
            var card_id = $(this).attr('card_id');
            swal({
                    title: "Are you sure to delete this card details?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-danger",
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "No, cancel!",
                    closeOnConfirm: false,
                    closeOnCancel: false
                },
                function (isConfirm) {
                    if (isConfirm) {
                        $.ajax({
                            type: 'get',
                            url: '{{ route('get:delete_user_card') }}',
                            data: {card_id: card_id},
                            success: function (result) {
                                if (result.success == true) {
                                    // $(".singleCard_"+card_id).remove();
                                    if($('.addNewCardCls > div').length > 1) {
                                        $(".singleCard_"+card_id).remove();
                                    } else {
                                        $("#hideManageCard").hide();
                                    }
                                    swal("success", result.errorMsg, "success");
                                    // location.reload();
                                }else{
                                    swal("Cancelled", result.errorMsg, "error");
                                }
                            }
                        })
                    } else {
                        swal("Cancelled", "Your card details is safe :)", "error");
                    }
                });
        });
    });

</script>
