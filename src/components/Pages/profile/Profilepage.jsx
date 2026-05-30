import { useState } from "react";
import "./Profilepage.css";

const packages = [
  { id: 1, title: "Sharm El Sheikh Honeymoon Escape", type: "Honeymoon", typeColor: "#e07b2a", dest: "Sharm El Sheikh", days: "5 Days / 4 Nights", price: "18,500 EGP", img: "https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&q=80" },
  { id: 2, title: "Cairo & Luxor Historical Tour", type: "Family", typeColor: "#1a3a6b", dest: "Cairo", days: "7 Days / 6 Nights", price: "24,000 EGP", img: "https://images.unsplash.com/photo-1539768942893-daf4379fb6c5?w=800&q=80" },
  { id: 6, title: "Siwa Oasis Adventure", type: "Family", typeColor: "#1a3a6b", dest: "Siwa", days: "4 Days / 3 Nights", price: "14,200 EGP", img: "https://images.unsplash.com/photo-1509316785289-025f5b846b35?w=800&q=80" },
  { id: 8, title: "Honeymoon Bliss Package", type: "Honeymoon", typeColor: "#e07b2a", dest: "Sharm El Sheikh", days: "6 Days / 5 Nights", price: "25,000 EGP", img: "https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800&q=80" },
  { id: 10, title: "White Desert Camping Safari", type: "Friends", typeColor: "#2e7d32", dest: "Fayoum", days: "3 Days / 2 Nights", price: "7,500 EGP", img: "https://images.unsplash.com/photo-1509316785289-025f5b846b35?w=800&q=80" },
  { id: 13, title: "Dahab Blue Hole Diving", type: "Friends", typeColor: "#2e7d32", dest: "Dahab", days: "4 Days / 3 Nights", price: "8,900 EGP", img: "https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&q=80" },
  { id: 19, title: "Taba Luxury Retreat", type: "Honeymoon", typeColor: "#e07b2a", dest: "Taba", days: "5 Days / 4 Nights", price: "19,500 EGP", img: "https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800&q=80" },
  { id: 21, title: "Marsa Matrouh Beach Paradise", type: "Family", typeColor: "#1a3a6b", dest: "Marsa Matrouh", days: "4 Days / 3 Nights", price: "11,500 EGP", img: "https://images.unsplash.com/photo-1559598467-f8b76c8155d0?w=800&q=80" },
];

const user = {
  name: "Ahmed Mohamed",
  email: "ahmed@example.com",
  memberSince: "March 2025",
  initials: "AM",
  points: 2450,
  nextRewardPoints: 3000,
  status: "Gold Explorer",
  referrals: 5,
  voucherDiscount: "15%",
  trips: 8,
  governorates: 12,
};

const myJourneys = [packages[0], packages[1], packages[2]];
const initialWishlist = [packages[3], packages[4], packages[5], packages[6], packages[7]];

const StarRating = ({ rating = 4 }) => (
  <div className="star-row">
    {[1, 2, 3, 4, 5].map((s) => (
      <svg key={s} width="12" height="12" viewBox="0 0 24 24" fill={s <= rating ? "#e07b2a" : "#ddd"}>
        <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26" />
      </svg>
    ))}
  </div>
);

const BookmarkIcon = ({ filled }) => (
  <svg width="16" height="16" viewBox="0 0 24 24" fill={filled ? "#1a3a6b" : "none"} stroke="#1a3a6b" strokeWidth="2">
    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" />
  </svg>
);

