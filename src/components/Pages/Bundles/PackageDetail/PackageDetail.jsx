import React from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import Navbar from '../../../Navbar/Navbar';
import packages from '../Data/Packages';
import './PackageDetail.css';

function PackageDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const pkg = packages.find(p => p.id === parseInt(id));

  if (!pkg) return (
    <div className="detail-page">
      <Navbar />
      <div className="detail-not-found">
        <h2>Package not found</h2>
        <button onClick={() => navigate('/bundles')}>Back to Bundles</button>
      </div>
    </div>
  );

  return (
    <div className="detail-page">
      <Navbar />

      <div className="detail-hero">
        <img src={pkg.img} alt={pkg.title} />
        <div className="detail-hero-overlay">
          <span className="detail-badge" style={{ background: pkg.typeColor }}>{pkg.type}</span>
          {pkg.bestSeller && <span className="detail-badge" style={{ background: '#1a3a6b' }}>Best Seller</span>}
          <h1>{pkg.title}</h1>
          <p>📍 {pkg.loc}</p>
        </div>
      </div>

      <div className="detail-content">
        <button className="back-btn" onClick={() => navigate('/bundles')}>← Back to Bundles</button>

        <div className="detail-grid">
          <div className="detail-main">
            <section className="detail-section">
              <h2>Overview</h2>
              <p>{pkg.description}</p>
            </section>

            <section className="detail-section">
              <h2>Full Itinerary</h2>
              <ul className="itinerary-list">
                {pkg.itinerary.map((item, i) => (
                  <li key={i}>
                    <span className="itin-dot" />
                    {item}
                  </li>
                ))}
              </ul>
            </section>

            <section className="detail-section">
              <h2>What's Included</h2>
              <div className="includes-grid">
                {pkg.includes.map(item => (
                  <div key={item} className="include-item">
                    <span className="check">✓</span> {item}
                  </div>
                ))}
              </div>
            </section>
          </div>

          <div className="detail-sidebar">
            <div className="sidebar-card">
              <div className="sidebar-price">
                <span>Starting from</span>
                <strong>{pkg.price}</strong>
              </div>
              <div className="sidebar-meta">
                <div><span>Duration</span><strong>{pkg.days}</strong></div>
                <div><span>Group</span><strong>{pkg.group}</strong></div>
                <div><span>Destination</span><strong>{pkg.loc}</strong></div>
              </div>
              <button className="btn-book">Book Now</button>
              <button className="btn-customize">Customize Package</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default PackageDetail;