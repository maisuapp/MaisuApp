@extends('front.layout.default')
@section('title')
    <title>Coupon Deals</title>
@endsection
@section('meta-tags')
    <meta name="description" content="Coupon Deals">
    <meta name="keywords" content="Coupon Deals">
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
                                        <span class="pr-3"><i class="fas fa-arrow-left"></i></span>  Coupon Deals
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
                                        <div class="col-12">
                                        <div class="row pt-3 mx-auto ">

                                            <div class="card mr-3 ml-3 mb-3 border-0 defaultWidth singleCard" style="">
                                                <img src="../../public/assets/front/images/template-images/lo-2.jpeg" class="card-img-top" alt="...">
                                                <div class="card-body cardBodyColor">
                                                    <div class="card-title offertitle mb-1">10% Discount on Pizza</div>
                                                    <p class="card-text offerstorename mb-2">MCD Foodzone</p>
                                                    <div class="row">
                                                        <div class="col-sm-7 ">
                                                            <strong class="price pull-left product_price currency">0.9</strong><br>
                                                            <div class="offerValidCls">Valid fot 15Days</div>
                                                        </div>
                                                        <div class="col-sm-5">
                                                            <input name="product_slug" value="" type="hidden">
                                                            <button class="sc-add-to-cart btn btn-success cartButton btn-sm pull-right" id="4">GET</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card mr-3 ml-3 mb-3 border-0 defaultWidth singleCard" style="">
                                                <img src="../../public/assets/front/images/template-images/lo-2.jpeg" class="card-img-top" alt="...">
                                                <div class="card-body cardBodyColor">
                                                    <div class="card-title offertitle mb-1">10% Discount on Pizza 0% Discount on Pizza</div>
                                                    <p class="card-text offerstorename mb-2">MCD Foodzone</p>
                                                    <div class="row">
                                                        <div class="col-sm-7 ">
                                                            <strong class="price pull-left product_price currency">0.9</strong><br>
                                                            <div class="offerValidCls">Valid fot 15Days Valid fot 15Day</div>
                                                        </div>
                                                        <div class="col-sm-5">
                                                            <input name="product_slug" value="" type="hidden">
                                                            <button class="sc-add-to-cart btn btn-success cartButton btn-sm pull-right" id="4">GET</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card mr-3 ml-3 mb-3 border-0 defaultWidth singleCard" style="">
                                                <img src="../../public/assets/front/images/template-images/lo-2.jpeg" class="card-img-top" alt="...">
                                                <div class="card-body cardBodyColor">
                                                    <div class="card-title offertitle mb-1">10% Discount on Pizza</div>
                                                    <p class="card-text offerstorename mb-2">MCD Foodzone</p>
                                                    <div class="row">
                                                        <div class="col-sm-7 ">
                                                            <strong class="price pull-left product_price currency">0.9</strong><br>
                                                            <div class="offerValidCls">Valid fot 15Days</div>
                                                        </div>
                                                        <div class="col-sm-5">
                                                            <input name="product_slug" value="" type="hidden">
                                                            <button class="sc-add-to-cart btn btn-success cartButton btn-sm pull-right" id="4">GET</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card mr-3 ml-3 mb-3 border-0 defaultWidth singleCard" style="">
                                                <img src="../../public/assets/front/images/template-images/lo-2.jpeg" class="card-img-top" alt="...">
                                                <div class="card-body cardBodyColor">
                                                    <div class="card-title offertitle mb-1">10% Discount on Pizza</div>
                                                    <p class="card-text offerstorename mb-2">MCD Foodzone</p>
                                                    <div class="row">
                                                        <div class="col-sm-7 ">
                                                            <strong class="price pull-left product_price currency">0.9</strong><br>
                                                            <div class="offerValidCls">Valid fot 15Days</div>
                                                        </div>
                                                        <div class="col-sm-5">
                                                            <input name="product_slug" value="" type="hidden">
                                                            <button class="sc-add-to-cart btn btn-success cartButton btn-sm pull-right" id="4">GET</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card mr-3 ml-3 mb-3 border-0 defaultWidth singleCard" style="">
                                                <img src="../../public/assets/front/images/template-images/lo-2.jpeg" class="card-img-top" alt="...">
                                                <div class="card-body cardBodyColor">
                                                    <div class="card-title offertitle mb-1">10% Discount on Pizza</div>
                                                    <p class="card-text offerstorename mb-2">MCD Foodzone</p>
                                                    <div class="row">
                                                        <div class="col-sm-7 ">
                                                            <strong class="price pull-left product_price currency">0.9</strong><br>
                                                            <div class="offerValidCls">Valid fot 15Days</div>
                                                        </div>
                                                        <div class="col-sm-5">
                                                            <input name="product_slug" value="" type="hidden">
                                                            <button class="sc-add-to-cart btn btn-success cartButton btn-sm pull-right" id="4">GET</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card mr-3 ml-3 mb-3 border-0 defaultWidth singleCard" style="">
                                                <img src="../../public/assets/front/images/template-images/lo-2.jpeg" class="card-img-top" alt="...">
                                                <div class="card-body cardBodyColor">
                                                    <div class="card-title offertitle mb-1">10% Discount on Pizza</div>
                                                    <p class="card-text offerstorename mb-2">MCD Foodzone</p>
                                                    <div class="row">
                                                        <div class="col-sm-7 ">
                                                            <strong class="price pull-left product_price currency">0.9</strong><br>
                                                            <div class="offerValidCls">Valid fot 15Days</div>
                                                        </div>
                                                        <div class="col-sm-5 p-0">
                                                            <input name="product_slug" value="" type="hidden">
