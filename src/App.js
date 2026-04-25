import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Footer from './components/footer/footer';
import Home from './components/Pages/Home/Home';
import Login from './components/Pages/Login/Login';
import Signup from './components/Pages/Signup/Signup';
import Bundles from './components/Pages/Bundles/Bundles';
import PackageDetail from './components/Pages/Bundles/PackageDetail/PackageDetail';

function App() {
  return (
    <Router>      
      
      <Routes>
        <Route path="/" element={<Home />} />
        {/* Add more routes here as you build more pages */}
        {/* <Route path="/explore" element={<Explore />} /> */}
        {/* <Route path="/bundles" element={<Bundles />} /> */}
        <Route path="/login" element={<Login />} />
        <Route path="/signup" element={<Signup />} />
         <Route path="/bundles"       element={<Bundles />} />
  <Route path="/bundles/:id"   element={<PackageDetail />} />
      </Routes>
      <Footer />
    </Router>
  );
}

export default App;