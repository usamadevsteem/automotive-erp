<header class="showroom-header">
    <div class="container showroom-header__inner">
        {{-- Logo --}}
        <a href="{{ route('showroom.home') }}" class="showroom-logo" >
            <span class="showroom-logo__mark">V </span>
            <span class="showroom-logo__text">
                <strong>Vehicles In Veranda</strong>
                <small>Premium Auto Showroom</small>
            </span>
        </a>


        {{-- Main navigation --}}
        <nav
            class="showroom-nav"
            aria-label="Main navigation"
        >

            <a
                href="{{ route('showroom.home') }}"
                class="{{ request()->routeIs('showroom.home') ? 'is-active' : '' }}"
            >Home</a>

            <a
                href="{{ route('showroom.inventory') }}"
                class="{{ request()->routeIs('showroom.inventory') ? 'is-active' : '' }}"
            >Inventory</a>
            <a href="{{ route('showroom.about') }}">About</a>
           <a href="{{ route('showroom.contact') }}">Contact</a>
        </nav>


        {{-- Header action --}}
        <div class="showroom-header__action">

            <a
                href="{{ route('showroom.inventory') }}"
                class="showroom-header__button"
            >Browse Cars <span>→</span>
            </a>
        </div>
    </div>
</header>