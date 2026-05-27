import React, { useState } from 'react';
import Navbar from '../../Navbar/Navbar';
import './Contact.css';

function Contact() {
  const [form, setForm] = useState({ name: '', email: '', phone: '', subject: '', message: '' });
  const [sent, setSent] = useState(false);

  const handleChange = e => setForm({ ...form, [e.target.name]: e.target.value });

  const handleSubmit = e => {
    e.preventDefault();
    setSent(true);
  };

  return (
    <div className="contact-page">
      <Navbar />
      <div 
  className="contact-hero"
  style={{ backgroundImage: `url('/images/Pyramids.jpg')` }}
   >
        <div className="contact-hero-overlay" />
        <h1>Contact Us</h1>
        <p>We're here to help plan your perfect Egyptian adventure</p>
      </div>

      <div className="contact-content">
        {/* Left — Info */}
        <div className="contact-info">
          <p className="contact-questions-tag">QUESTIONS?</p>
          <h2>Contact Our Team</h2>
          <p className="contact-desc">
            Our travel experts are available anytime to make your trip to Egypt the best ever!
            Connect with us on social media, send an email, or share your trip details using the form.
          </p>

          <div className="contact-socials">
            {['f', 'in', 'yt', 'tk'].map(s => (
              <a key={s} href="/" className="contact-social-btn">{s}</a>
            ))}
          </div>

          <a href="mailto:info@wejha.com" className="contact-method">
            <div className="contact-method-icon">✉</div>
            <div>
              <span>EMAIL US</span>
              <strong>info@wejha.com</strong>
            </div>
          </a>

          <a href="tel:+201234567890" className="contact-method">
            <div className="contact-method-icon">📞</div>
            <div>
              <span>CALL US</span>
              <strong>+20 123 456 7890</strong>
            </div>
          </a>
        </div>

        {/* Right — Form */}
        <div className="contact-form-card">
          {sent ? (
            <div className="contact-success">
              <div className="contact-success-icon">✓</div>
              <h3>Message Sent!</h3>
              <p>We'll get back to you within 24 hours.</p>
              <button onClick={() => setSent(false)}>Send Another</button>
            </div>
          ) : (
            <form onSubmit={handleSubmit}>
              <div className="contact-row">
                <div className="contact-field">
                  <label>NAME</label>
                  <input name="name" type="text" placeholder="Name" value={form.name} onChange={handleChange} required />
                </div>
                <div className="contact-field">
                  <label>EMAIL</label>
                  <input name="email" type="email" placeholder="Email" value={form.email} onChange={handleChange} required />
                </div>
              </div>
              <div className="contact-row">
                <div className="contact-field">
                  <label>PHONE</label>
                  <input name="phone" type="tel" placeholder="Phone" value={form.phone} onChange={handleChange} />
                </div>
                <div className="contact-field">
                  <label>SUBJECT</label>
                  <input name="subject" type="text" placeholder="Subject" value={form.subject} onChange={handleChange} required />
                </div>
              </div>
              <div className="contact-field">
                <label>WRITE A MESSAGE</label>
                <textarea name="message" placeholder="Write A Message" rows="6" value={form.message} onChange={handleChange} required />
              </div>
              <button type="submit" className="contact-submit">SEND A MESSAGE</button>
            </form>
          )}
        </div>
      </div>
    </div>
  );
}

export default Contact;