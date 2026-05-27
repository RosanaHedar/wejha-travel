import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import Navbar from '../../Navbar/Navbar';
import './CustomPackage.css';

const activities = {
  Hotels: [
    { id: 'h1', name: 'Luxury Beach Resort', price: 3500, img: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=400&q=70' },
    { id: 'h2', name: 'Desert Eco Lodge', price: 1800, img: 'https://images.unsplash.com/photo-1509316785289-025f5b846b35?w=400&q=70' },
    { id: 'h3', name: 'Nile View Hotel', price: 2200, img: 'https://images.unsplash.com/photo-1539768942893-daf53e448371?w=400&q=70' },
  ],
  Restaurants: [
    { id: 'r1', name: 'Nile View Restaurant', price: 450, img: 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=400&q=70' },
    { id: 'r2', name: 'Egyptian Cuisine House', price: 350, img: 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=400&q=70' },
    { id: 'r3', name: 'Seafood Delights', price: 550, img: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=400&q=70' },
  ],
  Cruise: [
    { id: 'c1', name: 'Luxury Nile Cruise (3 Days)', price: 4500, img: 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=400&q=70' },
    { id: 'c2', name: 'Sunset Dinner Cruise', price: 850, img: 'https://images.unsplash.com/photo-1559598467-f8b76c8155d0?w=400&q=70' },
  ],
  Activities: [
    { id: 'a1', name: 'Pyramids Tour', price: 600, img: 'https://images.unsplash.com/photo-1608836856752-3b8f7a48e7ce?w=400&q=70' },
    { id: 'a2', name: 'Hot Air Balloon Luxor', price: 1200, img: 'https://images.unsplash.com/photo-1539768942893-daf53e448371?w=400&q=70' },
    { id: 'a3', name: 'Desert Quad Biking', price: 800, img: 'https://images.unsplash.com/photo-1509316785289-025f5b846b35?w=400&q=70' },
  ],
  Cinema: [
    { id: 'ci1', name: 'Sound & Light Show — Karnak', price: 300, img: 'https://images.unsplash.com/photo-1608836856752-3b8f7a48e7ce?w=400&q=70' },
    { id: 'ci2', name: 'IMAX Egyptian Museum Film', price: 200, img: 'https://images.unsplash.com/photo-1539768942893-daf53e448371?w=400&q=70' },
  ],
};

function CustomPackage() {
  const navigate = useNavigate();
  const [step, setStep] = useState(1);
  const [days, setDays] = useState(3);
  const [budgetMin, setBudgetMin] = useState(2000);
  const [budgetMax, setBudgetMax] = useState(8000);
  const [selected, setSelected] = useState({});
  const [submitted, setSubmitted] = useState(false);

  const totalSelected = Object.values(selected).reduce((sum, item) => sum + item.price, 0);

  const toggleItem = (item) => {
    setSelected(prev => {
      const next = { ...prev };
      if (next[item.id]) {
        delete next[item.id];
      } else {
        next[item.id] = item;
      }
      return next;
    });
  };

  const isWithinBudget = (price) => price <= budgetMax;

  if (submitted) {
    return (
      <div className="cp-page">
        <Navbar />
        <div className="cp-success">
          <div className="cp-success-icon">✓</div>
          <h2>Your Package is Ready!</h2>
          <p>We'll contact you within 24 hours to confirm your custom Egyptian adventure.</p>
          <div className="cp-summary-box">
            <h3>Package Summary</h3>
            <div className="cp-summary-row">
              <span>Duration</span>
              <strong>{days} Days</strong>
            </div>
            <div className="cp-summary-row">
              <span>Budget Range</span>
              <strong>{budgetMin.toLocaleString()} – {budgetMax.toLocaleString()} EGP</strong>
            </div>
            <div className="cp-summary-row">
              <span>Selected Items</span>
              <strong>{Object.values(selected).length} items</strong>
            </div>
            <div className="cp-summary-row total">
              <span>Estimated Total</span>
              <strong>{totalSelected.toLocaleString()} EGP</strong>
            </div>
          </div>
          <div className="cp-success-btns">
            <button className="cp-btn-back" onClick={() => { setSubmitted(false); setStep(3); }}>
              ← Back
            </button>
            <button className="cp-btn-primary" onClick={() => navigate('/cart')}>
              Go to Cart →
            </button>
            <button className="cp-btn-back" onClick={() => {
              setStep(1);
              setSubmitted(false);
              setSelected({});
              setBudgetMin(2000);
              setBudgetMax(8000);
              setDays(3);
            }}>
              Build Another
            </button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="cp-page">
      <Navbar />

      <div className="cp-header">
        <h1>Custom Your Package</h1>
        <p>Create your perfect Egyptian adventure</p>
      </div>

      {/* Stepper */}
      <div className="cp-stepper">
        {[1, 2, 3].map((s) => (
          <React.Fragment key={s}>
            <div className={`cp-step ${step > s ? 'done' : ''} ${step === s ? 'active' : ''}`}>
              {step > s ? '✓' : s}
            </div>
            {s < 3 && <div className={`cp-step-line ${step > s ? 'done' : ''}`} />}
          </React.Fragment>
        ))}
      </div>

      <div className="cp-card">

        {/* Step 1 — Days */}
        {step === 1 && (
          <div className="cp-step-content">
            <h2>How many days?</h2>
            <div className="cp-counter">
              <button className="cp-counter-btn" onClick={() => setDays(d => Math.max(1, d - 1))}>−</button>
              <span className="cp-counter-val">{days}</span>
              <button className="cp-counter-btn" onClick={() => setDays(d => Math.min(30, d + 1))}>+</button>
            </div>
            <p className="cp-counter-label">{days} day{days > 1 ? 's' : ''} of unforgettable experiences</p>
          </div>
        )}

        {/* Step 2 — Budget */}
        {step === 2 && (
          <div className="cp-step-content">
            <h2>Select your budget range</h2>
            <div className="cp-budget-sliders">
              <div className="cp-slider-wrap">
                <label>Minimum</label>
                <input
                  type="range" min="500" max="50000" step="500"
                  value={budgetMin}
                  onChange={e => setBudgetMin(Math.min(Number(e.target.value), budgetMax - 500))}
                />
              </div>
              <div className="cp-slider-wrap">
                <label>Maximum</label>
                <input
                  type="range" min="500" max="50000" step="500"
                  value={budgetMax}
                  onChange={e => setBudgetMax(Math.max(Number(e.target.value), budgetMin + 500))}
                />
              </div>
            </div>
            <div className="cp-budget-display">
              <div className="cp-budget-val">
                <span>Minimum</span>
                <strong style={{ color: '#1a3a6b' }}>{budgetMin.toLocaleString()} EGP</strong>
              </div>
              <div className="cp-budget-divider">—</div>
              <div className="cp-budget-val">
                <span>Maximum</span>
                <strong style={{ color: '#e07b2a' }}>{budgetMax.toLocaleString()} EGP</strong>
              </div>
            </div>
            <div className="cp-budget-hint">
              Budget Range: <strong>{budgetMin.toLocaleString()} – {budgetMax.toLocaleString()} EGP</strong>
              <br />
              Recommended budget for {days} days: <strong>{(days * 2000).toLocaleString()} EGP</strong>
            </div>
          </div>
        )}

        {/* Step 3 — Activities */}
        {step === 3 && (
          <div className="cp-step-content">
            <h2>Choose your activities</h2>
            <div className="cp-budget-banner">
              Your budget range: <strong>{budgetMin.toLocaleString()} – <span style={{ color: '#e07b2a' }}>{budgetMax.toLocaleString()} EGP</span></strong>
              <br />
              <small>Activities shown are within your budget</small>
            </div>

            {Object.entries(activities).map(([category, items]) => {
              const inBudget = items.filter(i => isWithinBudget(i.price));
              if (inBudget.length === 0) return null;
              return (
                <div key={category} className="cp-category">
                  <h3>{category}</h3>
                  <div className="cp-activity-grid">
                    {inBudget.map(item => (
                      <div
                        key={item.id}
                        className={`cp-activity-card ${selected[item.id] ? 'selected' : ''}`}
                        onClick={() => toggleItem(item)}
                      >
                        <img src={item.img} alt={item.name} />
                        {selected[item.id] && <div className="cp-selected-badge">✓</div>}
                        <div className="cp-activity-info">
                          <p className="cp-activity-name">{item.name}</p>
                          <p className="cp-activity-price">{item.price.toLocaleString()} EGP</p>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              );
            })}

            {Object.values(selected).length > 0 && (
              <div className="cp-total-bar">
                <span>{Object.values(selected).length} items selected</span>
                <strong>Total: {totalSelected.toLocaleString()} EGP</strong>
              </div>
            )}
          </div>
        )}
      </div>

      {/* Navigation buttons */}
      <div className="cp-nav-btns">
        <button
          className="cp-btn-back"
          onClick={() => setStep(s => s - 1)}
          disabled={step === 1}
        >
          ← Back
        </button>
        {step < 3 ? (
          <button className="cp-btn-primary" onClick={() => setStep(s => s + 1)}>
            Next →
          </button>
        ) : (
          <button className="cp-btn-primary" onClick={() => setSubmitted(true)}>
            Confirm Package ✓
          </button>
        )}
      </div>
    </div>
  );
}

export default CustomPackage;