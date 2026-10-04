{{--offers--}}
<div class="content-container">

    <div class="container-fluid">
        <div class="col-md-12 col-xs-12">
    <div class="title">
        <h2>Offer List</h2>
    </div>
            @foreach($offers as $offer)
                <div class="col-md-6 col-sm-6 col-xs-12 offer_list">
                    <div class="offer">
                        <h4>{{ $offer->promo_code }}</h4>
                        <table>
                            <tr>
                                <td>
                                    Offer Discount
                                </td>
                                <td>
                                    {{--1 = amount & 2 = percentage--}}
                                    @if($offer->discount_type == 1)
                                        <i class="fa fa-usd" aria-hidden="true"></i> <span style="font-weight: 600">{{ $offer->discount_amount }}</span>
                                    @else
                                        <span style="font-weight: 600">{{ $offer->discount_amount."%" }}</span>
                                    @endif
                                    in any one order
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Valid till
                                </td>
                                <td>
                                    Expire in {{ date('d-m-Y', strtotime($offer->expiry_date_time)) }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
