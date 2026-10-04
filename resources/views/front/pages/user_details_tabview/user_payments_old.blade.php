{{--payments--}}
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-sweetalert/1.0.1/sweetalert.min.css">
<div class="content-container">

    <div class="container-fluid">
        <div class="payment">
        <div class="title">
            <h1>Paymentss</h1>
        </div>
        <div class="new-card">
        <h3>Add New Card</h3>
        <button class="delete" id="close">X</button>
        <div class="details">
            <div class="col-md-12 form-group ">
                <label for="holder_name">Holder Name:</label>
                <input class="form-control" id="holder_name" type="text" placeholder="Card Holder Full Name">
                <span class="error"></span>
            </div>
            <div class="col-md-12 form-group ">
                <label for="card_number">Card Number:</label>
                <input class="form-control" id="card_number" type="text" placeholder="Card Number">
            </div>
            <div class="col-md-4 col-sm-4 col-xs-4 form-group ">
                <label for="month">Expiry Month:</label>
                {{--<input class="form-control" id="month" type="text" placeholder="Month">--}}
                <select class="form-control" id="month">
                    <option value="01">Jan</option>
                    <option value="02">Feb</option>
                    <option value="03">Mar</option>
                    <option value="04">Apr</option>
                    <option value="05">May</option>
                    <option value="06">Jun</option>
                    <option value="07">Jul</option>
                    <option value="08">Aug</option>
                    <option value="09">Sep</option>
                    <option value="10">Oct</option>
                    <option value="11">Nov</option>
                    <option value="12">Dec</option>
                </select>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-4 form-group ">
                <label for="year">Expiry Year:</label>
                <input class="form-control" id="year" type="text" placeholder="Year">
            </div>
            <div class="col-md-4 col-sm-4 col-xs-4 form-group ">
                <label for="cvv">CVV:</label>
                <input class="form-control" id="cvv" type="password" placeholder="CVV">
            </div>
            <div class="col-md-12">
                <span class="error" id="card-error"></span>
            </div>
            <div class="form-group">
                <center>
                    <button id="card-save" class="address-btn btn-add-new">Save</button>
                </center>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
        <div class="payment-content">

        <div id="Cards">
            <h3>Credit & Debit Cards</h3>
            @foreach($payments as $payment)
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <div class="add-card">
                        <div class="add-left">
                            {{--<i class="fa fa-cc-visa fa-2x" aria-hidden="true"></i>--}}
                            <i class="fa fa-credit-card fa-2x" aria-hidden="true"></i>
                        </div>
                        <div class="add-right">
                            <h4>{{ "XXXX XXXX XXXX ". substr($payment->card_number,12,4) }}</h4>
                        </div>
                        <div class="bottom">
                            {{--<input type="button" class="address-btn btn-add-new" name="Pay" id="4242" readonly value="pay">--}}
                            <label class="radio-payment">
                                <span class="address-btn btn-add-new btn-delete-card" card_id="{{ $payment->id }}"> Delete </span>
                                {{--<input type="radio" name="radio" class="payment-type" value="card">--}}
                                {{--<span class="checkmark"></span>--}}
                            </label>
                        </div>
                    </div>
                </div>
            @endforeach
            {{--<div class="col-md-6 col-sm-6 col-xs-12">--}}
            {{--<div class="add-card">--}}
            {{--<div class="add-left">--}}
            {{--<i class="fa fa-cc-visa fa-2x" aria-hidden="true"></i>--}}
            {{--</div>--}}
            {{--<div class="add-right">--}}
            {{--<h4>XXXX XXXX XXXX 4242</h4>--}}
            {{--</div>--}}
            {{--<div class="bottom">--}}
            {{--<input type="button" class="address-btn btn-add-new" name="Pay" id="4242" readonly value="pay">--}}
            {{--<label class="radio-payment">--}}
            {{--<span class="address-btn btn-add-new"> Delete </span>--}}
            {{--<input type="radio" name="radio" class="payment-type" value="card">--}}
            {{--<span class="checkmark"></span>--}}
            {{--</label>--}}
            {{--</div>--}}
            {{--</div>--}}
            {{--</div>--}}
            {{--<div class="col-md-6 col-sm-6 col-xs-12">
                <div class="add-card add-new-card">
                    <div class="add-left">
                        <i class="fa fa-plus fa-2x" aria-hidden="true"></i>
                    </div>
                    <div class="add-right">
                        <h4>Add New</h4>
                    </div>
                    <div class="bottom">
                        <p class="address-txt1">20% cashback on 1st payment with Mastercard CC/DC (max
                            Rs.75/txn).TnC</p>
                        <input type="button" id="open-card" class="address-btn btn-add-new" name="Pay" readonly
                               value="Add New">
                    </div>
                </div>
            </div>--}}
        </div>
    </div>
</div>
    </div>
</div>
<script language="JavaScript" src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-sweetalert/1.0.1/sweetalert.min.js"
        type="text/javascript"></script>
<script>
    $('#open-card').on('click', function () {
        $('.new-card').show();
    });

    $('#close').on('click', function () {
        $('.new-card').hide();
    });

    $('#card-save').on('click', function () {

        var holder_name = $('#holder_name').val();
        var card_number = $('#card_number').val();
        var month = $('#month').val();
        var year = $('#year').val();
        var cvv = $('#cvv').val();
        var numbers = /^[0-9]+$/;
        if (holder_name == '') {
            $('#card-error').text("holder name is required");
        } else if (card_number == '') {
            $('#card-error').text("card number is required");
        } else if (month == '') {
            $('#card-error').text("expiry month is required");
        } else if (year == '') {
            $('#card-error').text("expiry year is required");
        } else if (cvv == '') {
            $('#card-error').text("cvv is required");
        } else if (isNaN(card_number)) {
            $('#card-error').text("card number is allow only digit");
        } else if (card_number.length < 16) {
            $('#card-error').text("card number is not 16 digit");
        } else if (card_number.length > 16) {
            $('#card-error').text("card number only allow 16 digit");
        } else if (isNaN(year)) {
            $('#card-error').text("year is allow only digit");
        } else if (isNaN(cvv)) {
            $('#card-error').text("cvv is allow only digit");
        } else if (year.length < 4 || year.length > 4) {
            $('#card-error').text("year is allow only 4 digit");
        } else if (cvv.length < 3 || cvv.length > 3) {
            $('#card-error').text("cvv is allow only 3 digit");
        } else {
            var form = new FormData();
            form.append('holder_name', holder_name);
            form.append('card_number', card_number);
            form.append('month', month);
            form.append('year', year);
            form.append('cvv', cvv);

            $.ajax({
                url: "{{ route('post:add_user_card') }}",
                type: 'post',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: form,
                ache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    if (data.success == true) {
                        location.reload();
                    }
                    else {
                        console.log(data);
                    }
                }
            });
        }
    });

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
                                // swal("success", "Your card details delete successfully :)", "success");
                                location.reload();
                            }
                        }
                    })
                } else {
                    swal("Cancelled", "Your card details is safe :)", "error");
                }
            });
    });
</script>
