import React from 'react';
import { Link } from 'react-router-dom';
import './footer.css';

function Footer() {
  return (
    <footer className="footer">
      <div className="footer-top">

        {/* Brand */}
        <div className="footer-brand">
          <div className="footer-logo">
            <span className="logo-we">WE</span>
            <span className="logo-sha">جHA</span>
            
          </div>
          <p className="footer-logo-arabic">وِجْهَة</p>
          <p className="footer-desc">
            Discover the magic of Egypt with customized travel experiences that
            create unforgettable memories.
          </p>
        </div>

        {/* Quick Links */}
        <div className="footer-col">
          <h4 className="footer-heading">Quick Links</h4>
          <ul className="footer-links">
            <li><Link to="/explore">Explore</Link></li>
            <li><Link to="/bundles">Bundles</Link></li>
            <li><Link to="/custom-package">Custom Package</Link></li>
            <li><Link to="/my-account">My Account</Link></li>
          </ul>
        </div>

        {/* Contact */}
        <div className="footer-col">
          <h4 className="footer-heading">
              <Link to="/contact" style={{ color: 'inherit', textDecoration: 'none' }}> Contact Us</Link>
            </h4>
          <ul className="footer-contact">
            <li>
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07
                  A19.5 19.5 0 0 1 4.07 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1
                  3 1.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0
                  1-.45 2.11L7.09 8.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1
                  2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21 16z" />
              </svg>
              <span>+20 123 456 7890</span>
            </li>
            <li>
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1
                  0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                <polyline points="22,6 12,13 2,6" />
              </svg>
              <span>info@wegha.eg</span>
            </li>
            <li>
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z" />
                <circle cx="12" cy="10" r="3" />
              </svg>
              <span>Cairo, Egypt</span>
            </li>
          </ul>
        </div>

        {/* Follow Us */}
        <div className="footer-col">
          <h4 className="footer-heading">Follow Us</h4>
          <div className="footer-socials">
            <a href="https://facebook.com" target="_blank" rel="noreferrer" className="social-icon" aria-label="Facebook">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1
                  1 0 0 1 1-1h3z" />
              </svg>
            </a>
            <a href="https://instagram.com" target="_blank" rel="noreferrer" className="social-icon" aria-label="Instagram">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
                <circle cx="12" cy="12" r="4" />
                <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none" />
              </svg>
            </a>
            <a href="https://twitter.com" target="_blank" rel="noreferrer" className="social-icon" aria-label="Twitter">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53A4.48 4.48 0 0 0
                  22.43.36a9 9 0 0 1-2.88 1.1A4.52 4.52 0 0 0 16.11 0c-2.5
                  0-4.52 2.02-4.52 4.52 0 .355.04.7.115 1.03C7.69 5.39 4.07
                  3.6 1.64.96a4.52 4.52 0 0 0-.61 2.27c0 1.57.8 2.95 2.01
                  3.76a4.5 4.5 0 0 1-2.05-.57v.057c0 2.19 1.56 4.02 3.63
                  4.43a4.5 4.5 0 0 1-2.04.077c.576 1.8 2.25 3.1 4.23
                  3.14A9.05 9.05 0 0 1 0 19.54a12.77 12.77 0 0 0 6.92
                  2.03c8.3 0 12.85-6.88 12.85-12.85 0-.196-.005-.39-.014-.583A9.17
                  9.17 0 0 0 23 3z" />
              </svg>
            </a>
          </div>
          <p className="footer-social-text">Stay updated with our latest offers and destinations!</p>
        </div>

      </div>

      {/* Divider */}
      <div className="footer-divider" />

      {/* Bottom Bar */}
      <div className="footer-bottom">
        <p>© 2026 WEجHA. All rights reserved.</p>
        <div className="footer-legal">
          <Link to="/privacy">Privacy Policy</Link>
          <Link to="/terms">Terms of Service</Link>
          <Link to="/cookies">Cookie Policy</Link>
        </div>
      </div>
    </footer>
  );
}

export default Footer;