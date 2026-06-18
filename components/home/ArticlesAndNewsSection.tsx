'use client';

import React from 'react';
import Link from 'next/link';
import { Eye, ArrowRight, FileText, ChevronRight, MapPin } from 'lucide-react';

export default function ArticlesAndNewsSection() {
  return (
    <div className="nb-articles-news-section bg-white pb-5">
      
      {/* 1. HERO ARTICLE BANNER (Image 1 Top) */}
      <div className="position-relative bg-dark text-white" style={{ minHeight: '400px', backgroundImage: 'url(https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1920&q=80)', backgroundSize: 'cover', backgroundPosition: 'center' }}>
        <div className="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-75"></div>
        
        <div className="container position-relative z-1 pt-4 pb-5">
          <div className="d-flex justify-content-between align-items-start mb-5">
            <div className="small text-white-50">
              Knowledge Centre <span className="mx-1">»</span> <Link href="/articles" className="text-white text-decoration-none fw-bold">Articles</Link>
            </div>
            <button className="btn btn-outline-light btn-sm rounded-circle p-2" style={{ width: '36px', height: '36px' }}>
              <MapPin size={16} />
            </button>
          </div>

          <div className="mb-5" style={{ maxWidth: '800px' }}>
            <span className="badge bg-warning text-dark mb-3 px-3 py-1">Housing Schemes</span>
            <h1 className="display-5 fw-bold mb-3 text-white">TNHB Housing Schemes in Coimbatore: All you need to know</h1>
            <div className="d-flex align-items-center justify-content-between">
              <div className="small text-white-50">
                <div className="fw-bold text-white">Sohina Sharma</div>
                <div>Research Analyst</div>
                <div>LocalRealty.com</div>
              </div>
              <div className="text-end small text-white-50">
                <div className="d-flex align-items-center gap-1 justify-content-end"><Eye size={14} /> 3046</div>
                <div>Jun 15, 2026</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* OVERLAPPING CARDS (Image 1 Middle) */}
      <div className="container position-relative z-2" style={{ marginTop: '-80px' }}>
        <div className="row g-3 mb-5">
          {/* Card 1 */}
          <div className="col-md-4">
            <Link href="/articles/investing-residential-plots-saravanampatti" className="text-decoration-none">
              <div className="card text-white border-0 h-100 p-4 rounded-0 shadow-sm" style={{ background: '#4585a4', minHeight: '160px' }}>
                <span className="badge bg-light bg-opacity-25 text-white align-self-start mb-2 px-2 py-1">Investment</span>
                <h5 className="fw-bold mb-4" style={{ fontSize: '1rem', lineHeight: '1.4' }}>Investing in residential plots in Saravanampatti? Consider key growth factors.</h5>
                <div className="mt-auto d-flex justify-content-between align-items-center small">
                  <div>Sohina Sharma<br/>Jun 17, 2026</div>
                  <div className="d-flex align-items-center gap-1"><Eye size={14} /> 7</div>
                </div>
              </div>
            </Link>
          </div>
          {/* Card 2 */}
          <div className="col-md-4">
            <Link href="/articles/luxury-housing-avinashi-road" className="text-decoration-none">
              <div className="card text-white border-0 h-100 p-4 rounded-0 shadow-sm" style={{ background: '#2c949a', minHeight: '160px' }}>
                <span className="badge bg-light bg-opacity-25 text-white align-self-start mb-2 px-2 py-1">New Homes</span>
                <h5 className="fw-bold mb-4" style={{ fontSize: '1rem', lineHeight: '1.4' }}>Top reasons to buy a luxury housing unit along Avinashi Road corridor.</h5>
                <div className="mt-auto d-flex justify-content-between align-items-center small">
                  <div>Sohina Sharma<br/>Jun 15, 2026</div>
                  <div className="d-flex align-items-center gap-1"><Eye size={14} /> 159</div>
                </div>
              </div>
            </Link>
          </div>
          {/* Card 3 */}
          <div className="col-md-4">
            <Link href="/articles/coimbatore-metro-phase-1" className="text-decoration-none">
              <div className="card text-white border-0 h-100 p-4 rounded-0 shadow-sm" style={{ background: '#4fa86b', minHeight: '160px' }}>
                <span className="badge bg-light bg-opacity-25 text-white align-self-start mb-2 px-2 py-1">Infrastructure</span>
                <h5 className="fw-bold mb-4" style={{ fontSize: '1rem', lineHeight: '1.4' }}>Coimbatore Metro Rail Phase 1: How it will impact local real estate.</h5>
                <div className="mt-auto d-flex justify-content-between align-items-center small">
                  <div>Sohina Sharma<br/>Jun 12, 2026</div>
                  <div className="d-flex align-items-center gap-1"><Eye size={14} /> 282</div>
                </div>
              </div>
            </Link>
          </div>
        </div>

        {/* 2. COIMBATORE REAL ESTATE GRID & SIDEBAR (Image 1 Bottom & Image 2) */}
        <h2 className="fw-bold text-dark mb-4 h3">Coimbatore Real Estate</h2>
        
        <div className="row g-4">
          {/* Left Content Column */}
          <div className="col-lg-9">
            
            {/* Top 2 Big Cards */}
            <div className="row g-4 mb-4">
              <div className="col-md-6">
                <Link href="/articles/puravankara-trichy-road" className="text-decoration-none text-dark d-flex gap-3">
                  <img src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=300&q=80" alt="Apartment" className="rounded" style={{ width: '160px', height: '160px', objectFit: 'cover' }} />
                  <div className="d-flex flex-column justify-content-center">
                    <span className="badge bg-info text-white align-self-start mb-2 px-2 py-1" style={{ backgroundColor: '#56b8e6' }}>Investment</span>
                    <h5 className="fw-bold fs-6 mb-2">The Puravankara story: Over two decades of excellence in Tamil Nadu</h5>
                    <div className="text-muted small mt-auto">
                      <div className="mb-1">Sohina Sharma<br/>Jun 02, 2026</div>
                      <div className="d-flex align-items-center gap-1"><Eye size={12} /> 322</div>
                    </div>
                  </div>
                </Link>
              </div>
              <div className="col-md-6">
                <Link href="/articles/sri-ramakrishna-villas" className="text-decoration-none text-dark d-flex gap-3">
                  <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=300&q=80" alt="Villas" className="rounded" style={{ width: '160px', height: '160px', objectFit: 'cover' }} />
                  <div className="d-flex flex-column justify-content-center">
                    <span className="badge bg-info text-white align-self-start mb-2 px-2 py-1" style={{ backgroundColor: '#70cfcf' }}>New Homes</span>
                    <h5 className="fw-bold fs-6 mb-2">Is TVS Emerald at Singanallur the next big luxury project in the city?</h5>
                    <div className="text-muted small mt-auto">
                      <div className="mb-1">Sohina Sharma<br/>Jun 01, 2026</div>
                      <div className="d-flex align-items-center gap-1"><Eye size={12} /> 395</div>
                    </div>
                  </div>
                </Link>
              </div>
            </div>

            {/* Seller Guide Banner */}
            <div className="bg-primary text-white p-3 rounded-0 mb-5 d-flex align-items-center justify-content-between" style={{ background: 'linear-gradient(90deg, #1e3a8a 0%, #3b82f6 100%)' }}>
              <div className="d-flex align-items-center gap-3">
                <div className="bg-white text-primary p-2 fw-bold text-center border border-2 border-warning" style={{ fontSize: '1.2rem', fontFamily: 'serif' }}>Seller<br/>Guide</div>
                <div>
                  <div className="small text-white-50">Be an informed home seller with LocalRealty</div>
                  <h4 className="fw-bold text-warning mb-0" style={{ letterSpacing: '1px' }}>Exclusive Home Selling Guide</h4>
                </div>
              </div>
              <button className="btn btn-warning fw-bold px-4 rounded-0 text-dark">UNLOCK NOW!</button>
            </div>

            {/* Grid of smaller cards */}
            <div className="row g-4 mb-4">
              {[
                { cat: 'New Homes', catColor: '#56b8e6', title: 'Casagrand Terra: Why homebuyers are drawn to this villa project in Kalapatti', img: 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=400&q=80', views: 18818 },
                { cat: 'New Homes', catColor: '#70cfcf', title: 'Looking for a ready-to-move project in RS Puram? Here\'s why it makes sense.', img: 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=400&q=80', views: 27590 },
                { cat: 'New Homes', catColor: '#70cfcf', title: 'Is Northern Lights by Puravankara the top choice in the Trichy Road corridor?', img: 'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=400&q=80', views: 23710 },
                { cat: 'New Homes', catColor: '#70cfcf', title: 'Inside Vadavalli The Autograph: Coimbatore\'s ultra-luxury residence.', img: 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=400&q=80', views: 25793 },
                { cat: 'Infrastructure', catColor: '#56b8e6', title: 'Coimbatore Western Ring Road: All you need to know about the project.', img: 'https://images.unsplash.com/photo-1515162816999-a0c47dc192f7?w=400&q=80', views: 1376 },
                { cat: 'Investment', catColor: '#70cfcf', title: 'Connectivity, commerce, and community: The future of L&T Bypass.', img: 'https://images.unsplash.com/photo-1449844908441-8829872d2607?w=400&q=80', views: 29427 },
              ].map((item, i) => (
                <div key={i} className="col-md-6 col-lg-6">
                  <Link href={`/articles/example-${i}`} className="text-decoration-none text-dark d-flex gap-3">
                    <img src={item.img} alt="Property" className="rounded-0" style={{ width: '120px', height: '120px', objectFit: 'cover' }} />
                    <div className="d-flex flex-column">
                      <span className="badge text-white align-self-start mb-2 px-2 py-1 rounded-0" style={{ backgroundColor: item.catColor }}>{item.cat}</span>
                      <h6 className="fw-bold fs-6 mb-2" style={{ lineHeight: '1.4' }}>{item.title}</h6>
                      <div className="text-muted small mt-auto">
                        <div className="mb-1" style={{ fontSize: '11px' }}>Sohina Saraf<br/>May 19, 2026</div>
                        <div className="d-flex align-items-center gap-1" style={{ fontSize: '11px' }}><Eye size={12} /> {item.views}</div>
                      </div>
                    </div>
                  </Link>
                </div>
              ))}
            </div>
            
          </div>

          {/* Right Sidebar Column */}
          <div className="col-lg-3">
            
            {/* Popular and Trending List */}
            <div className="mb-4 ps-lg-2" style={{ borderLeft: '4px solid #0076de' }}>
              <h4 className="fw-bold text-primary mb-4 ps-2 fs-5">Popular and Trending</h4>
              
              <div className="d-flex flex-column gap-3 ps-2">
                {[
                  { title: 'What is the cost of constructing a house in Coimbatore?', author: 'Subhadra Bhadauria', date: 'Jun 04, 2026', views: '273,175' },
                  { title: 'Latest electricity charges in Tamil Nadu per unit in 2025', author: 'Anirudh Singh Chauhan', date: 'Sep 17, 2025', views: '65,607' },
                  { title: 'Stamp duty and registration charges in Tamil Nadu in 2026', author: 'Aman', date: 'Jan 12, 2026', views: '63,557' }
                ].map((item, i) => (
                  <div key={i} className="pb-3 border-bottom border-light">
                    <Link href={`/articles/trending-${i}`} className="text-decoration-none text-dark fw-bold small d-block mb-2" style={{ lineHeight: '1.4' }}>
                      {item.title}
                    </Link>
                    <div className="text-muted d-flex justify-content-between align-items-end" style={{ fontSize: '10px' }}>
                      <div>{item.author}<br/>{item.date}</div>
                      <div className="d-flex align-items-center gap-1"><Eye size={10} /> {item.views}</div>
                    </div>
                  </div>
                ))}
              </div>
              <Link href="/articles" className="btn btn-link text-primary text-decoration-none fw-bold small ps-2 mt-2 px-0 text-uppercase d-flex align-items-center gap-1" style={{ fontSize: '12px' }}>
                View More
              </Link>
            </div>

            {/* Ad Widget 1 */}
            <div className="mb-4 bg-warning bg-opacity-25 p-4 text-center border">
              <h5 className="fw-bold mb-3" style={{ color: '#5b4a00' }}>Sell / Rent Property<br/>for Free</h5>
              <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=200&q=80" alt="City" className="w-100" style={{ mixBlendMode: 'multiply' }} />
            </div>

            {/* Ad Widget 2 (Sponsored) */}
            <div className="mb-4 bg-dark text-white p-4 text-center border">
              <h6 className="text-warning fw-bold mb-2 text-uppercase d-flex align-items-center justify-content-center gap-2">
                <span className="text-warning">◆</span> SPONSORED CONTENT
              </h6>
              <div className="small text-white-50 mb-3" style={{ fontSize: '10px', letterSpacing: '1px' }}>DEVELOPERS | PROJECTS | NEWS & VIEWS</div>
              <p className="small fst-italic text-white-50 mb-4" style={{ fontSize: '11px' }}>*A paid campaign for select builders</p>
              <button className="btn btn-warning btn-sm rounded-0 fw-bold px-4">EXPLORE NOW</button>
            </div>

            {/* Ad Widget 3 (Rent Agreement) */}
            <div className="mb-4 bg-white p-4 text-center border shadow-sm">
              <div className="mb-3">
                <FileText size={48} className="text-primary opacity-75" />
              </div>
              <h6 className="fw-bold text-dark mb-2">Now you can create your Rent Agreement Online</h6>
              <p className="small text-muted mb-3" style={{ fontSize: '11px' }}>100% Reliable | Delivered to your doorstep</p>
              <button className="btn btn-primary btn-sm rounded-1 fw-bold px-4">Know More</button>
            </div>

          </div>
        </div>
      </div>

      {/* 3. REAL ESTATE NEWS (Image 3) */}
      <div className="bg-light py-5 mt-4">
        <div className="container">
          <h2 className="text-center fw-extrabold text-dark mb-5">Real Estate News</h2>
          
          <div className="position-relative">
            <div className="d-flex overflow-auto gap-4 pb-4 px-2" style={{ scrollSnapType: 'x mandatory' }}>
              {[
                { title: 'TN lifts 5-year ban on Hill Area projects: What it means for investors', date: 'Apr 08, 2026', views: '5044' },
                { title: 'TN cabinet approves new Affordable Housing Policy 2026', date: 'Mar 12, 2026', views: '2751' },
                { title: 'Registry approved for 9,000 flats in Coimbatore IT Corridor', date: 'Jan 06, 2026', views: '6622' },
                { title: 'What is title-based property registry in Tamil Nadu?', date: 'Dec 17, 2025', views: '6525' }
              ].map((news, i) => (
                <div key={i} className="flex-shrink-0" style={{ width: '280px', scrollSnapAlign: 'start' }}>
                  <Link href={`/news/news-${i}`} className="text-decoration-none text-dark">
                    <h6 className="fw-bold mb-3" style={{ fontSize: '0.95rem', lineHeight: '1.4' }}>{news.title}</h6>
                    <div className="text-muted d-flex justify-content-between align-items-center" style={{ fontSize: '11px' }}>
                      <div>Anannya Purna<br/>{news.date}</div>
                      <div className="d-flex align-items-center gap-1"><Eye size={12} /> {news.views}</div>
                    </div>
                  </Link>
                </div>
              ))}
              <div className="d-flex align-items-center justify-content-center flex-shrink-0" style={{ width: '100px' }}>
                 <ChevronRight size={48} className="text-muted opacity-50" />
              </div>
            </div>
          </div>
          
          <div className="text-center mt-3">
            <Link href="/news" className="text-decoration-none text-dark border-bottom border-dark pb-1 d-inline-block">
              View All News
            </Link>
          </div>
        </div>
      </div>

      {/* 4. BLUE SUBSCRIPTION BANNER (Image 3 Bottom) */}
      <div className="bg-info py-4 text-white position-relative overflow-hidden" style={{ backgroundColor: '#2b8fd9' }}>
        <div className="container position-relative z-1 d-flex flex-wrap justify-content-center align-items-center gap-4">
          <div>
            <h3 className="fw-bold mb-0">Real Estate Updates</h3>
            <p className="mb-0 text-white-50">Be the first to know</p>
          </div>
          <button className="btn btn-dark rounded-pill px-4 py-2 d-flex align-items-center gap-2 fw-bold" style={{ backgroundColor: '#1864a3', border: 'none' }}>
            Subscribe Now <ArrowRight size={16} />
          </button>
        </div>
      </div>

    </div>
  );
}
