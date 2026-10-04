<div class="col-12">
    <div class="padding-10">
    </div>
    <div class="providerDetailMainSection pt-3">
        <div class="singelProvider row m-0">
            <input type="hidden" name="provider_service_id" id="provider_service_id" value="{{isset($provider_service_id)?$provider_service_id:0}}">
            <input type="hidden" name="current_selected_package" id="current_selected_package" value="0">
            <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12 d-flex">
                <img src="{{($provider_profile_image)}}" title="{{ucfirst($provider_name)}}" class="rounded-circle align-self-start mr-3 providerImg" alt="{{ucfirst($provider_name)}}" width="64" height="64">
                <div class="media-body">
                    <div class="d-flex usernamePart pt-2">
                        <span class="mt-0 username pr-3">{{ucfirst($provider_name)}}</span>
                        <div class="ratting"><i class="fa fa-star starColor pr-1"></i>{{($average_rating > 0)?$average_rating:0}}</div>
                    </div>
                    <div class="d-flex locationPart">
                        <span class="providerLocation pr-4 ">
                           {{ ($distance!="")?$distance:"0 km Away" }}
                        </span>
                        <span>
                            <i class="fa fa-shopping-bag pr-2"></i>
                            {{ ($total_completed_order > 0)?$total_completed_order:0 }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-12 p-3">
                <div class="input-group p-1 borderBtmCls pb-3">
                    <select name="service_sub_category" class="custom-select border-0 changeServiceSubCategory">
                        @if(isset($provider_package_category))
                            @foreach($provider_package_category as $single_package_category)

                                <option value="{{$single_package_category->sub_cat_id}}">{{$single_package_category->sub_cat_name}}</option>

                            @endforeach
                        @else
                            <option value="0">Please select sub category</option>
                        @endif
                    </select>
                </div>
            </div>

            <div class="col-12 mb-2">
                @if(isset($provider_package_lists))
                    @php
                        $i=0;
                    @endphp
                    @foreach($provider_package_lists as $keys=>$single_category_package_list)
                        <div class="packageLists parentSubCategoryPackage_{{$keys}} {{ ($i == 0)?"":"hide" }}">
                        <?php $i++ ;?>
                            @foreach($single_category_package_list as $key=>$single_package)
                            <div class="row m-0 packageBackgroundCls pb-2 mb-2 singlePackage">
                                <div class="col-lg-7 col-md-12 col-sm-12 col-xs-12 p-2  text-center text-lg-left " >
                                    <div class="col-12">
                                        <span class="categoryNameCls">{{ $single_package->sub_cat_name }}</span>
                                    </div>
                                    <div class="col-12">
                                        <span class="packageNameCls">{{ $single_package->package_name }}</span>
                                    </div>
                                </div>
                                <input type="hidden" name="userPackageList[{{$keys}}][{{$single_package->id}}]" id="userTotalPackage_{{$single_package->id}}" value="0">
                                <div class="col-lg-5 col-md-12 col-sm-12 col-xs-12 p-2  providerPkgSelect_{{ $single_package->id }}" >
                                    <div class="row pt-2">
                                        <div class="col-lg-4 col-md-6 col-xs-6 col-sm-6 text-center pt-2">
                                            <span class="packagePriceCls currency">{{$single_package->package_price}}</span>
                                        </div>
                                        <div class="col-lg-8 col-md-6 col-xs-6 col-sm-6 pt-1">
                                            <button class="btn btn-outline-secondary btnColor btn-block addUpdatePackage" data-packageid="{{ $single_package->id }}" type="button">Select</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-5 col-md-12 col-sm-12 col-xs-12 p-2  hide providerPkgCnt_{{ $single_package->id }}" >
                                    <div class="row pt-2">
                                        <div class="col-lg-4 col-md-6 col-xs-6 col-sm-6 text-center pt-2">
                                            <span class="packagePriceCls currency">{{$single_package->package_price}}</span>
                                        </div>
                                        <div class="col-lg-8 col-md-6 col-xs-6 col-sm-6 pt-2 text-center">
                                            <i class="fa fa-plus-circle pr-2 addUpdatePackage"  data-packageid="{{ $single_package->id }}"  aria-hidden="true"></i> <span class="userTotalPackage_{{ $single_package->id }}"> 1 </span> <i class="fa fa-minus-circle pl-2 removePackage" data-packageid="{{ $single_package->id }}" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endforeach
                @endif
            </div>

        </div>
    </div>
</div>
