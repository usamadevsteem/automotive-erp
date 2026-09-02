// ── Shared Navbar ───────────────────────────────────────────
function renderNavbar() {
    return `
    <nav class="navbar">
        <div class="container navbar__inner">
            <a href="index.html" class="navbar__logo">
                <div class="navbar__logo-icon">V</div>
                <div class="navbar__logo-text">
                  
                    Vehicles In Veranda
                    <small>Premium Car Showroom</small>
                </div>
            </a>
            <div class="nav" id="mainNav">
                <a href="index.html"    class="nav__link">Home</a>
                <a href="inventory.html" class="nav__link">Inventory</a>
                <a href="sell.html"     class="nav__link">Sell Your Car</a>
                <a href="about.html"    class="nav__link">About Us</a>
                <a href="contact.html"  class="nav__link">Contact</a>
            </div>
            <div class="navbar__cta">
                <a href="tel:03001234567" class="navbar__phone">📞 0300-1234567</a>
                <a href="inventory.html" class="btn btn-primary btn-sm">Browse Cars</a>
            </div>
            <div class="hamburger" id="hamburger" onclick="toggleMenu()">
                <span></span><span></span><span></span>
            </div>
        </div>
    </nav>`;
}

// ── Shared Footer ────────────────────────────────────────────
function renderFooter() {
    return `
    <footer class="footer">
        <div class="container">
            <div class="footer__grid">
                <div>
                    <div class="footer__brand">🚗 Vehicles In Veranda</div>
                    <p class="footer__desc">Pakistan's trusted name in quality pre-owned and new vehicles. Browse our inventory or visit us in Lahore.</p>
                    <div class="footer__social mt-3">
                        <a href="#">f</a>
                        <a href="#">in</a>
                        <a href="#">tw</a>
                        <a href="#">yt</a>
                    </div>
                </div>
                <div>
                    <div class="footer__heading">Quick Links</div>
                    <ul class="footer__links">
                        <li><a href="index.html">Home</a></li>
                        <li><a href="inventory.html">Browse Inventory</a></li>
                        <li><a href="sell.html">Sell Your Car</a></li>
                        <li><a href="about.html">About Us</a></li>
                        <li><a href="contact.html">Contact Us</a></li>
                    </ul>
                </div>
                <div>
                    <div class="footer__heading">Services</div>
                    <ul class="footer__links">
                        <li><a href="inventory.html">Car Showroom</a></li>
                        <li><a href="sell.html">Trade-In</a></li>
                        <li><a href="contact.html">Test Drive</a></li>
                        <li><a href="contact.html">Financing</a></li>
                        <li><a href="contact.html">Inspection</a></li>
                    </ul>
                </div>
                <div>
                    <div class="footer__heading">Contact</div>
                    <ul class="footer__links">
                        <li><a href="tel:03001234567">📞 0300-1234567</a></li>
                        <li><a href="mailto:info@vehiclesinveranda.com">✉️ info@vehiclesinveranda.com</a></li>
                        <li><a href="#">📍 Lahore, Pakistan</a></li>
                        <li><a href="#">🕐 Mon–Sat 9am–7pm</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer__bottom">
                <span>© ${new Date().getFullYear()} Vehicles In Veranda. All rights reserved.</span>
                <span>Powered by AutoDealer ERP</span>
            </div>
        </div>
    </footer>
    <a href="https://wa.me/923001234567" class="whatsapp-float" title="Chat on WhatsApp" target="_blank">💬</a>`;
}

// ── Toggle mobile menu ───────────────────────────────────────
function toggleMenu() {
    document.getElementById('mainNav').classList.toggle('open');
}

// ── Inject layout ────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const navEl = document.getElementById('navbar-placeholder');
    const footEl = document.getElementById('footer-placeholder');
    if (navEl)  navEl.innerHTML  = renderNavbar();
    if (footEl) footEl.innerHTML = renderFooter();

    // Set active nav link
    const page = window.location.pathname.split('/').pop() || 'index.html';
    document.querySelectorAll('.nav__link').forEach(a => {
        if (a.getAttribute('href') === page) a.classList.add('active');
    });
});
