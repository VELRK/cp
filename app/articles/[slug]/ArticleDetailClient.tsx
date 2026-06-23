'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { useAuth } from '../../../components/AuthContext';
import {
  User, Calendar, Heart, Eye, Share2, ThumbsUp, ChevronDown, ChevronUp, Send, Smartphone, X
} from 'lucide-react';

export default function ArticleDetail({ slug }: { slug: string }) {
  const { user, setAuthModalOpen } = useAuth();

  const [loading, setLoading] = useState(true);
  const [likesCount, setLikesCount] = useState(332);
  const [hasLiked, setHasLiked] = useState(false);
  const [sharesCount, setSharesCount] = useState(7);
  const [viewsCount, setViewsCount] = useState(4820);
  const [showContents, setShowContents] = useState(true);
  const [isCopied, setIsCopied] = useState(false);

  // Mock fetching delay
  useEffect(() => {
    const timer = setTimeout(() => {
      setLoading(false);
    }, 500);
    return () => clearTimeout(timer);
  }, []);

  const handleLike = () => {
    if (hasLiked) {
      setLikesCount(prev => prev - 1);
      setHasLiked(false);
    } else {
      setLikesCount(prev => prev + 1);
      setHasLiked(true);
    }
  };

  const handleShare = () => {
    if (typeof window !== 'undefined') {
      navigator.clipboard.writeText(window.location.href);
      setIsCopied(true);
      setSharesCount(prev => prev + 1);
      setTimeout(() => setIsCopied(false), 2000);
    }
  };

  if (loading) {
    return (
      <div style={{ background: '#f8fafc', minHeight: '100vh', padding: '3rem 0' }}>
        <div className="container placeholder-glow" style={{ maxWidth: '900px' }}>
           <div className="placeholder col-12 mb-3" style={{ height: '40px' }}></div>
           <div className="placeholder w-100 rounded-4 mb-4" style={{ height: '400px', backgroundColor: '#e2e8f0' }}></div>
        </div>
      </div>
    );
  }

  return (
    <div style={{ background: '#f8fafc', minHeight: '100vh', paddingBottom: '5rem' }}>

      {/* Sticky/Fixed Subheader Bar */}
      <div className="sticky-top bg-white border-bottom shadow-sm" style={{ zIndex: 1020, top: 0 }}>
        <div className="container d-flex justify-content-between align-items-center py-2 px-3">
          <div className="text-truncate fw-bold text-dark pe-3" style={{ fontSize: '0.9rem', maxWidth: '75%' }}>
            Investing in residential plots in Saravanampatti?
          </div>
          <div className="d-flex align-items-center gap-2 flex-shrink-0">
            <button type="button" className={`btn btn-sm d-flex align-items-center gap-1.5 rounded-pill border ${hasLiked ? 'btn-primary text-white border-primary' : 'btn-light text-secondary bg-white'}`} onClick={handleLike}>
              <ThumbsUp size={13} />
              <span className="fw-bold" style={{ fontSize: '12px' }}>{likesCount}</span>
            </button>
            <button type="button" className="btn btn-sm btn-light bg-white text-secondary border d-flex align-items-center gap-1.5 rounded-pill" onClick={handleShare}>
              <Share2 size={13} />
              <span className="fw-bold" style={{ fontSize: '12px' }}>{isCopied ? 'Copied' : 'Share'}</span>
            </button>
          </div>
        </div>
      </div>

      <div className="container pt-4">

        {/* Navigation Breadcrumb */}
        <div className="mb-3">
          <nav aria-label="breadcrumb">
            <ol className="breadcrumb small text-muted mb-0">
              <li className="breadcrumb-item"><Link href="/" className="text-decoration-none text-secondary">Knowledge Centre</Link></li>
              <li className="breadcrumb-item"><Link href="/articles" className="text-decoration-none text-secondary">Articles</Link></li>
              <li className="breadcrumb-item active text-dark fw-bold text-truncate" aria-current="page">Coimbatore Real Estate</li>
            </ol>
          </nav>
        </div>

        {/* Title Block */}
        <div className="mb-4">
          <h1 className="display-6 fw-extrabold text-dark mb-3" style={{ lineHeight: '1.25', color: '#0b2c56' }}>
            Investing in residential plots in Saravanampatti? Consider key growth factors.
          </h1>

          {/* Hashtag Badges & Share icons */}
          <div className="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-3 pb-3 border-bottom">
            <div className="d-flex flex-wrap gap-1.5">
              {['#Buyers', '#CoimbatoreRealEstate', '#Investment', '#Investors', '#NewHomes', '#Plots', '#Residential'].map((tag) => (
                <span key={tag} className="badge bg-light text-secondary border py-1.5 px-2.5 rounded-pill font-monospace" style={{ fontSize: '0.75rem', fontWeight: 500 }}>
                  {tag}
                </span>
              ))}
            </div>
            
            <div className="d-flex gap-2">
               <button className="btn btn-outline-info rounded-circle p-2" style={{width: '35px', height: '35px', display: 'flex', alignItems: 'center', justifyContent: 'center'}}><i className="fab fa-twitter"></i></button>
               <button className="btn btn-outline-success rounded-circle p-2" style={{width: '35px', height: '35px', display: 'flex', alignItems: 'center', justifyContent: 'center'}}><i className="fab fa-whatsapp"></i></button>
               <button className="btn btn-outline-primary rounded-circle p-2" style={{width: '35px', height: '35px', display: 'flex', alignItems: 'center', justifyContent: 'center'}}><i className="fab fa-facebook-f"></i></button>
               <button className="btn btn-outline-primary rounded-circle p-2" style={{width: '35px', height: '35px', display: 'flex', alignItems: 'center', justifyContent: 'center'}}><i className="fab fa-linkedin-in"></i></button>
            </div>
          </div>

          <div className="d-flex align-items-center gap-3 text-muted small mt-3">
             <span className="fw-bold">4 min read</span>
             <span>•</span>
             <span>Jun 17, 2026</span>
          </div>
        </div>

        {/* Main Content Layout */}
        <div className="row g-4 mt-1">
          
          {/* Left / Middle Content */}
          <div className="col-lg-8">
            
            <p className="fs-5 text-secondary mb-4" style={{ lineHeight: '1.6' }}>
              One of the trusted developers in South India has transformed the region's commercial and residential landscape with a strict commitment to quality. Boasting a legacy of over 20 years, Saravanampatti continues to raise the bar for modern real estate across Coimbatore.
            </p>

            <div className="rounded-4 overflow-hidden shadow-sm mb-4 bg-white border border-light" style={{ maxHeight: '420px' }}>
              <img src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800&q=80" alt="Cover" className="w-100 h-100 object-fit-cover" />
            </div>

            {/* Contents index box (Accordion) */}
            <div className="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden" style={{ backgroundColor: '#f4f5f7' }}>
              <button
                type="button"
                className="card-header bg-transparent border-0 py-3 px-4 d-flex justify-content-between align-items-center text-dark fw-bold"
                onClick={() => setShowContents(!showContents)}
                style={{ cursor: 'pointer', outline: 'none' }}
              >
                <span className="fw-bold m-0 text-dark">Contents</span>
                {showContents ? <ChevronUp size={18} /> : <ChevronDown size={18} />}
              </button>
              {showContents && (
                <div className="card-body pt-0 px-4 pb-4">
                  <ol className="list-group list-group-numbered border-0 bg-transparent mb-0">
                    <li className="list-group-item border-0 bg-transparent px-0 py-1.5 small text-secondary">
                      <a href="#section-1" className="text-decoration-none text-primary fw-semibold hover-primary">Quality, innovation and customer-centricity: The philosophy</a>
                    </li>
                    <li className="list-group-item border-0 bg-transparent px-0 py-1.5 small text-secondary">
                      <a href="#section-2" className="text-decoration-none text-primary fw-semibold hover-primary">The journey so far</a>
                      <ul className="list-unstyled ms-4 mt-2">
                        <li className="mb-1"><a href="#" className="text-decoration-none text-primary">2.1. Saravanampatti IT Park</a></li>
                        <li className="mb-1"><a href="#" className="text-decoration-none text-primary">2.2. DreamCity Villas</a></li>
                        <li className="mb-1"><a href="#" className="text-decoration-none text-primary">2.3. Autograph Apartments</a></li>
                      </ul>
                    </li>
                    <li className="list-group-item border-0 bg-transparent px-0 py-1.5 small text-secondary">
                      <a href="#section-3" className="text-decoration-none text-primary fw-semibold hover-primary">Completed projects</a>
                    </li>
                    <li className="list-group-item border-0 bg-transparent px-0 py-1.5 small text-secondary mt-2">
                      <a href="#section-faq" className="text-decoration-none text-primary fw-semibold hover-primary">Frequently Asked Questions</a>
                      <ul className="list-unstyled ms-4 mt-2">
                        <li className="mb-1"><a href="#" className="text-decoration-none text-primary">What are some of the popular luxury residential projects?</a></li>
                        <li className="mb-1"><a href="#" className="text-decoration-none text-primary">What apartment configurations are available?</a></li>
                        <li className="mb-1"><a href="#" className="text-decoration-none text-primary">Is Saravanampatti a good place to live?</a></li>
                      </ul>
                    </li>
                  </ol>
                </div>
              )}
            </div>

            <article className="text-dark fs-6" style={{ lineHeight: '1.8' }}>
              <p>In South India's hyper-competitive property market, a developer's word is everything. Today's property buyers, whether a family investing their life savings into a home or a multinational corporate scouting a flagship headquarters, are fiercely discerning.</p>
              <p>They demand a rare trifecta: zero compromise on construction quality, rock-solid timeline delivery, and a developer that stands behind its creations long after the keys are handed over.</p>
            </article>

          </div>

          {/* Right Sidebar */}
          <div className="col-lg-4">
            <div className="sticky-top" style={{ top: '80px' }}>
              
              <h5 className="fw-bold mb-4" style={{ fontSize: '1rem', borderBottom: '2px solid #0076de', display: 'inline-block', paddingBottom: '5px' }}>Latest Articles</h5>
              
              <div className="d-flex flex-column gap-3 mb-5">
                {[
                  { title: 'Why invest in Kuthambakkam, Chennai?', img: 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=100&q=80' },
                  { title: 'Ranka Group: Redefining premium housing', img: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=100&q=80' },
                  { title: 'Ananda The Drizzle, Narsingi: An overview', img: 'https://images.unsplash.com/photo-1515162816999-a0c47dc192f7?w=100&q=80' }
                ].map((item, idx) => (
                  <div key={idx} className="d-flex gap-3 align-items-center">
                    <img src={item.img} alt="Thumb" className="rounded" style={{ width: '60px', height: '48px', objectFit: 'cover' }} />
                    <Link href="#" className="text-decoration-none text-dark fw-bold small lh-sm">{item.title}</Link>
                  </div>
                ))}
                <Link href="/articles" className="text-primary text-decoration-none small fw-bold mt-2">View more</Link>
              </div>

              <h5 className="fw-bold mb-4" style={{ fontSize: '1rem', borderBottom: '2px solid #0076de', display: 'inline-block', paddingBottom: '5px' }}>Popular Articles</h5>
              
              <div className="d-flex flex-column gap-3 mb-5">
                {[
                  { title: 'NPCL electricity charges in Greater Noida', img: 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=100&q=80' },
                  { title: 'Best residential sectors on Avinashi Expressway', img: 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=100&q=80' },
                  { title: 'Top 5 affordable localities in Coimbatore', img: 'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=100&q=80' }
                ].map((item, idx) => (
                  <div key={idx} className="d-flex gap-3 align-items-center">
                    <img src={item.img} alt="Thumb" className="rounded" style={{ width: '60px', height: '48px', objectFit: 'cover' }} />
                    <Link href="#" className="text-decoration-none text-dark fw-bold small lh-sm">{item.title}</Link>
                  </div>
                ))}
                <Link href="/articles" className="text-primary text-decoration-none small fw-bold mt-2">View more</Link>
              </div>

            </div>
          </div>

        </div>
      </div>
    </div>
  );
}
