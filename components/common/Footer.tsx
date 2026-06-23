'use client';

import React, { useEffect, useState } from 'react';
import Link from 'next/link';
import { Home, Facebook, Twitter, Instagram, Linkedin, Mail } from 'lucide-react';
import { getCities } from '@/lib/frontendApi';

interface City {
  id: number;
  name: string;
  state: string;
}

const Footer: React.FC = () => {
  const [cities, setCities] = useState<City[]>([]);

  useEffect(() => {
    getCities()
      .then((res) => {
        if (res.data?.success && Array.isArray(res.data.cities)) {
          setCities(res.data.cities);
        }
      })
      .catch((e) => {
        console.warn('Could not fetch cities for footer:', e);
      });
  }, []);

  return (
    <footer className="nb-footer-site py-5" style={{ background: 'var(--nb-footer-bg)' }}>
      <div className="container">
        <div className="row g-4">
          <div className="col-lg-5">
            <div className="nb-footer-brand mb-3 d-flex align-items-center">
              <Home className="me-2 text-warning" size={24} fill="var(--nb-accent)" />
              <span className="fw-bold text-white fs-4">Coimbatore Properties</span>
            </div>
            <p className="small text-white-50 mb-4" style={{ lineHeight: '1.7' }}>
              Find rental homes, plots, and properties for sale in Coimbatore and neighboring regions. Owner-verified listings, zero brokerage.
            </p>
            <div className="d-flex gap-3 mb-4">
              <a href="#" className="text-white-50 fs-5 transition-transform hover-scale"><Facebook size={18} /></a>
              <a href="#" className="text-white-50 fs-5 transition-transform hover-scale"><Twitter size={18} /></a>
              <a href="#" className="text-white-50 fs-5 transition-transform hover-scale"><Instagram size={18} /></a>
              <a href="#" className="text-white-50 fs-5 transition-transform hover-scale"><Linkedin size={18} /></a>
            </div>
            <p className="small text-white-50 mb-0 d-flex align-items-center gap-2">
              <Mail size={14} className="text-warning" />
              <a href="mailto:support@coimbatoreproperties.com" className="text-white-50 text-decoration-none">support@coimbatoreproperties.com</a>
            </p>
          </div>

          <div className="col-6 col-lg-3">
            <div className="fw-semibold text-white mb-3 small text-uppercase tracking-wider">Explore</div>
            <ul className="list-unstyled small mb-0">
              <li className="mb-2"><Link href="/" className="text-white-50 text-decoration-none hover-white">Home</Link></li>
              <li className="mb-2"><Link href="/search" className="text-white-50 text-decoration-none hover-white">Search properties</Link></li>
              <li className="mb-2"><Link href="/about" className="text-white-50 text-decoration-none hover-white">About Us</Link></li>
              {/* <li className="mb-2"><Link href="/user/live-updates" className="text-white-50 text-decoration-none hover-white">Live Updates</Link></li> */}
            </ul>
          </div>

          <div className="col-6 col-lg-4">
            <div className="fw-semibold text-white mb-3 small text-uppercase tracking-wider">Popular Cities / Areas</div>
            <ul className="list-unstyled small mb-0 row row-cols-2 g-0">
              {cities.slice(0, 8).map((city) => (
                <li key={city.id} className="col mb-2">
                  <Link href={`/search?city_id=${city.id}`} className="text-white-50 text-decoration-none hover-white">
                    {city.name}
                  </Link>
                </li>
              ))}
              {cities.length === 0 && (
                <>
                  <li className="col mb-2"><Link href="/search?q=Gandhipuram" className="text-white-50 text-decoration-none hover-white">Gandhipuram</Link></li>
                  <li className="col mb-2"><Link href="/search?q=Peelamedu" className="text-white-50 text-decoration-none hover-white">Peelamedu</Link></li>
                  <li className="col mb-2"><Link href="/search?q=RS+Puram" className="text-white-50 text-decoration-none hover-white">RS Puram</Link></li>
                  <li className="col mb-2"><Link href="/search?q=Saravanampatti" className="text-white-50 text-decoration-none hover-white">Saravanampatti</Link></li>
                  <li className="col mb-2"><Link href="/search?q=Singanallur" className="text-white-50 text-decoration-none hover-white">Singanallur</Link></li>
                  <li className="col mb-2"><Link href="/search?q=Kovaipudur" className="text-white-50 text-decoration-none hover-white">Kovaipudur</Link></li>
                </>
              )}
            </ul>
          </div>
        </div>

        <hr className="border-secondary my-4 opacity-25" />

        <div className="nb-footer-legal d-flex flex-column flex-md-row align-items-center justify-content-between gap-2 gap-md-3 small text-white-50 w-100">
          <span className="text-center text-md-start mb-0">
            &copy; {new Date().getFullYear()} Coimbatore Properties NoBroker. All rights reserved.
          </span>
          <span className="text-white-50 text-center text-md-end flex-shrink-0">
            <a href="#" className="text-white-50 text-decoration-none me-3 hover-white">Privacy Policy</a>
            <a href="#" className="text-white-50 text-decoration-none hover-white">Terms of Use</a>
          </span>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
