'use client';

import React from 'react';
import Link from 'next/link';
import { BookOpen, User, Calendar, ArrowLeft, ArrowRight } from 'lucide-react';

export default function ArticlesIndex() {
  const articles = [
    {
      id: 'investing-residential-plots-saravanampatti',
      name: 'Investing in residential plots in Saravanampatti? Consider key growth factors.',
      author: 'Sohina Sharma',
      date: 'Jun 17, 2026',
      short_notes: 'Saravanampatti has emerged as a major IT hub. Investing here promises great returns.',
      category: 'Investment',
      image: 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=500&q=80'
    },
    {
      id: 'luxury-housing-avinashi-road',
      name: 'Top reasons to buy a luxury housing unit along Avinashi Road corridor.',
      author: 'Sohina Sharma',
      date: 'Jun 15, 2026',
      short_notes: 'Avinashi Road is the lifeline of Coimbatore. Luxury apartments here are selling fast.',
      category: 'New Homes',
      image: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=500&q=80'
    },
    {
      id: 'coimbatore-metro-phase-1',
      name: 'Coimbatore Metro Rail Phase 1: How it will impact local real estate.',
      author: 'Sohina Sharma',
      date: 'Jun 12, 2026',
      short_notes: 'The new Metro Phase 1 will boost property prices along the Avinashi Road and Trichy Road corridors.',
      category: 'Infrastructure',
      image: 'https://images.unsplash.com/photo-1515162816999-a0c47dc192f7?w=500&q=80'
    },
    {
      id: 'puravankara-trichy-road',
      name: 'The Puravankara story: Over two decades of excellence in Tamil Nadu',
      author: 'Sohina Sharma',
      date: 'Jun 02, 2026',
      short_notes: 'A deep dive into Puravankara\'s journey and their upcoming projects on Trichy Road.',
      category: 'Investment',
      image: 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=500&q=80'
    },
    {
      id: 'sri-ramakrishna-villas',
      name: 'Is TVS Emerald at Singanallur the next big luxury project in the city?',
      author: 'Sohina Sharma',
      date: 'Jun 01, 2026',
      short_notes: 'TVS Emerald is launching its premium residential project in Singanallur.',
      category: 'New Homes',
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
          <div className="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary p-3 rounded-circle mb-3">
            <BookOpen size={32} />
          </div>
          <h1 className="display-5 fw-extrabold text-dark mb-2" style={{ color: '#0b2c56' }}>Coimbatore Realty Articles</h1>
          <p className="text-secondary mx-auto" style={{ maxWidth: '600px', fontSize: '1.05rem' }}>
            In-depth analysis, investment guides, and expert articles about the real estate market in Coimbatore.
          </p>
        </div>

        <div className="row g-4">
          {articles.map((article) => (
            <div key={article.id} className="col-lg-4 col-md-6 col-12">
              <div className="card border-0 shadow-sm rounded-4 overflow-hidden h-100 d-flex flex-column" style={{ transition: 'transform 0.2s ease', backgroundColor: '#fff' }}>
                <div style={{ height: '220px', overflow: 'hidden', position: 'relative' }}>
                  <img src={article.image} alt={article.name} className="w-100 h-100 object-fit-cover" />
                  <span className="position-absolute top-0 start-0 m-3 badge rounded-pill bg-dark bg-opacity-75 small text-uppercase" style={{ fontSize: '0.65rem', letterSpacing: '0.5px' }}>
                    {article.category}
                  </span>
                </div>
                <div className="card-body p-4 d-flex flex-column flex-grow-1">
                  <div className="d-flex align-items-center gap-3 mb-3 text-muted small">
                    <span className="d-flex align-items-center gap-1">
                      <User size={14} />
                      <span>{article.author}</span>
                    </span>
                    <span className="d-flex align-items-center gap-1">
                      <Calendar size={14} />
                      <span>{article.date}</span>
                    </span>
                  </div>
                  
                  <h3 className="h5 fw-bold text-dark mb-2 line-clamp-2" style={{ lineHeight: '1.4' }}>
                    {article.name}
                  </h3>
                  
                  <p className="text-secondary small mb-4 line-clamp-3" style={{ lineHeight: '1.5' }}>
                    {article.short_notes}
                  </p>
                  
                  <Link href={`/articles/${article.id}`} className="btn btn-outline-primary btn-sm rounded-pill px-4 py-2 mt-auto fw-bold d-inline-flex align-items-center gap-1.5 align-self-start">
                    <span>Read Full Article</span>
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
