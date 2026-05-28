import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import './Home.css';
import aswanimg from '../../assests/images/Aswan-City-Egypt-Egypt-Tours-Portal.jpg';
import aswanimg2 from '../../assests/images/aswan1.jpg'
import aswanimg3 from '../../assests/images/cairohome.webp'
import aswanimg4 from '../../assests/images/1676131.jpg'
import aswanimg5 from '../../assests/images/Oasi-di-Siwa-Egitto-Africa-SOCIAL-1.jpg'
import aswanimg6 from '../../assests/images/9-Best-Things-to-Do-in-Alexandria-Egypt.webp'
// Replace these with your actual images inside src/assets/images/
// Example: import aswanImg from '../../assets/images/aswan.jpg';
// For now we use placeholder colors + labels so the slider still works
import Navbar from '../../Navbar/Navbar';

const slides = [
  {
    id: 1,
    title: 'Tranquil Aswan',
    subtitle: 'Sail along the legendary Nile River',
    image: aswanimg,
  },
  {
    id: 2,
    title: 'Mystical Luxor',
    subtitle: 'Walk among the temples of ancient kings',
    image: aswanimg2,
  },
  {
    id: 3,
    title: 'Timeless Cairo',
    subtitle: 'Where the pyramids meet the modern world',
    image: aswanimg3,
  },
  {
    id: 4,
    title: 'Coastal Hurghada',
    subtitle: 'Dive into crystal-clear Red Sea waters',
    image : aswanimg4
  },
  {
    id: 5,
    title: 'Serene Siwa',
    subtitle: 'Escape to the heart of the Western Desert',
    image : aswanimg5
  },
  {
    id: 6,
    title: 'Vibrant Alexandria',
    subtitle: 'A city shaped by centuries of history',
    image : aswanimg6
    
  },
];

