@extends('showroom.layout')

@section('title', $vehicle->make->name . ' ' . $vehicle->vehicleModel->name . ' | Vehicles In Veranda')

@section('description', 'View details, specifications and pricing for this vehicle.')

@section('content')

<section class="vehicle-detail">
    <div class="container">

        <a href="{{ route('showroom.inventory') }}" class="vehicle-detail__back">
            ← Back to inventory
        </a>

        <div class="vehicle-detail__layout">

            {{-- =====================================================
                 LEFT: IMAGE GALLERY
                 ===================================================== --}}
            <div class="vehicle-gallery">

                <div class="vehicle-gallery__main">

                    @if($featuredImageUrl)
                        <img
                            id="vehicleMainImage"
                            src="{{ $featuredImageUrl }}"
                            alt="{{ $vehicle->make->name }} {{ $vehicle->vehicleModel->name }}"
                        >
                    @else
                        <img
                            id="vehicleMainImage"
                            src="{{ asset('showroom/images/car-placeholder.svg') }}"
                            alt="Vehicle image unavailable"
                        >
                    @endif

                    @if($vehicleImages->count() > 1)
                        <button
                            type="button"
                            class="vehicle-gallery__arrow vehicle-gallery__arrow--prev"
                            id="vehiclePrev"
                            aria-label="Previous image"
                        >
                            ←
                        </button>

                        <button
                            type="button"
                            class="vehicle-gallery__arrow vehicle-gallery__arrow--next"
                            id="vehicleNext"
                            aria-label="Next image"
                        >
                            →
                        </button>

                        <div class="vehicle-gallery__counter">
                            <span id="vehicleCurrent">1</span>
                            /
                            {{ $vehicleImages->count() }}
                        </div>
                    @endif

                </div>

                @if($vehicleImages->count() > 1)

                    <div class="vehicle-gallery__thumbnails">

                        @foreach($vehicleImages as $index => $image)

                            <button
                                type="button"
                                class="vehicle-gallery__thumbnail {{ $index === 0 ? 'is-active' : '' }}"
                                data-index="{{ $index }}"
                                data-image="{{ $image->getUrl() }}"
                                aria-label="View vehicle image {{ $index + 1 }}"
                            >
                                <img
                                    src="{{ $image->getUrl('thumb') }}"
                                    alt=""
                                >
                            </button>

                        @endforeach

                    </div>

                @endif


                        {{-- =====================================================
             SPECIFICATIONS
             ===================================================== --}}
