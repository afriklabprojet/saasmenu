$('#customer1').on("change",function(){

    $('#customer_info').val($('#customer1').val());

});

$('#customer').on("change",function(){

    $('#customer_info').val($('#customer').val());

});

$('.give_discount').on('keyup',function(){
    
    "use strict";
    var discount = $(this).val();
    $('#discount').val($(this).val());
    $('#discount1').val($(this).val());
    var grand_total = $('#grand_total1').val();
    var sub_total = $('#sub_total').val();
    if(discount == '')
    {
        discount = 0;
    }
    if(parseFloat(sub_total - discount) >= 0)
    {
        $('#discount_amount1').html(currency_formate(discount));
        $('#discount_amount11').html(currency_formate(discount));
        $('#discount_amount').val(discount);
        $('#total_amount1').html(currency_formate(parseFloat(grand_total - discount)));
        $('#total_amount').html(currency_formate(parseFloat(grand_total - discount)));
        $('#grand_total').val(parseFloat(grand_total - discount));
        $('#discounttotal').val(parseFloat(parseFloat(grand_total- discount)));
        $("#modal_total_amount").val(parseFloat(grand_total - discount));
    }
    else
    {
        $('#discount_amount1').html(currency_formate(0));
        $('#discount_amount11').html(currency_formate(0));
        $('#discount_amount').val(0);
        $('#total_amount1').html(currency_formate(parseFloat(grand_total)));
        $('#total_amount').html(currency_formate(parseFloat(grand_total)));
        $('#grand_total').val(parseFloat(grand_total));
        $('#discounttotal').val(parseFloat(parseFloat(grand_total)));
        $("#modal_total_amount").val(parseFloat(grand_total));
        toastr.error(discount_message);
    }
});



function showaddons(variants_name,variants_price,extras_name,extras_price,item_name)
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

function qtyupdate(id, type, qtyurl,item_id,variant_id,cart_qty) {
    "use strict";
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: qtyurl,
        data: {
            id: id,
            type: type,
            item_id : item_id,
            variant_id : variant_id,
            qty : cart_qty,
        },
        method: 'POST',
        success: function (response) {
            if(response.status == 0)
            {
                toastr.error(response.message);
                setTimeout(() => {
                    document.location.reload();
                  }, 5000);
                
            }
            else
            {
                $("#cartview").html('');
                $("#cartview").html(response);
            }
           
        },
        error: function (e) {
            $('#preload').hide();
            $('.err' + id).html(e.message);
            return false;
        }
    });
}
