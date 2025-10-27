<footer class="bg-dark text-white">
    <div class="footer-bg-color py-5">
        <div class="container">
            <div class="footer-contain row justify-content-between">
                <div class="col-md-12 col-lg-4 mt-4">
                    <div class="logo mb-4">
                        <a href="#"><img src="{{ helper::image_path(helper::appdata('')->logo) }}" alt="logo" class="img-fluid" style="max-height: 50px;"></a>
                    </div>
                    <p class="footer-contain my-4 text-white-50">
                        {{ trans('landing.footer_description') }}
                    </p>
                    <div class="mt-4">
                        <h6 class="text-white mb-3">{{ trans('landing.connect_with_us') }}</h6>
                        <div class="social-icon d-flex gap-3">
                            @foreach (@helper::getsociallinks(1) as $links)
                                <a href="{{$links->link}}" class="btn btn-outline-light btn-sm rounded-circle" style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;" target="_blank">
                                    <i class="fa-brands fa-{{$links->name}} fs-5"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 col-md-12">
                    <div class="row">
                        <div class="col-md-4 col-lg-4 col-xl-4 col-12 footer-contain">
                            <p class="footer-title mb-3 mt-4 fw-bold text-white">{{ trans('landing.about_us') }}</p>
                            <ul class="list-unstyled">
                                <li class="mb-2"><a href="{{URL::to('/#home')}}" class="text-white-50 text-decoration-none hover-primary">{{ trans('landing.home') }}</a></li>
                                <li class="mb-2"><a href="{{URL::to('/#features')}}" class="text-white-50 text-decoration-none hover-primary">{{ trans('landing.features') }}</a></li>
                                @if (App\Models\SystemAddons::where('unique_identifier', 'subscription')->first() != null && App\Models\SystemAddons::where('unique_identifier', 'subscription')->first()->activated == 1)
                                <li class="mb-2"><a href="{{URL::to('/#pricing-plans')}}" class="text-white-50 text-decoration-none hover-primary">{{ trans('landing.pricing_plan') }}</a></li>
                                @endif
                                @if (App\Models\SystemAddons::where('unique_identifier', 'blog')->first() != null && App\Models\SystemAddons::where('unique_identifier', 'blog')->first()->activated == 1)
                                <li class="mb-2"><a href="{{URL::to('blog_list')}}" class="text-white-50 text-decoration-none hover-primary">{{ trans('landing.blogs') }}</a></li>
                                @endif
                                <li class="mb-1"><a href="#contect-us" class="text-white-50 text-decoration-none hover-primary">{{ trans('landing.contact_us') }}</a></li>
                            </ul>
                        </div>
                        <div class="col-md-4 col-lg-4 col-xl-4 col-12 footer-contain">
                            <p class="footer-title mb-3 mt-4 fw-bold text-white">{{ trans('landing.other_pages') }}</p>
                            <ul class="list-unstyled">
                                <li class="mb-2"><a href="{{URL::to('privacy_policy')}}" class="text-white-50 text-decoration-none hover-primary">{{ trans('landing.privacy_policy') }}</a></li>
                                <li class="mb-2"><a href="{{URL::to('refund_policy')}}" class="text-white-50 text-decoration-none hover-primary">{{ trans('landing.refund_policy') }}</a></li>
                                <li class="mb-2"><a href="{{URL::to('terms_condition')}}" class="text-white-50 text-decoration-none hover-primary">{{ trans('landing.terms_condition') }}</a></li>
                                <li class="mb-2"><a href="{{URL::to('about_us')}}" class="text-white-50 text-decoration-none hover-primary">{{ trans('landing.about_us') }}</a></li>
                                <li class="mb-2"><a href="{{URL::to('faqs')}}" class="text-white-50 text-decoration-none hover-primary">{{ trans('landing.faqs') }}</a></li>
                                <li class="mb-2"><a href="{{URL::to('/#our-stores')}}" class="text-white-50 text-decoration-none hover-primary">{{ trans('landing.our_stores') }}</a></li>
                            </ul>
                        </div>
                        <div class="col-md-4 col-lg-4 col-xl-4 col-12 footer-contain">
                            <p class="footer-title mb-3 mt-4 fw-bold text-white">{{ trans('landing.help') }}</p>
                            <ul class="list-unstyled">
                                <li class="mb-3">
                                    <i class="fa-solid fa-envelope text-primary me-2"></i>
                                    <a href="mailto:{{helper::appdata('')->email}}" class="text-white-50 text-decoration-none hover-primary">{{helper::appdata('')->email}}</a>
                                </li>
                                <li class="mb-3">
                                    <i class="fa-solid fa-phone text-primary me-2"></i>
                                    <a href="tel:{{helper::appdata('')->contact}}" class="text-white-50 text-decoration-none hover-primary">{{helper::appdata('')->contact}}</a>
                                </li>
                            </ul>
                            <div class="mt-4">
                                <span class="badge bg-success px-3 py-2 mb-2 d-inline-block">
                                    <i class="fa-solid fa-shield-check me-1"></i> Paiements Sécurisés
                                </span>
                                <span class="badge bg-info px-3 py-2 mb-2 d-inline-block">
                                    <i class="fa-solid fa-headset me-1"></i> Support 24/7
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="border-secondary my-4">
            <div class="copyright-sec row justify-content-between align-items-center">
                <p class="m-0 text-white-50 col-12 col-md-8 text-md-start text-center small">{{helper::appdata('')->copyright}}</p>
                <div class="col-12 col-md-4 d-flex justify-content-md-end justify-content-center mt-2 mt-md-0">
                    <p class="text-white-50 small mb-0">Propulsé par <span class="text-primary fw-bold">RestroSaaS</span> 🚀</p>
                </div>
            </div>
        </div>
    </div>
</footer>
