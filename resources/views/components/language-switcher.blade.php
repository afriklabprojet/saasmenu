{{-- Language Switcher Component - multi_language addon --}}
<div class="language-switcher">
    <div class="dropdown">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="languageSwitcher" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            @if(app()->getLocale() == 'fr')
                <img src="{{ asset('assets/images/flags/fr.png') }}" alt="Français" class="flag-icon" onerror="this.style.display='none'"> {{ __('Français') }}
            @elseif(app()->getLocale() == 'ar')
                <img src="{{ asset('assets/images/flags/ar.png') }}" alt="العربية" class="flag-icon" onerror="this.style.display='none'"> {{ __('العربية') }}
            @else
                <img src="{{ asset('assets/images/flags/en.png') }}" alt="English" class="flag-icon" onerror="this.style.display='none'"> {{ __('English') }}
            @endif
        </button>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="languageSwitcher">
            {{-- multi_language: Support FR/EN/AR --}}
            <a class="dropdown-item {{ app()->getLocale() == 'fr' ? 'active' : '' }}" href="?lang=fr">
                <img src="{{ asset('assets/images/flags/fr.png') }}" alt="Français" class="flag-icon" onerror="this.style.display='none'"> Français
            </a>
            <a class="dropdown-item {{ app()->getLocale() == 'en' ? 'active' : '' }}" href="?lang=en">
                <img src="{{ asset('assets/images/flags/en.png') }}" alt="English" class="flag-icon" onerror="this.style.display='none'"> English
            </a>
            <a class="dropdown-item {{ app()->getLocale() == 'ar' ? 'active' : '' }}" href="?lang=ar">
                <img src="{{ asset('assets/images/flags/ar.png') }}" alt="العربية" class="flag-icon" onerror="this.style.display='none'"> العربية
            </a>
        </div>
    </div>
</div>

<style>
.language-switcher {
    display: inline-block;
}

.flag-icon {
    width: 20px;
    height: 14px;
    margin-right: 5px;
    object-fit: cover;
    border: 1px solid #ddd;
}

.language-switcher .dropdown-item.active {
    background-color: #f8f9fa;
    font-weight: bold;
}

.language-switcher .dropdown-item:hover {
    background-color: #e9ecef;
}
</style>
