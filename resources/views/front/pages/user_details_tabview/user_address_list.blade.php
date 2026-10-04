{{--address list--}}
<div class="content-container">

    <div class="container-fluid">
        <div class="address-detail col-md-12">

    <div class="title">
        <h2>Delivery Address</h2>
        <span class="txt">User Add Only Five Address</span>
    </div>
    <div class="select-address hide" id="address">
        <h3>Add New Address</h3>
        <button class="delete" id="close">X</button>
        <div class="row pb-3">
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="googlecontainer">
                    <div class="input-field col s12">
                        <input id="pac-input" class="controls" type="text"
                               placeholder="Type city, zip or address here..">
                        <div id="map" style="width: 100%; height: 300px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12 input-val-address">
                <input id="lat" name="lat" placeholder="Latitude" type="hidden" class="validate" required>
                <input id="long" name="long" placeholder="Longitude" type="hidden" class="validate" required>
                <input id="address_id" name="address_id" type="hidden">
                <div class="form-group">
                    <label for="new_address">Address:</label>
                    <input class="form-control" id="new_address" type="text"
                           autocomplete="on" value="" name="new_address">
                </div>
                <label id="new_address-error" class="error" for="new_address"></label>
                <div class="form-group">
                    <label for="flat_no">Door / Flat no :</label>
                    <input class="form-control" type="text" name="flat_no" id="flat_no">
                </div>
                <label id="flat_no-error" class="error" for="new_address"></label>
                <div class="form-group">
                    <label for="landmark">Landmark:</label>
                    <input class="form-control" type="text" name="landmark" id="landmark">
                </div>
                <label id="landmark-error" class="error" for="landmark"></label>
                <div class="form-group">
                    <label>Address Type</label>
                    <select class="form-control" name="address_type" id="address_type">
                        <option value="home" selected>Home</option>
                        <option value="work">Work</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <button class="add-address-btn address-btn" id="add_new_user_address">Save &amp; Proceed</button>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
    <div class="old-address-list row">
        @foreach($user_address as $address)
            <div class="col-md-6 col-sm-6 col-xs-12 list-address pb-3">
                <div class="address">
                    <span class="edit-address-id-{{ $address->id }}">
                        @php
                            $address_lat_long = array_map('trim', explode(",", $address->lat_long));
                        @endphp
                    <input id="edit_address" type="hidden" value="{{ $address->address }}">
                    <input id="edit_lat" type="hidden" value="{{ trim($address_lat_long[0]) }}">
                    <input id="edit_long" type="hidden" value="{{ trim($address_lat_long[1]) }}">
                    <input id="edit_address_type" type="hidden" value="{{ $address->address_type }}">
                    <input id="edit_landmark" type="hidden" value="{{ $address->landmark }}">
                    <input id="edit_flat_no" type="hidden" value="{{ $address->flat_no }}">
                    </span>
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
                        <a href="#address" class="change_url">
                            <button class="address-btn edit" id="edit-address-id-{{ $address->id }}">Edit</button>
                        </a>
                        <button class="address-btn delete" id="delete-address-id-{{ $address->id }}">Delete</button>
                    </div>
                </div>
            </div>
        @endforeach
        @if($user_address_count < 5)
            <div class="col-md-6 col-sm-6 col-xs-12 list-address">
                <div class="address new-address">
                    <div class="add-left">
                        <i class="fa fa-map-marker fa-2x" aria-hidden="true"></i>
                    </div>
                    <div class="add-right">
                        <h3>Add New Address</h3>
                    </div>

                    <a href="#address">
                        <button class="btn-add-new " onclick="addNewAddress()">Add New</button>
                    </a>

                </div>
            </div>
        @endif
    </div>

    <div class="clearfix"></div>
</div>
    </div>
</div>

<script>
    if (window.location.hash && window.location.hash == '#address') {
        window.location = '';
    }

    function addNewAddress() {
        $('#new_address').val('');
        $('#flat_no').val('');
        $('#landmark').val('');
        $('#address_type').val('home');
        $('#address_id').val('');
        var lat = 22.29480976015071;
        var long = 70.81364918066402;
        changeMarker(lat, long);
        $('.select-address').show();
    }

    $('#close').on('click', function () {
        $('.select-address').hide();
        if (window.location.hash && window.location.hash == '#address') {
            window.location = '';
        }
    });

    $('.address-detail .address-btn').on('click', function () {
        var id = $(this).attr('id');
        var str1 = id;
        var str2 = "edit-address-id-";
        var str3 = "delete-address-id-";
        var address_id;
        if (str1.indexOf(str2) != -1) {
            address_id = id.replace('edit-address-id-', '');
            $('.select-address').show();
            $('.select-address h3').text("Edit Address");
            $('#new_address').val($('.' + id + ' #edit_address').val());
            $('#flat_no').val($('.' + id + ' #edit_flat_no').val());
            $('#landmark').val($('.' + id + ' #edit_landmark').val());
            $('#address_type').val($('.' + id + ' #edit_address_type').val());
            $('#address_id').val(address_id);
            changeMarker($('.' + id + ' #edit_lat').val(), $('.' + id + ' #edit_long').val());
        }
        if (str1.indexOf(str3) != -1) {
            address_id = id.replace('delete-address-id-', '');
            var form = new FormData();
            form.append('address_id', address_id);
            $.ajax({
                url: "{{ route('post:delete_user_address') }}",
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
                        location.reload();
                    }
                }
            });
        }
    });

    $('#add_new_user_address').on('click', function () {
        $("#new_address-error").text("");
        $("#flat_no-error").text("");
        $("#landmark-error").text("");
        var formerror = 0;
        var lat = $('#lat').val();
        var long = $('#long').val();
        var address_id = $('#address_id').val();
        var new_address = $('#new_address').val();
        var flat_no = $('#flat_no').val();
        var landmark = $('#landmark').val();
        var address_type = $('#address_type').val();
        var URL;

        if(address_id.length === 0){
            if(lat.length === 0 || long.length === 0 || new_address.length=== 0) {
                $("#new_address-error").text("Please enter address");
                formerror = 1;
            }
        }

        if(flat_no.length === 0) {
            $("#flat_no-error").text("Please enter door/flat no");
            formerror = 1;
        }
        if(landmark.length === 0) {
            $("#landmark-error").text("Please enter landmark");
            formerror = 1;
        }
        if(formerror === 0) {
            if (address_id.length === 0) {
                URL = "{{ route('post:new_address') }}";
            } else {
                URL = "{{ route('post:edit_user_address') }}";
            }
            var form = new FormData();
            form.append('lat', lat);
            form.append('long', long);
            form.append('address_id', address_id);
            form.append('new_address', new_address);
            form.append('flat_no', flat_no);
            form.append('landmark', landmark);
            form.append('address_type', address_type);
            $.ajax({
                url: URL,
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
                        location.reload();
                        if (window.location.hash && window.location.hash == '#address') {
                            window.location = '';
                        }
                    }
                    else {
                        location.reload();
                        console.log('duplicate address');
                        if (window.location.hash && window.location.hash == '#address') {
                            window.location = '';
                        }
                    }
                }
            });
        }

    });

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
