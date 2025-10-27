{{-- Language Switcher Component --}}
{{-- Référence le composant dans resources/views/components/language-switcher.blade.php --}}

<div class="language-selector">
    @include('components.language-switcher')
</div>

<style>
.language-selector {
    display: inline-block;
    position: relative;
}

.language-selector .dropdown-menu {
    min-width: 120px;
}

.language-selector .flag-icon {
    margin-right: 8px;
}

.language-selector .current-lang {
    display: flex;
    align-items: center;
    padding: 6px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #fff;
    cursor: pointer;
    text-decoration: none;
    color: #333;
}

.language-selector .current-lang:hover {
    background: #f5f5f5;
    text-decoration: none;
    color: #333;
}

/* RTL Support */
[dir="rtl"] .language-selector .flag-icon {
    margin-left: 8px;
    margin-right: 0;
}
</style>
