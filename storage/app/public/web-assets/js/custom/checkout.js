
$("#delivery_area").change(function() {
    "use strict";
    var deliverycharge = parseFloat($('option:selected', this).attr('price'));
    $('#shipping_charge').text(currency_formate(deliverycharge));
    $('#delivery_charge').val(deliverycharge);
    var sub_total = parseFloat($('#sub_total').val());
    var delivery_charge = parseFloat($('#delivery_charge').val());
    var tax = parseFloat($('#totaltax').val());
   
    var discount_amount = parseFloat($('#discount_amount').val());
    if (isNaN(discount_amount)) {
        $('#grand_total_view').text(currency_formate(parseFloat(sub_total + delivery_charge + tax)));
        $('#grand_total').val(((sub_total + delivery_charge + tax)).toFixed(2));
    } else {
        $('#grand_total_view').text(currency_formate(parseFloat(sub_total + delivery_charge + tax -
            discount_amount)));
        $('#grand_total').val(((sub_total + delivery_charge + tax - discount_amount)).toFixed(2));
    }
    $('#shipping_charge_hide').removeClass('d-none');
});
$(function() {
    "use strict";
    var dtToday = new Date();
    var month = dtToday.getMonth() + 1;
    var day = dtToday.getDate();
    var year = dtToday.getFullYear();
    if (month < 10)
        month = '0' + month.toString();
    if (day < 10)
        day = '0' + day.toString();
    var maxDate = year + '-' + month + '-' + day;
    $('#delivery_dt').attr('min', maxDate);
});
$(document).ready(function() {
    "use strict";
    $("input[name='cart-delivery']").on("click",function() {
        var test =  $(this).val();
       
        if (test == 1) {
            $("#open").show();
            $("#shipping_charge_hide").show();
            $("#delivery").show();
            $("#pickup").hide();
            $("#delivery_date").show();
            $("#pickup_date").hide();
            $("#table_show").hide();
            $("#data_time").show();
            var sub_total = parseFloat($('#sub_total').val());
            var delivery_charge = parseFloat($('#delivery_charge').val());
            var tax = parseFloat($('#totaltax').val());
            var discount_amount = parseFloat($('#discount_amount').val());
            if (isNaN(discount_amount)) {
                $('#total_amount').text(currency_formate(parseFloat(sub_total + tax +
                    delivery_charge)));
                $('#grand_total').val((sub_total + tax + delivery_charge).toFixed(2));
            } else {
                $('#total_amount').text(currency_formate(parseFloat(sub_total + tax +
                    delivery_charge - discount_amount)));
                $('#grand_total').val((sub_total + tax + delivery_charge - discount_amount).toFixed(
                    2));
            }
        } else if (test == 2)  {
            $("#open").hide();
            $("#shipping_charge_hide").hide();
            $("#delivery").hide();
            $("#pickup").show();
            $("#delivery_date").hide();
            $("#table_show").hide();
            $("#pickup_date").show();
            $("#data_time").show();
            var sub_total = parseFloat($('#sub_total').val());
            var delivery_charge = parseFloat($('#delivery_charge').val());
            var tax = parseFloat($('#totaltax').val());
            
            var discount_amount = parseFloat($('#discount_amount').val());
            if (isNaN(discount_amount)) {
                $('#total_amount').text(currency_formate(parseFloat(sub_total + tax)));
                $('#grand_total').val((sub_total + tax).toFixed(2));
            } else {
                $('#total_amount').text(currency_formate(parseFloat(sub_total + tax -
                    discount_amount)));
                $('#grand_total').val((sub_total + tax - discount_amount).toFixed(2));
            }
        }
        else
        {
            $("#open").hide();
            $("#shipping_charge_hide").hide();
            $("#delivery").hide();
            $("#pickup").hide();
            $("#delivery_date").hide();
            $("#pickup_date").hide();
            $("#table_show").show();
            $("#data_time").hide();
        }
    });
    if ("{{ helper::appdata($storeinfo->id)->delivery_type }}" != "both") {
        $(function() {
            $("input[name$='cart-delivery']:checked").click();
        });
    }
});
function ApplyCopon(coupon_code,vendor_id) {
    "use strict";
    
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: $('#applycoponurl').val(),
        method: 'post',
        data: {
            promocode: coupon_code,
            vendor_id : vendor_id,
            sub_total: parseFloat($('#sub_total').val())
        },
        success: function(response) {
            if (response.status == 1) {
                var html,html1;
                html = ' <a class=" text-danger" href="javascript:void(0)"  onclick="RemoveCopon()">Remove</a> ';
                html1 = '<li class="list-group-item" id="discount_1">Discount (-) <span>'+ currency_formate(response.data.price)+'</span></li>';
                var grand_total =  parseFloat($('#grand_total').val()) - response.data.price;
                $('#loyaltysection').hide();
                $('#shipping_charge_hide').after(html1);
                $('#promocode_button').html(html);
                $('#promocode_code').html(response.data.code + " Applied");
                $('#grand_total_view').html(currency_formate(grand_total));
                $('#grand_total').val(grand_total);
                $('#discount_amount').val(response.data.price);
                $('#offer_type').val('promocode');
                $('#couponcode').val(response.data.code);
                $('#offcanvasRight').offcanvas('hide');
            } else {
                $('#offcanvasRight').offcanvas('hide');
                toastr.error(response.message);
            }
        }
    });
}
$('#delivery_dt').on('change',function() {
    "use strict";
       $('#delivery_time').empty();
      $.ajax({
          headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
          },
          url: $('#sloturl').val(),
          type: "post",
          dataType: "json",
          data: {
            inputDate: $(this).val(),
            vendor_id: $("#store_id").val()
          },
          success: function(response) {
            let html = "";
            if (response == "1") {
              $('#store_close').removeClass('d-none');
              $('#delivery_time').addClass('d-none');
            } else {
              $('#store_close').addClass('d-none');
              $('#delivery_time').removeClass('d-none');
              $('#delivery_time').append('<option value="">Select</option>');
              for (var i in response) {
                  $('#delivery_time').append('<option value="'+ response[i]["slot"]+'">' + response[i]["slot"] + '</option>');
            }
            
          }
        }
      });
  });
  function Order() {
    "use strict";
   
    var sub_total = parseFloat($('#sub_total').val());
    var tax = $('#tax').val();
    var tax_name = $('#tax_name').val();
    var grand_total = parseFloat($('#grand_total').val());
    var delivery_time = $('#delivery_time').val();
    var delivery_date = $('#delivery_dt').val();
    var delivery_area = $('#delivery_area').val();
    var delivery_charge = parseFloat($('#delivery_charge').val());
    var discount_amount = parseFloat($('#discount_amount').val());
    var offer_type =$('#offer_type').val();
    var couponcode = $('#couponcode').val();
    var order_type = $("input:radio[name=cart-delivery]:checked").val();
    var address = $('#address').val();
    var postal_code = $('#postal_code').val();
    var building = $('#building').val();
    var landmark = $('#landmark').val();
    var notes = $('#notes').val();
    var customer_name = $('#customer_name').val();
    var customer_email = $('#customer_email').val();
    var customer_mobile = $('#customer_mobile').val();
    var vendor = $('#vendor').val();
    var payment_type = $('input[name="payment"]:checked').attr("data-payment_type");
    var flutterwavekey = $('#flutterwavekey').val();
    var paystackkey = $('#paystackkey').val();
    var mailformat = /^w+([.-]?w+)*@w+([.-]?w+)*(.w{2,3})+$/;
    var checkplanurl = $('#checkplanurl').val();
    var paymenturl = $('#paymenturl').val();
    var mecadourl = $('#mecadourl').val();
    var paypalurl = $('#paypalurl').val();
    var myfatoorahurl = $('#myfatoorahurl').val();
    var toyyibpayurl = $('#toyyibpayurl').val();
    var phonepeurl = $('#phonepeurl').val();
    var paytaburl = $('#paytaburl').val();
    var mollieurl = $('#mollieurl').val();
    var khaltiurl = $('#khaltiurl').val();
    var xenditurl = $('#xenditurl').val();
    var url = $('#payment_url').val();
    var website_title = $('#website_title').val();
    var image = $('#image').val();
    var slug = $('#slug').val();
    var failure = $('#failure').val();
    var table = $('#table').val();
    if (order_type == "1") {
        if (delivery_date == "") {
            toastr.error($('#delivery_date_required').val());
            
            return false;
        } else if (delivery_time == "") {
            toastr.error($('#delivery_time_required').val());
            return false;
        } else if (delivery_area == "") {
            toastr.error($('#delivery_area_required').val());
            return false;
        } else if (address == "") {
            toastr.error($('#address_required').val());
            return false;
        } 
    } else if (order_type == "2") {
        if (delivery_date == "") {
            toastr.error($('#pickup_date_required').val());
            return false;
        } else if (delivery_time == "") {
            toastr.error($('#pickup_time_required').val());
            return false;
        } 
    }
    else if (order_type == "3") {
        if (table == "") {
            toastr.error($('#table_required').val());
    
            return false;
    
        }
    }
    if (customer_name == "") {
        toastr.error($('#customer_name_required').val());
        return false;
    } else if (customer_mobile == "") {
        toastr.error($('#customer_mobile_required').val());
        return false;
    } else if (customer_email == "") {
        toastr.error($('#customer_email_required').val());
        return false;
    }
    $('#preloader').show();
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: checkplanurl,
        data: {
            vendor_id: vendor,
        },
        method: 'POST',
        success: function(response) {
           
            if (response.status == 1) {
                //COD
                if (payment_type == "1") {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: paymenturl,
                        data: {
                            sub_total: sub_total,
                            tax: tax,
                            tax_name : tax_name,
                            grand_total: grand_total,
                            delivery_time: delivery_time,
                            delivery_date: delivery_date,
                            delivery_area: delivery_area,
                            delivery_charge: delivery_charge,
                            discount_amount: discount_amount,
                            offer_type: offer_type,
                            couponcode: couponcode,
                            order_type: order_type,
                            address: address,
                            postal_code: postal_code,
                            building: building,
                            landmark: landmark,
                            notes: notes,
                            customer_name: customer_name,
                            customer_email: customer_email,
                            customer_mobile: customer_mobile,
                            vendor_id: vendor,
                            payment_type: payment_type,
                            slug: slug,
                            table : table,
                            buynow: $('#buynow_key').val(),
                        },
                        method: 'POST',
                        success: function(response) {
                            $('#preloader').hide();
                            if (response.status == 1) {
                                window.location.href =  response.url;
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function(error) {
                            $('#preloader').hide();
                        }
                    });
                }
                //Razorpay
                if (payment_type == "2") {
                    $('#preloader').hide();
                    var options = {
                        "key": $('#razorpay').val(),
                        "amount": (parseInt(grand_total * 100)), // 2000 paise = INR 20
                        "name": website_title,
                        "description": "Order payment",
                        "image": "image",
                        "handler": function(response) {
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                        'content')
                                },
                                url:  paymenturl,
                                type: 'post',
                                data: {
                                    payment_id: response.razorpay_payment_id,
                                    sub_total: sub_total,
                                    tax: tax,
                                    tax_name : tax_name,
                                    grand_total: grand_total,
                                    delivery_time: delivery_time,
                                    delivery_date: delivery_date,
                                    delivery_area: delivery_area,
                                    delivery_charge: delivery_charge,
                                    discount_amount: discount_amount,
                                    offer_type: offer_type,
                                    couponcode: couponcode,
                                    order_type: order_type,
                                    address: address,
                                    postal_code: postal_code,
                                    building: building,
                                    landmark: landmark,
                                    notes: notes,
                                    customer_name: customer_name,
                                    customer_email: customer_email,
                                    customer_mobile: customer_mobile,
                                    vendor_id: vendor,
                                    payment_type: payment_type,
                                    slug: slug,
                                    table : table,
                                    buynow: $('#buynow_key').val(),
                                },
                                success: function(response) {
                                    $('#preloader').hide();
                                    if (response.status == 1) {
                                        window.location.href = response.url;
                                    } else {
                                        toastr.error(response.message);
                                    }
                                },
                                error: function(error) {
                                    $('#preloader').hide();
                                }
                            });
                        },
                        "prefill": {
                            "contact": customer_mobile,
                            "email": customer_email,
                            "name": customer_name,
                        },
                        "theme": {
                            "color": "#366ed4"
                        }
                    };
                    var rzp1 = new Razorpay(options);
                    rzp1.open();
                    e.preventDefault();
                }
                //Stripe
            
                if (payment_type == "3") {
                    var handler = StripeCheckout.configure({
                        key: $('#stripekey').val(),
                        image: image,
                        locale: 'auto',
                        token: function(token) {
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                                        .attr('content')
                                },
                                url:  paymenturl,
                                data: {
                                    stripeToken: token.id,
                                    sub_total: sub_total,
                                    tax: tax,
                                    tax_name : tax_name,
                                    grand_total: grand_total,
                                    delivery_time: delivery_time,
                                    delivery_date: delivery_date,
                                    delivery_area: delivery_area,
                                    delivery_charge: delivery_charge,
                                    discount_amount: discount_amount,
                                    offer_type: offer_type,
                                    couponcode: couponcode,
                                    order_type: order_type,
                                    address: address,
                                    postal_code: postal_code,
                                    building: building,
                                    landmark: landmark,
                                    notes: notes,
                                    customer_name: customer_name,
                                    customer_email: customer_email,
                                    customer_mobile: customer_mobile,
                                    vendor_id: vendor,
                                    payment_type: payment_type,
                                    slug: slug,
                                    table : table,
                                    buynow: $('#buynow_key').val(),
                                },
                                method: 'POST',
                                success: function(response) {
                                    $('#preloader').hide();
                                    if (response.status == 1) {
                                        window.location.href = response.url;
                                    } else {
                                        toastr.error(response.message);
                                    }
                                },
                                error: function(error) {
                                    $('#preloader').hide();
                                }
                            });
                        },
                        opened: function() {
                            $('#preloader').hide();
                        },
                        closed: function() {
                            $('#preloader').hide();
                        }
                    });
                    //Stripe Popup
                    handler.open({
                        name: website_title,
                        description: 'Order payment',
                        amount: grand_total * 100,
                        currency: "USD",
                        email: customer_email
                    });
                    
                    // Close Checkout on page navigation:
                    $(window).on('popstate', function() {
                        handler.close();
                    });
                }
                //Flutterwave
                if (payment_type == "4") {
                    FlutterwaveCheckout({
                        public_key: flutterwavekey,
                        tx_ref: customer_name,
                        amount: grand_total,
                        currency: "NGN",
                        payment_options: " ",
                        customer: {
                            email: customer_email,
                            phone_number: customer_mobile,
                            name: customer_name,
                        },
                        callback: function(data) {
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                                        .attr('content')
                                },
                                url:  paymenturl,
                                method: 'POST',
                                data: {
                                    payment_id: data.flw_ref,
                                    sub_total: sub_total,
                                    tax: tax,
                                    tax_name : tax_name,
                                    grand_total: grand_total,
                                    delivery_time: delivery_time,
                                    delivery_date: delivery_date,
                                    delivery_area: delivery_area,
                                    delivery_charge: delivery_charge,
                                    discount_amount: discount_amount,
                                    offer_type: offer_type,
                                    couponcode: couponcode,
                                    order_type: order_type,
                                    address: address,
                                    postal_code: postal_code,
                                    building: building,
                                    landmark: landmark,
                                    notes: notes,
                                    customer_name: customer_name,
                                    customer_email: customer_email,
                                    customer_mobile: customer_mobile,
                                    vendor_id: vendor,
                                    payment_type: payment_type,
                                    slug: slug,
                                    table : table,
                                    buynow: $('#buynow_key').val(),
                                },
                                success: function(response) {
                                    $('#preloader').hide();
                                    if (response.status == 1) {
                                        window.location.href = response.url;
                                    } else {
                                        toastr.error(response.message);
                                    }
                                },
                                error: function(error) {
                                    $('#preloader').hide();
                                }
                            });
                        },
                        onclose: function() {
                            $('#preloader').hide();
                        },
                        customizations: {
                            title: website_title,
                            description: "Order payment",
                            logo: image,
                        },
                    });
                }
                //Paystack
                if (payment_type == "5") {
                    let handler = PaystackPop.setup({
                        key: paystackkey,
                        email: customer_email,
                        amount: grand_total * 100,
                        currency: 'GHS', // Use GHS for Ghana Cedis or USD for US Dollars
                        ref: 'trx_' + Math.floor((Math.random() * 1000000000) + 1),
                        label: "Order payment",
                        onClose: function() {
                            $('#preloader').hide();
                        },
                        callback: function(response) {
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                                        .attr('content')
                                },
                                url:  paymenturl,
                                data: {
                                    payment_id: response.trxref,
                                    sub_total: sub_total,
                                    tax: tax,
                                    tax_name : tax_name,
                                    grand_total: grand_total,
                                    delivery_time: delivery_time,
                                    delivery_date: delivery_date,
                                    delivery_area: delivery_area,
                                    delivery_charge: delivery_charge,
                                    discount_amount: discount_amount,
                                    offer_type: offer_type,
                                    couponcode: couponcode,
                                    order_type: order_type,
                                    address: address,
                                    postal_code: postal_code,
                                    building: building,
                                    landmark: landmark,
                                    notes: notes,
                                    customer_name: customer_name,
                                    customer_email: customer_email,
                                    customer_mobile: customer_mobile,
                                    vendor_id: vendor,
                                    payment_type: payment_type,
                                    slug: slug,
                                    table : table,
                                    buynow: $('#buynow_key').val(),
                                },
                                method: 'POST',
                                success: function(response) {
                                    $('#preloader').hide();
                                    if (response.status == 1) {
                                        window.location.href = response.url;
                                    } else {
                                        toastr.error(response.message);
                                    }
                                },
                                error: function(error) {
                                    $('#preloader').hide();
                                }
                            });
                        }
                    });
                    handler.openIframe();
                }
                //mercado pago
                if (payment_type == "7") {
                    $('#preloader').show();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url:  mecadourl,
                        data: {
                            sub_total: sub_total,
                            tax: tax,
                            tax_name : tax_name,
                            grand_total: grand_total,
                            delivery_time: delivery_time,
                            delivery_date: delivery_date,
                            delivery_area: delivery_area,
                            delivery_charge: delivery_charge,
                            discount_amount: discount_amount,
                            offer_type: offer_type,
                            couponcode: couponcode,
                            order_type: order_type,
                            address: address,
                            postal_code: postal_code,
                            building: building,
                            landmark: landmark,
                            notes: notes,
                            customer_name: customer_name,
                            customer_email: customer_email,
                            customer_mobile: customer_mobile,
                            vendor_id: vendor,
                            payment_type: payment_type,
                            slug: slug,
                            url: url,
                            failure: failure,
                            table : table,
                            buynow: $('#buynow_key').val(),
                        },
                        method: 'POST',
                        success: function(response) {
                            $('#preloader').hide();
                            if (response.status == 1) {
                                window.location.href = response.url;
                            }
                        },
                        error: function(error) {
                            toastr.error(response.message);
                            
                        }
                    });
                }
                //PayPal
                if (payment_type == "8") {
                    $('#preloader').show();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: paypalurl,
                        data: {
                            sub_total: sub_total,
                            tax: tax,
                            tax_name : tax_name,
                            grand_total: grand_total,
                            delivery_time: delivery_time,
                            delivery_date: delivery_date,
                            delivery_area: delivery_area,
                            delivery_charge: delivery_charge,
                            discount_amount: discount_amount,
                            offer_type: offer_type,
                            couponcode: couponcode,
                            order_type: order_type,
                            address: address,
                            postal_code: postal_code,
                            building: building,
                            landmark: landmark,
                            notes: notes,
                            customer_name: customer_name,
                            customer_email: customer_email,
                            customer_mobile: customer_mobile,
                            vendor_id: vendor,
                            payment_type: payment_type,
                            return: '1',
                            slug: slug,
                            url: url,
                            failure: failure,
                            table : table,
                            buynow: $('#buynow_key').val(),
                        },
                        method: 'POST',
                        success: function(response) {
                            $('#preloader').hide();
                            if (response.status == 1) {
                                $(".callpaypal").trigger("click")
                            }
                        },
                        error: function(error) {
                            toastr.error(response.message);
                        }
                    });
                }
                // myfatoorah
                if (payment_type == '9') {
                    $('#preloader').show();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url:myfatoorahurl,
                        data: {
                            sub_total: sub_total,
                            tax: tax,
                            tax_name : tax_name,
                            grand_total: grand_total,
                            delivery_time: delivery_time,
                            delivery_date: delivery_date,
                            delivery_area: delivery_area,
                            delivery_charge: delivery_charge,
                            discount_amount: discount_amount,
                            offer_type: offer_type,
                            couponcode: couponcode,
                            order_type: order_type,
                            address: address,
                            postal_code: postal_code,
                            building: building,
                            landmark: landmark,
                            notes: notes,
                            customer_name: customer_name,
                            customer_email: customer_email,
                            customer_mobile: customer_mobile,
                            vendor_id: vendor,
                            payment_type: payment_type,
                            return: '1',
                            slug: slug,
                            url: url,
                            failure: failure,
                            table : table,
                            buynow: $('#buynow_key').val(),
                        },
                        method: 'POST',
                        success: function(response) {
                            $('#preloader').hide();
                            if (response.status == 1) {
                                window.location.href = response.url;
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function(error) {
                            $('#preloader').hide();
                            toastr.error(wrong);
                            return false;
                        }
                    });
                }
                //toyyibpay
                if (payment_type == '10') {
                    $('#preloader').show();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: toyyibpayurl,
                        data: {
                            sub_total: sub_total,
                            tax: tax,
                            tax_name : tax_name,
                            grand_total: grand_total,
                            delivery_time: delivery_time,
                            delivery_date: delivery_date,
                            delivery_area: delivery_area,
                            delivery_charge: delivery_charge,
                            discount_amount: discount_amount,
                            offer_type: offer_type,
                            couponcode: couponcode,
                            order_type: order_type,
                            address: address,
                            postal_code: postal_code,
                            building: building,
                            landmark: landmark,
                            notes: notes,
                            customer_name: customer_name,
                            customer_email: customer_email,
                            customer_mobile: customer_mobile,
                            vendor_id: vendor,
                            payment_type: payment_type,
                            return: '1',
                            slug: slug,
                            url: url,
                            failure: failure,
                            table : table,
                            buynow: $('#buynow_key').val(),
                        },
                        method: 'POST',
                        success: function(response) {
                            $('#preloader').hide();
                            if (response.status == 1) {
                                window.location.href = response.url;
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function(error) {
                            $('#preloader').hide();
                            toastr.error(wrong);
                            return false;
                        }
                    });
                }

                //phonepe
                if (payment_type == '11') {
                    $('#preloader').show();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: phonepeurl,
                        data: {
                            sub_total: sub_total,
                            tax: tax,
                            tax_name : tax_name,
                            grand_total: grand_total,
                            delivery_time: delivery_time,
                            delivery_date: delivery_date,
                            delivery_area: delivery_area,
                            delivery_charge: delivery_charge,
                            discount_amount: discount_amount,
                            offer_type: offer_type,
                            couponcode: couponcode,
                            order_type: order_type,
                            address: address,
                            postal_code: postal_code,
                            building: building,
                            landmark: landmark,
                            notes: notes,
                            customer_name: customer_name,
                            customer_email: customer_email,
                            customer_mobile: customer_mobile,
                            vendor_id: vendor,
                            payment_type: payment_type,
                            return: '1',
                            slug: slug,
                            url: url,
                            failure: failure,
                            table : table,
                            buynow: $('#buynow_key').val(),
                        },
                        method: 'POST',
                        success: function(response) {
                            $('#preloader').hide();
                            if (response.status == 1) {
                                window.location.href = response.url;
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function(error) {
                            $('#preloader').hide();
                            toastr.error(wrong);
                            return false;
                        }
                    });
                }

                //paytab
                if (payment_type == '12') {
                    $('#preloader').show();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: paytaburl,
                        data: {
                            sub_total: sub_total,
                            tax: tax,
                            tax_name : tax_name,
                            grand_total: grand_total,
                            delivery_time: delivery_time,
                            delivery_date: delivery_date,
                            delivery_area: delivery_area,
                            delivery_charge: delivery_charge,
                            discount_amount: discount_amount,
                            offer_type: offer_type,
                            couponcode: couponcode,
                            order_type: order_type,
                            address: address,
                            postal_code: postal_code,
                            building: building,
                            landmark: landmark,
                            notes: notes,
                            customer_name: customer_name,
                            customer_email: customer_email,
                            customer_mobile: customer_mobile,
                            vendor_id: vendor,
                            payment_type: payment_type,
                            return: '1',
                            slug: slug,
                            url: url,
                            failure: failure,
                            table : table,
                            buynow: $('#buynow_key').val(),
                        },
                        method: 'POST',
                        success: function(response) {
                            $('#preloader').hide();
                            if (response.status == 1) {
                                window.location.href = response.redirecturl;
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function(error) {
                            $('#preloader').hide();
                            toastr.error(wrong);
                            return false;
                        }
                    });
                }
                
                //mollie
                if (payment_type == '13') {
                    $('#preloader').show();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: mollieurl,
                        data: {
                            sub_total: sub_total,
                            tax: tax,
                            tax_name : tax_name,
                            grand_total: grand_total,
                            delivery_time: delivery_time,
                            delivery_date: delivery_date,
                            delivery_area: delivery_area,
                            delivery_charge: delivery_charge,
                            discount_amount: discount_amount,
                            offer_type: offer_type,
                            couponcode: couponcode,
                            order_type: order_type,
                            address: address,
                            postal_code: postal_code,
                            building: building,
                            landmark: landmark,
                            notes: notes,
                            customer_name: customer_name,
                            customer_email: customer_email,
                            customer_mobile: customer_mobile,
                            vendor_id: vendor,
                            payment_type: payment_type,
                            return: '1',
                            slug: slug,
                            url: url,
                            failure: failure,
                            table : table,
                            buynow: $('#buynow_key').val(),
                        },
                        method: 'POST',
                        success: function(response) {
                            $('#preloader').hide();
                            if (response.status == 1) {
                                window.location.href = response.redirecturl;
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function(error) {
                            $('#preloader').hide();
                            toastr.error(wrong);
                            return false;
                        }
                    });
                }

                //khalti
                if (payment_type == '14') {
                    $('#preloader').show();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: khaltiurl,
                        data: {
                            sub_total: sub_total,
                            tax: tax,
                            tax_name : tax_name,
                            grand_total: grand_total,
                            delivery_time: delivery_time,
                            delivery_date: delivery_date,
                            delivery_area: delivery_area,
                            delivery_charge: delivery_charge,
                            discount_amount: discount_amount,
                            offer_type: offer_type,
                            couponcode: couponcode,
                            order_type: order_type,
                            address: address,
                            postal_code: postal_code,
                            building: building,
                            landmark: landmark,
                            notes: notes,
                            customer_name: customer_name,
                            customer_email: customer_email,
                            customer_mobile: customer_mobile,
                            vendor_id: vendor,
                            payment_type: payment_type,
                            return: '1',
                            slug: slug,
                            url: url,
                            failure: failure,
                            table : table,
                            buynow: $('#buynow_key').val(),
                        },
                        method: 'POST',
                        success: function(response) {
                            $('#preloader').hide();
                            if (response.status == 1) {
                                window.location.href = response.redirecturl;
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function(error) {
                            $('#preloader').hide();
                            toastr.error(wrong);
                            return false;
                        }
                    });
                }

                //xendit
                if (payment_type == '15') {
                    $('#preloader').show();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: xenditurl,
                        data: {
                            sub_total: sub_total,
                            tax: tax,
                            tax_name : tax_name,
                            grand_total: grand_total,
                            delivery_time: delivery_time,
                            delivery_date: delivery_date,
                            delivery_area: delivery_area,
                            delivery_charge: delivery_charge,
                            discount_amount: discount_amount,
                            offer_type: offer_type,
                            couponcode: couponcode,
                            order_type: order_type,
                            address: address,
                            postal_code: postal_code,
                            building: building,
                            landmark: landmark,
                            notes: notes,
                            customer_name: customer_name,
                            customer_email: customer_email,
                            customer_mobile: customer_mobile,
                            vendor_id: vendor,
                            payment_type: payment_type,
                            return: '1',
                            slug: slug,
                            url: url,
                            failure: failure,
                            table : table,
                        },
                        method: 'POST',
                        success: function(response) {
                            $('#preloader').hide();
                            if (response.status == 1) {
                                window.location.href = response.redirecturl;
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function(error) {
                            $('#preloader').hide();
                            toastr.error(wrong);
                            return false;
                        }
                    });
                }

                   // Banktransfer
                   if (payment_type == '6') {
                    $('#preloader').hide();
                    $('#modalbankdetails').modal('show');

                    $('#payment_type').val(payment_type);
                    $('#modal_customer_name').val($('#customer_name').val());
                    $('#modal_customer_email').val($('#customer_name').val());
                    $('#modal_customer_mobile').val($('#customer_mobile').val());
                    $('#modal_address').val(address);
                    $('#modal_delivery_date').val(delivery_date);
                    $('#modal_delivery_time').val(delivery_time);
                    $('#modal_delivery_area').val(delivery_area);
                    $('#modal_delivery_charge').val(delivery_charge);
                    $('#modal_discount_amount').val(discount_amount);
                    $('#modal_couponcode').val(couponcode);
                    $('#modal_ordertype').val(order_type);
                    $('#modal_building').val(building);
                    $('#modal_landmark').val(landmark);
                    $('#modal_postal_code').val(postal_code);
                    $('#modal_message').val(notes);
                    $('#modal_vendor_id').val(vendor);
                    $('#modal_subtotal').val(sub_total);
                    $('#modal_grand_total').val(grand_total);
                    $('#modal_tax').val(tax);
                    $('#modal_tax_name').val(tax_name);
                    $('#modal_order_type').val(order_type);
                    $('#modal_offer_type').val(offer_type);
                    $('#modal_table').val(table);
                    $('#modal_buynow').val($('#buynow_key').val());
                    $('#payment_description').html($('#bank_payment').val());

                }
            } else {
                $('#preloader').hide();
                $('#ermsg').text(response.message);
                $('#error-msg').addClass('alert-danger');
                $('#error-msg').css("display", "block");
                setTimeout(function() {
                    $("#error-msg").hide();
                }, 5000);
            }
        },
        error: function(error) {
            $('#preloader').hide();
        }
    });
}
function isNumber(evt) {
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}
function validateEmail($email) {
    var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
    return emailReg.test($email);
}

