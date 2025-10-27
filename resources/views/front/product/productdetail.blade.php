<div class="modal-content rounded-5">
    <div class="modal-header border-0 px-4">
        <p class="title pb-1 fs-5" id="viewitem_name">{{ $getitem->item_name }}</p>
        <button type="button" class="btn-close m-0" onclick="cleardata()" data-bs-dismiss="modal"
            aria-label="Close"></button>
    </div>
    <div class="modal-body px-sm-4 p-2">
        <div id="carouselExampleIndicators" class="carousel slide position-relative">
            <div class="carousel-indicators" id="image_buttons">
                @foreach ($itemimages as $key => $image)
                    <button type="button" data-bs-target="#carouselExampleIndicators"
                        data-bs-slide-to="{{ $key }}" class="{{ $key == 0 ? 'active' : '' }}"
                        aria-current="true" aria-label="Slide {{ $key }}"></button>
                @endforeach
            </div>
            <div class="carousel-inner card-modal-iages" id="item_images">
                @foreach ($itemimages as $key => $image)
                    <div class="carousel-item {{ $key == 0 ? 'active' : '' }} " name="image{{ $key }}">
                        <img class="img-fluid w-100" src="{{ helper::image_path($image->image) }}">
                    </div>
                    @if ($key == 0)
                        <input type="hidden" name="item_image" id="overview_item_image" value="{{ @$image->image }}">
                    @endif
                @endforeach
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators"
                data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">{{ trans('labels.previous') }}</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators"
                data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">{{ trans('labels.next') }}</span>
            </button>
        </div>
        @php
            if ($getitem['variation']->count() > 0) {
                if ($getitem->top_deals == 1) {
                    if (@$topdeals->offer_type == 1) {
                        $price = $getitem["variation"][0]->price - @$topdeals->offer_amount;
                    } else {
                        $price = $getitem["variation"][0]->price  - $getitem["variation"][0]->price  * (@$topdeals->offer_amount / 100);
                    }
                    $original_price = $getitem["variation"][0]->price ;
                }else{
                    $price = $getitem["variation"][0]->price ;
                    $original_price = $getitem["variation"][0]->original_price ;
                }
               
            } else {
             
                if ($getitem->top_deals == 1) {
                    if (@$topdeals->offer_type == 1) {
                        $price = $getitem->item_price - @$topdeals->offer_amount;
                    } else {
                        $price = $getitem->item_price - $getitem->item_price * (@$topdeals->offer_amount / 100);
                    }
                   
                    $original_price = $getitem->item_price;
                }
                else{
                    $price = $getitem->item_price;
                $original_price = $getitem->item_original_price;
                }
               
            }
            $off = $original_price > 0 ? number_format(100 - ($price * 100) / $original_price, 1) : 0;
        @endphp
        <div class="mt-4">
            <p id="laodertext" class="d-none laodertext"></p>
            @if ($getitem->is_available != 2 || $getitem->is_deleted == 1)
                <div class="products-price d-flex align-items-center">
                    <span class="price fs-5" id="detail_item_price">
                        @if ($getitem['variation']->count() > 0)
                            {{ helper::currency_formate($price, $getitem->vendor_id) }}
                        @else
                            {{ helper::currency_formate($price, $getitem->vendor_id) }}
                        @endif
                    </span>

                    <del id="detail_original_price">
                        @if ($getitem['variation']->count() > 0)
                            @if ($original_price > $price)
                                {{ $original_price > 0 ? helper::currency_formate($original_price, $getitem->vendor_id) : '' }}
                            @endif
                        @else
                            @if ($original_price > $price)
                                {{ $original_price > 0 ? helper::currency_formate($original_price, $getitem->vendor_id) : '' }}
                            @endif
                        @endif
                    </del>
                    <span class="badge text-bg-primary fs-7 mb-2" id="offer">{{ $off }}%</span>
                </div>

            @endif
            @if ($getitem->is_available != 2 || $getitem->is_deleted == 1)
                <p id="tax" class="responcive-tax text-left border-bottom pb-3"><span class="text-muted fs-7">
                        @if ($getitem->tax != null && $getitem->tax != '')
                            <span class="text-danger fs-7"> {{ trans('labels.exclusive_taxes') }}</span>
                        @else
                            <span class="text-success fs-7"> {{ trans('labels.inclusive_taxes') }}</span>
                        @endif
                    </span></p>
            @endif
            @if ($getitem->has_variants == 2)
                @if ($getitem->is_available == 2 || $getitem->is_deleted == 1)
                    <h3 class="text-danger">{{ trans('labels.not_available') }}</h3>
                @endif
            @else
                <h3 class="text-danger" id="detail_not_available_text"></h3>
            @endif
            <div class="border-bottom border-top mt-2 {{ $getitem->stock_management == 1 ? 'd-block' : 'd-none' }} {{ $getitem->is_available == 1 ? 'd-block' : 'd-none' }}"
                id="sku_stock">
                <div class="meta-content bg-gray py-2 rounded-2">
                    @if ($getitem->has_variants == 2 && $getitem->stock_management == 1)
                        <div class="sku-wrapper product_meta" id="stock">
                            <span class="fs-7"><span class="fw-semibold">{{ trans('labels.stock') }}:</span>
                            </span>
                            @if ($getitem->qty > 0)
                                <span class="text-success fs-7">{{ $getitem->qty }}
                                    {{ trans('labels.in_stock') }}</span>
                            @else
                                <span class="text-danger fs-7">{{ trans('labels.out_of_stock') }}</span>
                            @endif
                        </div>
                    @elseif ($getitem->has_variants == 1)
                        <div class="sku-wrapper product_meta" id="stock">
                            <span class="fs-7"><span class="fw-semibold">{{ trans('labels.stock') }}:
                                </span></span>
                            <span class="fs-7" id="detail_out_of_stock"></span>
                        </div>
                    @endif


                </div>
            </div>
            <input type="hidden" value="0" id="product_qty" name="product_qty">
            <p class="title mt-3 mb-1">{{ trans('labels.description') }}</p>
            <p class="description-cart" id="item_desc">{{ $getitem->description }}</p>
            @if ($getitem->has_variants == 1)
                <p class="title pb-1 pt-3 variants m-0" id="variants_title">{{ trans('labels.variants') }}</p>
                <div class="product-variations-wrapper">
                    <div class="size-variation" id="variation">

                        @for ($i = 0; $i < count($getitem->variants_json); $i++)
                            <label class="fw-semibold mt-2"
                                for="">{{ $getitem->variants_json[$i]['variant_name'] }}</label><br>
                            <div class="d-flex flex-wrap gap-2 border-bottom py-2">
                                @for ($t = 0; $t < count($getitem->variants_json[$i]['variant_options']); $t++)
                                    <label
                                        class="checkbox-inline check{{ str_replace(' ', '_', $getitem->variants_json[$i]['variant_name']) }} {{ $t == 0 ? 'active' : '' }}"
                                        id="check_{{ str_replace(' ', '_', $getitem->variants_json[$i]['variant_name']) }}-{{ str_replace(' ', '_', $getitem->variants_json[$i]['variant_options'][$t]) }}"
                                        for="{{ str_replace(' ', '_', $getitem->variants_json[$i]['variant_name']) }}-{{ str_replace(' ', '_', $getitem->variants_json[$i]['variant_options'][$t]) }}">
                                        <input type="checkbox" class="" name="skills"
                                            {{ $t == 0 ? 'checked' : '' }}
                                            value="{{ str_replace(' ', '_', $getitem->variants_json[$i]['variant_options'][$t]) }}"
                                            id="{{ str_replace(' ', '_', $getitem->variants_json[$i]['variant_name']) }}-{{ str_replace(' ', '_', $getitem->variants_json[$i]['variant_options'][$t]) }}">
                                        {{ $getitem->variants_json[$i]['variant_options'][$t] }}
                                    </label>
                                @endfor
                            </div>
                        @endfor
                    </div>
                </div>

            @endif

            @if (count($getitem['extras']) > 0)
                <div class="woo_pr_color flex_inline_center my-3 border-bottom pb-3">
                    <div class="woo_colors_list text-left">
                        <span id="extras">
                            <h5 class="extra-title fw-semibold">{{ trans('labels.extras') }}</h5>
                            <ul class="list-unstyled extra-food mt-2">
                                <div id="pricelist">
                                    @foreach ($getitem['extras'] as $key => $extras)
                                        <li class=" d-flex mb-2"><input type="checkbox" name="addons[]"
                                                extras_name="{{ $extras->name }}" class="Checkbox"
                                                value="{{ $extras->id }}" price="{{ $extras->price }}">
                                            <p class="mx-2">{{ $extras->name }} :
                                                {{ helper::currency_formate($extras->price, $getitem->vendor_id) }}
                                            </p>
                                        </li>
                                    @endforeach

                                </div>
                            </ul>
                        </span>

                    </div>
                </div>
            @endif
        </div>
        <input type="hidden" name="vendor" id="overview_vendor" value="{{ $getitem->vendor_id }}">
        <input type="hidden" name="item_id" id="overview_item_id" value="{{ $getitem->id }}">
        <input type="hidden" name="item_name" id="overview_item_name" value="{{ $getitem->item_name }}">
          <input type="hidden" name="item_image" id="overview_item_image" value="{{ $getitem['item_image']->image }}">
        <input type="hidden" name="item_min_order" id="item_min_order" value="{{ $getitem->min_order }}">
        <input type="hidden" name="item_max_order" id="item_max_order" value="{{ $getitem->max_order }}">
        <input type="hidden" name="item_price" id="overview_item_price" value="{{ $getitem->item_price }}">
        <input type="hidden" name="item_original_price" id="overview_item_original_price"
            value ="{{ $original_price }}">
        <input type="hidden" name="tax" id="item_tax" value="{{ $getitem->tax }}">
        <input type="hidden" name="variants_name" id="variants_name">
        <input type="hidden" name="stock_management" id="stock_management"
            value="{{ $getitem->stock_management }}">
        <input type="hidden" id="addtocarturl" value="{{ url('/add-to-cart') }}">
        <input type="hidden" id="showitemurl" value="{{ url('/product-details') }}">




    </div>
    <div class="modal-footer border-0 d-block">
        <div class="row d-flex justify-content-between align-items-center gx-2">
            <div class="col-12 col-md-3 mb-3 mb-md-0" id="detail_plus-minus">
                <nav aria-label="Page navigation example">
                    <ul class="pagination mb-0">
                        <li class="page-item">
                            <a class="page-link rounded-start rounded-end-0" id="minus" data-type="minus"
                                data-item_id="{{ $getitem->id }}"
                                onclick="changeqty($(this).attr('data-item_id'),'minus')" value="minus value"
                                href="javascript:void(0)" aria-label="Previous">
                                <span aria-hidden="true">
                                    <i class="fa-solid fa-minus fs-8"></i>
                                </span>
                            </a>
                        </li>
                        <li class="page-item">
                            <input type="text" class="page-link px-2 px-md-3 bg-light" id="item_qty"
                                value="1" readonly="">
                        </li>
                        <li class="page-item">
                            <a class="page-link rounded-end rounded-start-0" id="plus"
                                data-item_id="{{ $getitem->id }}"
                                onclick="changeqty($(this).attr('data-item_id'),'plus')" data-type="plus"
                                value="plus value" href="javascript:void(0)" aria-label="Next">
                                <span aria-hidden="true">
                                    <i class="fa-solid fa-plus fs-8"></i>
                                </span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <div class="col-6 col-md-3">
                <a class="btn-secondary rounded-3 w-100 text-center" id="enquiries"
                    href="https://api.whatsapp.com/send?phone={{ helper::appdata($getitem->vendor_id)->whatsapp_number }}&amp;text=I am interested for this item :{{ $getitem->item_name }}"
                    target="_blank">{{ trans('labels.enquiries') }}</a>
            </div>
            <div class="d-flex col-6 col-md-6">
                <div class="load showload-{{ $getitem->id }}" style="display:none"></div>
                <a class="btn-primary rounded-3 text-center add-btn {{ $getitem->is_available == 1 ? 'd-block' : 'd-none' }} addcartbtn-{{ $getitem->id }}"
                    href="javascript:void(0)" onclick="calladdtocart('0')">{{ trans('labels.add_to_cart') }}</a>
                <a class="btn-primary rounded-3 text-center mx-2 buynow add-btn {{ $getitem->is_available == 1 ? 'd-block' : 'd-none' }} buynowbtn-{{ $getitem->id }}"
                    href="javascript:void(0)" onclick="calladdtocart('1')">{{ trans('labels.buy_now') }}</a>
            </div>
        </div>
    </div>
