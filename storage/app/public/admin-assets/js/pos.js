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

 function categories_filter(cat_id,nexturl)
 {
    $('.scroll-list').hasClass('active');
    $('.active').removeClass('active');
    $('#search-keyword').val('');

    if(cat_id == '')
    {
        $("#cat").addClass('active');
    }
    $("#cat-"+cat_id).addClass('active');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: nexturl,
        method: "get",
        data: {
            id: cat_id 
        },
        success: function (data) {  
            $("#pos-item").html('');
            $("#cat_id").val();
            $("#pos-item").html(data);
        },
        error: function (data) {
            toastr.error(wrong);
            return false;
        }
    });
 }

 $("#plusqty").on("click", function() {
    "use strict";
   
    $.ajax({
      headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
      },
      url: qtyupdateurl,
      data: {
        item_id: $("#item_id").val(),
        item_min_order: $("#item_min_order").val(),
        item_max_order: $("#item_max_order").val(),
        variants_min_order: $("#variants_min_order").val(),
        variants_max_order: $("#variants_max_order").val(),
        variants_id: $("#variant_id").val(),
        qty: $("#qty").val(),
        variant_qty: $("#checked_product_qty").val(),
        stock_management: $("#stock_management").val()
      },
      method: "POST",
      success: function(response) {
        if (response.status == 0) {
          toastr.error(response.message);
          $("#qty").val(response.qty);
        } else {
          $("#qty").val(response.qty);
        }
      },
      error: function(e) {
        $("#qty").val(response.qty);
        toastr.error(response.message);
      }
    });
  });
  
  $("#minusqty").on("click", function() {
    "use strict";
    var qty = parseInt($("#qty").val());
    qty = qty - 1;
    if (qty < 1) {
      qty = 1;
    }
    $("#qty").val(qty);
  });


 

 $('#search-keyword').keyup(function(){
    "use strict";

    var cat_id = $('#cat_id').val();
    var keyword =  $('#search-keyword').val();
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: $('#search-url').val(),
        method: "get",
        data: {
            id: cat_id,
            keyword : keyword 
        },
        success: function (data) {  
            $("#pos-item").html('');
            $("#cat_id").val();
            $("#pos-item").html(data);
        },
        error: function (data) {
            toastr.error(wrong);
            return false;
        }
    });
 });
 function addtocart(
    id,
    name,
    price,
    image,
    tax,
    qty,
    item_min_order,
    item_max_order,
    orignal_price,
    product_qty,
    variant_min_order,
    variant_max_order
  ) { 
   
    var variants_id = $("#variant_id").val();
    var variants_price = $("#viewitem_price").val();
    var variant_qty = $("#checked_product_qty").val();
    var stock_management = $("#stock_management").val();
    var extras_id = $(".Checkbox:checked")
      .map(function() {
        return this.value;
      })
      .get()
      .join("| ");
    var extras_name = $(".Checkbox:checked")
      .map(function() {
        return $(this).attr("extras_name");
      })
      .get()
      .join("|");
    var extras_price = $(".Checkbox:checked")
      .map(function() {
        return $(this).attr("price");
      })
      .get()
      .join("| ");
    $(".addcartbtn-" + id).hide();
    $(".showload-" + id).show();
  
    $.ajax({
      headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
      },
      url: $("#addtocarturl").val(),
      data: {
        id: id,
        name: name,
        image: image,
        item_price: price,
        price: orignal_price,
        tax: tax,
        variants_id: variants_id,
        variants_price: variants_price,
        extras_id: extras_id,
        extras_name: extras_name,
        extras_price: extras_price,
        qty: qty,
        product_qty: product_qty,
        item_min_order: item_min_order,
        item_max_order: item_max_order,
        stock_management: stock_management
      },
      method: "POST", //Post method,
      success: function(response) {
        if (response.status == 0) {
          toastr.error(response.message);
          $(".addcartbtn-" + id).show();
            $(".showload-" + id).hide();
        } else {
          $("#variant_id").val("");
          $("#variants_name").val("");
          $(".showload-" + id).hide();
          $(".addbtn-" + id).show();
          $(".addactive-" + id).addClass("active");
          $("#additems").modal("hide");
          $("#cartview").html("");
          $("#cartview").html(response);
          $(".addcartbtn-" + id).show();
          $(".showload-" + id).hide();
          toastr.success("Add Success");
          cleardata();
        }
      },
      error: function(response) {
        toastr.error(response.message);
      }
    });
  }
  
  function calladdtocart() {
   
    var id = $("#item_id").val();
    var item_name = $("#item_name").val();
    var item_price = $("#item_price").val();
    var item_qty = $("#qty").val();
    var item_min_order = $("#item_min_order").val();
    var item_max_order = $("#item_max_order").val();
    var item_image = $("#modal_item_image").val();
    var tax = $("#item_tax").val();
    var orignal_price = $("#orignal_price").val();
    var variant_qty = $("#checked_product_qty").val();
    var variant_min_order = $("#variants_min_order").val();
    var variant_max_order = $("#variants_max_order").val();
    addtocart(
      id,
      item_name,
      item_price,
      item_image,
      tax,
      item_qty,
      item_min_order,
      item_max_order,
      orignal_price,
      variant_qty,
      variant_min_order,
      variant_max_order
    );
  }

    function showitems(id, item_name, item_price, qty) {
      
        "use strict";
        $('.showload-' + id).show();
        $('.addcartbtn-' + id).hide();
        $("#item_name").html(item_name);
        $.ajax({
          headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
          },
          url: $("#showitemurl").val(),
          method: "post",
          data: {
            id: id
          },
          success: function(response) {
            $('.showload-' + id).hide();
            $('.addcartbtn-' + id).show();
            var e;
            var i;
            let html = "";
            let html1 = "";
            var count_varient = 0;
            var count_extra = 0;
            let price = parseInt(item_price);
           if(response.getitem.has_variants == 1)
           {
            for (var i = 0; i < response.getitem.variants_json.length; i++) {
              html +=
                '<p class="variant_name variant_name  pro-title line-2 mb-2 fs-6">' +
                response.getitem.variants_json[i].variant_name +
                "</p>";
      
              html +=
                '<select name="product[' +
                [i] +
                ']"  id="pro_variants_name" class="form-control variant-selection  pro_variants_name' +
                [i] +
                ' pro_variants_name variant_loop variant_val mb-2 py-1">';
      
              for (
                var t = 0;
                t < response.getitem.variants_json[i].variant_options.length;
                t++
              ) {
                html +=
                  '<option value="' +
                  response.getitem.variants_json[i].variant_options[t] +
                  '" id="' +
                  response.getitem.variants_json[i].variant_options[t] +
                  '_varient_option">' +
                  response.getitem.variants_json[i].variant_options[t] +
                  "</option>";
              }
              html += "</select>";
            }
           }
            
           if(response.getitem.has_variants == 1)
           {
            $("#checked_product_qty").val(response.variants[0].qty);
            $("#viewitem_qty").val(response.variants[0].qty);
            $("#viewitem_name").html(response.variants[0].name);
            $("#variant_id").val(response.variants[0].id);
            $("#stock_management").val(response.variants[0].stock_management);
            $("#item_min_order").val(response.variants[0].min_order);
            $("#item_max_order").val(response.variants[0].max_order);
            if (response.variants[0].is_available == 2) {
              $("#not_available_text").html("(" + not_available_msg + ")");
              $(".add-btn").attr("disabled", true);
            } else {
              $("#not_available_text").html("");
              $(".add-btn").attr("disabled", false);
              if (response.variants[0].stock_management == 1) {
                if (response.variants[0].qty > 0) {
                  $("#out_of_stock").removeClass("text-danger");
                  $("#out_of_stock").addClass("text-dark");
                  $("#out_of_stock").html(
                    "(" + response.variants[0].qty + " " + in_stock + ")"
                  );
                } else {
                  $("#out_of_stock").removeClass("text-dark");
                  $("#out_of_stock").addClass("text-danger");
                  $("#out_of_stock").html("(" + out_of_stock_msg + ")");
                }
              } else {
                $("#out_of_stock").html("");
              }
            }
           }else{
            $("#checked_product_qty").val(response.getitem.qty);
            $("#stock_management").val(response.getitem.stock_management);
            $("#item_min_order").val(response.getitem.min_order);
            $("#item_max_order").val(response.getitem.max_order);
           
            if (response.getitem.is_available == 2) {
              $("#not_available_text").html("(" + not_available_msg + ")");
              $(".add-btn").attr("disabled", true);
            } else {
              $("#not_available_text").html("");
              $(".add-btn").attr("disabled", false);
              if (response.getitem.stock_management == 1) {
                if (response.getitem.qty > 0) {
                  $("#out_of_stock").removeClass("text-danger");
                  $("#out_of_stock").addClass("text-dark");
                  $("#out_of_stock").html(
                    "(" + response.getitem.qty + " " + in_stock + ")"
                  );
                } else {
                  $("#out_of_stock").removeClass("text-dark");
                  $("#out_of_stock").addClass("text-danger");
                  $("#out_of_stock").html("(" + out_of_stock_msg + ")");
                }
              } else {
                $("#out_of_stock").html("");
              }
            }
           }

           
          
      
            for (i in response.extras) {
              count_extra = parseInt(count_extra + 1);
              html1 +=
                ' <div><input class="form-check-input border Checkbox" type="checkbox" id="Extras' +
                response.extras[i].id +
                '" name="extras[]" value="' +
                response.extras[i].id +
                '" extras_name="' +
                response.extras[i].name +
                '" price="' +
                response.extras[i].price +
                '"><label class="form-check-label mx-1 text-primary fw-500 fs-7 " for="Extras' +
                response.extras[i].id +
                '">' +
                response.extras[i].name +
                '<span class="px-1 text-muted"> (' +
                currency_formate(response.extras[i].price) +
                ") </span></label></div>";
            }
      
            $("#qty").val(1);
            $("#extras").html(html1);
            $("#variants").html(html);
          
            $("#viewitem_price").html(" (" + currency_formate(item_price) + ")");
            
            if (count_extra == 0) {
              $("#extras_title").html("");
            }
            if (count_varient == 0) {
              $("#variants_title").html("");
            }
            $("#item_id").val(id);
            $("#item_name").val(item_name);
            $("#item_price").val(item_price);
            $("#item_tax").val(response.getitem.tax);
            $("#modal_item_image").val(response.getitem.item_image.image);
          
            $("#orignal_price").val(parseInt(item_price));
            set_variant_price()
            $("#additems").modal("show");
          },
          error: function(response) {
            $('.showload-' + id).hide();
            $('.addcartbtn-' + id).show();
            toastr.error(wrong);
            return false;
          }
        });
      }