export default function ProfilePage() {
  const [activeTab, setActiveTab] = useState("journeys");
  const [wishlist, setWishlist] = useState(initialWishlist);

  const progressPct = Math.min((user.points / user.nextRewardPoints) * 100, 100);
  const removeFromWishlist = (id) => setWishlist((w) => w.filter((p) => p.id !== id));

  return (
    <div style={{ fontFamily: "'Georgia', serif", background: "#f5f0eb", minHeight: "100vh", color: "#1a1a1a" }}>
      {/* Hero Banner */}
      <div className="hero-banner">
        <div className="hero-left">
          <div className="avatar">{user.initials}</div>
          <div className="hero-info">
            <div className="hero-name">{user.name}</div>
            <div className="hero-meta">{user.email}</div>
            <div className="hero-badge">
              <span>🏅</span>
              {user.status} · {user.trips} Trips · {user.points.toLocaleString()} Points · {user.governorates} Governorates
            </div>
            <div className="hero-stats">
              <div className="hero-stat">
                <div className="hero-stat-num">{user.trips}</div>
                <div className="hero-stat-lbl">Trips</div>
              </div>
              <div className="hero-stat">
                <div className="hero-stat-num">{user.referrals}</div>
                <div className="hero-stat-lbl">Referrals</div>
              </div>
              <div className="hero-stat">
                <div className="hero-stat-num">{user.points.toLocaleString()}</div>
                <div className="hero-stat-lbl">Points</div>
              </div>
            </div>
          </div>
        </div>
        <button className="signout-btn">Sign Out</button>
      </div>

      <div className="profile-wrap">
        {/* Rewards Card */}
        <div className="rewards-card">
          <div className="rewards-header">
            <div className="rewards-title">
              <span>🎁</span> Rewards Program
            </div>
            <div className="points-pill">{user.points.toLocaleString()} Points</div>
          </div>

          <div className="progress-label">
            <span>Progress to next reward</span>
            <span>{(user.nextRewardPoints - user.points).toLocaleString()} points to go</span>
          </div>
          <div className="progress-track">
            <div className="progress-fill" style={{ width: `${progressPct}%` }} />
          </div>

          <div className="rewards-stats">
            <div className="rewards-stat">
              <div className="rewards-stat-icon">🏅</div>
              <div className="rewards-stat-val">Gold Explorer</div>
              <div className="rewards-stat-lbl">Current Status</div>
            </div>
            <div className="rewards-stat">
              <div className="rewards-stat-icon">👥</div>
              <div className="rewards-stat-val">{user.referrals} Friends</div>
              <div className="rewards-stat-lbl">Referred</div>
            </div>
            <div className="rewards-stat">
              <div className="rewards-stat-icon">🎟️</div>
              <div className="rewards-stat-val">{user.voucherDiscount} Off</div>
              <div className="rewards-stat-lbl">Next Voucher</div>
            </div>
          </div>

          {/* Voucher Strip */}
          <div className="voucher-strip">
            <div className="voucher-info">
              <div className="voucher-label">Active Voucher</div>
              <div className="voucher-val">{user.voucherDiscount} OFF</div>
              <div className="voucher-sub">Valid on your next booking · 1 active</div>
            </div>
            <div className="voucher-code">
              <div className="voucher-code-label">Promo Code</div>
              <div className="voucher-code-val">WEJHA15</div>
            </div>
          </div>
        </div>

        {/* Tabs */}
        <div className="tab-row">
          <button
            className={`tab-btn ${activeTab === "journeys" ? "active" : ""}`}
            onClick={() => setActiveTab("journeys")}
          >
            My Journeys
          </button>
          <button
            className={`tab-btn ${activeTab === "wishlist" ? "active" : ""}`}
            onClick={() => setActiveTab("wishlist")}
          >
            Wish-List {wishlist.length > 0 && `(${wishlist.length})`}
          </button>
        </div>

        {/* My Journeys */}
        {activeTab === "journeys" && (
          <div>
            <p className="section-meta">Past trips · {myJourneys.length} journeys completed</p>
            <div className="cards-grid">
              {myJourneys.map((pkg) => (
                <div key={pkg.id} className="journey-card">
                  <div className="card-img-wrap">
                    <img
                      src={pkg.img}
                      alt={pkg.title}
                      className="card-img"
                      onError={(e) => {
                        e.target.src = "https://images.unsplash.com/photo-1539768942893-daf4379fb6c5?w=800&q=80";
                      }}
                    />
                  </div>
                  <div className="card-body">
                    <div
                      className="card-type-tag"
                      style={{ background: pkg.typeColor + "18", color: pkg.typeColor }}
                    >
                      {pkg.type}
                    </div>
                    <div className="card-title">{pkg.title}</div>
                    <div className="card-meta">📍 {pkg.dest} &nbsp;·&nbsp; 🗓 {pkg.days}</div>
                    <div className="card-footer">
                      <div className="card-price">{pkg.price}</div>
                      <StarRating rating={4} />
                    </div>
                    <div className="card-actions">
                      <button className="btn-outline">Rate & Review</button>
                      <button className="btn-solid">Book Again</button>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Wish-List */}
        {activeTab === "wishlist" && (
          <div>
            <p className="section-meta">Saved destinations · {wishlist.length} packages saved</p>
            {wishlist.length === 0 ? (
              <div className="empty-state">
                <div className="empty-state-icon">🗺️</div>
                <div className="empty-state-txt">
                  Your wish-list is empty. Start exploring and save your dream destinations!
                </div>
              </div>
            ) : (
              <div className="cards-grid">
                {wishlist.map((pkg) => (
                  <div key={pkg.id} className="journey-card">
                    <div className="card-img-wrap">
                      <img
                        src={pkg.img}
                        alt={pkg.title}
                        className="card-img"
                        onError={(e) => {
                          e.target.src = "https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800&q=80";
                        }}
                      />
                      <div className="card-bookmark-badge">
                        <BookmarkIcon filled />
                      </div>
                    </div>
                    <div className="card-body">
                      <div
                        className="card-type-tag"
                        style={{ background: pkg.typeColor + "18", color: pkg.typeColor }}
                      >
                        {pkg.type}
                      </div>
                      <div className="card-title">{pkg.title}</div>
                      <div className="card-meta">📍 {pkg.dest} &nbsp;·&nbsp; 🗓 {pkg.days}</div>
                      <div className="card-footer">
                        <div className="card-price">{pkg.price}</div>
                      </div>
                      <div className="wish-action-row">
                        <button className="btn-solid" style={{ flex: 2 }}>Book Now</button>
                        <button className="btn-remove" onClick={() => removeFromWishlist(pkg.id)}>
                          Remove
                        </button>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  );
}