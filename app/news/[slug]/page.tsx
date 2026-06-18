'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { useAuth } from '../../../components/AuthContext';
import {
  User, Calendar, Eye, Share2, ThumbsUp, ChevronDown, ChevronUp
} from 'lucide-react';

export default function NewsDetail({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = React.use(params);
  const { user, setAuthModalOpen } = useAuth();

  const [loading, setLoading] = useState(true);
  const [likesCount, setLikesCount] = useState(128);
  const [hasLiked, setHasLiked] = useState(false);
  const [sharesCount, setSharesCount] = useState(12);
  const [viewsCount, setViewsCount] = useState(5044);
  const [showContents, setShowContents] = useState(true);
  const [isCopied, setIsCopied] = useState(false);

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
            TN lifts 5-year ban on Hill Area projects
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
              <li className="breadcrumb-item"><Link href="/" className="text-decoration-none text-secondary">Home</Link></li>
              <li className="breadcrumb-item"><Link href="/news" className="text-decoration-none text-secondary">Real Estate News</Link></li>
              <li className="breadcrumb-item active text-dark fw-bold text-truncate" aria-current="page">Policy & Regulations</li>
            </ol>
          </nav>
        </div>

        {/* Title Block */}
        <div className="mb-4">
          <h1 className="display-6 fw-extrabold text-dark mb-3" style={{ lineHeight: '1.25', color: '#0b2c56' }}>
            TN lifts 5-year ban on Hill Area projects: What it means for investors
          </h1>

          <div className="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-3 pb-3 border-bottom">
            <div className="d-flex flex-wrap gap-1.5">
              {['#Policy', '#RealEstateNews', '#TamilNadu', '#HillArea', '#Investors'].map((tag) => (
                <span key={tag} className="badge bg-light text-secondary border py-1.5 px-2.5 rounded-pill font-monospace" style={{ fontSize: '0.75rem', fontWeight: 500 }}>
                  {tag}
                </span>
              ))}
            </div>
          </div>

          <div className="d-flex align-items-center gap-3 text-muted small mt-3">
             <span className="d-flex align-items-center gap-1"><User size={13} /> Anannya Purna</span>
             <span>•</span>
             <span className="d-flex align-items-center gap-1"><Calendar size={13} /> Apr 08, 2026</span>
             <span>•</span>
             <span className="fw-bold">2 min read</span>
          </div>
        </div>

        {/* Main Content Layout */}
        <div className="row g-4 mt-1">
          
          <div className="col-lg-8">
            
            <p className="fs-5 text-secondary mb-4" style={{ lineHeight: '1.6' }}>
              The Tamil Nadu government has conditionally lifted the ban on real estate development in specific hill area zones, providing a major boost to local developers and investors looking for eco-friendly resort and residential plotting projects.
            </p>

            <div className="rounded-4 overflow-hidden shadow-sm mb-4 bg-white border border-light" style={{ maxHeight: '420px' }}>
              <img src="https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&q=80" alt="Hill Area" className="w-100 h-100 object-fit-cover" />
            </div>

            <article className="text-dark fs-6 bg-white p-4 rounded-4 shadow-sm border" style={{ lineHeight: '1.8' }}>
              <h2 className="h5 fw-bold mb-3">Background of the Ban</h2>
              <p>The ban was initially implemented five years ago to curb rampant and unregulated construction activities that were harming the fragile ecosystems in regions like Ooty, Kodaikanal, and the Nilgiris.</p>
              
              <h2 className="h5 fw-bold mb-3 mt-4">The New Guidelines</h2>
              <p>Under the new guidelines, projects will be approved strictly on a case-by-case basis. Developers must adhere to stringent environmental norms, including limited ground coverage, mandatory rainwater harvesting, and the use of sustainable building materials.</p>
              
              <h2 className="h5 fw-bold mb-3 mt-4">Impact on Investors</h2>
              <p>For investors, this opens up lucrative opportunities. The demand for second homes and wellness retreats in these areas has always been high, and the regulated supply is expected to drive up property values significantly in the coming years.</p>
            </article>

          </div>

          {/* Right Sidebar */}
          <div className="col-lg-4">
            <div className="sticky-top" style={{ top: '80px' }}>
              
              <h5 className="fw-bold mb-4" style={{ fontSize: '1rem', borderBottom: '2px solid #0076de', display: 'inline-block', paddingBottom: '5px' }}>More News</h5>
              
              <div className="d-flex flex-column gap-3 mb-5">
                {[
                  { title: 'TN cabinet approves new Affordable Housing Policy 2026', img: 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=100&q=80' },
                  { title: 'Registry approved for 9,000 flats in Coimbatore IT Corridor', img: 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=100&q=80' },
                  { title: 'What is title-based property registry in Tamil Nadu?', img: 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=100&q=80' }
                ].map((item, idx) => (
                  <div key={idx} className="d-flex gap-3 align-items-center">
                    <img src={item.img} alt="Thumb" className="rounded" style={{ width: '60px', height: '48px', objectFit: 'cover' }} />
                    <Link href="#" className="text-decoration-none text-dark fw-bold small lh-sm">{item.title}</Link>
                  </div>
                ))}
                <Link href="/news" className="text-primary text-decoration-none small fw-bold mt-2">View all news</Link>
              </div>

              {/* Newsletter subscribe widget */}
              <div className="bg-white border rounded-4 p-4 shadow-sm">
                <h4 className="fw-bold text-dark mb-2" style={{ fontSize: '1rem' }}>Get Real Estate News</h4>
                <p className="text-muted mb-3" style={{ fontSize: '0.8rem', lineHeight: '1.4' }}>
                  Delivered straight to your inbox.
                </p>
                <form className="d-flex flex-column gap-2" onSubmit={(e) => e.preventDefault()}>
                  <input type="email" className="form-control rounded-3" placeholder="Email address" required />
                  <button type="submit" className="btn btn-primary w-100 rounded-3 fw-bold">Subscribe</button>
                </form>
              </div>

            </div>
          </div>

        </div>
      </div>
    </div>
  );
}