<section class="vehicle-information" id="vehicleInformation">
    <div class="vehicle-information__tabs" role="tablist">
        <button
            type="button"
            class="vehicle-information__tab is-active"
            data-tab="specifications"
            role="tab"
            aria-selected="true"
        >
            Specifications
        </button>

        <button
            type="button"
            class="vehicle-information__tab"
            data-tab="overview"
            role="tab"
            aria-selected="false"
        >
            Overview
        </button>

        <button
            type="button"
            class="vehicle-information__tab"
            data-tab="enquire"
            role="tab"
            aria-selected="false"
        >
            Enquire
        </button>

    </div>



    <div
        class="vehicle-information__panel is-active"
        data-panel="specifications"
    >

        <div class="vehicle-specifications__grid">

            <div class="vehicle-specification">
                <span>Make</span>
                <strong>{{ $vehicle->make->name }}</strong>
            </div>

            <div class="vehicle-specification">
                <span>Model</span>
                <strong>{{ $vehicle->vehicleModel->name }}</strong>
            </div>

            @if($vehicle->variant)
                <div class="vehicle-specification">
                    <span>Variant</span>
                    <strong>{{ $vehicle->variant->name }}</strong>
                </div>
            @endif

            <div class="vehicle-specification">
                <span>Year</span>
                <strong>{{ $vehicle->year }}</strong>
            </div>

            @if($vehicle->color)
                <div class="vehicle-specification">
                    <span>Color</span>
                    <strong>{{ $vehicle->color }}</strong>
                </div>
            @endif

            <div class="vehicle-specification">
                <span>Mileage</span>
                <strong>{{ number_format($vehicle->mileage) }} km</strong>
            </div>

            <div class="vehicle-specification">
                <span>Fuel Type</span>
                <strong>{{ ucfirst($vehicle->fuel_type) }}</strong>
            </div>

            <div class="vehicle-specification">
                <span>Transmission</span>
                <strong>{{ ucfirst($vehicle->transmission) }}</strong>
            </div>

            @if($vehicle->engine)
                <div class="vehicle-specification">
                    <span>Engine</span>
                    <strong>{{ $vehicle->engine }}</strong>
                </div>
            @endif

            @if($vehicle->condition)
                <div class="vehicle-specification">
                    <span>Condition</span>
                    <strong>{{ ucfirst($vehicle->condition) }}</strong>
                </div>
            @endif

            <div class="vehicle-specification">
                <span>Import Status</span>
                <strong>
                    {{ \App\Models\Vehicle::IMPORT_STATUSES[$vehicle->import_status] ?? ucfirst($vehicle->import_status) }}
                </strong>
            </div>

            <div class="vehicle-specification">
                <span>Stock No.</span>
                <strong>{{ $vehicle->stock_number }}</strong>
            </div>

            <div class="vehicle-specification">
                <span>Category</span>
                <strong>
                    {{ \App\Models\Vehicle::CATEGORIES[$vehicle->category] ?? ucfirst(str_replace('_', ' ', $vehicle->category)) }}
                </strong>
            </div>

            <div class="vehicle-specification">
                <span>Status</span>
                <strong>
                    {{ \App\Models\Vehicle::STATUSES[$vehicle->status] ?? ucfirst($vehicle->status) }}
                </strong>
            </div>

        </div>

    </div>


    {{-- =====================================================
         OVERVIEW
         ===================================================== --}}
    <div
        class="vehicle-information__panel"
        data-panel="overview"
    >

        <div class="vehicle-overview">

            <h2>
                {{ $vehicle->make->name }}
                {{ $vehicle->vehicleModel->name }}
                {{ $vehicle->year }}
            </h2>

            @if($vehicle->notes)

                <p>
                    {{ $vehicle->notes }}
                </p>

            @else

                <p>
                    This {{ $vehicle->make->name }}
                    {{ $vehicle->vehicleModel->name }}
                    is currently available at Vehicles In Veranda.
                    Contact us for more information, inspection and
                    test-drive availability.
                </p>

            @endif

        </div>

    </div>


    {{-- =====================================================
         ENQUIRE
         ===================================================== --}}
    <div
        class="vehicle-information__panel"
        data-panel="enquire"
    >

        <div class="vehicle-enquiry">

            <div>
                <span class="section-heading__label">
                    INTERESTED IN THIS VEHICLE?
                </span>

                <h2>
                    Enquire about this vehicle
                </h2>

                <p>
                    Contact our showroom team for pricing, availability,
                    inspection or a test drive.
                </p>
            </div>

            <div class="vehicle-enquiry__actions">

                <a
                    href="https://wa.me/923001234567"
                    target="_blank"
                    class="btn btn-whatsapp"
                >
                    WhatsApp Us
                </a>

                <a
                    href="tel:03001234567"
                    class="btn btn-primary"
                >
                    Call 0300-1234567
                </a>

            </div>
        </div>
    </div>
</section>

            </div>


            {{-- =====================================================
                 RIGHT: VEHICLE SUMMARY
                 ===================================================== --}}
            <aside class="vehicle-summary">

                <div class="vehicle-summary__make">
                    {{ $vehicle->make->name }}
                </div>

                <h1>
                    {{ $vehicle->vehicleModel->name }}
                    {{ $vehicle->year }}
                </h1>

                @if($vehicle->variant)
                    <p class="vehicle-summary__variant">
                        {{ $vehicle->variant->name }}
                    </p>
                @endif

                <div class="vehicle-summary__price">
                    PKR {{ number_format($vehicle->sale_price) }}
                </div>


                <div class="vehicle-summary__actions">
                 <a href="{{ route('showroom.contact', ['vehicle_id' => $vehicle->id]) }}" class="btn btn-primary">
                    Send Enquiry</a>                  
                  <a href="https://wa.me/923001234567" target="_blank" class="btn btn-whatsapp">  WhatsApp Us </a>
                  <a href="tel:03001234567" class="btn btn-outline">  Call 0300-1234567 </a>
                </div>


                {{-- Quick specs --}}
                <div class="vehicle-summary__quick">

                    <div class="vehicle-summary__quick-title">
                        QUICK SPECS
                    </div>

                    <div class="vehicle-summary__quick-row">
                        <span>Year</span>
                        <strong>{{ $vehicle->year }}</strong>
                    </div>

                    <div class="vehicle-summary__quick-row">
                        <span>Mileage</span>
                        <strong>
                            {{ number_format($vehicle->mileage) }} km
                        </strong>
                    </div>

                    <div class="vehicle-summary__quick-row">
                        <span>Fuel</span>
                        <strong>
                            {{ ucfirst($vehicle->fuel_type) }}
                        </strong>
                    </div>

                    <div class="vehicle-summary__quick-row">
                        <span>Transmission</span>
                        <strong>
                            {{ ucfirst($vehicle->transmission) }}
                        </strong>
                    </div>

                    @if($vehicle->color)
                        <div class="vehicle-summary__quick-row">
                            <span>Color</span>
                            <strong>{{ $vehicle->color }}</strong>
                        </div>
                    @endif

                </div>


                <div class="vehicle-summary__availability">

                    <span>Available at</span>

                    <strong>
                        {{ $vehicle->branch->name ?? 'Vehicles In Veranda' }}
                    </strong>

                </div>

                       {{-- =====================================================
     SIMILAR VEHICLES
     ===================================================== --}}
