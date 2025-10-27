function cleardata()
{
    $('#additems').modal('hide');
    $('#item_id').val('');
    $('#item_name').val('');
    $('#item_price').val('');
    $('#item_tax').val('');
    $('#item_image').val('');
    $('#orignal_price').val('');
    $('#qty').val('');
    $('#extras').html('');
    $('#variants').html('');
    $('#viewitem_name').html('');
    $('#viewitem_price').html('');

}



    $('#plusqty').on("click",function(){
        "use strict";
        var qty = parseInt($('#qty').val());
        qty = qty + 1;
        $('#qty').val(qty);
    });

    $('#minusqty').on("click",function(){
        "use strict";
        var qty = parseInt($('#qty').val());
        qty = qty - 1;
        if(qty < 1)
        {
            qty = 1;
        }
        $('#qty').val(qty);
    });
     

    
 

function addtocart(id,name,price,image,tax,qty,orignal_price,buynow) {
       "use strict";
       $('.showload-'+item_id).show();
        var vendor = $('#overview_vendor').val();
        var item_id = $('#overview_item_id').val();
        if(buynow == 1)
        {
           $('.buynowbtn-'+ item_id).hide();
        }else{
           $('.addcartbtn-'+ item_id).hide();
        }
        var item_name = $('#overview_item_name').val();
        var item_image = $('#overview_item_image').val();
        var item_price = $('#overview_item_price').val();
        var item_qty = $('#detail_plus-minus #item_qty').val();
        var item_original_price = $('#overview_item_original_price').val();
        var tax = $('#tax_val').val();
        var price = $('#price').val();
        var variants_name = $('#variants_name').val();
        var item_qty = $('#item_qty').val();
        var min_order = $('#item_min_order').val();
        var max_order = $('#item_max_order').val();
        var tax = $('#item_tax').val();
        var stock_management = $('#stock_management').val();
        var extras_id = ($('.Checkbox:checked').map(function() {
            return this.value;
        }).get().join('| '));
        var extras_name = ($('.Checkbox:checked').map(function() {
            return $(this).attr('extras_name');
        }).get().join('| '));
        var extras_price = ($('.Checkbox:checked').map(function() {
            return $(this).attr('price');
        }).get().join('| '));
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: $('#addtocarturl').val(),
            data: {
                vendor_id: vendor,
                item_id: item_id,
                item_name: item_name,
                item_image: item_image,
                item_price: item_price,
                item_original_price: item_original_price,
                tax: tax,
                variants_name: variants_name,
                extras_id: extras_id,
                extras_name: extras_name,
                extras_price: extras_price,
                qty: item_qty,
                price: price,
                min_order: min_order,
                max_order: max_order,
                stock_management: stock_management,
                buynow: buynow,
                tax:tax,
            },
            method: 'POST', //Post method,
            success: function(response) {
             
               if(response.status == 1)
               {
                $('.showload-'+item_id).hide();
                if(response.buynow == 1)
                {
                   $('.buynowbtn-'+ item_id).show();
                }else{
                   $('.addcartbtn-'+ item_id).show();
                }
               
                $('.addactive-'+id).addClass('active');
                $('#additems').modal('hide');
                $('#cartcount').html(response.totalcart);
                $('#cartcount_mobile').html(response.totalcart);
                cleardata();
                toastr.success(response.message);
                if(response.buynow == 1)
                {
                    window.location.replace(response.checkouturl);
                   
                }else{
                    location.reload();
                }
               }else{
                $('.showload-'+ item_id).hide();
                if(response.buynow == 1)
                {
                   $('.buynowbtn-'+ item_id).show();
                }else{
                   $('.addcartbtn-'+ item_id).show();
                }
                $('#preloader').hide();
                toastr.error(response.message);
               }
            },
            error: function(response) {
                $('.showload-'+ item_id).hide();
                if(response.buynow == 1)
                {
                   $('.buynowbtn-'+ item_id).show();
                }else{
                   $('.addcartbtn-'+ item_id).show();
                }
                $('#preloader').hide();
                toastr.error(response.message);
            }
        })
    };


    function calladdtocart(buynow)
    {
        "use strict";
        var id = $('#item_id').val();
        var item_name = $('#item_name').val();
        var item_price = $('#item_price').val();
        var item_qty = $('#qty').val();
        var item_image = $('#item_image').val();
        var tax = $('#item_tax').val();
        var orignal_price = $('#orignal_price').val(); 

        addtocart(id,item_name,item_price,item_image,tax,item_qty,orignal_price,buynow);
    }

 function showitems(id,item_name,item_price)
 {
    "use strict";
    $('.showload-' + id).show();
    $('.addcartbtn-'+ id).hide();
    var message = 'I am interested for this item : ';
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: $('#showitemurl').val(),
        method: "post",
        data: {
            id: id,
            vendor_id: vendor_id,
        },
        success: function (response) {  
            $('.showload-'+id).hide();
            $('.addcartbtn-'+ id).show();
        $('#viewproduct_body').html(response.output);
           $('#additems').modal('show');
        },
        error: function (response) {
            $('.showload-'+id).hide();
            $('.addcartbtn-'+ id).show();
            toastr.error(wrong);
            return false;
        }
    });   
 }

 function showextra(variants_name,variants_price,extras_name,extras_price,item_name)
 {
    "use strict";
    $('#cart_item_name').html(item_name);
   
    var i=0;
    var extras = extras_name.split("|");
    var variations = variants_name.split(',');
    var extra_price = extras_price.split('|');
    var html = "";
    if (variations != '') {
        html +='<p class="fw-bolder m-0" id="variation_title">'+ variation_title + '</p><ul class="m-0 ps-2">';
        html += '<li class="px-0">' + variations + ' : <span class="text-muted">' + currency_formate(parseFloat(variants_price)) + '</span></li>'
        html += '</ul>';
    }
    $('#item-variations').html(html);
    var html1 = '';
    if (extras != '') {
        $('#extras_title').removeClass('d-none');
        html1 +='<p class="fw-bolder m-0" id="extras_title">'+ extra_title +'</p><ul class="m-0 ps-2">';
        for (i in extras) {
            html1 += '<li class="px-0">' + extras[i] + ' : <span class="text-muted">' + currency_formate(parseFloat(extra_price[i])) + '</span></li>'
        }
        html1 += '</ul>';
    }
    $('#item-extras').html(html1);
    $('#customisation').modal('show');
 }

 function qtyupdate(cart_id, item_id, type) {
    var qtys = parseInt($("#number_" + cart_id).val());
    var item_id = item_id;
    var cart_id = cart_id;
    if (type == "minus") {
        qty = qtys - 1;
    } else {
        qty = qtys + 1;
    }
    if (qty >= "1") {
        $('#preloader').show();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: $('#qtyupdate_url').val(),
            data: {
                cart_id: cart_id,
                qty: qty,
                item_id: item_id,
                type:type, 
            },
            method: 'POST',
            success: function(response) {
                if (response.status == 1) {
                    $('#number_'+ cart_id).val(response.qty);
                    location.reload();
                } else {
                    $('#preloader').hide();
                    $('#number_'+ cart_id).val(response.qty);
                    toastr.error(response.message);
                    
                 
                }
            },
            error: function(error) {}
        });
    } else {
        // $('#preloader').show();
        if (qty < "1") {
            $('#ermsg').text("You've reached the minimum units allowed for the purchase of this item");
            $('#error-msg').addClass('alert-danger');
            $('#error-msg').css("display", "block");
            setTimeout(function() {
                $("#error-msg").hide();
            }, 5000);
        }
    }
}


