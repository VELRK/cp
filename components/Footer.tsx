'use client';

import React, { useEffect, useState } from 'react';
import Link from 'next/link';
import { Home, Facebook, Twitter, Instagram, Linkedin, Mail } from 'lucide-react';
import api from '../lib/api';

interface City {
  id: number;
  name: string;
  state: string;
}

const Footer: React.FC = () => {
  const [cities, setCities] = useState<City[]>([]);

  useEffect(() => {
    api.get('/api/nb/cities')
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
    <footer className="nb-footer-site">
      <div className="container">
        <div className="row g-4">
          <div className="col-lg-5">
            <div className="nb-footer-brand mb-2 d-flex align-items-center">
              <Home className="me-2 text-warning" size={24} fill="var(--nb-accent)" />
              <span>Coimbatore Properties</span>
            </div>
            <p className="small text-white-50 mb-4">
              Find rental homes, plots, and properties for sale in Coimbatore and neighboring regions. Owner-verified listings, zero brokerage.
            </p>
            <div className="d-flex gap-3 mb-3">
              <a href="#" className="text-white-50 fs-5"><Facebook size={18} /></a>
              <a href="#" className="text-white-50 fs-5"><Twitter size={18} /></a>
              <a href="#" className="text-white-50 fs-5"><Instagram size={18} /></a>
              <a href="#" className="text-white-50 fs-5"><Linkedin size={18} /></a>
            </div>
            <p className="small text-white-50 mb-0 d-flex align-items-center gap-1">
              <Mail size={14} />
              <a href="mailto:support@coimbatoreproperties.com" className="text-white-50">support@coimbatoreproperties.com</a>
            </p>
          </div>

          <div className="col-6 col-lg-3">
            <div className="fw-semibold text-white mb-2 small text-uppercase">Explore</div>
            <ul className="list-unstyled small mb-0">
              <li className="mb-2"><Link href="/">Home</Link></li>
              <li className="mb-2"><Link href="/search">Search properties</Link></li>
              <li className="mb-2"><Link href="/about">About Us</Link></li>
              <li className="mb-2"><Link href="/user/live-updates">Live Updates</Link></li>
            </ul>
          </div>

          <div className="col-6 col-lg-4">
            <div className="fw-semibold text-white mb-2 small text-uppercase">Popular Cities / Areas</div>
            <ul className="list-unstyled small mb-0 row row-cols-2 g-0">
              {cities.slice(0, 8).map((city) => (
                <li key={city.id} className="col mb-2">
                  <Link href={`/search?city_id=${city.id}`}>
                    {city.name}
                  </Link>
                </li>
              ))}
              {cities.length === 0 && (
                <>
                  <li className="col mb-2"><Link href="/search?q=Gandhipuram">Gandhipuram</Link></li>
                  <li className="col mb-2"><Link href="/search?q=Peelamedu">Peelamedu</Link></li>
                  <li className="col mb-2"><Link href="/search?q=RS+Puram">RS Puram</Link></li>
                  <li className="col mb-2"><Link href="/search?q=Saravanampatti">Saravanampatti</Link></li>
                  <li className="col mb-2"><Link href="/search?q=Singanallur">Singanallur</Link></li>
                  <li className="col mb-2"><Link href="/search?q=Kovaipudur">Kovaipudur</Link></li>
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
            <a href="#" className="text-white-50 text-decoration-none me-3">Privacy Policy</a>
            <a href="#" className="text-white-50 text-decoration-none">Terms of Use</a>
          </span>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
