<div class="card">
    <h5 class="card-header ">Add Wallet</h5>
    <div class="card-body addWalletCls">
            <div class="form-row fieldRow mb-2 text-center" >
                <div class="form-group col-12 pt-4 mb-0 ">
                    <label class="card-label currBalLbl">Current Balance</label>
                </div>
                <div class="form-group col-12">
                    <label class="card-label currBalAmt font-weight-bold"><span class="currency">
                            @if(isset($total_avail_bal))
                                {{ ($total_avail_bal->remaining_balance > 0)?number_format($total_avail_bal->remaining_balance,2):0.00 }}
                            @else
                                0.00
                            @endif
                        </span></label>
                </div>
            </div>
            <div class="form-row " >
                <div class="form-group col-12 pt-4 mb-0 ">
                    <label class="card-label font-weight-bold addMoneyLbl m-0">Add Money</label>
                </div>
                <div class="form-group col-12">
                    <label class="card-label  secureLbl">We Used Secure Technology to Protect Your Data</label>
                </div>
            </div>
            <form action="{{ route('post:user_wallet') }}" id="add_wallet_frm" name="add_wallet_frm" method="post" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="form-row mb-2 fieldRow">
                    <div class="form-group col-lg-8 col-md-8 col-sm-12 col-xs-12 pl-3 pr-3">
                        <label class="card-label" for="firstname">Recharge Amount:</label>
                        <input type="text" class="form-control textBox" autocomplete="off" id="amount" name="amount" placeholder="Enter amount">
                        <input type="hidden" class="form-control textBox" autocomplete="off" id="card_id" name="card_id" value="0" >
                    </div>
                    <div class="form-group col-lg-4 col-md-4 col-sm-12 col-xs-12 pl-3 pr-3  pt-4">
                        <button type="submit" class="btn btn-block btn-outline-secondary btnColor  " >Add Amount </button>
                    </div>
                </div>
            </form>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="paymentModalPopup" tabindex="-1" role="dialog" aria-labelledby="paymentModalPopup" aria-hidden="true">
    <div class="modal-dialog <!--modal-dialog-centered-->" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Add Money to Wallet</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div class="cc-selector-2 pt-3 " id="card_lists">
                    <label class="font-weight-bold cardtextBorderBottom">Select Card</label><br/>
                    @if(isset($card_list) && count($card_list) > 0)
                        <div class="col-md-12">
                            <div class="row">
                                @foreach($card_list as $key=>$single_card_list)
                                    <div class="col-md-6">
                                        <input id="card{{$single_card_list->id}}" {{ ($key == 0)?'checked="checked"':"" }} class="selectedCardCls"  type="radio" name="card_id" value="{{$single_card_list->id}}" />
                                        <label class="drinkcard-cc " for="card{{$single_card_list->id}}"><i class="fas fa-credit-card"></i> xx-{{ substr($single_card_list->card_number,-4) }}</label>
                                    </div>
                                @endforeach
                            </div>

                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <div class=" col-6">
                    <a type="button" class="float-left btn btnColor btncouponcart" href="{{ route('get:user_payments') }}">Add New Card</a>
                </div>
                <div class=" col-6">
                    <button type="button" class="float-right btn btnColor btncouponcart addWalletBtn">Pay Now</button>
                    <button type="button" class="float-right btn btn-secondary mr-2" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

@if(isset($get_wallet_history) && count($get_wallet_history) > 0 )
<div class="clearfix pt-3 pb-3"></div>
<div class="card">
    <h5 class="card-header ">User wallet histroy</h5>
    <div class="card-body addWalletCls">
        @if(count($get_wallet_history) > 0)
            @foreach($get_wallet_history  as $singel_history)
                @php
                    if($singel_history->transaction_type == 1){
                        $img_path = asset('/assets/front/img/png/transaction_plus.png');
                        $currency_color ="creditColor";
                        $currency_sign ="";

                    }else{
                        $img_path = asset('/assets/front/img/png/transection_up_arrow.png');
                        $currency_color ="debitColor";
                        $currency_sign ="-";
                    }
                @endphp
                <div class="form-row pt-3 d-flex borderBtmClsWallet">
                        <div class="form-group col-lg-1  col-md-2 col-sm-2 col-xs-2 pl-3 pr-3">
                            <img src="{{$img_path}}" >
                        </div>
                        <div class="form-group col-lg-9 col-md-8 col-sm-7 col-xs-7 pl-3 pr-3">
                            <div class="row">
                                <label class="card-label transactionText m-0">{{ ($singel_history->subject!="")?$singel_history->subject:"--" }} </label>
                            </div>
                            <div class="row">
                                <label class="card-label transactionDate">{{ ($singel_history->created_at!="")?date("D d, M, Y, H:s a",strtotime($singel_history->created_at)):"" }}</label>
                            </div>
                        </div>
                        <div class="form-group col-lg-2 col-md-2 col-sm-3 col-xs-3 pl-3 pr-3">
                            <label class="card-label transactionText {{$currency_color}}">{{ $currency_sign }}<span class="currency">{{($singel_history->amount>0)?$singel_history->amount:0}}</span></label>
                        </div>
                </div>
            @endforeach
                <div class="float-right  pt-2">
                    {!! $get_wallet_history->links() !!}
                </div>
        @else

        @endif
    </div>
</div>
@endif



<script type="text/javascript">
    $(document).ready(function (){
        $("#add_wallet_frm").validate({
            rules: {
                amount: {
                    required : true,
                    min:1
                }
            },
            messages: {
                amount: {
                    required :"Please enter amount",
                    min :"Amount must be greater than 0.",
                }

            },
            errorPlacement: function(error, element) {
                if(element.attr("name") == "expire_date") {
                    error.insertAfter( element.parent("div"));
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler: function(form) {
                $('#paymentModalPopup').modal('show');
            }
        });

        $(document).on("click",".addWalletBtn",function (){
            $('#paymentModalPopup').modal('hide');
            var card_id = $(".selectedCardCls").val();
            $("#card_id").val(card_id);
            // $("#add_wallet_frm").submit();
            $('#add_wallet_frm')[0].submit();
        });

    });

</script>
