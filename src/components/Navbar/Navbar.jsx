import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import './Navbar.css';

function Navbar() {
  const [menuOpen, setMenuOpen] = useState(false);

  return (
    <nav className="navbar">
      {/* Logo */}
      <div className="navbar-logo">
        <Link to="/">
          <span className="logo-we">WE</span>
          <span className="logo-sha">جHA</span>
          <span className="logo-arabic">وِجْهَة</span>
        </Link>
      </div>

      {/* Nav Links */}
      <ul className={`navbar-links ${menuOpen ? 'open' : ''}`}>
        <li><Link to="/" onClick={() => setMenuOpen(false)}>Home</Link></li>
        <li><Link to="/explore" onClick={() => setMenuOpen(false)}>Explore</Link></li>
        <li><Link to="/bundles" onClick={() => setMenuOpen(false)}>Bundles</Link></li>
      </ul>

      {/* Icons */}
      <div className="navbar-icons">
        <button className="icon-btn" title="Search">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
            viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
            <circle cx="11" cy="11" r="8" />
            <line x1="21" y1="21" x2="16.65" y2="16.65" />
          </svg>
        </button>
        <button className="icon-btn" title="Account">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
            viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
          </svg>
        </button>
        <button className="icon-btn" title="Cart">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
            viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
            <line x1="3" y1="6" x2="21" y2="6" />
            <path d="M16 10a4 4 0 0 1-8 0" />
          </svg>
        </button>
      </div>

      {/* Hamburger (mobile) */}
      <button className="hamburger" onClick={() => setMenuOpen(!menuOpen)}>
        <span /><span /><span />
      </button>
    </nav>
  );
}

export default Navbar;