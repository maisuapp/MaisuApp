@extends('front.layout.default')
@section('title')
    <title>Service Booking</title>
@endsection
@section('meta-tags')
    <meta name="description" content="Ride Services">
    <meta name="keywords" content="Ride Services">
@endsection
@section('page-css')


@endsection

@section('page-content')
    <main id="main " class="providerBookingMainSection">

        <div class="container-fluid minheights mainSection">

            <div class="row">
                <div  class="col-lg-4 col-md-4 col-sm-12 col-xs-12 order-md-1 order-12 mb-5 contentPart minheights" id="style-3" >
                    <div id="cover-spin">
<!--                        <a class="boxclose cancelRide" data-toggle="modal" data-target="#exampleModal" id="boxclose" title="Cancel"></a>-->
                    </div>
                    <div id="retry-spin">
                        <a href="javascript:void(0)" class="boxclose1 retryRide" title="Retry"></a>
                        <a class="boxclose cancelRide" data-toggle="modal" data-target="#exampleModal" id="boxclose" title="Cancel"></a>
                    </div>



                    <div class="flex-column flex-nowrap vh-100 overflow-auto rightPartSection">
                        <div class="row">
                            <div class="col-12">
                                <div class="padding-10">
                                    <div>
                                        <a href="{{ route('get:homepage') }}"> <i class="fas fa-angle-left"></i></a> <span class="ride-service-title">{{ isset($service_cat_name)?$service_cat_name:"Service Booking" }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <form name="providerServiceFrm" class="providerServiceFrm" id="providerServiceFrm" method="post" href="javscript:void(0)">
                            @csrf
                            <div class="row service-select-section">
                                <div class="col-md-12">

                                    <div class="padding-10">
                                        <div class="text-center">
                                            <div class="pickup-drop-location provider-pickup-drop-location">
                                                <div class="form-group providerPickup">
                                                    <input type="text" name="source_address" class="source_address" id="source_address" value="{{ ($current_address!="")?$current_address:"" }}" placeholder="Pick up location"  >
                                                    <input type="hidden" name="source_lat" id="source_lat" class="source_lat" value="{{ ($current_latitude!="")?$current_latitude:"0" }}" >
                                                    <input type="hidden" name="source_long" id="source_long" class="source_long" value="{{ ($current_longitude!="")?$current_longitude:"0" }}" >
{{--                                                    <span class="curr_loc"> <span>Current Location</span>  <span class="material-icons getLocationicon">my_location</span></span>--}}
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="providerMainPart">

                                <!-- Section of  Provider Service -->
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <div class="padding-10">
                                            <div class="ride-inner-title">
                                                <span >Select Category</span>
                                            </div>
                                        </div>
                                        <div class="borderCls">
                                            <div class="input-group p-1">
                                                <select name="category" id="category" class="custom-select category border-0">
                                                    @if(isset($sub_service_category_lists))
                                                        <option value="0">Please select category</option>
                                                        @foreach($sub_service_category_lists as $single_sub_service_cat)
                                                            <option value="{{$single_sub_service_cat->id}}">{{$single_sub_service_cat->name}}</option>
                                                        @endforeach
                                                    @else
                                                        <option value="0">Please select category</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Section of  Provider Service -->

                                <!-- Section of  Provider List  -->
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <div class="padding-10">
                                            <div class="ride-inner-title">
                                                <span >Provider Lists</span>
                                            </div>
                                        </div>
                                        <div class="ProviderList">

                                            <div class="singelProvider row borderBtmCls">
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 font-weight-bold text-center">
                                                    Provider Not Found....
                                                </div>
                                            </div>

    <!--                                        <div class="singelProvider row borderBtmCls">
                                                <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12 d-flex">
                                                    <img src="http://localhost/php/order-angel/public/assets/front/img/png/taxi.jpg" class="providerImg rounded-circle align-self-start mr-3" alt="..." width="64" height="64">
                                                    <div class="media-body">
                                                        <div class="d-flex usernamePart pt-2">
                                                            <span class="mt-0 username pr-3">John Cena John Cena John Cena </span>
                                                            <div class="ratting"><i class="fa fa-star starColor pr-1"></i>5.4</div>
                                                        </div>
                                                        <div class="d-flex locationPart">
                                                            <span class="providerLocation pr-4 ">
                                                                1.2 Km Away
                                                            </span>
                                                            <span>
                                                                <i class="fa fa-shopping-bag pr-2"></i>
                                                                200
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12 pt-2">
                                                    <button class="btn btn-outline-secondary btnColor btn-block p-1" type="button">Book Service</button>
                                                </div>
                                            </div>
        -->

                                        </div>
                                    </div>

                                </div>
                                <!-- End Section of  Provider List -->

                            </div>

                            <div class="providerDetailsMainPart" style="display: none">

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="padding-10">
                                                <div class="ride-inner-title">
                                                    <a href="{{ isset($service_cat_slug)?route('get:service-booking',[$service_cat_slug]):"#" }}"> <i class="fas fa-angle-left pr-2"></i></a> <span class="ride-inner-title">Provider Details</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- start disaply provider with package -->
                                    <div class="row providerPackageDetail">

                                            <div class="providerListWithPackage">

                                            </div>


                                    </div>
                                    <!-- End disaply provider with package -->

                                    <!-- Section of  promocode -->
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="padding-10">
                                                <div class="ride-inner-title">
                                                    <span >Payment Types</span>
                                                </div>
                                            </div>
                                            <div class="borderCls">
                                                <div class="input-group p-1">
                                                    <select name="payment_type"  id="payment_type" class="custom-select border-0">
                                                        <option value="1">Cash</option>
                                                        <option value="2">Card</option>
                                                        <option value="3">Wallet</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

{{--                                        <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">--}}
{{--                                            <div class="padding-10">--}}
{{--                                                <div class="ride-inner-title">--}}
{{--                                                    <span >Apply Promocode</span>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                            <div class="borderCls">--}}
{{--                                                <div class="input-group p-1">--}}
{{--                                                    <input type="text" name="promocode" class="form-control border-0" placeholder="Promocode" aria-label="Promocode" aria-describedby="basic-addon2">--}}
{{--                                                    <div class="input-group-append">--}}
{{--                                                        <button class="btn btn-outline-secondary btnColor applyPromoCode" type="button">APPLY</button>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="padding-10">
                                                <div class="ride-inner-title">
                                                    <span >Schedule Book Type</span>
                                                </div>
                                            </div>
                                            <div class="checkboxSection">
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" class="custom-control-input rideTypeCls" id="ride_now" name="book_type" value="0" checked>
                                                    <label class="custom-control-label" for="ride_now">Book Now</label>
                                                </div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" class="custom-control-input rideTypeCls" id="schedule_ride" name="book_type" value="1">
                                                    <label class="custom-control-label" for="schedule_ride">Schedule Book</label>
                                                </div>
                                            </div>
                                            <div class="checkboxSection padding-10 schduleSection" style="display: none;">
                                                <div class="input-append date form_datetime">
                                                    <div class="input-group date" id="datetimepicker1" data-target-input="nearest">
                                                        <input type="text" required name="pickup_date_time" id="datepicker" class="form-control form-font-cls datetimepicker-input" data-target="#datetimepicker1" autocomplete="off" />
                                                        <div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">
                                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Section of  promocode -->

                                    <!-- Section of  Fare Section -->
                                    <div class="row fare_data providerFareHtml">
<!--                                        <div class="col-12">
                                            <div class="padding-10">
                                            </div>
                                            <div class="faremainSetion pt-3">
                                                <div class="col-12 fareLabel">
                                                    <label class="labelFld">Distance Travel</label>
                                                    <label class="labelvalue">0 Km</label>

                                                </div>
                                                <div class="col-12 fareLabel">
                                                    <label class="labelFld">Taken Time</label>
                                                    <label class="labelvalue">0 Min</label>

                                                </div>
                                                <div class="col-12 fareLabel borderBtmCls">
                                                    <label class="labelFld">Base Fare</label>
                                                    <label class="labelvalue currency">0</label>
                                                </div>
                                                <div class="col-12 fareLabel pt-2">
                                                    <label class="labelFld">Sub Total</label>
                                                    <label class="labelvalue currency">0</label>

                                                </div>
                                                <div class="col-12 fareLabel borderBtmCls">
                                                    <label class="labelFld">Tax</label>
                                                    <label class="labelvalue currency">0</label>
                                                </div>
                                                <div class="col-12 fareLabel  font-weight-bold">
                                                    <label class="labelFld">Total</label>
                                                    <label class="labelvalue currency">0</label>
                                                </div>
                                            </div>
                                        </div>-->
                                    </div>
                                    <!-- End Section of  Fare Section -->

                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="padding-10">
                                            </div>
                                            <div class="col">
                                                <input type="hidden" name="total_all_packages" id="total_all_packages" value="0">
                                                <button type="button" class="btn btn-outline-secondary btnColor text-uppercase btn-block booknowbtn" disabled value="Provider Book">Provider Book</button>
                                            </div>
                                        </div>
                                    </div>

                            </div>

                        </form>

                    </div>

                </div>
                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12 order-md-12 order-1 p-0 mapPart">
                    <div class="map minheights" id="map" style="width: 100%;">
                    </div>
                </div>
            </div>
        </div>


    </main>

@endsection

@section('page-js')

    <script type="text/javascript">
        if (window.location.hash && window.location.hash == '#_=_') {
            window.location = '';
        }
    </script>
    <script>
        var modal = document.getElementById('user');

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function (event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        };
    </script>

    <script>
        //new js for ride booking
        $(document).ready(function (){
            var source_lat = $("#source_lat").val();
            var source_long = $("#source_long").val();
            changeMarker(source_lat,source_long)
            //map height set at run time
            var contentPart = $(".mainSection").height()

            $("#map").height(contentPart);

            $('.user').easyResponsiveTabs({
                type: 'default', //Types: default, vertical, accordion
                width: 'auto', //auto or any width like 600px
                fit: true   // 100% fit in a container
            });


            $(document).on("click",".curr_loc",function (){
                //timeour set for disaply marker on map after ajax
                setTimeout(function(){
                    var source_lat = $("#source_lat").val();
                    var source_long = $("#source_long").val();
                    changeMarker(source_lat,source_long)
                }, 1000);
            });

            //check change provider category
            $(document).on("change",".category",function (){
                getProviderLists()
            });

            //get single provider details
            $(document).on("click",".getProviderDetails",function (){
                var provider_service_id =  $(this).data('providerid');
                if(provider_service_id > 0)
                {
                    getSingleProviderList(provider_service_id)
                }else{
                    errormesssageDisplay("Please select Provider for get Details","danger")
                }
            });

            //change in sub category change based change package list
            $(document).on("change",".changeServiceSubCategory",function (){

                    var package_sub_cat_id =$(this).val();
                    $(".packageLists").addClass('hide');
                    $(".parentSubCategoryPackage_"+package_sub_cat_id).removeClass('hide');

            });

            $(document).on("click",".addUpdatePackage",function (){
                var packageId = $(this).data('packageid');
                if(packageId > 0){
                    $(".providerPkgSelect_"+packageId).hide();
                    $(".providerPkgCnt_"+packageId).show();
                    $("#current_selected_package").val(packageId);
                    addUpdateUserPackage(packageId,"add")
                }
            });

            $(document).on("click",".removePackage",function (){
                var packageId =$(this).data('packageid');
                var totalPackage = $("#userTotalPackage_"+packageId).val();
                if(totalPackage > 1)
                {
                    if(packageId > 0){
                        $(".providerPkgSelect_"+packageId).hide();
                        $(".providerPkgCnt_"+packageId).show();
                        $("#current_selected_package").val(packageId);
                        addUpdateUserPackage(packageId,"remove")
                    }
                } else {
                    $(".providerPkgSelect_"+packageId).show();
                    $(".providerPkgCnt_"+packageId).hide();
                    $("#current_selected_package").val(packageId);
                    addUpdateUserPackage(packageId,"remove")
                }
            });

            $(document).on("click",".applyPromoCode",function (){
                var form=$("#providerServiceFrm");
                frmdata = form.serializeArray();
                frmdata.push({name: 'action', value: "applyPromocode"});
                $.ajax({
                    type:'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    async :false,
                    cache: false,

                    /*contentType: "application/json",*/
                    dataType: "json",
                    /*processData: false,*/
                    url:'{{ route('post:get-add-update-provider-package') }}',
                    data: frmdata,
                    success:function(data){

                        $("#cover-spin").css('display',"block");
                        $('#cover-spin').height($( ".rightPartSection" ).height());
                        if(data.success == true){
                            $(".providerFareHtml").empty();
                            $(".providerFareHtml").append(data.get_fare_html);
                            // $(".booknowbtn").removeClass('disabled');
                            $(".booknowbtn").attr("disabled", false);
                            $("#cover-spin").css('display',"none");
                        }else{
                            $(".providerFareHtml").empty();
                            $(".providerFareHtml").append(data.get_fare_html);
                            errormesssageDisplay(data.errorMsg,"danger")
                            $("#cover-spin").css('display',"none");

                        }

                        if(data.total_package == 0){
                            $(".booknowbtn").attr("disabled", true);
                        }
                    }
                });
            });

            $(document).on("click",".booknowbtn",function (){

                $("#providerServiceFrm").validate({
                    rules: {
                        pickup_date_time: {
                            required : true,
                        }
                    },
                    messages: {
                        pickup_date_time: {
                            required :"Please select date time for schedule order",
                        },
                    },
                    errorPlacement: function(error, element) {
                        if(element.attr("name") == "pickup_date_time") {
                            error.insertAfter( element.parent("div #datetimepicker1"));
                        } else {
                            error.insertAfter(element);
                        }
                    },
                    success: function(label) {
                        label.addClass("valid").text("Ok!")
                    },
                    submitHandler: function(forms) {

                    }
                });


                if($("#providerServiceFrm").valid()){
                    //due to some jquery issue check ajax in valid function
                    var form=$("#providerServiceFrm");
                    frmdata = form.serializeArray();
                    frmdata.push({name: 'action', value: "formSubmit"});
                    $.ajax({
                        type:'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        async :false,
                        cache: false,

                        /*contentType: "application/json",*/
                        dataType: "json",
                        /*processData: false,*/
                        url:'{{ route('post:post-OtherServicePlaceOrder') }}',
                        data: frmdata,
                        success:function(data){
                            console.log("formsubmit");
                            console.log(data);
                            $("#cover-spin").css('display',"block");
                            $('#cover-spin').height($( ".rightPartSection" ).height());
                            if(data.status == 1){
                                window.location.href = "{{ route('get:user_orders_all',['on-demand','all']) }}";
                                /*$(".providerFareHtml").empty();
                                $(".providerFareHtml").append(data.get_fare_html);
                                $(".booknowbtn").removeClass('disabled');
                                $("#cover-spin").css('display',"none");*/
                            }else{
                                /*$(".providerFareHtml").empty();
                                $(".providerFareHtml").append(data.get_fare_html);*/
                                errormesssageDisplay(data.message,"danger")
                                $("#cover-spin").css('display',"none");
                            }
                        }
                    });
                }


            });

            //code fot hide show schedule ride
            //ride type radio check with display datepicker
            $(document).on("change",".rideTypeCls",function (){
                var ridetype = $(this).val();
                if(ridetype == 1){
                    $(".schduleSection").css('display','block');
                }else{
                    $(".schduleSection").css('display','none');
                }

            });
            //code for datetime pickers
            var oldDate = new Date();
            var hour = oldDate.getHours();
            var newDate = oldDate.setHours(hour + 1);
            $('#datepicker').datetimepicker({
                format: "yyyy-mm-dd hh:ii",
                autoclose: true,
                todayBtn: true,
                minuteStep: 10,
                startDate: new Date(new Date().setHours(new Date().getHours())),
                // 'minDateTime': new Date()
            });

        });

        //get provider Lists
        function getProviderLists()
        {
            var category_id = $("#category").val();
            if(category_id > 0){
                var source_lat = $("#source_lat").val();
                var source_long = $("#source_long").val();
                if(source_lat != 0 && source_long != 0){
                    var form=$("#providerServiceFrm");
                    $.ajax({
                        type:'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        async :false,
                        cache: false,

                        /*contentType: "application/json",*/
                         dataType: "json",
                        /*processData: false,*/
                        url:'{{ route('post:get-provider-lists') }}',
                        data: form.serialize(),
                        success:function(data){
                            console.log(data);
                            $("#cover-spin").css('display',"block");
                            if(data.success == true){

                                var providerLatLong = data.providerLatLong;
                                var source_lat = $("#source_lat").val();
                                var source_long = $("#source_long").val();
                                changeMarker(source_lat,source_long,providerLatLong)
                                var html_data =  data.html_data;
                                $(".ProviderList").empty();
                                $(".ProviderList").append(html_data);
                                $("#cover-spin").css('display',"none");

                            }else{
                                var html_data =  data.html_data;
                                $(".ProviderList").empty();
                                $(".ProviderList").append(html_data);
                                $("#cover-spin").css('display',"none");
                                errormesssageDisplay(data.errorMsg,"danger")
                            }
                        }
                    });
                }else{
                    errormesssageDisplay("Please Select Proper location","error")
                }

            }else{
                errormesssageDisplay("Please Select Proper Category","error")
            }
        }

        //get provider details lists
        function getSingleProviderList(provider_service_id){
                    $("#cover-spin").css('display',"block");
                    $('#cover-spin').height($( ".rightPartSection" ).height());
                    if(provider_service_id > 0){
                        var sub_cat_id = $("#category").val();
                        var source_lat = $("#source_lat").val();
                        var source_long = $("#source_long").val();

                        $.ajax({
                            type:'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            async :false,
                            cache: false,

                            /*contentType: "application/json",*/
                            dataType: "json",
                            /*processData: false,*/
                            url:'{{ route('post:get-provider-with-package-lists') }}',
                            data: {
                                    'sub_cat_id' : sub_cat_id,
                                    'provider_service_id' : provider_service_id,
                                    'source_lat' : source_lat,
                                    'source_long' : source_long,
                            },
                            success:function(data){
                                /*console.log(data);*/

                                if(data.success == true){
                                    $(".providerMainPart").hide();
                                    $(".providerDetailsMainPart").show();
                                    $(".providerListWithPackage").empty()
                                    $(".providerListWithPackage").append(data.provider_with_package_list)
                                    $("#cover-spin").css('display',"none");

                                }else{
                                    var html_data =  data.html_data;
                                    $(".ProviderList").empty();
                                    $(".ProviderList").append(html_data);
                                    $("#cover-spin").css('display',"none");
                                    errormesssageDisplay(data.errorMsg,"danger")
                                }
                            }
                        });
                    }else{
                        $(".providerMainPart").show();
                        $(".providerDetailsMainPart").hide();
                        $("#cover-spin").css('display',"none");
                    }
        }

        //add or update package of user
        function  addUpdateUserPackage(packageId,action="add"){
            var total_all_packages= $("#total_all_packages").val();
            var form=$("#providerServiceFrm");
            frmdata = form.serializeArray();
            frmdata.push({name: 'action', value: action});
            $.ajax({
                type:'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                async :false,
                cache: false,

                /*contentType: "application/json",*/
                dataType: "json",
                /*processData: false,*/
                url:'{{ route('post:get-add-update-provider-package') }}',
                data: frmdata,
                success:function(data){

                    $("#cover-spin").css('display',"block");
                    $('#cover-spin').height($( ".rightPartSection" ).height());
                    if(data.success == true){
                        $(".userTotalPackage_"+packageId).empty();
                        $(".userTotalPackage_"+packageId).append(data.total_package);
                        $("#userTotalPackage_"+packageId).val(parseInt(data.total_package))

                        if(data.get_fare_html !=""){
                            $(".providerFareHtml").empty();
                            $(".providerFareHtml").append(data.get_fare_html);
                            // $(".booknowbtn").removeClass('disabled');
                            $(".booknowbtn").attr("disabled", false);
                        }
                        if(action == "add"){
                            total_all_pack =  parseInt(total_all_packages) + 1;
                            $("#total_all_packages").val(total_all_pack);
                        }else{
                            total_all_pack =  parseInt(total_all_packages) - 1;
                            $("#total_all_packages").val(total_all_pack);
                        }
                        var total_all_pack = $("#total_all_packages").val();

                        if(total_all_pack == 0){
                            $(".booknowbtn").attr("disabled", true);
                        }
                        /*var providerLatLong = data.providerLatLong;
                        var source_lat = $("#source_lat").val();
                        var source_long = $("#source_long").val();
                        changeMarker(source_lat,source_long,providerLatLong)
                        var html_data =  data.html_data;
                        $(".ProviderList").empty();
                        $(".ProviderList").append(html_data);*/

                        $("#cover-spin").css('display',"none");

                    }else{
                        /*var html_data =  data.html_data;
                        $(".ProviderList").empty();
                        $(".ProviderList").append(html_data);*/
                        $("#cover-spin").css('display',"none");
                        errormesssageDisplay(data.errorMsg,"danger")

                    }

                }
            });
        }

        function init() {
            var addressInput = document.getElementById('source_address');
            var autocomplete = new google.maps.places.Autocomplete(addressInput);



            google.maps.event.addListener(autocomplete, 'place_changed', function () {

                var place = autocomplete.getPlace();

//            document.getElementById('pac-input').value = place.formatted_address;
                document.getElementById('source_lat').value = place.geometry.location.lat();
                document.getElementById('source_long').value = place.geometry.location.lng();
                changeMarker(place.geometry.location.lat(), place.geometry.location.lng());
            });


        }

        google.maps.event.addDomListener(window, 'load', init);

        function changeMarker(lat, long,providerLatLong="") {

            var latlng = new google.maps.LatLng(lat, long);
            var map = new google.maps.Map(document.getElementById('map'), {
                center: latlng,
                zoom: 14
            });
            var marker = new google.maps.Marker({
                map: map,
                position: latlng,
                draggable: true,
                {{--icon : "{{ asset('assets/front/img/png/pin.png') }}",--}}
                anchorPoint: new google.maps.Point(0, -29)
            });
            var input = document.getElementById('source_address');
            // map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
            var geocoder = new google.maps.Geocoder();
            var autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.bindTo('bounds', map);

            // Create an info window to share between markers.
            const infoWindow = new google.maps.InfoWindow();

            // Create the markers.
            // tourStops.forEach(([position, title], i) => {
            for (var i = 0; i < providerLatLong.length; i++) {
                var singel_provider = providerLatLong[i];
                console.log("single");
                console.log(singel_provider);
                var myLatlng = new google.maps.LatLng(singel_provider.lat, singel_provider.lng);

               const marker = new google.maps.Marker({
                    position : myLatlng,
                    map,
                    title: singel_provider.title,
                    optimized: false,
                    icon: "{{ asset('assets/front/img/png/pin.png') }}",
                });

                // Add a click listener for each marker, and set up the info window.
                marker.addListener("click", () => {
                    // infoWindow.close();
                    infoWindow.setContent(marker.getTitle());
                    infoWindow.open(marker.getMap(), marker);
                });
            }
            // });

            autocomplete.addListener('place_changed', function () {
                /*infowindow.close();*/
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


                document.getElementById('source_lat').value = place.geometry.location.lat();
                document.getElementById('source_long').value = place.geometry.location.lng();
                changeMarker(place.geometry.location.lat(), place.geometry.location.lng());
                getProviderLists();
                bindDataToForm(place.formatted_address, place.geometry.location.lat(), place.geometry.location.lng());
                // infowindow.setContent(place.formatted_address);
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
            document.getElementById('source_address').value = address;
            document.getElementById('source_lat').value = lat;
            document.getElementById('source_long').value = lng;
        }

    </script>
@endsection