</div>

<script>
    var not_available = "{{ trans('labels.not_available') }}";
    var out_stock = "{{ trans('labels.out_of_stock') }}";
    var in_stock = "{{ trans('labels.in_stock') }}";
</script>
<script>
    $(document).ready(function($) {
        var selected = [];
        $('.size-variation input:checked').each(function() {
            var label = $("label[for='" + $(this).attr('id') + "']").attr('id');
            $('#' + label).addClass('active');
            selected.push($(this).attr('value'));
        });

        if (selected != "" && selected != null) {

            set_variant_price(selected);
        }

    });
    $('#variation input:checkbox').click(function() {
        var selected = [];
        var divselected = [];
        const myArray = this.id.split("-");

        var id = this.id;
        $('.check' + myArray[0] + ' input:checked').each(function() {
            divselected.push($(this).attr('value'));
        });
        if (divselected.length == 0) {
            $(this).prop('checked', true);
        }


        $('.check' + myArray[0] + ' input:checkbox').not(this).prop('checked', false);
        $('.check' + myArray[0]).removeClass('active');
        var label = $("label[for='" + $(this).attr('id') + "']").attr('id');
        $('#' + label).addClass('active');
        $('.size-variation input:checked').each(function() {
            selected.push($(this).attr('value'));
        });
        if (selected != "" && selected != null) {
            $('.product-detail-price').addClass('d-none');
            $('#laodertext').removeClass('d-none');
            $('#laodertext').html(
                '<span class="loader"></span>'
            );
            set_variant_price(selected);
        }
    });

    function set_variant_price(variants) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ URL::to('get-products-variant-quantity') }}",
            data: {
                name: variants,
                item_id: $('#overview_item_id').val(),
                vendor_id: $('#overview_vendor').val(),
            },
            success: function(data) {
                if (data.status == 1) {
                    setTimeout(function() {
                        $('#laodertext').html('');
                    }, 4000);
                    var off = ((1 - (data.price / data.original_price)) * 100).toFixed(1);
                    $('#laodertext').addClass('d-none');
                    $('.product-detail-price').removeClass('d-none');
                    $('#variants_name').val(variants);
                    $('#detail_item_price').text(currency_formate(parseFloat(data.price)));
                    $('#overview_item_price').val(data.price);
                    $('#offer').removeClass('d-none');
                    if (parseFloat(data.original_price) > parseFloat(data.price)) {
                        $('#detail_original_price').text(currency_formate(parseFloat(data.original_price)));
                        $('#offer').text($.number(off, 2) + '%');
                    } else {
                        $('#detail_original_price').text('');
                        $('#offer').text('');
                    }
                    $('#overview_item_original_price').val(data.original_price);
                    $('#stock_management').val(data.stock_management);
                    $('#item_min_order').val(data.min_order);
                    $('#item_max_order').val(data.max_order);
                    console.log(data);
                    if (data.is_available == 2) {
                        $('#offer').addClass('d-none');
                        $('#detail_not_available_text').html(not_available);
                        $('.add-btn').attr('disabled', true);
                        $('.add-btn').addClass('d-none');
                        $('#detail_item_price').addClass('d-none');
                        $('#detail_original_price').addClass('d-none');
                        $('#sku_stock').addClass('d-none');
                        $('#detail_plus-minus').addClass('d-none');
                        $('#tax').addClass('d-none');
                        $('#stock').addClass('d-none');

                    } else {
                        $('#offer').removeClass('d-none');
                        $('#detail_not_available_text').html('');
                        $('.add-btn').removeClass('d-none');
                        $('#detail_item_price').removeClass('d-none');
                        $('#detail_original_price').removeClass('d-none');
                        $('#detail_plus-minus').removeClass('d-none');
                        $('#sku_stock').addClass('d-none');
                        $('#tax').removeClass('d-none');
                        $('#stock').addClass('d-none');
                        if (data.stock_management == 1) {
                            $('#stock').removeClass('d-none');
                            $('#sku_stock').removeClass('d-none');
                            $('#detail_out_of_stock').removeClass('d-none');
                            if (data.quantity > 0) {
                                $('#detail_out_of_stock').removeClass('text-danger');
                                $('#detail_out_of_stock').addClass('text-success');
                                $('#detail_out_of_stock').html('(' + data.quantity +
                                    ' {{ trans('labels.in_stock') }})');
                            } else {
                                $('#detail_out_of_stock').removeClass('text-dark');
                                $('#detail_out_of_stock').addClass('text-danger');
                                $('#detail_out_of_stock').html(out_stock);
                            }
                        } else {
                            $('#detail_out_of_stock').addClass('d-none');
                        }

                    }
                }

            }
        });
    }

    function changeqty(item_id, type) {
        var qtys = parseInt($('#item_qty').val());
        if (type == "minus") {
            qty = qtys - 1;
        } else {
            qty = qtys + 1;
        }
        if (qty >= "1") {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ URL::to('/changeqty') }}",
                data: {
                    item_id: item_id,
                    type: type,
                    qty: qty,
                    vendor_id: $('#overview_vendor').val(),
                    variants_name: $('#variants_name').val(),
                    stock_management: $('#stock_management').val(),
                },
                method: 'POST',
                success: function(response) {
                    if (response.status == 1) {
                        // $("#plus").removeClass('disabled');
                        $('#item_qty').val(response.qty);
                        // location.reload();
                    } else {
                        $('#preloader').hide();
                        // $("#plus").addClass('disabled');
                        $('#item_qty').val(response.qty);
                        toastr.error(response.message);
                    }
                },
                error: function(error) {}
            });
        }

    }
</script>
