
    @php
      if(@$itemdata['item_image']->image_name != null )
            {
            $image = @$itemdata['item_image']->image_name;
            }
            else{
            $image = $itemdata->image;
            }
            if ($itemdata['variation']->count() > 0) {
                if ($itemdata->top_deals == 1) {
                    if (@$topdeals->offer_type == 1) {
                        $price =  $itemdata["variation"][0]->price - @$topdeals->offer_amount;
                    } else {
                        $price =  $itemdata["variation"][0]->price  -  $itemdata["variation"][0]->price  * (@$topdeals->offer_amount / 100);
                    }
                    $original_price = $itemdata["variation"][0]->price ;
                }else{
                    $price = $itemdata["variation"][0]->price ;
                    $original_price = $itemdata["variation"][0]->original_price ;
                }
               
            } else {
                if ($itemdata->top_deals == 1) {
                    if (@$topdeals->offer_type == 1) {
                        $price =  $itemdata->item_price - @$topdeals->offer_amount;
                    } else {
                        $price =  $itemdata->item_price -  $itemdata->item_price * (@$topdeals->offer_amount / 100);
                    }
                   
                    $original_price =  $itemdata->item_price;
                }
                else{
                    $price = $itemdata->item_price;
                $original_price = $itemdata->item_original_price;
                }
                
            }
            $off = $original_price > 0 ? number_format(100 - ($price * 100) / $original_price, 1) : 0;
        @endphp
    <div class="card h-100 position-relative " data-aos="fade-up" data-aos-delay="150" data-aos-duration="1000"
        data-aos="fade-up" data-aos-delay="400" data-aos-duration="1000">
        <div class="overflow-hidden theme1grid_image">
            <img src="@if (@$itemdata['item_image']->image_url != null) {{ @$itemdata['item_image']->image_url }} @else {{ helper::image_path($itemdata->image) }} @endif"
                alt="" class="p-2 p-md-3"
                onclick="showitems('{{ $itemdata->id }}','{{ $itemdata->item_name }}','{{ $price  }}')">
        </div>
        <div class="card-body p-2 p-md-3 pb-0 pb-md-3">
            @if (Auth::user() && Auth::user()->type == 3)
                <div class="theme-10-heart-option">
                    <div class="theme-10-product-heart-icon set-fav1-{{ $itemdata->id }}">
                        @if ($itemdata->is_favorite == 1)
                            <a href="javascript:void(0)"
                                onclick="managefavorite('{{ $vdata }}','{{ $itemdata->id }}',0,'{{ URL::to($storeinfo->slug . '/managefavorite') }}')"><i
                                    class="fa-solid fa-heart"></i></a>
                        @else
                            <a href="javascript:void(0)"
                                onclick="managefavorite('{{ $vdata }}','{{ $itemdata->id }}',1,'{{ URL::to($storeinfo->slug . '/managefavorite') }}')"><i
                                    class="fa-regular fa-heart"></i></a>
                        @endif
                    </div>
                </div>
            @endif
            <a href="javascript:void(0)" class="title pb-1"
                onclick="showitems('{{ $itemdata->id }}','{{ $itemdata->item_name }}','{{$price }}')">{{ $itemdata->item_name }}</a>
            <small class="d_sm_none">{{ $itemdata->description }}</small>
        </div>
        <div class="card-footer bg-transparent border-0 p-2 p-md-3 pt-0 pt-md-3">
            <div class="row justify-content-between align-items-center gx-0">
                <div class="products-price col-9 mb-2 mb-md-0">
                    <span class="price">{{ helper::currency_formate($price, $vdata) }}</span>
                    <del>{{ helper::currency_formate($original_price, $vdata) }}</del>
                </div>
                <div class="col-3 d-flex justify-content-end mb-2 mb-md-0">

                   
                        <a class="btn-primary product-cart-icon"
                            onclick="showitems('{{ $itemdata->id }}','{{ $itemdata->item_name }}','{{$price }}')">

                            <i class="fa-solid fa-cart-shopping"></i>

                        </a>
                 
                </div>
            </div>
        </div>
    </div>