function RemoveCart(cart_id) {
    "use strict";
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success mx-1',
            cancelButton: 'btn btn-danger bg-danger mx-1'
        },
        buttonsStyling: false
    })
    swalWithBootstrapButtons.fire({
        icon: 'error',
        title: are_you_sure,
        showCancelButton: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        confirmButtonText: yes,
        cancelButtonText: no,
        reverseButtons: true,
        showLoaderOnConfirm: true,
        preConfirm: function() {
            return new Promise(function(resolve, reject) {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: $('#removecart').val(),
                    data: {
                        cart_id: cart_id
                    },
                    method: 'POST',
                    success: function(response) {
                        if (response.status == 1) {
                            location.reload();
                        } else {
                            swal("Cancelled", "{{ trans('messages.wrong') }} :(",
                                "error");
                        }
                    },
                    error: function(e) {
                        swal("Cancelled", "{{ trans('messages.wrong') }} :(",
                            "error");
                    }
                });
            });
        },
    }).then((result) => {
        if (!result.isConfirmed) {
            result.dismiss === Swal.DismissReason.cancel
        }
    })
}


function removefavorite(vendor_id,slug, type, manageurl) {
 
    "use strict";
    $("#preload").show();
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: manageurl,
        data: {
            slug: slug,
            type: type,
            favurl: manageurl,
            vendor_id : vendor_id
        },
        method: 'POST',
        success: function (response) {
          
            location.reload();
           
        },
        error: function (e) {
            $("#preload").hide();
            return false;
        }
    });
}



