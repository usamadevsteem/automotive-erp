@extends('showroom.layout')

@section('title', 'Inventory | Vehicles In Veranda')

@section('description', 'Browse all available vehicles at Vehicles In 
Veranda.')

@section('content')

<section class="inventory-page">
    <div class="container">

        <div class="inventory-page__header">
            <div>
                <span class="section-heading__label">
                    OUR INVENTORY
                </span>

                <h1>
                    Available Vehicles
                </h1>

                <p>
                    Browse our complete selection of vehicles currently
                    available in the showroom.
                </p>
            </div>

            <div class="inventory-page__count">
                {{ $vehicles->total() }} vehicles
            </div>
        </div>

           {{-- FILTERS GO HERE --}}

<form method="GET" action="{{ route('showroom.inventory') }}" class="inventory-filters">

    <div class="inventory-filters__search">
        <label for="search">Search</label>

        <input
            type="text"
            id="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Search make, model or stock number..."
        >
    </div>

    <div>
        <label for="make">Make</label>

        <select id="make" name="make">
            <option value="">All Makes</option>

            @foreach($makes as $make)
                <option
                    value="{{ $make->id }}"
                    @selected(request('make') == $make->id)
                >
                    {{ $make->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="fuel_type">Fuel</label>

        <select id="fuel_type" name="fuel_type">
            <option value="">All Fuel Types</option>

            @foreach(\App\Models\Vehicle::FUEL_TYPES as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(request('fuel_type') === $value)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="transmission">Transmission</label>

        <select id="transmission" name="transmission">
            <option value="">All Transmissions</option>

            @foreach(\App\Models\Vehicle::TRANSMISSIONS as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(request('transmission') === $value)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="min_price">Min Price</label>

        <input
            type="number"
            id="min_price"
            name="min_price"
            value="{{ request('min_price') }}"
            placeholder="Min PKR"
            min="0"
        >
    </div>

    <div>
        <label for="max_price">Max Price</label>

        <input
            type="number"
            id="max_price"
            name="max_price"
            value="{{ request('max_price') }}"
            placeholder="Max PKR"
            min="0"
        >
    </div>

    <div>
        <label for="sort">Sort</label>

        <select id="sort" name="sort">
            <option value="">Newest</option>

            <option
                value="price_low"
                @selected(request('sort') === 'price_low')
            >
                Price: Low to High
            </option>

            <option
                value="price_high"
                @selected(request('sort') === 'price_high')
            >
                Price: High to Low
            </option>

            <option
                value="oldest"
                @selected(request('sort') === 'oldest')
            >
                Oldest
            </option>
        </select>
    </div>

    <div class="inventory-filters__actions">
        <button type="submit" class="btn btn-primary">
            Apply Filters
        </button>

        <a
            href="{{ route('showroom.inventory') }}"
            class="btn btn-outline"
        >
            Clear
        </a>
    </div>

</form>



        @if($vehicles->count())

           <div class="vehicle-grid">
    @foreach($vehicles as $vehicle)

        <article class="vehicle-card">

            <a href="{{ route('showroom.vehicle', $vehicle) }}">
                <div class="vehicle-card__image">
                    <img
                        src="{{ $vehicle->featuredImageUrl('gallery') ?? asset('showroom/images/car-placeholder.svg') }}"
                        alt="{{ $vehicle->make->name }} {{ $vehicle->vehicleModel->name }}"
                    >
                </div>
            </a>

            <div class="vehicle-card__body">

                <div class="vehicle-card__make">
                    {{ $vehicle->make->name }}
                </div>

                <h2>
                    {{ $vehicle->vehicleModel->name }}
                    {{ $vehicle->year }}
                </h2>

                @if($vehicle->variant)
                    <div class="vehicle-card__variant">
                        {{ $vehicle->variant->name }}
                    </div>
                @endif

                <div class="vehicle-card__specs">
                    <span>{{ number_format($vehicle->mileage) }} km</span>
                    <span>{{ ucfirst($vehicle->fuel_type) }}</span>
                    <span>{{ ucfirst($vehicle->transmission) }}</span>
                </div>

                <div class="vehicle-card__footer">
                    <strong>
                        PKR {{ number_format($vehicle->sale_price) }}
                    </strong>

                    <a href="{{ route('showroom.vehicle', $vehicle) }}">
                        View →
                    </a>
                </div>

            </div>
        </article>

    @endforeach
</div>

 @if($vehicles->hasPages())
    <div class="inventory-pagination">

        <div class="inventory-pagination__summary">
            Showing
            <strong>{{ $vehicles->firstItem() }}</strong>
            to
            <strong>{{ $vehicles->lastItem() }}</strong>
            of
            <strong>{{ $vehicles->total() }}</strong>
            vehicles
        </div>

        <div class="inventory-pagination__links">

            @if($vehicles->onFirstPage())
                <span class="is-disabled">
                    ← Previous
                </span>
            @else
                <a href="{{ $vehicles->previousPageUrl() }}">
                    ← Previous
                </a>
            @endif

            @for($page = 1; $page <= $vehicles->lastPage(); $page++)

                @if($page === $vehicles->currentPage())
                    <span class="is-active">
                        {{ $page }}
                    </span>
                @else
                    <a href="{{ $vehicles->url($page) }}">
                        {{ $page }}
                    </a>
                @endif

            @endfor

            @if($vehicles->hasMorePages())
                <a href="{{ $vehicles->nextPageUrl() }}">
                    Next →
                </a>
            @else
                <span class="is-disabled">
                    Next →
                </span>
            @endif

        </div>

    </div>
@endif

      @else

    <div class="inventory-empty">
        <div class="inventory-empty__icon">🚗</div>
        <span class="section-heading__label"> NO MATCHES</span>
        <h2>No vehicles found</h2>
        <p>
            We couldn't find any vehicles matching your current filters.
            Try changing your search criteria.
        </p>

        <a
            href="{{ route('showroom.inventory') }}"
            class="btn btn-primary"
        >Clear All Filters</a>
    </div>

@endif

    </div>
</section>

@endsection
