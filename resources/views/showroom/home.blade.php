@extends('showroom.layout')

@section('title', 'Vehicles In Veranda | Find Your Next Vehicle')

@section('description', 'Browse available vehicles at Vehicles In Veranda.')

@section('content')



<section class="showroom-hero">
    <div class="container">
        <div class="showroom-hero__grid">
            {{-- Left: Hero content --}}
            <div class="showroom-hero__content">
                <span class="showroom-hero__eyebrow">
                    VEHICLES IN VERANDA
                </span>
                <h1>
                    Find a vehicle
                    <span>worth driving.</span>
                </h1>
                <p>
                    Discover carefully selected vehicles with
                    transparent pricing, complete documentation,
                    and trusted service.
                </p>
                <div class="showroom-hero__actions">
                    <a href="{{ route('showroom.inventory') }}" class="btn btn-primary"
                    >Browse Inventory
                        <span>→</span>
                    </a>
                    <a href="{{ route('showroom.contact') }}" class="btn btn-outline hero_contact_btn"
                    >
                        Contact Us
                    </a>
                </div>
                <div class="showroom-hero__trust">
                    <div>
                        <strong>01</strong>
                        <span>Verified Vehicles</span>
                    </div>
                    <div>
                        <strong>02</strong>
                        <span>Transparent Pricing</span>
                    </div>
                    <div>
                        <strong>03</strong>
                        <span>Trusted Service</span>
                    </div>
                </div>
            </div>


            {{-- Right: Hero visual --}}
            <div class="showroom-hero__visual">
                <div class="showroom-hero__image">
                    <img
                        src="{{ asset('showroom/images/Hero_banner.png') }}"
                        alt="Premium vehicle at Vehicles In Veranda"
                    >
                </div>
                <div class="showroom-hero__badge">
                    <strong> Premium</strong>
                    <span>Auto Showroom</span>
                 </div>
            </div>
        </div>
    </div>
</section>


{{-- =====================================================
     VEHICLE SEARCH
     ===================================================== --}}