//  function showitems(id,item_name,item_price)
//  {
//     "use strict";
//     $.ajax({
//         headers: {
//             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//         },
//         url: $('#showitemurl').val(),
//         method: "post",
//         data: {
//             id: id,
//         },
//         success: function (response) {  
//            var e;
//            var i;
//            let html = '';
//            let html1 = '';
//            var count_varient = 0;
//            var count_extra = 0;
//            let price = parseInt(item_price);
//            for(e in response.variants)
//            {
//             count_varient = parseInt(count_varient + 1);
//                  if (e == 0) 
//                  {
//                     var checked = "checked";
//                  }
//                  else
//                  {
//                     var checked = "";
//                  }
                
//                 html += '<div><input class="form-check-input" type="radio" '+ checked + ' id="variants'+response.variants[e].id+'" name="variants" variation-id="'+response.variants[e].id+'" variants_name="'+response.variants[e].name+'" price="'+response.variants[e].price+'" onclick="pricechange()"  ><label class="form-check-label mx-1 text-primary fw-500 fs-7 " for="variants'+response.variants[e].id+'">'+ response.variants[e].name +' <span class="px-1 text-muted"> ('+ currency_formate(response.variants[e].price) +') </span></label></div>';
//            }

//            for(i in response.extras)
//            {
//             count_extra = parseInt(count_extra + 1);   
//                 html1 += ' <div><input class="form-check-input border Checkbox" type="checkbox" id="Extras'+response.extras[i].id+'" name="extras[]" value="'+response.extras[i].id+'" extras_name="'+response.extras[i].name+'" price="'+response.extras[i].price+'"><label class="form-check-label mx-1 text-primary fw-500 fs-7 " for="Extras'+response.extras[i].id+'">'+response.extras[i].name+'<span class="px-1 text-muted"> ('+ currency_formate(response.extras[i].price) +') </span></label></div>';
//            }

