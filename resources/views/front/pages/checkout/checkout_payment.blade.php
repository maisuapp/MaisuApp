<div class="payment ">
    <div class="title">
        <h1>Payment</h1>
    </div>
    <div class="payment-content col-md-12">
        <div class="row">
            <div class="col-md-4">
                <div class="payment_type">
                    <div >
                        <a href="javascript:void(0)" class=" btn tablinks" onclick="openCity(event, 'Cash')" id="defaultOpen"><i class="fa fa-money" aria-hidden="true"></i> Cash</a>
                    </div>
                    <div >
                        <a href="javascript:void(0)" class="btn tablinks" onclick="openCity(event, 'Cards')"><i class="fa fa-credit-card" aria-hidden="true"></i> Credit & Debit Cards</a>
                    </div>
                    <div >
                        <a href="javascript:void(0)" class="btn tablinks" onclick="openCity(event, 'Wallet')"><i class="fas fa-wallet" aria-hidden="true"></i> Wallets </a>
                    </div>
                </div>
            </div>
            <div class=" col-md-8">

                <div id="Cash" class="tabcontent">
                    <h3>Cash</h3>
                    <label class="radio-payment">Cash On Delivery
                        <input type="radio" name="radio" class="payment-type" value="1">
                        <span class="checkmark"></span>
                    </label>
                </div>

                <div id="Cards" class="tabcontent">
                    <h3>Credit & Debit Cards</h3>
                    <div class="row col-md">
                        @if(count($card_list) > 0)
                        @foreach($card_list as $single_card)
                            <div class="add-card ">
                                <div class="add-left">
                                    <i class="fa fa-cc-visa fa-2x" aria-hidden="true"></i>
                                </div>
                                <div class="add-right">
                                    <h4>{{ isset($single_card->card_number)?'XXXX-XXXX-XXXX-'.substr($single_card->card_number,-4):"N/A" }}</h4>
                                </div>
                                <div class="bottom">
                                    <label class="radio-payment">
                                        <span class="address-btn"> Pay </span>
                                        <input type="radio" name="radio" class="payment-type" value="2">
                                        <span class="checkmark"></span>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                        @else
                            <h6>
                            <label>Currently you have not added card. Click <a href="{{ route('get:user_payments') }}" target="_blank">Add New Card</a></label>
                            </h6>
                        @endif
                    </div>
{{--                    <div class="add-card">--}}
{{--                        <div class="add-left">--}}
{{--                            <i class="fa fa-cc-visa fa-2x" aria-hidden="true"></i>--}}
{{--                        </div>--}}
{{--                        <div class="add-right">--}}
{{--                            <h4>XXXX XXXX XXXX 4242</h4>--}}
{{--                        </div>--}}
{{--                        <div class="bottom">--}}
{{--                            <label class="radio-payment">--}}
{{--                                <span class="address-btn"> Pay </span>--}}
{{--                                <input type="radio" name="radio" class="payment-type" value="2">--}}
{{--                                <span class="checkmark"></span>--}}
{{--                            </label>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="add-card">--}}
{{--                        <div class="add-left">--}}
{{--                            <i class="fa fa-cc-visa fa-2x" aria-hidden="true"></i>--}}
{{--                        </div>--}}
{{--                        <div class="add-right">--}}
{{--                            <h4>XXXX XXXX XXXX 4242</h4>--}}
{{--                        </div>--}}
{{--                        <div class="bottom">--}}
{{--                            --}}{{--<input type="button" class="address-btn btn-add-new" name="Pay" id="4242" readonly value="pay">--}}
{{--                            <label class="radio-payment">--}}
{{--                                <span class="address-btn"> Pay </span>--}}
{{--                                --}}{{--                        <span class="address-btn btn-add-new"> Pay </span>--}}
{{--                                <input type="radio" name="radio" class="payment-type" value="2">--}}
{{--                                <span class="checkmark"></span>--}}
{{--                            </label>--}}
{{--                        </div>--}}
{{--                    </div>--}}
        {{--            <div class="add-card add-new-card">--}}
        {{--                <div class="add-left">--}}
        {{--                    <i class="fa fa-plus fa-2x" aria-hidden="true"></i>--}}
        {{--                </div>--}}
        {{--                <div class="add-right">--}}
        {{--                    <h4>Add New</h4>--}}
        {{--                </div>--}}
        {{--                <div class="bottom">--}}
        {{--                    <p class="address-txt1">20% cashback on 1st payment with Mastercard CC/DC (max Rs.75/txn).TnC</p>--}}
        {{--                    <input type="button" class="address-btn btn-add-new" name="Pay" readonly value="Add New">--}}
        {{--                </div>--}}
        {{--            </div>--}}
                </div>

                <div id="Wallet" class="tabcontent">
                    <h3>Wallet</h3>
                    <label class="radio-payment">Wallet
                        <input type="radio" name="radio" class="payment-type" value="3">
                        <span class="checkmark"></span>
                    </label>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
    $('.payment-type').on('change', function () {
        var payment_type = $(this).val();
        $('#form_payment_type').val(payment_type);
    });

    function openCity(evt, cityName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        document.getElementById(cityName).style.display = "block";
        evt.currentTarget.className += " active";
    }

    // Get the element with id="defaultOpen" and click on it
    document.getElementById("defaultOpen").click();
</script>
