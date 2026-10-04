@extends('front.layout.default')
@section('title')
    <title>Coupon Details</title>
@endsection
@section('meta-tags')
    <meta name="description" content="Coupon Details">
    <meta name="keywords" content="Coupon Details">
@endsection
@section('page-css')


@endsection

@section('page-content')
    <section id="main " class="CouponListMainSection">
        <div class="container-fluid  minheights mainSection">
                <div class="row couponheader">
                    <div class="col-12">
                        <div class="container">
                            <div class="row ">
                                <div class="col-12 px-md-5 couponheaderTitle">
                                    <div class="p-3">
                                        <span class="pr-3"><i class="fas fa-arrow-left"></i></span>  Coupon Details
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sticky row md-5 pt-4">
                    <div  class="col-lg-3 col-md-3 col-sm-12 col-xs-12 couponLists  " >
                        <div class="row d-block">
                            <div class="float-right">
                                <div class="col-12">
                                    <div class="cateborderCls">
                                        <div class="catetitleCls">
                                              Category Lists
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12" id="catList">
                                    <ul class="nav nav-pills flex-column " id="myTab" role="tablist">
                                        <li class="nav-item ">
                                            <a class="nav-link active couponListScroll" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Home</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link couponListScroll" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Profile</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link couponListScroll" id="contact-tab" data-toggle="tab" href="#contacts" role="tab" aria-controls="contact" aria-selected="false">Contact</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div  class="col-lg-6 col-md-6 col-sm-12 col-xs-12  " >
                        <div class="row">
                            <div class="col-12">
                                <div class="cateborderCls pl-0">
                                    <div class="catetitleCls ">
                                        Category One
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                                        <div class="col-12 p-3 ">
                                            <div class="col-12 pb-3 couponDetailsTitle font-weight-bold">
                                                <span class="pr-3"><i class="fas fa-angle-left"></i></span> MDC Food Store
                                            </div>
                                            <div class="col-12 mx-auto ">
                                                <div class="card bg-dark text-white">
                                                    <img class="card-img" src="../../public/assets/front/images/template-images/lo-2.jpeg" alt="Card image" height="270">
                                                </div>
                                            </div>
                                            <div class="col-12 pt-3 pb-3 ">

                                                <div class="row">
                                                    <div class="col-lg-9 col-md-9 col-sm-12">
                                                            <div class="couponDetailsTitle font-weight-bold pb-2">Coupon Details</div>
                                                            <div class="lableTitle pb-1">Product Details</div>
                                                            <div class="producttitle">MCD Store</div>
                                                    </div>
                                                    <div class="col-lg-3 col-md-3 col-sm-12 text-center my-auto">
                                                        <div class="couponDetailsTitle font-weight-bold">$10</div>
                                                        <button class="sc-add-to-cart btn btn-success cartButton btn-sm " >GET</button>
                                                    </div>
                                                </div>
                                                <div class="row pt-2 pb-2">
                                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                                        <div class="lableTitle pb-1">Coupon Code</div>
                                                    </div>
                                                    <div class="col-lg-12 col-md-12 col-sm-12 ">
                                                        <div class="row mx-0 py-2 couponcodeBackColor">
                                                            <div class="col-lg-6 col-md-6 col-sm-12">
                                                                <div class="couponcode couponcodeColor font-weight-bold">#########</div>
                                                            </div>
                                                            <div class="col-lg-6 col-md-6 col-sm-12">
                                                                <div class="couponValidText">Valid for 15 Days</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row pt-2 pb-2">
                                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                                        <div class="lableTitle pb-1">Link</div>
                                                    </div>
                                                    <div class="col-lg-12 col-md-12 col-sm-12 ">
                                                        <div class="row mx-0 py-2 ">
                                                            <div class="col-lg-12 col-md-12 col-sm-12">
                                                                <div class="couponcode couponcodeColor font-weight-bold">
                                                                       <a href="#"> couponcodelink.com </a>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row pt-2 pb-2 d-block couponInstruction">
                                                    <div class="panel-group" id="accordion">
                                                        <div class="panel panel-default border-0 accordionColor mb-0">
                                                            <div class="panel-heading border-0 pb-0">
                                                                <h4 class="panel-title">
                                                                    <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
                                                                        How to Redeem <span class="pull-right"><i class="fas fa-angle-down"></i></span>
                                                                    </a>
                                                                </h4>
                                                            </div>
                                                            <div id="collapseOne" class="panel-collapse collapse in show">
                                                                <ol class="instructionOL">
                                                                    <li class="instructionLI">Go through to checkout proucess untill you seen payment method</li>
                                                                    <li class="instructionLI">Select payment method</li>
                                                                    <li class="instructionLI">Enter your code</li>
                                                                    <li class="instructionLI">Follow Screen instruction</li>
                                                                </ol>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row pt-2 pb-2 d-block couponInstruction">
                                                    <div class="panel-group" id="accordion1">
                                                        <div class="panel panel-default border-0 accordionColor mb-0">
                                                            <div class="panel-heading border-0 pb-0">
                                                                <h4 class="panel-title">
                                                                    <a data-toggle="collapse" data-parent="#accordion1" href="#collapseTwo">
                                                                        Term and Conditions of Use (Coupons) <span class="pull-right"><i class="fas fa-angle-down"></i></span>
                                                                    </a>
                                                                </h4>
                                                            </div>
                                                            <div id="collapseTwo" class="panel-collapse collapse in">
                                                                <ol class="instructionOL">
                                                                    <li class="instructionLI">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,</li>
                                                                    <li class="instructionLI">when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries,</li>
                                                                    <li class="instructionLI">but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</li>
                                                                    <li class="instructionLI">Follow Screen instruction</li>
                                                                </ol>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>

                    <div  class="col-lg-3 col-md-3 col-sm-12 col-xs-12 pt-4 pb-4 " >
                        <div class="card ">
                            <div class="card-header font-weight-bold">
                                Coupon Lists
                            </div>
                            <div class="card-body">
                                <div class="singelCouponList pb-2 border-bottom-default">
                                    <div class="row">
                                        <div class="col-9">
                                        <div class="card-title offertitle mb-1">10% Discount on Pizza</div>
                                        </div>
                                        <div class="col-3 ">
                                            <button class="btn pull-right btn-circle btn-circle-sm m-1 btnHeigthWidth"><i class="fa fa-close"></i></button>
                                        </div>
                                    </div>
                                    <p class="card-text offerstorename mb-2">MCD Foodzone</p>
                                    <div class="row">
                                        <div class="col-sm-5">
                                            <input name="product_slug" value="" type="hidden">
                                            <button class="btn btn-success btn-circle  m-1 btncouponcartPlusMinus btnHeigthWidth"><i class="fa fa-plus"></i></button>
                                            <label class="totalQty">1</label>
                                            <button class="btn btn-success btn-circle  m-1 btncouponcartPlusMinus btnHeigthWidth"><i class="fa fa-minus"></i></button>
                                        </div>
                                        <div class="col-sm-7 float-right">
                                            <strong class="price pull-right product_price singleCouponPrice currency">0.9</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="singelCouponList pb-2 border-bottom-default">
                                    <div class="row">
                                        <div class="col-9">
                                            <div class="card-title offertitle mb-1">10% Discount on Pizza</div>
                                        </div>
                                        <div class="col-3 ">
                                            <button class="btn pull-right btn-circle btn-circle-sm m-1 btnHeigthWidth"><i class="fa fa-close"></i></button>
                                        </div>
                                    </div>
                                    <p class="card-text offerstorename mb-2">MCD Foodzone</p>
                                    <div class="row">
                                        <div class="col-sm-5">
                                            <input name="product_slug" value="" type="hidden">
                                            <button class="btn btn-success btn-circle  m-1 btncouponcartPlusMinus btnHeigthWidth"><i class="fa fa-plus"></i></button>
                                            <label class="totalQty">1</label>
                                            <button class="btn btn-success btn-circle  m-1 btncouponcartPlusMinus btnHeigthWidth"><i class="fa fa-minus"></i></button>
                                        </div>
                                        <div class="col-sm-7 float-right">
                                            <strong class="price pull-right product_price singleCouponPrice currency">0.9</strong>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="card-footer text-muted text-center">
                                <a href="#" class="btn btn-primary btn-block btnColor btncouponcart ">Proceed To Pay $150.00</a>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </section>

@endsection

@section('page-js')
    <script src="{{ asset('assets/front/js/theia-sticky-sidebar.min.js') }}" type="text/javascript"></script>
<script>
    $(document).ready(function () {
        $('.couponLists').theiaStickySidebar({
                additionalMarginTop: 30
        });
    });
</script>
@endsection