<!--                                                            <button class="sc-add-to-cart btn btn-success cartButton btn-sm pull-right" id="4">GET</button>-->
                                                            <button class="btn btn-success btn-circle btn-circle-sm m-1 btncouponcartPlusMinus"><i class="fa fa-plus"></i></button>
                                                            <label class="totalQty">1</label>
                                                            <button class="btn btn-success btn-circle btn-circle-sm m-1 btncouponcartPlusMinus"><i class="fa fa-minus"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>



                                        </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                                        <div class="col-12">
                                            <div class="row mx-auto pt-3">

                                                <div class="card mr-3 ml-3 mb-3 border-0 defaultWidth singleCard" style="">
                                                    <img src="../../public/assets/front/images/template-images/lo-2.jpeg" class="card-img-top" alt="...">
                                                    <div class="card-body cardBodyColor">
                                                        <div class="card-title offertitle mb-1">10% Discount on Pizza</div>
                                                        <p class="card-text offerstorename mb-2">MCD Foodzone</p>
                                                        <div class="row">
                                                            <div class="col-sm-7 ">
                                                                <strong class="price pull-left product_price currency">0.9</strong><br>
                                                                <div class="offerValidCls">Valid fot 15Days</div>
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <input name="product_slug" value="" type="hidden">
                                                                <button class="sc-add-to-cart btn btn-success cartButton btn-sm pull-right" id="4">GET</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card mr-3 ml-3 mb-3 border-0 defaultWidth singleCard" style="">
                                                    <img src="../../public/assets/front/images/template-images/lo-2.jpeg" class="card-img-top" alt="...">
                                                    <div class="card-body cardBodyColor">
                                                        <div class="card-title offertitle mb-1">10% Discount on Pizza 0% Discount on Pizza</div>
                                                        <p class="card-text offerstorename mb-2">MCD Foodzone</p>
                                                        <div class="row">
                                                            <div class="col-sm-7 ">
                                                                <strong class="price pull-left product_price currency">0.9</strong><br>
                                                                <div class="offerValidCls">Valid fot 15Days Valid fot 15Day</div>
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <input name="product_slug" value="" type="hidden">
                                                                <button class="sc-add-to-cart btn btn-success cartButton btn-sm pull-right" id="4">GET</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card mr-3 ml-3 mb-3 border-0 defaultWidth singleCard" style="">
                                                    <img src="../../public/assets/front/images/template-images/lo-2.jpeg" class="card-img-top" alt="...">
                                                    <div class="card-body cardBodyColor">
                                                        <div class="card-title offertitle mb-1">10% Discount on Pizza</div>
                                                        <p class="card-text offerstorename mb-2">MCD Foodzone</p>
                                                        <div class="row">
                                                            <div class="col-sm-7 ">
                                                                <strong class="price pull-left product_price currency">0.9</strong><br>
                                                                <div class="offerValidCls">Valid fot 15Days</div>
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <input name="product_slug" value="" type="hidden">
                                                                <button class="sc-add-to-cart btn btn-success cartButton btn-sm pull-right" id="4">GET</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card mr-3 ml-3 mb-3 border-0 defaultWidth singleCard" style="">
                                                    <img src="../../public/assets/front/images/template-images/lo-2.jpeg" class="card-img-top" alt="...">
                                                    <div class="card-body cardBodyColor">
                                                        <div class="card-title offertitle mb-1">10% Discount on Pizza</div>
                                                        <p class="card-text offerstorename mb-2">MCD Foodzone</p>
                                                        <div class="row">
                                                            <div class="col-sm-7 ">
                                                                <strong class="price pull-left product_price currency">0.9</strong><br>
                                                                <div class="offerValidCls">Valid fot 15Days</div>
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <input name="product_slug" value="" type="hidden">
                                                                <button class="sc-add-to-cart btn btn-success cartButton btn-sm pull-right" id="4">GET</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card mr-3 ml-3 mb-3 border-0 defaultWidth singleCard" style="">
                                                    <img src="../../public/assets/front/images/template-images/lo-2.jpeg" class="card-img-top" alt="...">
                                                    <div class="card-body cardBodyColor">
                                                        <div class="card-title offertitle mb-1">10% Discount on Pizza</div>
                                                        <p class="card-text offerstorename mb-2">MCD Foodzone</p>
                                                        <div class="row">
                                                            <div class="col-sm-7 ">
                                                                <strong class="price pull-left product_price currency">0.9</strong><br>
                                                                <div class="offerValidCls">Valid fot 15Days</div>
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <input name="product_slug" value="" type="hidden">
                                                                <button class="sc-add-to-cart btn btn-success cartButton btn-sm pull-right" id="4">GET</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card mr-3 ml-3 mb-3 border-0 defaultWidth singleCard" style="">
                                                    <img src="../../public/assets/front/images/template-images/lo-2.jpeg" class="card-img-top" alt="...">
                                                    <div class="card-body cardBodyColor">
                                                        <div class="card-title offertitle mb-1">10% Discount on Pizza</div>
                                                        <p class="card-text offerstorename mb-2">MCD Foodzone</p>
                                                        <div class="row">
                                                            <div class="col-sm-7 ">
                                                                <strong class="price pull-left product_price currency">0.9</strong><br>
                                                                <div class="offerValidCls">Valid fot 15Days</div>
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <input name="product_slug" value="" type="hidden">
                                                                <button class="sc-add-to-cart btn btn-success cartButton btn-sm pull-right" id="4">GET</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="contacts" role="tabpanel" aria-labelledby="contact-tab">
                                        <div class="col-12">
                                            <div class="row mx-auto pt-3">

                                                <div class="card mr-3 ml-3 mb-3 border-0 defaultWidth singleCard" style="">
                                                    <img src="../../public/assets/front/images/template-images/lo-2.jpeg" class="card-img-top" alt="...">
                                                    <div class="card-body cardBodyColor">
                                                        <div class="card-title offertitle mb-1">10% Discount on Pizza</div>
                                                        <p class="card-text offerstorename mb-2">MCD Foodzone</p>
                                                        <div class="row">
                                                            <div class="col-sm-7 ">
                                                                <strong class="price pull-left product_price currency">0.9</strong><br>
                                                                <div class="offerValidCls">Valid fot 15Days</div>
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <input name="product_slug" value="" type="hidden">
                                                                <button class="sc-add-to-cart btn btn-success cartButton btn-sm pull-right" id="4">GET</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card mr-3 ml-3 mb-3 border-0 defaultWidth singleCard" style="">
                                                    <img src="../../public/assets/front/images/template-images/lo-2.jpeg" class="card-img-top" alt="...">
                                                    <div class="card-body cardBodyColor">
                                                        <div class="card-title offertitle mb-1">10% Discount on Pizza 0% Discount on Pizza</div>
                                                        <p class="card-text offerstorename mb-2">MCD Foodzone</p>
                                                        <div class="row">
                                                            <div class="col-sm-7 ">
                                                                <strong class="price pull-left product_price currency">0.9</strong><br>
                                                                <div class="offerValidCls">Valid fot 15Days Valid fot 15Day</div>
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <input name="product_slug" value="" type="hidden">
                                                                <button class="sc-add-to-cart btn btn-success cartButton btn-sm pull-right" id="4">GET</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card mr-3 ml-3 mb-3 border-0 defaultWidth singleCard" style="">
                                                    <img src="../../public/assets/front/images/template-images/lo-2.jpeg" class="card-img-top" alt="...">
                                                    <div class="card-body cardBodyColor">
                                                        <div class="card-title offertitle mb-1">10% Discount on Pizza</div>
                                                        <p class="card-text offerstorename mb-2">MCD Foodzone</p>
                                                        <div class="row">
                                                            <div class="col-sm-7 ">
                                                                <strong class="price pull-left product_price currency">0.9</strong><br>
                                                                <div class="offerValidCls">Valid fot 15Days</div>
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <input name="product_slug" value="" type="hidden">
                                                                <button class="sc-add-to-cart btn btn-success cartButton btn-sm pull-right" id="4">GET</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card mr-3 ml-3 mb-3 border-0 defaultWidth singleCard" style="">
                                                    <img src="../../public/assets/front/images/template-images/lo-2.jpeg" class="card-img-top" alt="...">
                                                    <div class="card-body cardBodyColor">
                                                        <div class="card-title offertitle mb-1">10% Discount on Pizza</div>
                                                        <p class="card-text offerstorename mb-2">MCD Foodzone</p>
                                                        <div class="row">
                                                            <div class="col-sm-7 ">
                                                                <strong class="price pull-left product_price currency">0.9</strong><br>
                                                                <div class="offerValidCls">Valid fot 15Days</div>
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <input name="product_slug" value="" type="hidden">
                                                                <button class="sc-add-to-cart btn btn-success cartButton btn-sm pull-right" id="4">GET</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card mr-3 ml-3 mb-3 border-0 defaultWidth singleCard" style="">
                                                    <img src="../../public/assets/front/images/template-images/lo-2.jpeg" class="card-img-top" alt="...">
                                                    <div class="card-body cardBodyColor">
                                                        <div class="card-title offertitle mb-1">10% Discount on Pizza</div>
                                                        <p class="card-text offerstorename mb-2">MCD Foodzone</p>
                                                        <div class="row">
                                                            <div class="col-sm-7 ">
                                                                <strong class="price pull-left product_price currency">0.9</strong><br>
                                                                <div class="offerValidCls">Valid fot 15Days</div>
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <input name="product_slug" value="" type="hidden">
                                                                <button class="sc-add-to-cart btn btn-success cartButton btn-sm pull-right" id="4">GET</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card mr-3 ml-3 mb-3 border-0 defaultWidth singleCard" style="">
                                                    <img src="../../public/assets/front/images/template-images/lo-2.jpeg" class="card-img-top" alt="...">
                                                    <div class="card-body cardBodyColor">
                                                        <div class="card-title offertitle mb-1">10% Discount on Pizza</div>
                                                        <p class="card-text offerstorename mb-2">MCD Foodzone</p>
                                                        <div class="row">
                                                            <div class="col-sm-7 ">
                                                                <strong class="price pull-left product_price currency">0.9</strong><br>
                                                                <div class="offerValidCls">Valid fot 15Days</div>
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <input name="product_slug" value="" type="hidden">
                                                                <button class="sc-add-to-cart btn btn-success cartButton btn-sm pull-right" id="4">GET</button>
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
        $('.couponLists')
            .theiaStickySidebar({
                additionalMarginTop: 30
            });
    });
</script>
@endsection