<section class="showroom-search">
    <div class="container">
        <div class="showroom-search__box">
            <div class="showroom-search__intro">
                <span> FIND YOUR VEHICLE  </span>
                <h2> What are you looking for? </h2>
            </div>


            <form
                method="GET"
                action="{{ route('showroom.inventory') }}"
                class="showroom-search__form"  >

                {{-- Search --}}
                <div class="showroom-search__field showroom-search__field--wide">
                    <label for="home-search"> Search</label>
                    <input
                        type="text"
                        id="home-search"
                        name="search"
                        placeholder="Make, model or stock number" >
                </div>


                {{-- Fuel --}}
                <div class="showroom-search__field">
                    <label for="home-fuel">Fuel </label>
                    <select
                        id="home-fuel"
                        name="fuel_type" >
                        <option value=""> Any Fuel </option>
                        @foreach(\App\Models\Vehicle::FUEL_TYPES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>


                {{-- Transmission --}}
                <div class="showroom-search__field">
                    <label for="home-transmission"> Transmission</label>
                    <select
                        id="home-transmission"
                        name="transmission" >
                        <option value=""> Any Transmission </option>
                        @foreach(\App\Models\Vehicle::TRANSMISSIONS as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>


                {{-- Search button --}}
                <button
                    type="submit"
                    class="showroom-search__button">
                    Search Vehicles
                    <span>→</span>
                </button>
            </form>


            <div class="showroom-search__bottom">
                <span>Looking for something specific? </span>
                <a href="{{ route('showroom.inventory') }}">View all vehicles →</a>
            </div>
        </div>
    </div>
</section>

{{-- =====================================================
     FEATURED VEHICLES
     ===================================================== --}}

<sectionon class="showroom-vehicles">
    <div class="container">
        <div class="section-heading">
            <div>
                <span class="section-heading__label"> FEATURED INVENTORY </span>
                <h2>Find your next car.</h2>
            </div>
            <div class="section-heading__side">
                <p> A selection of vehicles currently available
                    at Vehicles In Veranda. </p>

                <a
                    href="{{ route('showroom.inventory') }}"
                    class="section-heading__link"  > View all inventory
                    <span>→</span>
                </a>
            </div>
        </div>


        @if($vehicles->count())
            <div class="vehicle-grid">
                @foreach($vehicles as $vehicle)
                    <article class="vehicle-card">
                        {{-- Image --}}
                        <a href="{{ route('showroom.vehicle', $vehicle) }}"
                            class="vehicle-card__image-link"  >
                            <div class="vehicle-card__image">
                                <img
                                    src="{{ $vehicle->featuredImageUrl('gallery') ?? asset('showroom/images/car-placeholder.svg') }}"
                                    alt="{{ $vehicle->make->name }} {{ $vehicle->vehicleModel->name }}"
                                    loading="lazy" >
                                <span class="vehicle-card__status"> Available</span>
                            </div>
                        </a>


                        {{-- Content --}}
                        <div class="vehicle-card__body">
                            <div class="vehicle-card__make"> {{ $vehicle->make->name }} </div>
                            <h3>{{ $vehicle->vehicleModel->name }} {{ $vehicle->year }} </h3>

                            @if($vehicle->variant)
                                <div class="vehicle-card__variant"> {{ $vehicle->variant->name }} </div>

                            @endif


                            <div class="vehicle-card__specs">
                                <span>  {{ number_format($vehicle->mileage) }} km </span>
                                <span>{{ ucfirst($vehicle->fuel_type) }}</span>
                                <span>{{ ucfirst($vehicle->transmission) }}</span>
                            </div>


                            <div class="vehicle-card__footer">
                                <strong> PKR {{ number_format($vehicle->sale_price) }}  </strong>
                                <a href="{{ route('showroom.vehicle', $vehicle) }}">View Details →
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="showroom-vehicles__bottom">
                <a
                    href="{{ route('showroom.inventory') }}"
                    class="btn btn-outline"  > Browse Full Inventory
                    <span>→</span>
                </a>
            </div>

        @else

            <div class="showroom-vehicles__empty">
                <h3> No vehicles currently available. </h3>
                <p>Please check back soon for new arrivals. </p>
                <a
                    href="{{ route('showroom.inventory') }}"
                    class="btn btn-primary"> View Inventory
                </a>
            </div>

        @endif
    </div>
</section>



<!-- ── Showcase Categories ───────────────────────────────── -->
<section class="showcase-section">
    <div class="showcase-grid">
        <a href="inventory.html" class="showcase-card">
            <img src="{{ asset('showroom/images/1.jpg') }}" alt="Used Cars">
            <div class="showcase-overlay"></div>
            <div class="showcase-content">
                <span>BEST 2026</span>
                <h2>USED CARS</h2>
                <span class="showcase-btn">VIEW PRODUCTS</span>
            </div>
        </a>

        <a href="inventory.html?status=sold" class="showcase-card">
            <img src="{{ asset('showroom/images/2.jpg') }}" alt="Sold Vehicles">
            <div class="showcase-overlay"></div>
            <div class="showcase-content">
                <span>PREVIOUSLY</span>
                <h2>SOLD</h2>
                <span class="showcase-btn">VIEW PRODUCTS</span>
            </div>
        </a>

        <a href="inventory.html?status=new" class="showcase-card">
            <img src="{{ asset('showroom/images/4.jpg') }}" alt="New Arrivals">
            <div class="showcase-overlay"></div>
            <div class="showcase-content">
                <span>2026</span>
                <h2>NEW ARRIVAL</h2>
                <span class="showcase-btn">VIEW PRODUCTS</span>
            </div>
        </a>

        <a href="inventory.html" class="showcase-card">
            <img src="{{ asset('showroom/images/3.jpg') }}" alt="Quality Cars">
            <div class="showcase-overlay"></div>
            <div class="showcase-content">
                <span>NEW CONDITION</span>
                <h2>CARS</h2>
                <span class="showcase-btn">VIEW PRODUCTS</span>
            </div>
        </a>

        <a href="inventory.html" class="showcase-card">
            <img src="{{ asset('showroom/images/5.jpg') }}" alt="Japan Imports">
            <div class="showcase-overlay"></div>
            <div class="showcase-content">
                <span>DIRECTLY IMPORT</span>
                <h2>JAPAN IMPORTS</h2>
                <span class="showcase-btn">VIEW PRODUCTS</span>
            </div>
        </a>

        <a href="sell.html" class="showcase-card">
            <img src="{{ asset('showroom/images/6.jpg') }}" alt="Sell Your Car">
            <div class="showcase-overlay"></div>
            <div class="showcase-content">
                <span>SELL YOUR CAR</span>
                <h2>SELL NOW</h2>
                <span class="showcase-btn">VIEW PRODUCTS</span>
            </div>
        </a>

    </div>

</section>

{{-- =====================================================
     WHY CHOOSE US
     ===================================================== --}}

<section class="showroom-why">
    <div class="container">
        <div class="showroom-why__grid">
            {{-- Left heading --}}
            <div class="showroom-why__intro">
                <span class="section-heading__label">  WHY VEHICLES IN VERANDA </span>

                <h2> Buying a car
                    should feel simple. </h2>
                <p>
                    We focus on making every step clear, straightforward
                    and dependable — from choosing your vehicle to
                    driving it home.
                </p>
                <a
                    href="{{ route('showroom.inventory') }}"
                    class="btn btn-primary" >Explore Our Vehicles
                    <span>→</span>
                </a>
            </div>


            {{-- Right features --}}
            <div class="showroom-why__features">
                <article class="showroom-why__feature">
                    <span class="showroom-why__number">   01 </span>
                    <div>
                        <h3>Carefully Selected</h3>
                        <p>
                            Every vehicle is selected with quality,
                            condition and value in mind.
                        </p>
                    </div>
                </article>


                <article class="showroom-why__feature">
                    <span class="showroom-why__number">  02  </span>

                    <div>
                        <h3>Transparent Dealing </h3>
                        <p>Clear pricing and straightforward vehicle
                            information, without unnecessary surprises. </p>
                    </div>
                </article>


                <article class="showroom-why__feature">
                    <span class="showroom-why__number"> 03</span>
                    <div>
                        <h3> Complete Documentation</h3>
                        <p>We help make the documentation and ownership
                            process simple and easy to understand. </p>
                    </div>
                </article>


                <article class="showroom-why__feature">
                    <span class="showroom-why__number">  04</span>
                    <div>
                        <h3>Trusted Service  </h3>
                        <p>Our team is here before, during and after
                            your vehicle purchase. </p>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>


{{-- =====================================================
     ABOUT THE SHOWROOM
     ===================================================== --}}

<section class="showroom-about" id="about">
    <div class="container">
        <div class="showroom-about__grid">

            {{-- Image --}}
            <div class="showroom-about__visual">
                <div class="showroom-about__image">
                    <img
                        src="{{ asset('showroom/images/veranda.jpg') }}"
                        alt="Vehicles In Veranda showroom"
                        loading="lazy" >
                </div>
                <div class="showroom-about__caption">
                    <span> VEHICLES IN VERANDA   </span>
                    <strong>Lahore, Pakistan  </strong>
                </div>
            </div>


            {{-- Content --}}
            <div class="showroom-about__content">
                <span class="section-heading__label">  ABOUT OUR SHOWROOM </span>
                <h2> A better way
                    to buy a vehicle.   </h2>
                <p class="showroom-about__lead">
                    At Vehicles In Veranda, we believe buying a vehicle
                    should be straightforward, transparent and enjoyable.  </p>
                <p>Our showroom brings together carefully selected
                    vehicles for customers who value quality, honest
                    information and dependable service.</p>

                <div class="showroom-about__facts">
                    <div>
                        <strong>Quality </strong>
                        <span> Carefully selected vehicles </span>
                    </div>
                    <div>
                        <strong> Trust </strong>
                        <span>Transparent vehicle dealing </span>
                    </div>
                    <div>
                        <strong>Service</strong>
                        <span>Support from showroom to ownership</span>
                    </div>
                </div>

                <div class="showroom-about__actions">
                    <a href="{{ route('showroom.inventory') }}"  class="btn btn-primary" > Browse Inventory <span>→</span> </a>
                    <a href="#contact" class="btn btn-outline">Talk to Our Team</a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- =====================================================
     SERVICES
     ===================================================== --}}

