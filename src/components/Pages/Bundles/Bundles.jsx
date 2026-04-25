import React, { useState, useMemo } from 'react';
import { useNavigate } from 'react-router-dom';
import Navbar from '../../Navbar/Navbar';
import packages from './Data/Packages';
import './Bundles.css';

const DESTINATIONS = [
  'Cairo','Alexandria','Dahab','Ain Sokhna','Sharm El Sheikh',
  'Nuweiba','Taba','Hurghada','North Coast','Port Said',
  'Luxor & Aswan','Marsa Alam','Siwa','Ras Sidr','Marsa Matrouh','Fayoum',
];

function Bundles() {
  const navigate = useNavigate();
  const [destOpen, setDestOpen]     = useState(false);
  const [sortOpen, setSortOpen]     = useState(false);
  const [selectedDest, setSelectedDest] = useState('');
  const [sort, setSort]             = useState('Best Seller');
  const [search, setSearch]         = useState('');

  const filtered = useMemo(() => {
    let list = [...packages];
    if (selectedDest) list = list.filter(p => p.dest === selectedDest);
    if (search)       list = list.filter(p =>
      p.title.toLowerCase().includes(search.toLowerCase()) ||
      p.loc.toLowerCase().includes(search.toLowerCase())
    );
    if (sort === 'Day Use')        list = list.filter(p => p.dayUse);
    else if (sort === 'Highest Price') list.sort((a, b) => b.priceNum - a.priceNum);
    else if (sort === 'Lowest Price')  list.sort((a, b) => a.priceNum - b.priceNum);
    else list.sort((a, b) => (b.bestSeller ? 1 : 0) - (a.bestSeller ? 1 : 0));
    return list;
  }, [selectedDest, sort, search]);

  const closeAll = () => { setDestOpen(false); setSortOpen(false); };

  return (
    <div className="bundles-page" onClick={closeAll}>
      <Navbar />

      <div className="bundles-hero">
        <h1>Bundle Packages</h1>
        <p>Curated travel packages for every type of traveler</p>
      </div>

      <div className="bundles-controls" onClick={e => e.stopPropagation()}>

        {/* Destination Dropdown */}
        <div className="ctrl-dropdown">
          <button className="ctrl-btn" onClick={() => { setDestOpen(!destOpen); setSortOpen(false); }}>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
            </svg>
            {selectedDest || 'Select Destination'}
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </button>
          {destOpen && (
            <div className="drop-menu">
              <p className="drop-title">Destinations</p>
              <div className="dest-grid">
                {DESTINATIONS.map(d => (
                  <span
                    key={d}
                    className={`dest-tag ${selectedDest === d ? 'sel' : ''}`}
                    onClick={() => { setSelectedDest(selectedDest === d ? '' : d); setDestOpen(false); }}
                  >{d}</span>
                ))}
              </div>
            </div>
          )}
        </div>

        {/* Search Bar */}
        <input
          className="bundles-search"
          placeholder="Search packages..."
          value={search}
          onChange={e => setSearch(e.target.value)}
        />

        {/* Sort Dropdown */}
        <div className="ctrl-dropdown" style={{ marginLeft: 'auto' }}>
          <button className="ctrl-btn" onClick={() => { setSortOpen(!sortOpen); setDestOpen(false); }}>
            Sort: {sort}
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </button>
          {sortOpen && (
            <div className="drop-menu drop-right">
              {['Best Seller','Highest Price','Lowest Price','Day Use'].map(s => (
                <div key={s} className="sort-opt" onClick={() => { setSort(s); setSortOpen(false); }}>{s}</div>
              ))}
            </div>
          )}
        </div>
      </div>

      <p className="bundles-count">Showing {filtered.length} package{filtered.length !== 1 ? 's' : ''}</p>

      <div className="bundles-grid">
        {filtered.map(p => (
          <div key={p.id} className="pkg-card">
            <div className="pkg-img">
              <img src={p.img} alt={p.title} />
              <span className="badge-type" style={{ background: p.typeColor }}>{p.type}</span>
              {p.bestSeller && <span className="badge-bs">Best Seller</span>}
              <button className="heart-btn" onClick={e => e.stopPropagation()}>♡</button>
            </div>
            <div className="pkg-body">
              <h3 className="pkg-title">{p.title}</h3>
              <div className="pkg-meta">
                <span>📅 {p.days}</span>
                <span>👥 {p.group}</span>
              </div>
              <p className="pkg-loc">📍 {p.loc}</p>
              <div className="pkg-preview">
                <strong>Itinerary Preview:</strong>
                <ul>{p.itinerary.slice(0, 3).map((item, i) => <li key={i}>{item}</li>)}</ul>
                {p.itinerary.length > 3 && <span className="more-days">+ {p.itinerary.length - 3} more days</span>}
              </div>
              <div className="pkg-tags">
                {p.includes.slice(0, 2).map(t => <span key={t} className="pkg-tag">{t}</span>)}
                {p.includes.length > 2 && <span className="pkg-tag">+{p.includes.length - 2} more</span>}
              </div>
              <div className="pkg-footer">
                <div className="pkg-price">
                  <span>Starting from</span>
                  <strong>{p.price}</strong>
                </div>
                <button className="btn-detail" onClick={() => navigate(`/bundles/${p.id}`)}>
                  View Details
                </button>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

export default Bundles;