function RedeemPoints(vendor_id) {
    "use strict";

    var points = $('#points').val();
    var sub_total = parseFloat($('#sub_total').val());

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: $('#applyredeempoints').val(),
        method: 'post',
        data: {
            vendor_id : vendor_id,
            points : points,
            sub_total : sub_total,
        },
        success: function(response) {
            if (response.status == 1) {
                var html,html1;
                html = ' <button class="copybtn btn-primary" href="javascript:void(0)" onclick="RemovePoints()">Remove</button> ';
                html1 = '<li class="list-group-item" id="discount_1">Loyalty Discount <span>'+ currency_formate(response.data.price)+'</span></li>';
                var grand_total =  parseFloat($('#grand_total').val()) - response.data.price;
                $('#promocodesection').hide();
                $('#shipping_charge_hide').after(html1);
              
                $('#points_button').html(html);
                $('#grand_total_view').html(currency_formate(grand_total));
                $('#grand_total').val(grand_total);
                $('#discount_amount').val(response.data.price);
                $('#offer_type').val(response.data.offer_type);
                $('#couponcode').val(response.data.points);
                $("#points").prop("readonly", true);
            } else {
                $('#offcanvasRight').offcanvas('hide');
                toastr.error(response.message);
            }
        }
    });
}

function RemovePoints() {
    "use strict";
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success mx-1 yes-btn',
            cancelButton: 'btn btn-danger mx-1 no-btn'
        },
        buttonsStyling: false
    })
    swalWithBootstrapButtons.fire({
        title: are_you_sure,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: yes,
        cancelButtonText: no,
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: $('#removecouponurl').val(),
                method: 'post',
                data: {
                    promocode: jQuery('#promocode').val()
                },
                success: function(response) {
                    $('#preloader').hide();
                    if (response.status == 1) {
                        var html;
                        
                        html = '<button class="btn-primary btn" onclick="RedeemPoints(' + storeid + ')">redeem</button>';
                        var grand_total = (parseFloat($('#grand_total').val()) + parseFloat($('#discount_amount').val()));
                        $('#promocodesection').show();
                        $('#discount_1').remove();
                        $('#points_button').html(html);
                        $('#discount_amount').val('');
                        $('#offer_type').val('');
                        $('#couponcode').val('');
                        $('#grand_total_view').html(currency_formate(grand_total));
                        $('#grand_total').val(grand_total);
                        $("#points").prop("readonly", false);
                        $('#points').val('');
                    } else {
                        $('#ermsg').text(response.message);
                        $('#error-msg').addClass('alert-danger');
                        $('#error-msg').css("display", "block");
                        setTimeout(function() {
                            $("#success-msg").hide();
                        }, 5000);
                    }
                }
            });
        } else if (
            result.dismiss === Swal.DismissReason.cancel
        ) {
            Swal.DismissReason.cancel
        }
    })
}