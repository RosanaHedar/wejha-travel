import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import Navbar from '../../Navbar/Navbar';
import './Cart.css';

const initialItems = [
  { id: 'h1', name: 'Pyramids View Resort', category: 'Hotels', price: 3200, qty: 1, img: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=200&q=70' },
  { id: 'r1', name: 'Nile View Restaurant', category: 'Restaurants', price: 450, qty: 1, img: 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=200&q=70' },
  { id: 'a1', name: 'Egyptian Museum', category: 'Attractions', price: 850, qty: 1, img: 'https://images.unsplash.com/photo-1608836856752-3b8f7a48e7ce?w=200&q=70' },
  { id: 'ci1', name: 'Open Air Cinema', category: 'Cinema', price: 150, qty: 1, img: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=200&q=70' },
  { id: 'e1', name: 'Belly Dance Show', category: 'Entertainment', price: 650, qty: 1, img: 'https://images.unsplash.com/photo-1518834107812-67b0b7c58434?w=200&q=70' },
];

function Cart() {
  const navigate = useNavigate();
  const [items, setItems] = useState(initialItems);
  const [toast, setToast] = useState(true);

  const updateQty = (id, delta) => {
    setItems(prev => prev.map(item =>
      item.id === id
        ? { ...item, qty: Math.max(1, item.qty + delta) }
        : item
    ));
  };

  const removeItem = (id) => {
    setItems(prev => prev.filter(item => item.id !== id));
  };

  const subtotal = items.reduce((sum, i) => sum + i.price * i.qty, 0);
  const serviceFee = Math.round(subtotal * 0.05);
  const total = subtotal + serviceFee;

  if (items.length === 0) {
    return (
      <div className="cart-page">
        <Navbar />
        <div className="cart-empty">
          <div className="cart-empty-icon">🛒</div>
          <h2>Your cart is empty</h2>
          <p>Add some packages or activities to get started</p>
          <Link to="/bundles" className="cart-btn-primary">Browse Packages</Link>
        </div>
      </div>
    );
  }

  return (
    <div className="cart-page">
      <Navbar />

      {toast && (
        <div className="cart-toast">
          <span className="cart-toast-icon">✓</span>
          Package customized! Review your cart.
          <button onClick={() => setToast(false)}>×</button>
        </div>
      )}

      <div className="cart-content">
        <div className="cart-left">
          <h1>Your Cart</h1>
          <p className="cart-count">{items.length} items in your cart</p>

          <div className="cart-items">
            {items.map(item => (
              <div key={item.id} className="cart-item">
                <img src={item.img} alt={item.name} />
                <div className="cart-item-info">
                  <h3>{item.name}</h3>
                  <span className="cart-item-cat">{item.category}</span>
                  <div className="cart-item-qty">
                    <button onClick={() => updateQty(item.id, -1)}>−</button>
                    <span>{item.qty}</span>
                    <button onClick={() => updateQty(item.id, +1)}>+</button>
                  </div>
                </div>
                <div className="cart-item-right">
                  <button className="cart-remove" onClick={() => removeItem(item.id)}>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#e53935" strokeWidth="2">
                      <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                      <path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
                    </svg>
                  </button>
                  <p className="cart-item-price">{(item.price * item.qty).toLocaleString()} EGP</p>
                </div>
              </div>
            ))}
          </div>
        </div>

        <div className="cart-right">
          <div className="cart-summary">
            <h2>Order Summary</h2>
            <div className="cart-summary-row">
              <span>Subtotal</span>
              <span>{subtotal.toLocaleString()} EGP</span>
            </div>
            <div className="cart-summary-row">
              <span>Service Fee</span>
              <span>{serviceFee.toLocaleString()} EGP</span>
            </div>
            <div className="cart-summary-divider" />
            <div className="cart-summary-row total">
              <strong>Total</strong>
              <strong className="cart-total-price">{total.toLocaleString()} EGP</strong>
            </div>
            <button
              className="cart-btn-primary"
              onClick={() => navigate('/checkout', { state: { items, subtotal, serviceFee, total } })}
            >
              Proceed to Checkout
            </button>
            <Link to="/bundles" className="cart-continue">Continue Shopping</Link>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Cart;