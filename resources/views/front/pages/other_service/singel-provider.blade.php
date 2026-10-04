<div class="singelProvider row borderBtmCls provider_id_{{$provider_service_id}}">
    <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12 d-flex">
        <img src="{{($provider_profile_image)}}" title="{{ucfirst($provider_name)}}" class="providerImg rounded-circle align-self-start mr-3" alt="{{ucfirst($provider_name)}}" width="64" height="64">
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
    <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12 pt-2">
        <button class="btn btn-outline-secondary btnColor btn-block p-1 getProviderDetails" data-providerId="{{$provider_service_id}}" type="button">Book Service</button>
    </div>
</div>
