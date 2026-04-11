import React, { useState, useEffect } from 'react';
import './Home.css';
import aswanimg from '../../assests/images/Aswan-City-Egypt-Egypt-Tours-Portal.jpg';
import aswanimg2 from '../../assests/images/aswan1.jpg'
import aswanimg3 from '../../assests/images/the-best-temples-in-luxor-and-aswan-egypt-header.jpg'
import aswanimg4 from '../../assests/images/1676131.jpg'
import aswanimg5 from '../../assests/images/Oasi-di-Siwa-Egitto-Africa-SOCIAL-1.jpg'
import aswanimg6 from '../../assests/images/9-Best-Things-to-Do-in-Alexandria-Egypt.webp'
// Replace these with your actual images inside src/assets/images/
// Example: import aswanImg from '../../assets/images/aswan.jpg';
// For now we use placeholder colors + labels so the slider still works

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
          <button className="hero-btn">Custom your Package</button>
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

      {/* Add more sections below as you build the page */}
    </div>
  );
}

export default Home;