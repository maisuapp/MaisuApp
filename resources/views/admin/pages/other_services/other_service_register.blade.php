@extends('admin.layout.other_service')
@section('title')
    Add Provider Services
@endsection
@section('page-css')
    <style>
        /*checkbox style*/
        .border-checkbox-section .border-checkbox-group .border-checkbox-label {
            height: 7px;
            padding-left: 20px;
            margin-right: 7px;
        }

        .border-checkbox-section .border-checkbox-group {
            margin-right: 15px;
        }
    </style>
@endsection
@section('page-content')

    <div class="pcoded-content">
        <!-- [ breadcrumb ] start -->
        <div class="page-header card">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="feather icon-edit-1 bg-c-blue"></i>
                        <div class="d-inline">
                            <h5>Provider Services Register</h5>
                            <span>Add Provider Services</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- [ breadcrumb ] end -->

        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <!-- Page body start -->
                    <div class="page-body">
                        <form id="main" method="post"
                              action="{{ route('post:provider-admin:service-register') }}"
                              enctype="multipart/form-data">
                            {{csrf_field() }}

                            <div class="card">
                                <div class="card-header">
                                    <h5>Add Provider Services</h5>
                                </div>
                                <div class="card-block">
                                    {{--@if(isset($service_category))--}}
                                    {{--<input type="hidden" name="id" value="{{$service_category->id}}">--}}
                                    {{--@endif--}}
                                    <div class="row">

                                        <div class="form-group col-sm-12">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Select Services:<sup
                                                            class="error">*</sup></label>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-12">
                                                    <div class="border-checkbox-section row">
                                                        @if(isset($service_category))
                                                            @foreach($service_category as $key => $service_category_details)
                                                                <div class="col-sm-4" style="margin: 10px 0">
                                                                    <div class="border-checkbox-group border-checkbox-group-primary">
                                                                        <input name="provider_services[]"
                                                                               value="{{ $service_category_details->id }}"
                                                                               class="border-checkbox"
                                                                               type="checkbox"
                                                                               id="checkbox{{ $service_category_details->id }}">
                                                                        <label class="border-checkbox-label"
                                                                               for="checkbox{{ $service_category_details->id }}"></label>
                                                                        {{ $service_category_details->name }}
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                    <span class="error">{{ $errors->first('delivery_boy') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header">
                                    <h5>Add Your Location</h5>
                                </div>
                                <div class="card-block">

                                    {{--@if(isset($service_category))--}}
                                    {{--<input type="hidden" name="id" value="{{$service_category->id}}">--}}
                                    {{--@endif--}}
                                    <div class="row">

                                        <div class="form-group col-sm-12">
                                            <div class="form-group row">
                                                <div class="col-sm-12">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-form-label">Address:<sup
                                                                    class="error">*</sup></label>
                                                        <div class="col-sm-12">
                                                            <input type="text" class="form-control" name="address"
                                                                   required readonly id="address"
                                                                   placeholder="Select Address on Map"
                                                                   value="{{ (isset($store_details)) ? $store_details->store_address : old('store_address') }}">
                                                            <input type="hidden"
                                                                   value="{{ (isset($store_details)) ? $store_details->address_lat : old('address_lat')}}"
                                                                   name="address_lat" id="lat">
                                                            <input type="hidden"
                                                                   value="{{ (isset($store_details)) ? $store_details->address_long : old('address_long')}}"
                                                                   name="address_long" id="lng">
                                                            <span class="error">{{ $errors->first('store_address') }}</span>
                                                            <span class="error">{{ $errors->first('address_lat') }}</span>
                                                            <span class="error">{{ $errors->first('address_long') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="map" id="map"
                                                             style="width: 100%; height: 300px;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <center>
                                        <button type="submit" class="btn btn-primary m-b-0">Save</button>
                                    </center>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Page body end -->
            </div>
        </div>
    </div>

@endsection
@section('page-js')
    @php $map_key = \App\model\GeneralSettings::first() @endphp
    <script src="//maps.googleapis.com/maps/api/js?key={{ isset($map_key)? ($map_key->map_key != Null) ? $map_key->map_key : 'AIzaSyDlDQ2_7bCJJ-hAsIpzJB5RV5blrWyRBsY' : 'AIzaSyDlDQ2_7bCJJ-hAsIpzJB5RV5blrWyRBsY' }}&libraries=places"></script>
    <script>
        function initialize() {
                    @if(isset($store_details))
            var lati = '{{ $store_details->address_lat }}';
            var longi = '{{ $store_details->address_long }}';
            var latlng = new google.maps.LatLng(lati, longi);
            @else
            var latlng = new google.maps.LatLng(35.6762, 139.6503);
                    @endif



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
                            infowindow.setContent(results[0].formatted_address);
                            infowindow.open(map, marker);
                        }
                    }
                });
            });
        }

        function bindDataToForm(address, lat, lng) {
            document.getElementById('address').value = address;
            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;
        }

        google.maps.event.addDomListener(window, 'load', initialize);
    </script>
@endsection

