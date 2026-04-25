import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Footer from './components/footer/footer';
import Home from './components/Pages/Home/Home';
import Login from './components/Pages/Login/Login';

function App() {
  return (
    <Router>      
      
      <Routes>
        <Route path="/" element={<Home />} />
        {/* Add more routes here as you build more pages */}
        {/* <Route path="/explore" element={<Explore />} /> */}
        {/* <Route path="/bundles" element={<Bundles />} /> */}
        <Route path="/login" element={<Login />} />
      </Routes>
      <Footer />
    </Router>
  );
}

export default App;