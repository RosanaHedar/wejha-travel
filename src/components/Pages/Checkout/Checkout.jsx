import React, { useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import Navbar from '../../Navbar/Navbar';
import './Checkout.css';

function Checkout() {
  const navigate = useNavigate();
  const location = useLocation();
  const { items = [], subtotal = 0, serviceFee = 0, total = 0 } = location.state || {};

  const [form, setForm] = useState({
    fullName: '', email: '', phone: '',
    cardNumber: '', expiry: '', cvv: '',
  });
  const [done, setDone] = useState(false);

  const handleChange = (e) => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    setDone(true);
  };

  if (done) {
    return (
      <div className="checkout-page">
        <Navbar />
        <div className="checkout-success">
          <div className="checkout-success-icon">✓</div>
          <h2>Booking Confirmed!</h2>
          <p>Thank you, {form.fullName || 'traveler'}! Your booking is confirmed.<br />A confirmation email has been sent to {form.email || 'your email'}.</p>
          <button className="checkout-btn" onClick={() => navigate('/')}>Back to Home</button>
        </div>
      </div>
    );
  }

  return (
    <div className="checkout-page">
      <Navbar />

      <div className="checkout-header">
        <h1>Checkout</h1>
        <p>Complete your booking securely</p>
      </div>

      <div className="checkout-content">
        {/* Left — Form */}
        <div className="checkout-form-wrap">
          <form onSubmit={handleSubmit}>
            <div className="checkout-section">
              <h2>Payment Details</h2>

              <div className="checkout-field">
                <label>Full Name</label>
                <input
                  name="fullName" type="text"
                  placeholder="Ahmed Mohamed"
                  value={form.fullName}
                  onChange={handleChange}
                  required
                />
              </div>

              <div className="checkout-field">
                <label>Email</label>
                <input
                  name="email" type="email"
                  placeholder="ahmed@example.com"
                  value={form.email}
                  onChange={handleChange}
                  required
                />
              </div>

              <div className="checkout-field">
                <label>Phone Number</label>
                <input
                  name="phone" type="tel"
                  placeholder="+20 123 456 7890"
                  value={form.phone}
                  onChange={handleChange}
                  required
                />
              </div>
            </div>

            <div className="checkout-section">
              <h2>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1a3a6b" strokeWidth="2">
                  <rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>
                </svg>
                Card Information
              </h2>

              <div className="checkout-field">
                <label>Card Number</label>
                <input
                  name="cardNumber" type="text"
                  placeholder="1234 5678 9012 3456"
                  maxLength="19"
                  value={form.cardNumber}
                  onChange={handleChange}
                  required
                />
              </div>

              <div className="checkout-row">
                <div className="checkout-field">
                  <label>Expiry Date</label>
                  <input
                    name="expiry" type="text"
                    placeholder="MM/YY"
                    maxLength="5"
                    value={form.expiry}
                    onChange={handleChange}
                    required
                  />
                </div>
                <div className="checkout-field">
                  <label>CVV</label>
                  <input
                    name="cvv" type="text"
                    placeholder="123"
                    maxLength="3"
                    value={form.cvv}
                    onChange={handleChange}
                    required
                  />
                </div>
              </div>
            </div>

            <button type="submit" className="checkout-btn">
              Complete Payment
            </button>
          </form>
        </div>

        {/* Right — Order Summary */}
        <div className="checkout-summary">
          <h2>Order Summary</h2>

          <div className="checkout-summary-items">
            {items.length > 0 ? items.map(item => (
              <div key={item.id} className="checkout-summary-item">
                <span>{item.name}</span>
                <strong>{(item.price * item.qty).toLocaleString()} EGP</strong>
              </div>
            )) : (
              <p style={{ color: '#888', fontSize: '14px' }}>No items</p>
            )}
          </div>

          <div className="checkout-summary-divider" />

          <div className="checkout-summary-item">
            <span>Subtotal</span>
            <span>{subtotal.toLocaleString()} EGP</span>
          </div>
          <div className="checkout-summary-item">
            <span>Service Fee (5%)</span>
            <span>{serviceFee.toLocaleString()} EGP</span>
          </div>

          <div className="checkout-summary-divider" />

          <div className="checkout-summary-item total">
            <strong>Total</strong>
            <strong className="checkout-total">{total.toLocaleString()} EGP</strong>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Checkout;