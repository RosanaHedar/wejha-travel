import React, { useState, useEffect, } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import './Navbar.css';

function Navbar() {
  const [menuOpen, setMenuOpen]       = useState(false);
  const [profileOpen, setProfileOpen] = useState(false);
  const [servicesOpen, setServicesOpen] = useState(false);
  const [scrolled, setScrolled]       = useState(false);
  const navigate = useNavigate();

  useEffect(() => {
    const handleScroll = () => setScrolled(window.scrollY > 50);
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  return (
    <nav className={`navbar ${scrolled ? 'scrolled' : ''}`}>

      {/* Logo */}
      <div className="navbar-logo">
        <Link to="/">
          <img src="/images/logo2.png" alt="WEzHA Logo" className="navbar-logo-img" />
        </Link>
      </div>

      {/* Nav Links */}
      <ul className={`navbar-links ${menuOpen ? 'open' : ''}`}>

        <li>
          <Link to="/" onClick={() => setMenuOpen(false)}>Home</Link>
        </li>

        <li>
          <Link to="/bundles" onClick={() => setMenuOpen(false)}>Bundles</Link>
        </li>

        {/* Services Dropdown */}
        <li
          className="nav-services"
          onMouseEnter={() => setServicesOpen(true)}
          onMouseLeave={() => setServicesOpen(false)}
        >
          <span className="nav-services-label">
            Services
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" strokeWidth="2">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </span>
          {servicesOpen && (
            <div className="services-dropdown">
              <Link to="/bundles" onClick={() => { setServicesOpen(false); setMenuOpen(false); }}>
                📦 Packages
              </Link>
              <Link to="/bundles" onClick={() => { setServicesOpen(false); setMenuOpen(false); }}>
                🏨 Hotels
              </Link>
              <Link to="/bundles" onClick={() => { setServicesOpen(false); setMenuOpen(false); }}>
                🗺️ Day Trips
              </Link>
              <Link to="/bundles" onClick={() => { setServicesOpen(false); setMenuOpen(false); }}>
                🛍️ Shopping
              </Link>
            </div>
          )}
        </li>

        <li>
          <Link to="/#reviews" onClick={() => setMenuOpen(false)}>Reviews</Link>
        </li>

         </ul>

      {/* Icons */}
      <div className="navbar-icons">

        {/* Search */}
        <button className="icon-btn search-label" title="Search">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
            viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
            <circle cx="11" cy="11" r="8"/>
            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <span>Search</span>
        </button>

        {/* Profile Dropdown */}
        <div
          className="profile-dropdown-wrap"
          onMouseEnter={() => setProfileOpen(true)}
          onMouseLeave={() => setProfileOpen(false)}
        >
          <button className="icon-btn" title="Account">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
              viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
          </button>
          {profileOpen && (
            <div className="profile-dropdown">
              <p className="profile-dropdown-title">Welcome to WEzHA</p>
              <p className="profile-dropdown-sub">Sign in to access your saved itineraries</p>
              <Link to="/login" className="profile-dropdown-item"
                onClick={() => setProfileOpen(false)}>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" strokeWidth="2">
                  <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                  <polyline points="10 17 15 12 10 7"/>
                  <line x1="15" y1="12" x2="3" y2="12"/>
                </svg>
                <div>
                  <strong>Log in</strong>
                  <span>Access your saved itineraries</span>
                </div>
              </Link>
              <Link to="/signup" className="profile-dropdown-item"
                onClick={() => setProfileOpen(false)}>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" strokeWidth="2">
                  <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                  <circle cx="8.5" cy="7" r="4"/>
                  <line x1="20" y1="8" x2="20" y2="14"/>
                  <line x1="23" y1="11" x2="17" y2="11"/>
                </svg>
                <div>
                  <strong>Sign Up</strong>
                  <span>Create a free WEzHA account</span>
                </div>
              </Link>
            </div>
          )}
        </div>

        {/* Cart */}
        <button className="icon-btn" title="Cart" onClick={() => navigate('/cart')}>
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
            viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <path d="M16 10a4 4 0 0 1-8 0"/>
          </svg>
        </button>

      </div>

      {/* Hamburger */}
      <button className="hamburger" onClick={() => setMenuOpen(!menuOpen)}>
        <span/><span/><span/>
      </button>

    </nav>
  );
}

export default Navbar;