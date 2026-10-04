<div class="address-detail col-sm-12 mainAddressSection">
    <div class="title">
        <h1>Delivery Address
            <span id="address-select"></span>
            <span id="address-change">Change</span>
        </h1>
        @if(!$user_address->isEmpty())
            <span class="txt">Multiple addresses in this location</span>
        @endif
    </div>
    <div class="old-address-list row">
        @foreach($user_address as $address)
            <div class="col-md-6 col-sm-12 col-xs-12 list-address">
                <div class="address">
                    <div class="add-left">
                        @if($address->address_type == "home")
                            <i class="fa fa-home fa-2x" aria-hidden="true"></i>
                        @elseif($address->address_type == "work")
                            <i class="fa fa-briefcase fa-2x" aria-hidden="true"></i>
                        @else
                            <i class="fa fa-map-marker fa-2x" aria-hidden="true"></i>
                        @endif
                    </div>
                    <div class="add-right">
                        <h3>{{ ucfirst($address->address_type) }}</h3>
                        <p>{{ $address->address }}</p>
                        <h6 class="delivery_time" style="display: none">{{ $restaurant_details->estimated_delivery_time }} Mins</h6>
                        <a href="javascript:void(0)" class="address-btn" id="address-id-{{ $address->id }}">Delivery Here</a>
                    </div>
                </div>
            </div>
        @endforeach
            @php $add_count = count($user_address); @endphp
            @if($user_address_count < 5)
                <div class="col-md-6 col-sm-12 col-xs-12 @if($add_count % 2 == 0) add_new_left @else add_new_right @endif">
                    <div class="address new-address">
                        <div class="add-left">
                            <i class="fa fa-map-marker fa-2x" aria-hidden="true"></i>
                        </div>
                        <div class="add-right">
                            <h3>Add New Address</h3>
                        </div>
                        <center>
                            <a href="{{ route('get:user_address') }}" target="_blank" class="btn-add-new" >Add New</a>
                        </center>
                    </div>
                </div>
            @else
                <div class="col-md-6 col-sm-12 col-xs-12 @if($add_count % 2 == 0) add_new_left @else add_new_right @endif">
                    <div class="address new-address">
                        <div class="add-left">
                            <i class="fa fa-map-marker fa-2x" aria-hidden="true"></i>
                        </div>
                        <div class="add-right">
                            <h3>Manage Address List</h3>
                            <span>User Add Only Five Address</span>
                        </div>
                        <center>
                            <a href="{{ route('get:user_address') }}">
                                <button class="btn-add-new">Manage Address</button>
                            </a>
                        </center>
                    </div>
                </div>
            @endif
    </div>


    <div class="clearfix"></div>
</div>
<div class="select-address">
    <form name="add_address" id="add_address" method="post">
        <h3>Add New Address</h3>
        <div class="col-md-6">
            <div class="googlecontainer">
                <div class="input-field col s12">
                    <input id="pac-input" class="controls" type="text"
                           placeholder="Type city, zip or address here..">
                    <div id="map" style="width: 100%; height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 input-val-address">
            <input id="lat" name="lat" placeholder="Latitude" type="hidden" class="validate" required>
            <input id="long" name="long" placeholder="Longitude" type="hidden" class="validate" required>
            <div class="form-group">
                <label for="new_address">Address:</label>
                <input class="form-control" id="new_address" type="text"
                       autocomplete="on" value="" name="new_address">
            </div>
            <div class="form-group">
                <label for="flat_no">Door / Flat no.:</label>
                <input class="form-control" type="text" name="flat_no" id="flat_no">
            </div>
            <div class="form-group">
                <label for="landmark">Landmark:</label>
                <input class="form-control" type="text" name="landmark" id="landmark">
            </div>
            <div class="form-group">
                <label>Address Type</label>
                <select class="form-control" name="address_type" id="address_type">
                    <option value="home" selected>Home</option>
                    <option value="work">Work</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <button class="add-address-btn address-btn " id="add_new_user_address">Save &amp; Proceed</button>
        </div>
        <div class="clearfix"></div>
    </form>

</div>
{{--<script src="//maps.googleapis.com/maps/api/js?key=AIzaSyC703aCvZmrdfFlNxArFXzBL_OBNuF4AC4&libraries=places"></script>--}}
{{--<script src="http://maps.googleapis.com/maps/api/js?libraries=places" type="text/javascript"></script>--}}

