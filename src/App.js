import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Footer from './components/footer/footer';
import Home from './components/Pages/Home/Home';
import Login from './components/Pages/Login/Login';
import Signup from './components/Pages/Signup/Signup';
import Bundles from './components/Pages/Bundles/Bundles';
import PackageDetail from './components/Pages/Bundles/PackageDetail/PackageDetail';
import CustomPackage from './components/Pages/CustomPackage/CustomPackage';
import Cart from './components/Pages/Cart/Cart';
import Checkout from './components/Pages/Checkout/Checkout';
import Contact from './components/Pages/Contact/Contact';
import ProfilePage from './components/Pages/profile/Profilepage';

<Route path="/custom-package" element={<CustomPackage />} />

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
        <Route path="/profile" element={<ProfilePage/>}/>
         <Route path="/bundles"       element={<Bundles />} />
  <Route path="/bundles/:id"   element={<PackageDetail />} />
  <Route path="/custom-package" element={<CustomPackage />} />
  <Route path="/cart" element={<Cart />} />
<Route path="/checkout" element={<Checkout />} />
 <Route path="/contact" element={<Contact />} />
      </Routes>
      <Footer />
    </Router>
  );
}

export default App;