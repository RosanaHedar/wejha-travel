import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import './Signup.css';

function Signup() {
 
  const [showPassword, setShowPassword] = useState(false);
  const [formData, setFormData] = useState({
    fullName: '',
    email: '',
    password: '',
  });

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    console.log('Signup submitted:', formData);
    // connect your API here, then:
    // navigate('/');
  };

  return (
    <div className="signup-page">
      <div className="signup-bg" />

      <div className="signup-card">

        <div className="signup-logo">
          <span className="we">WE</span>
          <span className="z">ج</span>
          <span className="ha">HA</span>
          <p className="logo-ar">وِجْهَة</p>
        </div>

        <div className="signup-heading">
          <h2>Create Account</h2>
          <p>Start your journey with us</p>
        </div>

        <form onSubmit={handleSubmit}>

          <div className="field">
            <label htmlFor="fullName">Full Name</label>
            <div className="input-wrap">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" strokeWidth="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
              <input
                id="fullName" name="fullName" type="text"
                placeholder="Ahmed Mohamed"
                value={formData.fullName}
                onChange={handleChange}
                required
              />
            </div>
          </div>

          <div className="field">
            <label htmlFor="email">Email</label>
            <div className="input-wrap">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" strokeWidth="2">
                <rect x="2" y="4" width="20" height="16" rx="2"/>
                <path d="m2 7 10 7 10-7"/>
              </svg>
              <input
                id="email" name="email" type="email"
                placeholder="ahmed@example.com"
                value={formData.email}
                onChange={handleChange}
                required
              />
            </div>
          </div>

          <div className="field">
            <label htmlFor="password">Password</label>
            <div className="input-wrap">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" strokeWidth="2">
                <rect x="3" y="11" width="18" height="11" rx="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
              <input
                id="password" name="password"
                type={showPassword ? 'text' : 'password'}
                placeholder="••••••••"
                value={formData.password}
                onChange={handleChange}
                required
              />
              <button
                type="button"
                className="eye-btn"
                onClick={() => setShowPassword(!showPassword)}
              >
                {showPassword ? (
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" strokeWidth="2">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>
                  </svg>
                ) : (
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" strokeWidth="2">
                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                )}
              </button>
            </div>
          </div>

          <button type="submit" className="btn-create">Create Account</button>

        </form>

        <div className="divider">
          <hr /><span>Or continue with</span><hr />
        </div>

        <div className="social-row">
          <button className="btn-social">
            <img src="https://www.google.com/favicon.ico" width="16" height="16" alt="Google" />
            Google
          </button>
          <button className="btn-social">
            <img src="https://www.facebook.com/favicon.ico" width="16" height="16" alt="Facebook" />
            Facebook
          </button>
        </div>

        <p className="login-row">
          Already have an account? <Link to="/login">Sign In</Link>
        </p>

      </div>
    </div>
  );
}

export default Signup;