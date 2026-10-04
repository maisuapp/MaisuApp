{{--favourite--}}
<div class="content-container">

    <div class="container-fluid">
        <div class="vendor-list scrolls">
    @if($restaurants != Null)
        @foreach($restaurants as $key=>$restaurant)
            {{--            <a href="{{ route('get:restaurant_data',[$locations[$key],$restaurant->slug]) }}"--}}
            <a href="{{ route('get:restaurant_data',[$restaurant->id]) }}"
               class="scroll">
                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 food-list">
                    <div class="top">
                        <span class="heart" id="user-fav"><i class="fa fa-heart" aria-hidden="true"></i></span>
                        <img src="{{ asset('/assets/images/store-images/'.$restaurant->image) }}">
                    </div>
                    <div class="bottom">
                        <h4 class="name">{{ $restaurant->name }}</h4>
                        <h5>
                            @foreach($cuisines[$key] as $cuisine)
                                {{ $loop->first ? '' : ', ' }}
                                {{ $cuisine->name }}
                            @endforeach
                        </h5>
                        <div class="about">
                            <span class="rate"><i class="fa fa-star"></i> 5.0</span>
                            <p>
                                @if(isset($res_time))
                                    @if(!empty($res_time[$key]))
                                        {{ date('g:i A', strtotime($res_time[$key]->res_opens)) }}
                                        to
                                        {{ date('g:i A', strtotime($res_time[$key]->res_closes)) }}
                                    @endif
                                @endif
                            </p>
                            <p><i class="fa fa-phone"></i> {{ App\model\User::ContactNumber2Stars($restaurant->contact_details) }}</p>
                        </div>
                        @if( $restaurant->offer_min_amount != Null && $restaurant->offer_percentage != Null)
                            <div class="discount">
                                <p>
                                    <img src="{{asset('/assets/front/images/icons/discount-24.png')}}"> {{ $restaurant->offer_percentage."%" }}
                                    off on orders above Rs {{ $restaurant->offer_min_amount }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </a>
        @endforeach
    @endif
    <div class="more" style="display: none">
        <a href="" id="loadMore">Load More</a>
    </div>
</div>
    </div>
</div>
