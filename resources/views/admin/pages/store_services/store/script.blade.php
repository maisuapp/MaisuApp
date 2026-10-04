{{--<script src="{{ asset('assets/js/plugin/mdtimepicker.min.js')}}"></script>--}}
{{--<script type="text/javascript">--}}
{{--    $(document).ready(function () {--}}
{{--        $('.timepicker').mdtimepicker(); //Initializes the time picker--}}
{{--    });--}}
{{--</script>--}}
<script src="{{ asset('assets/js/plugin/mdtimepicker.min.js')}}"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('.timepicker').mdtimepicker().on('timechanged', function (e) {
            // console.log( $(this).children().first().val(e.value));
            // console.log( $(this).children().last().val(e.time));
            var time = e.time;
            var check_type = $(this).attr('check_type');
            if (check_type == 1) {
                var check_type_value = $(this).attr('check_open_type_value');
                var closeClassName = ".check_close_type_value_" + check_type_value;
                var openClassName = ".check_open_type_value_" + check_type_value;
                var errorClassName = ".check_open_type_error_" + check_type_value;

                var check_close_type_value = $(closeClassName).attr('data-time');
                if (check_close_type_value != "" && check_close_type_value != undefined) {
                    if (time >= check_close_type_value) {
                        $(errorClassName).show();
                        $(errorClassName).text("Invalid time range selection.");
                        $(openClassName).val('');
                        $(openClassName).attr('value', '');
                        $(openClassName).attr('data-time', '');
                        $(openClassName).attr('required', 'required');
                    } else {
                        $(errorClassName).hide();
                        console.log($(this).children().first().val(''));
                        console.log($(this).children().last().val(''));
                    }
                } else {
                    $(errorClassName).hide();
                    console.log($(this).children().first().val(''));
                    console.log($(this).children().last().val(''));
                }
            } else {
                console.log("dd");
                console.log(check_type);
                var check_type_value = $(this).attr('check_close_type_value');
                var openClassName = ".check_open_type_value_" + check_type_value;
                var closeClassName = ".check_close_type_value_" + check_type_value;
                var errorClassName = ".check_close_type_error_" + check_type_value;

                var check_open_type_value = $(openClassName).attr('data-time');
                if (check_open_type_value != "" && check_open_type_value != undefined) {
                    if (time <= check_open_type_value) {
                        $(errorClassName).show();
                        $(errorClassName).text("Invalid time range selection.");
                        $(closeClassName).val('');
                        $(closeClassName).attr('value', '');
                        $(closeClassName).attr('data-time', '');
                        $(closeClassName).attr('required', 'required');
                    } else {
                        $(errorClassName).hide();
                        console.log($(this).children().first().val(''));
                        console.log($(this).children().last().val(''));
                    }
                } else {
                    $(errorClassName).show();
                    $(errorClassName).text("First select open time.");
                    $(closeClassName).val('');
                    $(closeClassName).attr('value', '');
                    $(closeClassName).attr('data-time', '');
                    $(closeClassName).attr('required', 'required');
                }
            }
        });
    });
</script>

<script src="{{ asset('assets/js/upload_image.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $.uploadPreview({
            input_field: "#image-upload-1", // Default: .image-upload
            preview_box: "#image-preview-1", // Default: .image-preview
            label_field: "#image-label-1", // Default: .image-label
            label_default: "Choose Image", // Default: Choose File
            label_selected: "Change Image", // Default: Change File
            no_label: false // Default: false
        });
        $.uploadPreview({
            input_field: "#image-upload-2", // Default: .image-upload
            preview_box: "#image-preview-2", // Default: .image-preview
            label_field: "#image-label-2", // Default: .image-label
            label_default: "Choose Image", // Default: Choose File
            label_selected: "Change Image", // Default: Change File
            no_label: false // Default: false
        });
    });
</script>

<script type="text/javascript">
    $(function () {
        $("#checkbox_every").click(function () {
            if ($(this).is(":checked")) {
                $("#day_wise").hide();
                $(".every_d_cls_time").show();
                $(".every_d_opn_time").show();

            } else {
                $("#day_wise").show();
                $(".every_d_cls_time").hide();
                $(".every_d_opn_time").hide();
            }
        });

        $("#checkbox_every").click(function () {
            if ($(this).is(":checked")) {
                $("#day_wise").hide();
                $("#day_wise .checkbox_all").removeClass('required');
                $(".every_d_cls_time").show();
                $(".every_d_opn_time").show();
                $(".every_d_cls_time .time_all").addClass('required');
                $(".every_d_opn_time .time_all").addClass('required');
            } else {
                $("#day_wise").show();
                $("#day_wise .checkbox_all").addClass('required');
                $(".every_d_cls_time .time_all").removeClass('required');
                $(".every_d_opn_time .time_all").removeClass('required');
                $(".every_d_cls_time").hide();
                $(".every_d_opn_time").hide();
            }
        });
    });
</script>

<script type="text/javascript">
    $(document).ready(function () {
        $('#discount').on('click', function () {
            var discount_value = $(this).val();
            if (discount_value == 1) {
                $(this).val(0);
                $(this).removeAttr("checked");
                $('.discount').hide();
                $('#discount_amount').removeAttr("required");
                $('#discount_type').removeAttr("required");
            } else {
                $(this).attr("checked", "checked");
                $(this).val(1);
                $('.discount').show();
                $('#discount_amount').attr("required", "required");
                $('#discount_type').attr("required", "required");
            }
        });
        $('#food_type').on('click', function () {
            var food_type = $(this).val();
            if (food_type == 1) {
                $(this).val(2);
            } else {
                $(this).val(1);
            }
        });
    });
</script>
<script>
    $("input").not("#submit").keydown(function (event) {
        if (event.keyCode == 13) {
            event.preventDefault();
            return false;
        }
    });
</script>

@php $map_key = \App\model\GeneralSettings::first() @endphp
<script src="//maps.googleapis.com/maps/api/js?key={{ isset($map_key)? ($map_key->map_key != Null)? $map_key->map_key : 0 : 0 }}&libraries=places"></script>
<script>
    $("#adduser input").not("#submit").keydown(function (event) {
        if (event.keyCode == 13) {
            event.preventDefault();
            return false;
        }
    });

    function initialize() {
                @if(isset($store_details) && $store_details->address_lat != Null && $store_details->address_long != Null)
        var lati = '{{ $store_details->address_lat }}';
        var longi = '{{ $store_details->address_long }}';
        var latlng = new google.maps.LatLng(lati, longi);
                @else
        var latlng = new google.maps.LatLng(22.3039, 70.8022);
                @endif
        var map = new google.maps.Map(document.getElementById('map'), {
                center: latlng,
                zoom: 10
            });
        var marker = new google.maps.Marker({
            map: map,
            position: latlng,
            draggable: true,
            anchorPoint: new google.maps.Point(0, -29)
        });
        var input = document.getElementById('searchInput');
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
                        // console.log(results);
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
        document.getElementById('searchInput').value = address;
        // console.log(address);
    }

    google.maps.event.addDomListener(window, 'load', initialize);
</script>