function Home() {
  const [currentSlide, setCurrentSlide] = useState(0);
  const navigate = useNavigate();

  // Auto-advance every 5 seconds
  useEffect(() => {
    const timer = setInterval(() => {
      setCurrentSlide((prev) => (prev + 1) % slides.length);
    }, 5000);
    return () => clearInterval(timer);
  }, []);

  const goTo = (index) => setCurrentSlide(index);
  const goPrev = () => setCurrentSlide((prev) => (prev - 1 + slides.length) % slides.length);
  const goNext = () => setCurrentSlide((prev) => (prev + 1) % slides.length);

  const slide = slides[currentSlide];

  return (
    <div className="home">
      {/* Hero Slider */}
      <Navbar/>
      <section
        className="hero"
        style={{
          // If you have a real image: backgroundImage: `url(${slide.image})`
          backgroundImage: `url(${slide.image})`
        }}
      >
        {/* Dark overlay */}
        <div className="hero-overlay" />

        {/* Content */}
        <div className="hero-content" key={currentSlide}>
          <h1 className="hero-title">{slide.title}</h1>
          <p className="hero-subtitle">{slide.subtitle}</p>
         <button className="hero-btn" onClick={() => navigate('/custom-package')}>
  Custom your Package
</button>
        </div>

        {/* Prev Arrow */}
        <button className="slider-arrow left" onClick={goPrev}>
          &#8249;
        </button>

        {/* Next Arrow */}
        <button className="slider-arrow right" onClick={goNext}>
          &#8250;
        </button>

        {/* Dots */}
        <div className="slider-dots">
          {slides.map((_, i) => (
            <button
              key={i}
              className={`dot ${i === currentSlide ? 'active' : ''}`}
              onClick={() => goTo(i)}
            />
          ))}
        </div>
      </section>
      {/* ── Browse Categories ─────────────────────── */}
<section className="categories-section">
  <div className="categories-arrow">
    <svg viewBox="0 0 120 60" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M10 50 Q40 10 80 30" stroke="white" strokeWidth="2" strokeDasharray="6 4" fill="none"/>
      <polygon points="78,22 88,34 76,36" fill="white"/>
      <text x="20" y="20" fill="white" fontSize="13" fontFamily="Georgia, serif" fontStyle="italic">OR BROWSE CATEGORIES</text>
    </svg>
  </div>
  <div className="categories-grid">
    {[
      { icon: '🏛️', label: 'Museums',  color: '#dbeafe', iconBg: '#93c5fd' },
      { icon: '🏨', label: 'Hotels',   color: '#fef3c7', iconBg: '#fcd34d' },
      { icon: '🛍️', label: 'Shopping', color: '#e0e7ff', iconBg: '#a5b4fc' },
      { icon: '🚢', label: 'Cruises',  color: '#d1fae5', iconBg: '#6ee7b7' },
    ].map(cat => (
      <div key={cat.label} className="cat-card">
        <div className="cat-icon-wrap" style={{ background: cat.color }}>
          <span className="cat-icon" style={{ background: cat.iconBg }}>{cat.icon}</span>
        </div>
        <p className="cat-label">{cat.label}</p>
      </div>
    ))}
  </div>
</section>

{/* ── Why Choose Us ─────────────────────────── */}
<section className="why2-section">
  <div className="why2-header">
    <h2>Why Choose Us?</h2>
    <p>We craft unforgettable adventures for passionate explorers around the world.</p>
  </div>
  <div className="why2-grid">
    {[
      {
        img: 'https://images.unsplash.com/photo-1539768942893-daf53e448371?w=600&q=80',
        title: 'Customize Your Package',
        desc: 'Every journey is uniquely yours. Design your perfect Egyptian adventure day by day — choose your destinations, activities, hotels and dining, all crafted around your vision.',
      },
      {
        img: 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=600&q=80',
        title: 'The Best of Youth Travel',
        desc: 'Built for the bold and the curious. We bring you high-energy itineraries, budget-smart stays, and electric experiences that match the pace and passion of young explorers.',
      },
      {
        img: 'https://images.unsplash.com/photo-1506929562872-bb421503ef21?w=600&q=80',
        title: 'Tailored Experiences',
        desc: 'No two travelers are alike. Whether you seek ancient ruins at dawn, desert silence at sunset, or reef dives at first light — we shape every detail around what moves you.',
      },
      {
        img: 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=600&q=80',
        title: 'Secure Payment',
        desc: 'Book with complete confidence. Our encrypted payment system protects every transaction, so you can focus on the adventure ahead — not the fine print.',
      },
    ].map(card => (
      <div key={card.title} className="why2-card">
        <img src={card.img} alt={card.title} />
        <div className="why2-card-body">
          <h3>{card.title}</h3>
          <p>{card.desc}</p>
        </div>
      </div>
    ))}
  </div>
</section>
{/* ── Hot Deals ─────────────────────────────── */}
<section className="hotdeals-section">
  <div className="hotdeals-header">
    <p className="hotdeals-tag">⭐ PREMIUM SELECTION</p>
    <h2>Hot Deals</h2>
    <div className="hotdeals-divider"><span /><span className="dot-divider" /><span /></div>
    <p className="hotdeals-sub">Discover Egypt's Best Premium Experiences</p>
  </div>
  <div className="hotdeals-grid">
    {[
      { img: 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=600&q=80', dest: 'Sharm El Sheikh', badge: 'Luxury', badgeColor: '#f97316', rating: 4.9, desc: 'Luxury Red Sea resort with pristine beaches', duration: '5 Days / 4 Nights', price: '18,500', priceNum: 18500 },
      { img: 'https://images.unsplash.com/photo-1559598467-f8b76c8155d0?w=600&q=80', dest: 'Hurghada', badge: 'Bestseller', badgeColor: '#ec4899', rating: 4.8, desc: 'All-inclusive beach paradise experience', duration: '4 Days / 3 Nights', price: '15,200', priceNum: 15200 },
      { img: 'https://images.unsplash.com/photo-1509316785289-025f5b846b35?w=600&q=80', dest: 'Siwa Oasis', badge: 'Summer Offer', badgeColor: '#f97316', rating: 5.0, desc: 'Mystical desert retreat and natural springs', duration: '3 Days / 2 Nights', price: '14,200', priceNum: 14200 },
      { img: 'https://images.unsplash.com/photo-1608836856752-3b8f7a48e7ce?w=600&q=80', dest: 'Luxor & Aswan', badge: 'Honeymoon', badgeColor: '#a855f7', rating: 4.9, desc: 'Ancient wonders and Nile cruise adventure', duration: '6 Days / 5 Nights', price: '22,000', priceNum: 22000 },
      { img: 'https://images.unsplash.com/photo-1539768942893-daf53e448371?w=600&q=80', dest: 'Dahab', badge: 'Family Trip', badgeColor: '#10b981', rating: 4.7, desc: 'Diving paradise with world-class coral reefs', duration: '3 Days / 2 Nights', price: '9,500', priceNum: 9500 },
      { img: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600&q=80', dest: 'North Coast', badge: 'Adventure', badgeColor: '#3b82f6', rating: 4.8, desc: 'Mediterranean elegance and beach clubs', duration: '4 Days / 3 Nights', price: '16,800', priceNum: 16800 },
      { img: 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=600&q=80', dest: 'Cairo Nile Cruise', badge: 'VIP', badgeColor: '#f59e0b', rating: 4.6, desc: 'Romantic evening on the legendary Nile', duration: '1 Day Experience', price: '8,900', priceNum: 8900 },
      { img: 'https://images.unsplash.com/photo-1559598467-f8b76c8155d0?w=600&q=80', dest: 'Ain Sokhna', badge: 'Limited Offer', badgeColor: '#ef4444', rating: 4.5, desc: 'Quick getaway resort with beach access', duration: '2 Days / 1 Night', price: '6,500', priceNum: 6500 },
      { img: 'https://images.unsplash.com/photo-1539768942893-daf53e448371?w=600&q=80', dest: 'Alexandria Escape', badge: 'New', badgeColor: '#8b5cf6', rating: 4.7, desc: 'Historic coastal city with Mediterranean charm', duration: '3 Days / 2 Nights', price: '11,200', priceNum: 11200 },
    ].map((deal, i) => (
      <div key={i} className="deal-card">
        <div className="deal-img-wrap">
          <img src={deal.img} alt={deal.dest} loading="lazy" />
          <span className="deal-badge" style={{ background: deal.badgeColor }}>{deal.badge}</span>
          <span className="deal-rating">⭐ {deal.rating}</span>
        </div>
        <div className="deal-body">
          <h3>{deal.dest}</h3>
          <p className="deal-desc">{deal.desc}</p>
          <p className="deal-duration">🕐 {deal.duration}</p>
          <div className="deal-footer">
            <div className="deal-price">
              <span>Starting from</span>
              <strong>{deal.price} <small>EGP</small></strong>
            </div>
            <button className="deal-btn">Book Now →</button>
          </div>
        </div>
      </div>
    ))}
  </div>
  <div className="hotdeals-cta">
    <button className="hotdeals-view-all">View All Deals →</button>
  </div>
</section>

{/* ── Customer Reviews ──────────────────────── */}
<section className="reviews-section">
  <div className="reviews-header">
    <p className="reviews-tag">⭐ TESTIMONIALS</p>
    <h2>Customer Reviews</h2>
    <p className="reviews-sub">Discover what travelers love most about their unforgettable experiences with WEzHA</p>
  </div>
  <div className="reviews-track-wrap">
    <div className="reviews-track">
      {[
        { name: 'Sarah Rodriguez', role: 'Verified Traveler', stars: 5, text: '"Best travel agency in Egypt! The customized Siwa package was beyond amazing. Authentic experiences, comfortable accommodations, and incredible value for money."', initials: 'SR', color: '#f97316' },
        { name: 'David Chen', role: 'Verified Traveler', stars: 5, text: '"Our Nile cruise was spectacular! Luxurious boat, gourmet meals, and fascinating historical sites. The WEzHA team handled every detail with professionalism and care."', initials: 'DC', color: '#3b82f6' },
        { name: 'Layla Mohamed', role: 'Verified Traveler', stars: 5, text: '"Outstanding service for our desert safari and beach resort trip. Flawless coordination for our group. Will definitely book another Egypt adventure."', initials: 'LM', color: '#10b981' },
        { name: 'James Wilson', role: 'Verified Traveler', stars: 5, text: '"WEzHA transformed our honeymoon into a fairy tale. The attention to detail, surprise upgrades, and seamless service made it truly magical."', initials: 'JW', color: '#a855f7' },
        { name: 'Nour Hassan', role: 'Verified Traveler', stars: 5, text: '"The Luxor & Aswan package exceeded every expectation. Private guides, stunning temples at sunrise, and a Nile felucca dinner — absolutely unforgettable."', initials: 'NH', color: '#ec4899' },
        { name: 'Marco Rossi', role: 'Verified Traveler', stars: 5, text: '"From the pyramids at dawn to diving the Red Sea at sunset — WEzHA packed our week with pure wonder. The best travel investment we ever made."', initials: 'MR', color: '#f59e0b' },
        // duplicate for infinite scroll
        { name: 'Sarah Rodriguez', role: 'Verified Traveler', stars: 5, text: '"Best travel agency in Egypt! The customized Siwa package was beyond amazing. Authentic experiences, comfortable accommodations, and incredible value for money."', initials: 'SR', color: '#f97316' },
        { name: 'David Chen', role: 'Verified Traveler', stars: 5, text: '"Our Nile cruise was spectacular! Luxurious boat, gourmet meals, and fascinating historical sites. The WEzHA team handled every detail with professionalism and care."', initials: 'DC', color: '#3b82f6' },
        { name: 'Layla Mohamed', role: 'Verified Traveler', stars: 5, text: '"Outstanding service for our desert safari and beach resort trip. Flawless coordination for our group. Will definitely book another Egypt adventure."', initials: 'LM', color: '#10b981' },
      ].map((rev, i) => (
        <div key={i} className="review-card">
          <div className="review-quote">❝❝</div>
          <div className="review-stars">{'⭐'.repeat(rev.stars)}</div>
          <p className="review-text">{rev.text}</p>
          <div className="review-author">
            <div className="review-avatar" style={{ background: rev.color }}>{rev.initials}</div>
            <div>
              <p className="review-name">{rev.name}</p>
              <p className="review-role">✅ {rev.role}</p>
            </div>
          </div>
        </div>
      ))}
    </div>
  </div>
</section>

      {/* Add more sections below as you build the page */}
     
    </div>
  );
}


export default Home;