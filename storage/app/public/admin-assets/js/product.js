// common
// $('.has_variants').on('change', function () {
//     "use strict";
//     check_variation_validation($(this).val())
// });
// $('.has_variants:checked').on('change', function () {
//     "use strict";
//     check_variation_validation($(this).val())
// }).change();
// function check_variation_validation(value) {
//     "use strict";
//     if (value == 1) {
//         document.getElementById('price_row').style.display = 'none';
//         if (location.href.includes("add") == true) {
//             document.getElementById('variations').style.display = 'flex';
//         } else {
//             document.getElementById('variations').style.display = 'grid';
//         }
//         $('.variations, .btn-add-variations').show();
//         $(".variation , .variation_price, .variation_original_price").prop('required', true);
//         $("#price, #original_price").prop('required', false);
//     } else {
//         document.getElementById('price_row').style.display = 'flex';
//         document.getElementById('variations').style.display = 'none';
//         $('.variations, .btn-add-variations').hide();
//         $(".variation , .variation_price, .variation_original_price").prop('required', false);
//         $("#price, #original_price").prop('required', true);
//         $('#more_variation_fields').html('');
//         $('#edititem_fields').html('');
//     }
// }

// common
$(".has_variants").on("change", function() {
  "use strict";

  check_variation_validation($(this).val());
});
$(".has_variants:checked")
  .on("change", function() {
    "use strict";
    check_variation_validation($(this).val());
  })
  .change();
$(".has_stock").on("change", function() {
  "use strict";
  check_stock_validation($(this).val());
});
$(".has_stock:checked")
  .on("change", function() {
    "use strict";
    check_stock_validation($(this).val());
  })
  .change();

function check_variation_validation(value) {
  "use strict";
  var table = document.getElementById("tblvariants");
  if (value == 1) {
    document.getElementById("price_row").style.display = "none";
    if (location.href.includes("add") == true) {
      document.getElementById("variations").style.display = "flex";
    } else {
      document.getElementById("variations").style.display = "grid";
    }
    $(".variations, .btn-add-variations").show();
    // $(".variation , .variation_price").prop('required', true);

    $("#btn_addvariants").removeClass("d-none");
    $("#original_price").prop("required", false);
    $("#price").prop("required", false);
    $("#qty").prop("required", false);
    $("#min_order").prop("required", false);
    $("#max_order").prop("required", false);
    $("#low_qty").prop("required", false);
    if (table != null) {
      var rows = table.getElementsByTagName("tr");
      for (var i = 0; i < rows.length; i++) {
        $("#vprice_" + i).prop("required", true);
        $("#voriginal_price_" + i).prop("required", true);
        if ($("#vlow_qty_" + i).prop("checked") == true) {
          $("#vquantity_" + i).prop("required", true);
          $("#vmin_order_" + i).prop("required", true);
          $("#vmax_order_" + i).prop("required", true);
          $("#vlow_qty_" + i).prop("required", true);
        } else {
          $("#vquantity_" + i).prop("required", false);
          $("#vmin_order_" + i).prop("required", false);
          $("#vmax_order_" + i).prop("required", false);
          $("#vlow_qty_" + i).prop("required", false);
        }
      }
    }
  } else {
    document.getElementById("price_row").style.display = "flex";
    document.getElementById("variations").style.display = "none";
    $(".variations, .btn-add-variations").hide();
    $("#edititem_fields").html("");
    $("#btn_addvariants").addClass("d-none");
    $("#original_price").prop("required", true);
    $("#price").prop("required", true);
    // $("#qty").prop("required", true);
    // $("#min_order").prop("required", true);
    // $("#max_order").prop("required", true);
    // $("#low_qty").prop("required", true);

    if (table != null) {
      var rows = table.getElementsByTagName("tr");
      for (var i = 0; i < rows.length; i++) {
        $("#vprice_" + i).prop("required", false);
        $("#voriginal_price_" + i).prop("required", false);
        $("#vquantity_" + i).prop("required", false);
        $("#vmin_order_" + i).prop("required", false);
        $("#vmax_order_" + i).prop("required", false);
        $("#vlow_qty_" + i).prop("required", false);
      }
    }
  }
}
function check_stock_validation(value) {
  "use strict";
  if (value == 1) {
    document.getElementById("block_stock_qty").style.display = "block";
    document.getElementById("block_min_order").style.display = "block";
    document.getElementById("block_max_order").style.display = "block";
    document.getElementById("block_product_low_qty_warning").style.display =
      "block";
    $("#qty").prop("required", true);
    $("#min_order").prop("required", true);
    $("#max_order").prop("required", true);
    $("#low_qty").prop("required", true);
  } else {
    document.getElementById("block_stock_qty").style.display = "none";
    document.getElementById("block_min_order").style.display = "none";
    document.getElementById("block_max_order").style.display = "none";
    document.getElementById("block_product_low_qty_warning").style.display =
      "none";
    $("#qty").prop("required", false);
    $("#min_order").prop("required", false);
    $("#max_order").prop("required", false);
    $("#low_qty").prop("required", false);
  }
}
$(function() {
  $("#image").on("change", function() {
    "use strict";
    if (this.files) {
      var filesAmount = this.files.length;
      $("div.gallery").html("");
      $("div.gallery").addClass("row my-2");
      var n = 0;
      for (var i = 0; i < filesAmount; i++) {
        var reader = new FileReader();
        reader.onload = function(event) {
          $($.parseHTML("<div>"))
            .attr("class", "col-lg-2 col-md-3 col-4 text-center")
            .html(
              '<img src="' +
                event.target.result +
                '" class="img-fluid w-auto rounded-3 mt-3">'
            )
            .appendTo("div.gallery");
          n++;
        };
        reader.readAsDataURL(this.files[i]);
      }
    }
  });
});

