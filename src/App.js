import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Navbar from './components/Navbar/Navbar';
import Footer from './components/footer/footer';
import Home from './components/Pages/Home/Home';

function App() {
  return (
    <Router>
      <Navbar />
      <Routes>
        <Route path="/" element={<Home />} />
        {/* Add more routes here as you build more pages */}
        {/* <Route path="/explore" element={<Explore />} /> */}
        {/* <Route path="/bundles" element={<Bundles />} /> */}
      </Routes>
      <Footer />
    </Router>
  );
}

export default App;