<footer class="showroom-footer" id="contact">
    <div class="container">
        <div class="showroom-footer__grid">

            {{-- Brand --}}
            <div class="showroom-footer__brand">

                <a href="{{ route('showroom.home') }}" class="showroom-footer__logo" >
                    <span class="showroom-footer__logo-mark">V</span>
                    <span> Vehicles In Veranda</span>
                </a>

                <p>
                    Carefully selected vehicles.
                    Transparent dealing.
                    Trusted service.
                </p>
                <a href="{{ route('showroom.inventory') }}" class="showroom-footer__cta" >
                    Browse Inventory <span>→</span>
                </a>
            </div>


            {{-- Showroom --}}
            <div class="showroom-footer__column">
             <h3>Showroom</h3>
                <a href="{{ route('showroom.home') }}">Home</a>
                <a href="{{ route('showroom.inventory') }}">Inventory</a>
                <a href="{{ route('showroom.home') }}#about">About Us</a>
                <a href="#contact">Contact</a>

            </div>


            {{-- Services --}}
            <div class="showroom-footer__column">
                <h3>Services</h3>
                <a href="{{ route('showroom.inventory') }}">Buy a Vehicle</a>
                <a href="#contact">Sell Your Car</a>
                <a href="#contact">Trade-In</a>
                <a href="#contact">Vehicle Inspection</a>
            </div>


            {{-- Contact --}}
            <div class="showroom-footer__column showroom-footer__contact">
                <h3>Contact</h3>
                <a href="tel:03001234567">0300-1234567</a>
                <a href="mailto:info@vehiclesinveranda.com">info@vehiclesinveranda.com</a>
                <span>Lahore, Pakistan</span>
                <span> Mon–Sat · 9am–7pm</span>
            </div>
        </div>


        {{-- Bottom --}}
        <div class="showroom-footer__bottom">
            <span>© {{ date('Y') }} Vehicles In Veranda. All rights reserved.</span>
            <span>Premium Auto Showroom</span>
        </div>
    </div>
</footer>