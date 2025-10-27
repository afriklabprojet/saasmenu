<form method="POST"
    action="{{ URL::to('admin/products/get-product-variants-possibilities') }}"
    id="editvariants">
    @csrf
    <input name="variant_edit" type="hidden" value="edit">

    @foreach($productVariantOption as $kry => $variantOpt)
        <div class="form-group">
            <h4> {{ $variantOpt['variant_name'] }}:
                <small>{{ __('Variant Options') }}</small> </h4>
        </div>
        <div class="form-group">
            <input class="form-control" name="variant_edt[{{ $kry }}][variant_name]" type="hidden"
                id="variant_name{{ $kry }}" value="{{ $variantOpt['variant_name'] }}"
                onkeyup="this.value = this.value.replace(/[`\/\\~_$&+,:;=?[\]@#{}'<>.^*()%!-/]/, '')">
            <input class="form-control" name="variant_edt[{{ $kry }}][variant_options]" type="text"
                onkeyup="this.value = this.value.replace(/[`\/\\~_$&+,:;=?[\]@#{}'<>.^*()%!-/]/, '')" id="variant_options{{ $kry }}"
                placeholder="{{ __('Variant Options separated by|pipe symbol, i.e Black|Blue|Red') }}">
        </div>
    @endforeach
    <div class="form-group col-12 d-flex justify-content-end col-form-label">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-secondary btn-light"
            data-bs-dismiss="modal">
        <input type="button" value="{{ __('Add Variants') }}"
            class="btn btn-secondary addOredit-variants ms-2">
    </div>
</form>
<script>
    $(document).on('click', '.addOredit-variants', function (e) {
        e.preventDefault();
        var forms = $(this).parents('form');

        var hiddenVariantOptions = $('#hiddenVariantOptions').val();
        let form = document.getElementById('editvariants');
        let fd = new FormData(form);

        fd.append('hiddenVariantOptions', hiddenVariantOptions);
        $.ajax({
            type: 'POST',
            url: "{{ URL::to('admin/products/product-variants-possibilities', ['item_id' => $item_id]) }}",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: fd,
            processData: false,
            contentType: false,
            cache: false,
            success: function (data) {

                $('#hiddenVariantOptions').val(data.hiddenVariantOptions);
                $('.variant-table').html(data.varitantHTML);
                $("#commonModal").modal('hide');
            },
        });
    });
</script>
<script>
    function regularexpession(id) {
        $('#' + id).keypress(function (e) {
            setTimeout(function () {
                var value = $('#variant_options').val();
                var updated = value.replace(/[`\/\\~_$&+,:;=?[\]@#{}'<>.^*()%!-/]/, '');
                $('#variant_options').val(updated);
            });

        });
    }
</script>
<script>
    function validation(value, id) {
        if (value.includes('@')) {
            newval = value.replaceAll('@', '');
            $('#' + id).val(newval);
        }
        if (value.includes('\\')) {
            newval = value.replaceAll('\\', '');
            $('#' + id).val(newval);
        }
    }
</script>