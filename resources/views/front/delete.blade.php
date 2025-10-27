@extends('front.theme.default')
<!------ breadcrumb ------>
<section class="breadcrumb-sec">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a class="text-dark"
                        href="{{URL::to($storeinfo->slug.'/')}}">{{trans('labels.home')}}</a>
                </li>
                <li class="text-muted breadcrumb-item active" aria-current="page">{{ trans('labels.delete_profile') }}</li>
            </ol>
        </nav>
    </div>
</section>
<section class="product-prev-sec product-list-sec">
    <div class="container">
        <div class="user-bg-color mb-5">
            <div class="container">
                <div class="row">
                    @include('front.theme.user_sidebar')
                    <div class="col-xl-3 col-lg-8 col-xxl-9 col-12 deleteprofile py-5">
                        <div class="card shadow border-0 rounded-5">
                            <div class="settings-box-header border-bottom px-4">
                                <h5 class="text-dark m-0 profile-title my-4"><i class="fa-light fa-trash"></i><span
                                        class="px-2">{{ trans('labels.delete_profile') }}</span></h5>
                            </div>
                            <!-- Card body START -->
                            <div class="card-body">
                                <h6 class="fw-bold text-dark mb-1">Before you go...</h6>
                                <ol>
                                    <li class="text-muted">Take a backup of your data <a href="#">Here</a> </li>
                                    <li class="text-muted">If you delete your account, you will lose your all data.</li>
                                </ol>
                                <div class="form-check form-check-md my-4 text-muted">
                                    <input class="form-check-input p-0" type="checkbox" id="delete_account">
                                    <label class="form-check-label text-muted" for="delete_account">Yes, I'd like to
                                        delete my
                                        account</label>
                                </div>
                                <div class="d-md-flex align-items-center">
                                    <a href="#"
                                        class="col-12 col-md-4 col-xl-3 btn btn-store mb-3 mb-md-0 me-2">Keep my account</a>
                                    <a onclick="deleteaccount('{{ URL::to($storeinfo->slug . '/deleteaccount/') }}')" 
                                        class="col-12 col-md-4 col-xl-3 btn btn-danger"> {{ trans('labels.delete_profile') }}
                                    </a>
                                </div>
                            </div>
                            <!-- Card body END -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- newsletter -->
@include('front.theme.footer')
<script>
     var requiredmsg = "{{trans('messages.checkbox_delete_account')}}";
    function deleteaccount(nexturl) {
        var deleted = document.getElementById("delete_account").checked;
        if (deleted == true) {
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success mx-1',
                    cancelButton: 'btn btn-danger mx-1'
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
                    $('#preloader').show();
                    location.href = nexturl;
                } else {
                    result.dismiss === Swal.DismissReason.cancel
                }
            })
        } else {
            toastr.error(requiredmsg);
        }
    }
</script>