//            $('#qty').val(1);
//            $('#extras').html(html1);
//            $('#variants').html(html);
//            $('#viewitem_name').html(item_name);
//            $('#viewitem_price').html(" ("+ currency_formate(item_price) + ")");
        
//            if(count_extra == 0)
//            {
//              $('#extras_title').html('');
//            }

//            if(count_varient == 0)
//            {
//              $('#variants_title').html('');
//            }

//            $('#item_id').val(id);
//            $('#item_name').val(item_name);
//            $('#item_price').val(item_price);
//            $('#item_tax').val(response.getitem.tax);
//            $('#item_image').val(response.getitem.item_image.image_name);
//            $('#orignal_price').val(parseInt(item_price));

//            $('#additems').modal('show');
//         },
//         error: function (response) {
//             toastr.error(wrong);
//             return false;
//         }
//     });

//  }


 
var variants = document.getElementsByName('variants');

function pricechange()
{
    var variant_price = $('input[name="variants"]:checked').attr("price");
    $('#viewitem_price').html(" ("+ currency_formate(variant_price) + ")");
}

function validation(value) {
  var remaining = $("#modal_total_amount").val() - value;
  $("#ramin_amount").val(remaining.toFixed(2));
}
function order() {
  var discount_amount = $("#discount_amount").val();
  var payment_type = $('input[name="payment_type"]:checked').val();
  var sub_total = $("#sub_total").val();
  var tax = $("#tax_data").val();
  var grand_total = $("#grand_total").val();

  $.ajax({
    headers: {
      "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
    },
    url: $("#orderurl").val(),
    data: {
      discount_amount: discount_amount,
      name: $("#customer_name").val(),
      email: $("#customer_email").val(),
      mobile: $("#customer_mobile").val(),
      payment_type: payment_type,
      sub_total: sub_total,
      tax: tax,
      tax_name: $("#tax_name").val(),
      grand_total: grand_total
    },
    method: "POST",
    success: function(response) {
      if (response.status == 0) {
        toastr.error(response.message);
      } else {
        $("#cartview").html("");
        $("#cartview").html(response);
        toastr.success("Order Placed!!");
        $("#pos-invoice").modal("show");
      }
    },
    error: function(e) {
      swal("Cancelled", "{{ trans('messages.wrong') }} :(", "error");
    }
  });
}

