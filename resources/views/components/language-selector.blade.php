<!-- Language Selector Component -->
<div class="language-selector">
    <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="languageDropdown"
            data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-globe me-1"></i>
            @switch(app()->getLocale())
                @case('fr')
                    <img src="{{ asset('flags/fr.png') }}" alt="Français" width="16" height="12" class="me-1"> Français
                @break

                @case('ar')
                    <img src="{{ asset('flags/ar.png') }}" alt="العربية" width="16" height="12" class="me-1"> العربية
                @break

                @default
                    <img src="{{ asset('flags/en.png') }}" alt="English" width="16" height="12" class="me-1"> English
            @endswitch
        </button>
        <ul class="dropdown-menu" aria-labelledby="languageDropdown">
            <li>
                <a class="dropdown-item {{ app()->getLocale() == 'en' ? 'active' : '' }}"
                    href="{{ route('lang.switch', 'en') }}">
                    <img src="{{ asset('flags/en.png') }}" alt="English" width="16" height="12" class="me-2">
                    English
                </a>
            </li>
            <li>
                <a class="dropdown-item {{ app()->getLocale() == 'fr' ? 'active' : '' }}"
                    href="{{ route('lang.switch', 'fr') }}">
                    <img src="{{ asset('flags/fr.png') }}" alt="Français" width="16" height="12" class="me-2">
                    Français
                </a>
            </li>
            <li>
                <a class="dropdown-item {{ app()->getLocale() == 'ar' ? 'active' : '' }}"
                    href="{{ route('lang.switch', 'ar') }}">
                    <img src="{{ asset('flags/ar.png') }}" alt="العربية" width="16" height="12" class="me-2">
                    العربية
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
    .language-selector .dropdown-item.active {
        background-color: #0d6efd;
        color: white;
    }

    .language-selector .dropdown-item:hover {
        background-color: #f8f9fa;
    }

    .language-selector img {
        border-radius: 2px;
    }
</style>
