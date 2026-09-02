@extends('showroom.layout')

@section('title', 'Contact Us | Vehicles In Veranda')

@section('description', 'Contact Vehicles In Veranda for vehicle 
enquiries, test drives and showroom information.')

@section('content')

<section class="contact-page">

    {{-- HERO --}}
    <section class="contact-hero">
        <div class="container">

            <span class="contact-eyebrow">
                CONTACT VEHICLES IN VERANDA
            </span>

            <h1>
                Let's talk about
                your next vehicle.
            </h1>

            <p>
                Have a question, want to book a test drive,
                or need help finding the right vehicle?
                Our team is here to help.
            </p>

        </div>
    </section>


    {{-- CONTACT AREA --}}
    <section class="contact-main">

        <div class="container">

            <div class="contact-grid">

                {{-- INFORMATION --}}
                <div class="contact-info">

                    <span class="section-heading__label">
                        GET IN TOUCH
                    </span>

                    <h2>
                        We'd love to
                        hear from you.
                    </h2>

                    <p class="contact-info__intro">
                        Speak with our showroom team about available
                        vehicles, test drives, trade-ins or anything
                        else you need help with.
                    </p>


                    <div class="contact-details">

                        <div class="contact-detail">

                            <span class="contact-detail__label">
                                PHONE
                            </span>

                            <a href="tel:03001234567">
                                0300-1234567
                            </a>

                        </div>


                        <div class="contact-detail">

                            <span class="contact-detail__label">
                                EMAIL
                            </span>

                            <a href="mailto:info@vehiclesinveranda.com">
                                info@vehiclesinveranda.com
                            </a>

                        </div>


                        <div class="contact-detail">

                            <span class="contact-detail__label">
                                LOCATION
                            </span>

                            <p>
                                Lahore, Pakistan
                            </p>

                        </div>


                        <div class="contact-detail">

                            <span class="contact-detail__label">
                                SHOWROOM HOURS
                            </span>

                            <p>
                                Monday – Saturday<br>
                                9:00 AM – 7:00 PM
                            </p>

                        </div>

                    </div>

                </div>


                {{-- FORM --}}
                <div class="contact-form-wrap">
                    <div class="contact-form-heading">
                        <span class="contact-eyebrow"> SEND AN ENQUIRY</span>
                        <h2>How can we help?</h2>
                         @if($vehicle)
                            <div class="contact-form__vehicle">
                                <span>ENQUIRING ABOUT</span>

                                <strong>
                                    {{ $vehicle->make->name }}
                                    {{ $vehicle->vehicleModel->name }}
                                    {{ $vehicle->year }}
                                </strong>

                                @if($vehicle->variant)
                                    <small>{{ $vehicle->variant->name }}</small>
                                @endif
                            </div>
                        @endif

                    </div>

                   @if(session('contact_success'))
                        <div class="contact-form__success">
                            {{ session('contact_success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="contact-form__errors">
                            Please check the highlighted information and try again.
                        </div>
                    @endif

                   <form method="POST" action="{{ route('showroom.contact.submit') }}" class="contact-form" >
                        @csrf
                        @if($vehicle)
                            <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                        @endif

                        <div class="contact-form__row">
                            <div class="contact-form__field">
                                <label for="name"> Your Name  </label>
                                <input type="text" id="name"=  name="name" value="{{ old('name') }}" placeholder="Your name" required >
                            </div>


                            <div class="contact-form__field">

                                <label for="phone">  Phone Number </label>
                                <input type="tel"  id="phone" name="phone" value="{{ old('phone') }}" placeholder="0300-1234567" required >
                            </div>

                        </div>


                        <div class="contact-form__field">
                            <label for="email">Email Address</label>
                           <input  type="email"  id="email"   name="email"  value="{{ old('email') }}" placeholder="you@example.com">
                        </div>

                        <div class="contact-form__field">
                            <label for="subject">
                                What can we help with?
                            </label>

                            <select id="subject" name="subject" required>
                                <option value="">Select an option</option>
                                <option value="vehicle-enquiry" @selected(old('subject') === 'vehicle-enquiry')>
                                    Vehicle Enquiry </option>

                                <option value="test-drive" @selected(old('subject') === 'test-drive')  >
                                    Book a Test Drive</option>
                                <option value="sell-trade" @selected(old('subject') === 'sell-trade') >
                                    Sell / Trade-In</option>
                                <option value="financing" @selected(old('subject') === 'financing')>
                                    Financing
                                </option>
                                <option value="general" @selected(old('subject') === 'general')>
                                    General Question
                                </option>
                            </select>
                        </div>


                        <div class="contact-form__field">

                            <label for="message">
                                Message
                            </label>
                            <textarea id="message" name="message" rows="6"  placeholder="Tell us how we can help..." required >
                               {{ old('message') }}
                           </textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Send Enquiry →
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>


    {{-- SHOWROOM --}}
    <section class="contact-showroom">

        <div class="container">

            <div class="contact-showroom__inner">

                <div>

                    <span class="contact-eyebrow">
                        VISIT OUR SHOWROOM
                    </span>

                    <h2>
                        Come see the
                        vehicles for yourself.
                    </h2>

                    <p>
                        Visit us in Lahore to explore our current
                        inventory and speak with our team in person.
                    </p>

                </div>

                <div class="contact-showroom__details">

                    <strong>
                        Vehicles In Veranda
                    </strong>

                    <p>
                        Lahore, Pakistan
                    </p>

                    <p>
                        Mon–Sat · 9am–7pm
                    </p>

                    <a
                        href="tel:03001234567"
                        class="btn btn-outline"
                    >
                        Call 0300-1234567
                    </a>

                </div>

            </div>

        </div>

    </section>


    {{-- CTA --}}
    <section class="contact-cta">

        <div class="container">

            <div class="contact-cta__inner">

                <span class="contact-eyebrow">
                    STILL LOOKING?
                </span>

                <h2>
                    Find your next vehicle.
                </h2>

                <p>
                    Browse our latest available vehicles and
                    find something that fits your needs.
                </p>

                <a
                    href="{{ route('showroom.inventory') }}"
                    class="btn btn-primary"
                >
                    Browse Inventory
                </a>

            </div>

        </div>

    </section>

</section>

@endsection
