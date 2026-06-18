'use client';

import React from 'react';
import Link from 'next/link';
import { Newspaper, User, Calendar, ArrowLeft, ArrowRight } from 'lucide-react';

export default function NewsIndex() {
  const newsList = [
    {
      id: 'tn-lifts-5-year-ban-hill-area',
      name: 'TN lifts 5-year ban on Hill Area projects: What it means for investors',
      author: 'Anannya Purna',
      date: 'Apr 08, 2026',
      short_notes: 'The Tamil Nadu government has conditionally lifted the ban on real estate development in specific hill area zones.',
      category: 'Policy & Regulations',
      image: 'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=500&q=80'
    },
    {
      id: 'tn-affordable-housing-policy',
      name: 'TN cabinet approves new Affordable Housing Policy 2026',
      author: 'Anannya Purna',
      date: 'Mar 12, 2026',
      short_notes: 'The new policy aims to boost the development of affordable housing units across major tier-2 cities including Coimbatore.',
      category: 'Government Policies',
      image: 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=500&q=80'
    },
    {
      id: 'coimbatore-it-corridor-registry',
      name: 'Registry approved for 9,000 flats in Coimbatore IT Corridor',
      author: 'Nupur Tolia',
      date: 'Jan 06, 2026',
      short_notes: 'Relief for thousands of homebuyers as the state administration clears the registry for stalled apartments in Saravanampatti.',
      category: 'Real Estate Updates',
      image: 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=500&q=80'
    },
    {
      id: 'title-based-property-registry',
      name: 'What is title-based property registry in Tamil Nadu?',
      author: 'Nupur Tolia',
      date: 'Dec 17, 2025',
      short_notes: 'An in-depth explanation of the new title-based property registration system being rolled out in the state.',
      category: 'Tax & Legal',
      image: 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=500&q=80'
    }
  ];

  return (
    <div style={{ background: '#f8fafc', minHeight: '100vh', padding: '3rem 0' }}>
      <div className="container">
        
        {/* Navigation Breadcrumb */}
        <div className="mb-4">
          <Link href="/" className="btn btn-link text-decoration-none text-secondary p-0 d-flex align-items-center gap-2 fw-semibold">
            <ArrowLeft size={16} />
            <span>Back to Home</span>
          </Link>
        </div>

        {/* Page Title & Intro */}
        <div className="mb-5 text-center">
          <div className="d-inline-flex align-items-center justify-content-center text-white p-3 rounded-circle mb-3" style={{ backgroundColor: '#2b8fd9' }}>
            <Newspaper size={32} />
          </div>
          <h1 className="display-5 fw-extrabold text-dark mb-2" style={{ color: '#0b2c56' }}>Real Estate News</h1>
          <p className="text-secondary mx-auto" style={{ maxWidth: '600px', fontSize: '1.05rem' }}>
            Latest updates, policies, and news affecting the real estate landscape in Coimbatore and Tamil Nadu.
          </p>
        </div>

        <div className="row g-4">
          {newsList.map((news) => (
            <div key={news.id} className="col-lg-6 col-12">
              <div className="card border-0 shadow-sm rounded-4 overflow-hidden h-100 d-flex flex-row" style={{ transition: 'transform 0.2s ease', backgroundColor: '#fff' }}>
                <div style={{ width: '40%', minHeight: '200px', overflow: 'hidden', position: 'relative' }}>
                  <img src={news.image} alt={news.name} className="w-100 h-100 object-fit-cover" />
                  <span className="position-absolute top-0 start-0 m-2 badge rounded-pill bg-dark bg-opacity-75 small text-uppercase" style={{ fontSize: '0.65rem', letterSpacing: '0.5px' }}>
                    {news.category}
                  </span>
                </div>
                <div className="card-body p-4 d-flex flex-column flex-grow-1" style={{ width: '60%' }}>
                  <div className="d-flex align-items-center gap-3 mb-2 text-muted small">
                    <span className="d-flex align-items-center gap-1">
                      <User size={13} />
                      <span>{news.author}</span>
                    </span>
                    <span className="d-flex align-items-center gap-1">
                      <Calendar size={13} />
                      <span>{news.date}</span>
                    </span>
                  </div>
                  
                  <h3 className="h6 fw-bold text-dark mb-2 line-clamp-2" style={{ lineHeight: '1.4' }}>
                    {news.name}
                  </h3>
                  
                  <p className="text-secondary small mb-3 line-clamp-2" style={{ lineHeight: '1.4' }}>
                    {news.short_notes}
                  </p>
                  
                  <Link href={`/news/${news.id}`} className="mt-auto fw-bold d-inline-flex align-items-center gap-1.5 align-self-start text-decoration-none" style={{ color: '#0076de', fontSize: '0.9rem' }}>
                    <span>Read Full Story</span>
                    <ArrowRight size={14} />
                  </Link>
                </div>
              </div>
            </div>
          ))}
        </div>

      </div>
    </div>
  );
}