const swalWithBootstrapButtons = Swal.mixin({
  customClass: {
    confirmButton: "btn btn-success mx-1",
    cancelButton: "btn btn-danger mx-1"
  },
  buttonsStyling: false
});

function swal_cancelled(issettitle) {
  "use strict";
  var title = wrong;
  if (issettitle) {
    title = "" + issettitle + "";
  }
  swalWithBootstrapButtons.fire("Cancelled", title, "error");
}

// for add PRODUCT
var variation_row = 1;
function variation_fields(variation, price, original_price) {
  "use strict";
  variation_row++;
  var divtest = document.createElement("div");
  divtest.setAttribute("class", "form-group mb-0 removeclass" + variation_row);
  divtest.innerHTML =
    '<div class="row variations"><div class="col-md-4"><div class="form-group"><input type="text" class="form-control variation" name="variation[]" placeholder="' +
    variation +
    '" required></div></div><div class="col-md-4"><div class="form-group"><input type="text" class="form-control numbers_only variation_price" name="variation_price[]" placeholder="' +
    price +
    '" required></div></div><div class="col-md-4"><div class="form-group d-flex"><input type="text" class="form-control numbers_only variation_original_price" name="variation_original_price[]" placeholder="' +
    original_price +
    '" required><button class="btn btn-danger btn-sm rounded-5 ms-2 pricebtn" type="button" onclick="remove_variation_fields(' +
    variation_row +
    ');"><i class="fa-sharp fa-solid fa-xmark"></i></button> </div></div><div class="col-m d-1 d-flex align-items-end p-md-0"></div></div>';
  $("#more_variation_fields").append(divtest);
}
// function remove_variation_fields(rid) {
//     "use strict";
//     $('.removeclass' + rid).remove();
// }
// var extras_row = 1;
// function extras_fields(name, price) {
//     "use strict";
//     extras_row++;
//     var divtest = document.createElement("div");
//     divtest.setAttribute("class", "form-group mb-0 removeextras" + extras_row);
//     divtest.innerHTML = '<div class="row variations"><div class="col-md-6"><div class="form-group"><input type="text" class="form-control" name="extras_name[]" placeholder="' + name + '" required></div></div><div class="col-md-6"><div class="form-group d-flex"><input type="text" class="form-control numbers_only" name="extras_price[]" placeholder="' + price + '" required>  <button class="btn btn-danger btn-sm rounded-5 ms-2 pricebtn" type="button" onclick="remove_extras_fields(' + extras_row + ');"><i class="fa-sharp fa-solid fa-xmark"></i></button>   </div></div><div class="col-md-1 d-flex align-items-end p-md-0"></div></div>';
//     $('#more_extras_fields').append(divtest)
// }
// function remove_extras_fields(rid) {
//     "use strict";
//     $('.removeextras' + rid).remove();
// }
function imageview(id, image) {
  "use strict";
  $("#item_id").val(id);
  $("#img_name").val(image);
  $("#editModal").modal("show");
}
function edititem_fields(variation, price, original_price) {
  "use strict";
  if (!$("span").hasClass("hiddencount")) {
    $("#edititem_fields").prepend(
      '<span class="hiddencount d-none">' + 1 + "</span>"
    );
  }
  var editroom = $("span.hiddencount:last()").html();
  editroom++;
  var editdivtest = document.createElement("div");
  editdivtest.setAttribute(
    "class",
    "form-group mb-0 editremoveclass" + editroom
  );
  editdivtest.innerHTML =
    '<input type="hidden" class="form-control" name="variation_id[' +
    editroom +
    ']"><div class="row"><div class="col-md-4"><div class="form-group"><input type="text" class="form-control variation" name="variation[' +
    editroom +
    ']" placeholder="' +
    variation +
    '" required></div></div><div class="col-md-4"><div class="form-group"><input type="text" class="form-control numbers_only variation_price" name="variation_price[' +
    editroom +
    ']" placeholder="' +
    price +
    '" required></div></div><div class="col-md-4"><div class="form-group d-flex"><input type="text" class="form-control numbers_only variation_original_price" name="variation_original_price[' +
    editroom +
    ']" placeholder="' +
    original_price +
    '" required value="0"><button class="btn btn-danger btn-sm rounded-5 ms-2 pricebtn" type="button" onclick="remove_edit_fields(' +
    editroom +
    ');"><i class="fa-sharp fa-solid fa-xmark"></i></button></div></div><div class="col-md-1 d-flex align-items-end p-md-0"></div></div>';
  $("span.hiddencount:last()").html(editroom);
  $("#edititem_fields").append(editdivtest);
}
function remove_edit_fields(rid) {
  "use strict";
  $(".editremoveclass" + rid).remove();
}


