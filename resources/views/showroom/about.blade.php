@extends('showroom.layout')

@section('title', 'About Us | Vehicles In Veranda')

@section('description', 'Learn more about Vehicles In Veranda, our 
approach to vehicle sales, inspection and customer service.')

@section('content')

<section class="about-page">

    {{-- HERO --}}
    <section class="about-hero">
        <div class="container">

            <span class="about-eyebrow">
                ABOUT VEHICLES IN VERANDA
            </span>

            <h1>
                Buying a vehicle
                should feel simple.
            </h1>

            <p>
                We believe finding the right vehicle should be a
                straightforward, transparent and trusted experience.
            </p>

        </div>
    </section>


    {{-- STORY --}}
    <section class="about-story">
        <div class="container">

            <div class="about-story__grid">

                <div class="about-story__visual">
                    <img
                        src="{{ asset('showroom/images/veranda.jpg') }}"
                        alt="Vehicles In Veranda showroom"
                    >
                </div>

                <div class="about-story__content">

                    <span class="section-heading__label">
                        OUR APPROACH
                    </span>

                    <h2>
                        A better way to
                        buy a vehicle.
                    </h2>

                    <p>
                        Vehicles In Veranda is built around carefully
                        selected vehicles, transparent dealing and
                        dependable customer service.
                    </p>

                    <p>
                        Whether you are looking for your next daily
                        driver, family vehicle or something special,
                        our team is here to make the process easier.
                    </p>

                    <a
                        href="{{ route('showroom.inventory') }}"
                        class="btn btn-primary"
                    >
                        Browse Inventory
                    </a>

                </div>

            </div>

        </div>
    </section>


    {{-- VALUES --}}
    <section class="about-values">

        <div class="container">

            <div class="section-heading">

                <div>
                    <span class="section-heading__label">
                        WHY VEHICLES IN VERANDA
                    </span>

                    <h2>
                        Built around trust.
                    </h2>
                </div>

                <p>
                    Every part of our showroom experience is designed
                    to give customers clear information and confidence
                    in their decision.
                </p>

            </div>


            <div class="about-values__grid">

                <article class="about-value">
                    <span>01</span>

                    <h3>
                        Carefully Selected
                    </h3>

                    <p>
                        We focus on vehicles that meet our standards
                        for quality, condition and value.
                    </p>
                </article>


                <article class="about-value">
                    <span>02</span>

                    <h3>
                        Transparent Dealing
                    </h3>

                    <p>
                        Clear vehicle information and straightforward
                        communication from start to finish.
                    </p>
                </article>


                <article class="about-value">
                    <span>03</span>

                    <h3>
                        Complete Documentation
                    </h3>

                    <p>
                        We provide the information and documentation
                        you need to make an informed decision.
                    </p>
                </article>


                <article class="about-value">
                    <span>04</span>

                    <h3>
                        Trusted Service
                    </h3>

                    <p>
                        Our team stays available before, during and
                        after your vehicle purchase.
                    </p>
                </article>

            </div>

        </div>

    </section>


    {{-- CTA --}}
    <section class="about-cta">

        <div class="container">

            <div class="about-cta__inner">

                <div>

                    <span class="about-eyebrow">
                        READY TO START?
                    </span>

                    <h2>
                        Find your next vehicle.
                    </h2>

                    <p>
                        Explore our current inventory or speak with
                        our team about what you are looking for.
                    </p>

                </div>

                <div class="about-cta__actions">

                    <a
                        href="{{ route('showroom.inventory') }}"
                        class="btn btn-primary"
                    >
                        Browse Inventory
                    </a>

                    <a
                        href="{{ route('showroom.home') }}#contact"
                        class="btn btn-outline"
                    >
                        Contact Us
                    </a>

                </div>

            </div>

        </div>

    </section>

</section>

@endsection