$(document)
  .on("change", "#pro_variants_name", function() {
    set_variant_price();
  })
  .change();

function set_variant_price() {
  var variants = [];
  $(".variant-selection").each(function(index, element) {
    variants.push(element.value);
  });

  if (variants.length > 0) {
    $.ajax({
      headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
      },
      url: $("#variantsurl").val(),
      data: {
        name: variants.join("|"),
        item_id: $("#item_id").val(),
        vendor_id: vendor_id
      },
      success: function(data) {
        $("#item_min_order").val(data.min_order);
        $("#item_max_order").val(data.max_order);
        $("#checked_product_qty").val(data.quantity);
        $("#orignal_price").html(
          currency_formate(parseFloat(data.original_price))
        );
        $("#item_price").text(currency_formate(parseFloat(data.price)));
        $("#viewitem_price").html(" (" + currency_formate(data.price) + ")");
        $('#viewitem_name').html(data.variants_name);
        $("#variants_name").val(data.variants_name);
        $("#stock_management").val(data.stock_management);
        $("#variant_id").val(data.variant_id);
        if (data.is_available == 2) {
          $("#not_available_text").html("(" + not_available_msg + ")");
          $('#viewitem_name').addClass('d-none');
          $('#viewitem_price').addClass('d-none');
          $('#out_of_stock').addClass('d-none');
          $(".add-btn").addClass("disabled", true);
          $("#out_of_stock").html("");
        } else {
          $("#not_available_text").html("");
          $(".add-btn").removeClass("disabled", false);
          $('#viewitem_price').removeClass('d-none');
          $('#viewitem_name').removeClass('d-none');
          $('#out_of_stock').removeClass('d-none');
          if (data.stock_management == 1) {
            if (data.quantity > 0) {
              $("#out_of_stock").removeClass("text-danger");
              $("#out_of_stock").addClass("text-dark");
              $("#out_of_stock").html("(" + data.quantity + in_stock + ")");
            } else {
              $("#out_of_stock").removeClass("text-dark");
              $("#out_of_stock").addClass("text-danger");
              $("#out_of_stock").html("(" + out_of_stock_msg + ")");
            }
          }
        }
      }
    });
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
            title: title,
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
                        url: $('#deletecarturl').val(),
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
    function cash() {
    
      var sub_total = $("#grand_total").val();
      if (parseFloat(minorderamount) > parseFloat(sub_total))
       {
        toastr.error(minorderamountmsg);
        return false;
      } else {
        $("#modal_total_amount").val(parseFloat(sub_total));
        $("#paymentModal").modal("show");
      }
    }


    function placeorder() {
      var discount_amount = $("#discount_amount").val();
      var customer = $("#customer").val();
      var payment_type = $('input[name="payment_type"]:checked').val();
      var sub_total = $("#sub_total").val();
      var tax = $("#tax_data").val();
      var tax_name = $("#tax_name").val();
      var grand_total = $("#grand_total").val();
    
      if (parseFloat(minorderamount) > parseFloat(sub_total))
       {
        toastr.error(minorderamountmsg);
        return false;
      }
    
      $.ajax({
        headers: {
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
        },
        url: $("#checkorderurl").val(),
        data: {
          discount_amount: discount_amount,
          customer: customer,
          payment_type: payment_type,
          sub_total: sub_total,
          tax: tax,
          tax_name: tax_name,
          grand_total: grand_total
        },
        method: "POST",
        success: function(response) {
          if (response.status == 1) {
            var remain_amount = $("#ramin_amount").val();
            if (remain_amount > 0) {
              toastr.error(amount_msg);
              $("#paymentModal").modal("show");
              return false;
            }
            if (payment_type == null || payment_type == "") {
              toastr.error("Please select payment type!!");
            } else {
              if ($("#customer").val() == "walk-in customer") {
                $("#customermodal").modal("show");
              } else {
                if (payment_type == null || payment_type == "") {
                  toastr.error("Please select payment type!!");
                }
                $.ajax({
                  headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                  },
                  url: $("#orderurl").val(),
                  data: {
                    discount_amount: discount_amount,
                    customer: customer,
                    payment_type: payment_type,
                    sub_total: sub_total,
                    tax: tax,
                    tax_name: tax_name,
                    grand_total: grand_total
                  },
                  method: "POST",
                  success: function(response) {
                    $("#cartview").html("");
                    $("#cartview").html(response);
                    toastr.success("Order Placed!!");
                    $("#pos-invoice").modal("show");
                  },
                  error: function(e) {
                    swal("Cancelled", "{{ trans('messages.wrong') }} :(", "error");
                  }
                });
              }
            }
          }else{
            toastr.error(response.message);
          }
        },
        error: function(e) {
          swal("Cancelled", "{{ trans('messages.wrong') }} :(", "error");
        }
      });
    }