function deleteItemImage(id, item_id, deleteurl) {
  "use strict";
  swalWithBootstrapButtons
    .fire({
      icon: "warning",
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
              "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            },
            url: deleteurl,
            data: { id: id, item_id: item_id },
            method: "POST",
            success: function(response) {
              if (response == 1) {
                location.reload();
              } else if (response == 2) {
                swal_cancelled(last_image);
              } else {
                swal_cancelled();
              }
            },
            error: function(e) {
              swal_cancelled();
            }
          });
        });
      }
    })
    .then(result => {
      if (!result.isConfirmed) {
        result.dismiss === Swal.DismissReason.cancel;
      }
    });
}

$("#extras_no")
  .on("change", function() {
    "use strict";
    if ($("#extras_no").prop("checked") == true) {
      $("#extras").addClass("d-none");
      $("#add_extra").addClass("d-none");
      $("#add_extras").addClass("d-none");
      $("#globalextra").addClass("d-none");
      $("#extras input:text").prop("required", false);
    }
  })
  .change();
$("#extras_yes")
  .on("change", function() {
    "use strict";
    if ($("#extras_yes").prop("checked") == true) {
      $("#extras").removeClass("d-none");
      $("#add_extra").removeClass("d-none");
      $("#add_extras").removeClass("d-none");
      $("#globalextra").removeClass("d-none");
      $("#extras input:text").prop("required", true);
    }
  })
  .change();