<script>
    function addNewAddress() {
        $('.select-address').show();
    }

    $('#address-change').on('click', function () {
        $('#address-select').hide();
        $('#address-change').hide();
        $('.delivery_time').hide();
        $('.address-detail .list-address').show();
        $('.address-detail .new-address').show();
        $('.address-detail .old-address-list .selected').show().css('padding-left', '');
        $('.payment .payment-content').hide();
    });

    $('.address-detail .address-btn').on('click', function () {
        var id = $(this).attr('id');
        var address_id = id.replace('address-id-', '');
        var store_id = '{{ $restaurant_details->id }}';
        $.ajax({
            type: 'get',
            url: '{{ route('get:delivery_cost') }}',
            data: {
                store_id: store_id,
                address_id: address_id,
                user_taken_type: "n",
            },
            success: function (data) {
                if (data.success == true) {
                    $('#form_address_delivery').val(address_id);
                    var cart_element = $(".product-cart-list");
                    cart_element.empty();
                    cart_element.append(data.cart_item_data);
                    $(".badge").empty();
                    $(".badge").append(data.cart_item);

                    $("#all-store-to-pay-grand-total").empty();
                    $("#all-store-to-pay-grand-total").append(data.all_store_total_pay);

                } else {
                    console.log(data);
                    location.reload();
                }
            }
        });

        $(".address-detail .address-btn").removeClass('change-color');
        $('#' + id).addClass('change-color');
        $(".address-detail .list-address").removeClass('selected');
        $(this).closest('.list-address').addClass('selected');
        moveAddressToPayment();
    });

    $('#add_new_user_address').on('click', function () {
        $("#add_address").validate({
            rules: {
                new_address: {
                    required : true,
                },
                flat_no: {
                    required : true,
                },
                landmark: {
                    required : true,
                }
            },
            messages: {
                new_address: {
                    required :"Please enter proper address",
                },
                flat_no: {
                    required :"Please enter door/flat no",
                },
                landmark: {
                    required :"Please enter landmark",
                }

            },
            errorPlacement: function(error, element) {
                error.insertAfter(element);

            },
            submitHandler: function(form) {
                var lat = $('#lat').val();
                var long = $('#long').val();
                var new_address = $('#new_address').val();
                var flat_no = $('#flat_no').val();
                var landmark = $('#landmark').val();
                var address_type = $('#address_type').val();
                var form = new FormData();
                form.append('lat', lat);
                form.append('long', long);
                form.append('new_address', new_address);
                form.append('flat_no', flat_no);
                form.append('landmark', landmark);
                form.append('address_type', address_type);
                $.ajax({
                    url: '{{ route('post:new_address') }}',
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
                            console.log('success');
                            console.log(data);
                            console.log(data.address_type);
                            $(".address-detail .list-address").removeClass('selected');
                            if (data.address_type == "Home" || data.address_type == "home") {
                                var temp = '<i class="fa fa-home fa-2x" aria-hidden="true"></i>';
                            } else if (data.address_type == "Work" ||  data.address_type == "work") {
                                var temp = '<i class="fa fa-briefcase fa-2x" aria-hidden="true"></i>';
                            } else {
                                var temp = '<i class="fa fa-map-marker fa-2x" aria-hidden="true"></i>';
                            }
                            $('.old-address-list').append('<div class="col-md-6 col-xs-12 list-address selected"><div class="address"><div class="add-left">' + temp + '</div><div class="add-right"><h3>' + (data.address_type) + '</h3><p>' + data.address_name + '</p><h6>{{ $restaurant_details->estimated_delivery_time }} Mins</h6><button class="address-btn change-color" id="address-id-' + data.address_id + '">Delivery Here</button></div></div></div>');
                            $('#form_address_delivery').val(data.address_id);
                            moveAddressToPayment();
                        } else {
                            console.log('duplicate address');
                            $(".address-detail .list-address").removeClass('selected');
                            var id = '#address-id-' + data.address_id;
                            $(id).addClass('change-color');
                            $('#form_address_delivery').val(data.address_id);
                            $(id).closest('.list-address').addClass('selected');
                            moveAddressToPayment();
                        }
                    }
                });
            }
        });

    });

    function moveAddressToPayment() {
        $('#address-select').show();
        $('#address-change').show();
        $('.delivery_time').show();
        $('.select-address').hide();
        $('.address-detail .list-address').hide();
        $('.address-detail .old-address-list .selected').show().css('padding-left', '15px');
        $('.address-detail .new-address').hide();
        $('.payment .payment-content').show();
    }

    function address() {
        var addressInput = document.getElementById('new_address');
        var autocomplete = new google.maps.places.Autocomplete(addressInput);
        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            var place = autocomplete.getPlace();
//            document.getElementById('pac-input').value = place.formatted_address;
            document.getElementById('lat').value = place.geometry.location.lat();
            document.getElementById('long').value = place.geometry.location.lng();
            changeMarker(place.geometry.location.lat(), place.geometry.location.lng());
        });
    }

    google.maps.event.addDomListener(window, 'load', address);

    function initialize() {
        var latlng = new google.maps.LatLng(22.29480976015071, 70.81364918066402);
        var lat = 22.29480976015071;
        var long = 70.81364918066402;
        changeMarker(lat, long);
    }

    function changeMarker(lat, long) {
        var latlng = new google.maps.LatLng(lat, long);
        var map = new google.maps.Map(document.getElementById('map'), {
            center: latlng,
            zoom: 14
        });
        var marker = new google.maps.Marker({
            map: map,
            position: latlng,
            draggable: true,
            anchorPoint: new google.maps.Point(0, -29)
        });
        var input = document.getElementById('pac-input');
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
        var geocoder = new google.maps.Geocoder();
        var autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.bindTo('bounds', map);
        var infowindow = new google.maps.InfoWindow();
        autocomplete.addListener('place_changed', function () {
            infowindow.close();
            marker.setVisible(false);
            var place = autocomplete.getPlace();
            if (!place.geometry) {
                window.alert("Autocomplete's returned place contains no geometry");
                return;
            }

            // If the place has a geometry, then present it on a map.
            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(17);
            }

            marker.setPosition(place.geometry.location);
            marker.setVisible(true);

            bindDataToForm(place.formatted_address, place.geometry.location.lat(), place.geometry.location.lng());
            infowindow.setContent(place.formatted_address);
            infowindow.open(map, marker);

        });
// this function will work on marker move event into map
        google.maps.event.addListener(marker, 'dragend', function () {
            geocoder.geocode({'latLng': marker.getPosition()}, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (results[0]) {
                        bindDataToForm(results[0].formatted_address, marker.getPosition().lat(), marker.getPosition().lng());
//                        infowindow.setContent(results[0].formatted_address);
//                        infowindow.open(map, marker);
                    }
                }
            });
        });
    }

    function bindDataToForm(address, lat, lng) {
        document.getElementById('new_address').value = address;
//        document.getElementById('pac-input').value = address;
        document.getElementById('lat').value = lat;
        document.getElementById('long').value = lng;
    }

    google.maps.event.addDomListener(window, 'load', initialize);
</script>