<section class="showroom-services">
    <div class="container">
        <div class="showroom-services__header">
            <div>
                <span class="section-heading__label">OUR SERVICES </span>
                <h2>More than
                    just a showroom.</h2> 
            </div>
            <p>
                From finding the right vehicle to making ownership
                simple, our team is here to help at every step.
            </p>
        </div>


        <div class="showroom-services__list">
            {{-- Service 01 --}}
            <a href="{{ route('showroom.inventory') }}" class="showroom-service" >
                <span class="showroom-service__number">  01 </span>
                <div class="showroom-service__content">
                    <h3>Buy a Vehicle</h3>
                    <p>
                        Explore our available vehicles and find one
                        that fits your needs and budget.
                    </p>
                </div>
                <span class="showroom-service__arrow"> ↗ </span>
            </a>


            {{-- Service 02 --}}
            <a href="#contact" class="showroom-service">
                <span class="showroom-service__number"> 02 </span>
                <div class="showroom-service__content">
                    <h3> Sell or Trade-In  </h3>
                    <p>
                        Looking to move on from your current vehicle?
                        Speak with our team about selling or trading it.
                    </p>
                </div>

                <span class="showroom-service__arrow">  ↗ </span>
            </a>


            {{-- Service 03 --}}
            <a href="#contact" class="showroom-service"  >
                <span class="showroom-service__number">  03   </span>
                <div class="showroom-service__content">
                    <h3> Vehicle Inspection  </h3>
                    <p>
                        Get clear information about vehicle condition
                        before making your decision.
                    </p>
                </div>
                <span class="showroom-service__arrow"> ↗</span>
            </a>


            {{-- Service 04 --}}
            <a href="#contact"  class="showroom-service">
                <span class="showroom-service__number">  04 </span>
                <div class="showroom-service__content">
                    <h3>Financing & Support </h3>
                    <p>Talk to our team about financing options,
                        documentation and the ownership process. </p>
                </div>
                <span class="showroom-service__arrow"> ↗</span>
            </a>
        </div>
    </div>
</section>

{{-- =====================================================
     FINAL CTA
     ===================================================== --}}

<section class="showroom-cta">
    <div class="container">
        <div class="showroom-cta__box">
            <div class="showroom-cta__content">
                <span class="showroom-cta__eyebrow"> READY TO FIND YOUR VEHICLE? </span>
                <h2>Your next vehicle
                    could be here. </h2>
                <p>
                    Explore our available inventory or speak with
                    our team and let us help you find the right vehicle.
                </p>


                <div class="showroom-cta__actions">
                    <a href="{{ route('showroom.inventory') }}" class="btn showroom-cta__primary" >
                        Browse Inventory
                        <span>→</span>
                    </a>

                    <a href="#contact" class="btn showroom-cta__secondary" >
                        Contact Us
                    </a>
                </div>
            </div>
            <div class="showroom-cta__mark">
                V
            </div>
        </div>
    </div>
</section>




@endsection