var extras_row = 1;
function extras_fields(name, price) {
  "use strict";
  extras_row++;
  var divtest = document.createElement("div");
  divtest.setAttribute("class", "form-group mb-0 removeextras" + extras_row);
  divtest.innerHTML =
    '<div class="row variations"><div class="col-md-6"><div class="form-group"><input type="text" class="form-control" name="extras_name[]" placeholder="' +
    name +
    '" required></div></div><div class="col-md-6"><div class="form-group"><div class="d-flex"><input type="number" step="any" class="form-control numbers_only" name="extras_price[]"  placeholder="' +
    price +
    '" required><button class="btn btn-danger btn-sm rounded-5 ms-2 pricebtn btn-size" type="button" onclick="remove_extras_fields(' +
    extras_row +
    ');"><i class="fa-sharp fa-solid fa-xmark"></i></button></div></div></div>';
  $("#more_extras_fields").append(divtest);
}
function remove_extras_fields(rid) {
  "use strict";
  $(".removeextras" + rid).remove();
}

function more_editextras_fields(name, price) {
  "use strict";
  if (!$("span").hasClass("hiddenextrascount")) {
    $("#more_editextras_fields").prepend(
      '<span class="hiddenextrascount d-none">' + 1 + "</span>"
    );
  }
  var editroom = $("span.hiddenextrascount:last()").html();
  editroom++;
  var editdivtest = document.createElement("div");
  editdivtest.setAttribute("class", "row  editextrasclass" + editroom);
  editdivtest.innerHTML =
    '<input type="hidden" class="form-control" name="extras_id[]"><div class="col-md-6"><div class="form-group"><input type="text" class="form-control" name="extras_name[]"  placeholder="' +
    name +
    '" required></div></div><div class="col-md-6"><div class="form-group"><div class="d-flex"><input type="number" step="any" class="form-control numbers_only" name="extras_price[]"  placeholder="' +
    price +
    '" required><button class="btn btn-outline-danger mx-2 btn-size" type="button" onclick="remove_editextras_fields(' +
    editroom +
    ');"><i class="fa-sharp fa-solid fa-xmark"></i></button></div></div></div>';
  $("span.hiddenextrascount:last()").html(editroom);
  $("#more_editextras_fields").append(editdivtest);
  if ($("#more_editextras_fields").find(".form-group").length > 1) {
    $(".extras_name, .extras_price").prop("required", true);
  }
}

function remove_editextras_fields(rid) {
  "use strict";
  $(".editextrasclass" + rid).remove();
  if ($("#more_editextras_fields").find(".form-group").length == 0) {
    $(".extras_name, .extras_price").prop("required", false);
  }
}

// for add variant
function commonModal() {
  $("#commonModal").modal("show");
}
function addvariantModal() {
  $("#addvariantModal").modal("show");
}
$(document).on("click", ".add-variants", function(e) {
  e.preventDefault();

  var form = $(this).parents("form");
  var variantNameEle = $("#variant_name");
  var variantOptionsEle = $("#variant_options");
  var isValid = true;

  if (variantNameEle.val() == "") {
    variantNameEle.focus();
    isValid = false;
  } else if (variantOptionsEle.val() == "") {
    variantOptionsEle.focus();
    isValid = false;
  }

  if (isValid) {
    $.ajax({
      url: form.attr("action"),
      datType: "json",
      data: {
        variant_name: variantNameEle.val(),
        variant_options: variantOptionsEle.val(),
        hiddenVariantOptions: $("#hiddenVariantOptions").val()
      },
      success: function(data) {
        $("#hiddenVariantOptions").val(data.hiddenVariantOptions);
        $(".variant-table").html(data.varitantHTML);
        $("#variant_card").removeClass("d-none");
        if (page == "add") {
          $("#commonModal").modal("hide");
        }
        if (page == "edit") {
          $("#addvariantModal").modal("hide");
        }
      }
    });
  }
});

