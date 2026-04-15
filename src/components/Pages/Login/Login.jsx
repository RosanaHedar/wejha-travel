import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import './Login.css';

const Login = () => {
  const [showPassword, setShowPassword] = useState(false);
  const [formData, setFormData] = useState({ email: '', password: '' });

  const handleSubmit = (e) => {
    e.preventDefault();
    console.log('Login submitted:', formData);
  };

  return (
    <div className="login-page">
      <div className="login-bg" />
      <div className="login-card">

        <div className="login-logo">
          <span className="we">WE</span><span className="z">ج</span><span className="ha">HA</span>
          <p className="logo-ar">وِجْهَة</p>
        </div>

        <div className="login-heading">
          <h2>Welcome Back</h2>
          <p>Sign in to continue your adventure</p>
        </div>

        <form onSubmit={handleSubmit}>
          <div className="field">
            <label htmlFor="email">Email</label>
            <div className="input-wrap">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 7 10-7"/>
              </svg>
              <input
                id="email" type="email" placeholder="ahmed@example.com"
                value={formData.email}
                onChange={e => setFormData({...formData, email: e.target.value})}
                required
              />
            </div>
          </div>

          <div className="field">
            <label htmlFor="password">Password</label>
            <div className="input-wrap">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
              <input
                id="password"
                type={showPassword ? 'text' : 'password'}
                placeholder="••••••••"
                value={formData.password}
                onChange={e => setFormData({...formData, password: e.target.value})}
                required
              />
              <button type="button" className="eye-btn" onClick={() => setShowPassword(!showPassword)}>
                {showPassword ? '🙈' : '👁'}
              </button>
            </div>
          </div>

          <div className="forgot-row">
            <Link to="/forgot-password">Forgot password?</Link>
          </div>

          <button type="submit" className="btn-signin">Sign In</button>
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

        <p className="signup-row">
          Don't have an account? <Link to="/">Sign Up</Link>
        </p>

      </div>
    </div>
  );
};

export default Login;