@if($similarVehicles->count())

    <div class="similar-vehicles">

        <div class="similar-vehicles__heading">
            <span>YOU MAY ALSO LIKE</span>
            <h2>Similar Vehicles</h2>
        </div>

        <div class="similar-vehicles__list">

            @foreach($similarVehicles as $similar)

                <a
                    href="{{ route('showroom.vehicle', $similar) }}"
                    class="similar-vehicle"
                >

                    <div class="similar-vehicle__image">

                        <img
                            src="{{ $similar->featuredImageUrl('gallery') ?? asset('showroom/images/car-placeholder.svg') }}"
                            alt="{{ $similar->make->name }} {{ $similar->vehicleModel->name }}"
                        >

                    </div>

                    <div class="similar-vehicle__content">

                        <span class="similar-vehicle__make">
                            {{ $similar->make->name }}
                        </span>

                        <strong class="similar-vehicle__title">
                            {{ $similar->vehicleModel->name }}
                            {{ $similar->year }}
                        </strong>

                        @if($similar->variant)
                            <span class="similar-vehicle__variant">
                                {{ $similar->variant->name }}
                            </span>
                        @endif

                        <span class="similar-vehicle__price">
                            PKR {{ number_format($similar->sale_price) }}
                        </span>

                    </div>

                </a>

            @endforeach

        </div>

    </div>




            </aside>

        </div>





 

        @endif

    </div>
</section>

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    const mainImage = document.getElementById('vehicleMainImage');
    const thumbnails = Array.from(
        document.querySelectorAll('.vehicle-gallery__thumbnail')
    );

    const prevButton = document.getElementById('vehiclePrev');
    const nextButton = document.getElementById('vehicleNext');
    const currentCounter = document.getElementById('vehicleCurrent');

    if (!mainImage || !thumbnails.length) {
        return;
    }

    let currentIndex = 0;

    function showImage(index) {

        if (index < 0) {
            index = thumbnails.length - 1;
        }

        if (index >= thumbnails.length) {
            index = 0;
        }

        currentIndex = index;

        mainImage.src = thumbnails[index].dataset.image;

        thumbnails.forEach(function (thumbnail, thumbnailIndex) {
            thumbnail.classList.toggle(
                'is-active',
                thumbnailIndex === currentIndex
            );
        });

        if (currentCounter) {
            currentCounter.textContent = currentIndex + 1;
        }
    }

    thumbnails.forEach(function (thumbnail, index) {

        thumbnail.addEventListener('click', function () {
            showImage(index);
        });

    });

    if (prevButton) {
        prevButton.addEventListener('click', function () {
            showImage(currentIndex - 1);
        });
    }

    if (nextButton) {
        nextButton.addEventListener('click', function () {
            showImage(currentIndex + 1);
        });
    }

});

</script>
<script>
        /*
     * Vehicle information tabs
     */
    const informationTabs = document.querySelectorAll(
        '.vehicle-information__tab'
    );

    const informationPanels = document.querySelectorAll(
        '.vehicle-information__panel'
    );

    informationTabs.forEach(function (tab) {

        tab.addEventListener('click', function () {

            const target = this.dataset.tab;

            informationTabs.forEach(function (item) {
                item.classList.remove('is-active');
                item.setAttribute('aria-selected', 'false');
            });

            informationPanels.forEach(function (panel) {
                panel.classList.remove('is-active');
            });

            this.classList.add('is-active');
            this.setAttribute('aria-selected', 'true');

            const targetPanel = document.querySelector(
                '.vehicle-information__panel[data-panel="' + target + '"]'
            );

            if (targetPanel) {
                targetPanel.classList.add('is-active');
            }

        });

    });
</script>

@endpush

@endsection