function allowNumbersOnly(e) {
  var code = e.which ? e.which : e.keyCode;
  if (code > 31 && (code < 48 || code > 57)) {
    e.preventDefault();
  }
}
$("#variant_options").keypress(function(e) {
  setTimeout(function() {
    var value = $("#variant_options").val();
    var updated = value.replace(/[`\/\\~_$&+,:;=?[\]@#{}'<>.^*()%!-/]/, "");
    $("#variant_options").val(updated);
  });
});

function edit_stock_management(id) {
  value = id.split("_");
  if ($("#" + id).prop("checked") == true) {
    $("#vqty_" + value[1]).prop("required", true);
    $("#vmin_order_" + value[1]).prop("required", true);
    $("#vmax_order_" + value[1]).prop("required", true);
    $("#vlow_qty_" + value[1]).prop("required", true);
  } else {
    $("#vqty_" + value[1]).prop("required", false);
    $("#vmin_order_" + value[1]).prop("required", false);
    $("#vmax_order_" + value[1]).prop("required", false);
    $("#vlow_qty_" + value[1]).prop("required", false);
  }
}

function remove_variation_fields(rid) {
  "use strict";
  $(".removeclass" + rid).remove();
}

$(".product_available")
  .on("change", function() {
    var id = this.id;
    if ($("#vstockmanagement_" + id).prop("checked") == true) {
      $("#voriginal_price_" + id).prop("required", true);
      $("#vprice_" + id).prop("required", true);
      $("#vquantity_" + id).prop("required", true);
      $("#vqty_" + id).prop("required", true);
      $("#vmin_order_" + id).prop("required", true);
      $("#vmax_order_" + id).prop("required", true);
      $("#vlow_qty_" + id).prop("required", true);
    } else {
      $("#vquantity_" + id).prop("required", false);
      $("#vqty_" + id).prop("required", false);
      $("#vmin_order_" + id).prop("required", false);
      $("#vmax_order_" + id).prop("required", false);
      $("#vlow_qty_" + id).prop("required", false);
    }
    if ($(this).prop("checked") == true) {
      $("#voriginal_price_" + id).prop("readonly", false);
      $("#vprice_" + id).prop("readonly", false);
      $("#vquantity_" + id).prop("readonly", false);
      $("#vqty_" + id).prop("readonly", false);
      $("#vmin_order_" + id).prop("readonly", false);
      $("#vmax_order_" + id).prop("readonly", false);
      $("#vlow_qty_" + id).prop("readonly", false);
      $("#vstockmanagement_" + id).prop("readonly", false);
    } else {
      $("#voriginal_price_" + id).prop("readonly", true);
      $("#vprice_" + id).prop("readonly", true);
      $("#vquantity_" + id).prop("readonly", true);
      $("#vmin_order_" + id).prop("readonly", true);
      $("#vmax_order_" + id).prop("readonly", true);
      $("#vlow_qty_" + id).prop("readonly", true);
      $("#vstockmanagement_" + id).prop("readonly", true);
      $("#vqty_" + id).prop("readonly", true);
    }
  })
  .change();

function deleteRow(id, btn) {
  let data = $("#hiddenVariantOptions").val();
  var parsedTest = JSON.parse(data);
  let res = parsedTest.map(item => ({
    variant_options: item.variant_options.filter(
      op => op !== $("#deleterow_" + id).val()
    )
  }));
  $("#hiddenVariantOptions").val(JSON.stringify(res));
  var row = btn.parentNode.parentNode;
  var Cells = row.getElementsByTagName("td");
  row.parentNode.removeChild(row);
}
function global_extras() {
  $.ajax({
    headers: {
      "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
    },
    url: extrasurl,
    data: {
      vendor_id: vendor_id
    },
    method: "get",
    success: function (response) {
      if (response.status == 1) {
        if (extras_row == 0) {
          extras_row = 1;
        }
        var html = "";
        for (i in response.responsdata) {
          {
            extras_row++;
            html +=
              '<div class="row  removeextras' +
              extras_row +
              '"><div class="col-md-6 form-group"><input type="text" class="form-control" name="extras_name[]" value="' +
              response.responsdata[i].name +
              '" placeholder="' +
              placehodername +
              '"></div><div class="col-md-6"><div class="d-flex"><input type="number" step="any" class="form-control numbers_only" value="' +
              response.responsdata[i].price +
              '" name="extras_price[]" placeholder="' +
              placeholderprice +
              '"><button class="btn btn-danger btn-size mx-2" type="button" onclick="remove_extras_fields(' +
              extras_row +
              ');"><i class="fa-sharp fa-solid fa-xmark"></i></button></div></div></div>';
          }
          $("#global-extras").html(html);
        }
      }
    },
    error: function (e) { }
  });
}
