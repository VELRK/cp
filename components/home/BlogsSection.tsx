'use client';

import React from 'react';
import Link from 'next/link';
import { ArrowRight } from 'lucide-react';
import { toFrontendAssetUrl } from '@/lib/cityImages';

export interface Blog {
  id: number;
  name: string;
  author: string;
  date: string;
  short_notes: string;
  description: string;
  gallery: string[];
  image: string | null;
  category?: string;
}

interface BlogsSectionProps {
  blogs: Blog[];
  loadingBlogs: boolean;
  activeBlogCategory: 'news' | 'tax' | 'guide' | 'investment';
  setActiveBlogCategory: (val: 'news' | 'tax' | 'guide' | 'investment') => void;
}

const BlogsSection: React.FC<BlogsSectionProps> = ({
  blogs,
  loadingBlogs,
  activeBlogCategory,
  setActiveBlogCategory
}) => {
  return (
    <div className="nb-blogs-section-wrapper p-4 bg-white rounded-4 shadow-sm" style={{ border: '1px solid var(--nb-card-border)' }}>
      <div className="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
        <div>
          <h2 className="h4 fw-bold m-0" style={{ color: 'var(--nb-primary)', letterSpacing: '-0.5px' }}>Top articles on home buying</h2>
          <p className="text-muted small m-0 mt-1" style={{ fontSize: '0.9rem' }}>Expert guides, tax regulations, and latest real estate news</p>
        </div>
        <Link href="/blog" className="text-decoration-none fw-bold small d-flex align-items-center gap-1 px-3 py-2 rounded-pill" style={{ color: 'var(--nb-primary)', backgroundColor: 'var(--nb-primary-soft)', transition: 'all 0.2s ease' }}>
          <span>Read realty news, guides & articles</span>
          <ArrowRight size={16} />
        </Link>
      </div>

      {/* Tab List */}
      <div className="nb-blogs-tabs-scroll-wrap mb-4" style={{ borderBottom: '2px solid var(--nb-mint)' }}>
        <div className="nb-blogs-tabs-row d-flex gap-4">
          {[
            { key: 'news', label: 'News' },
            { key: 'tax', label: 'Tax & Legal' },
            { key: 'guide', label: 'Help Guides' },
            { key: 'investment', label: 'Investment' }
          ].map((tab) => (
            <button
              key={tab.key}
              type="button"
              className={`bg-transparent border-0 pb-2 px-1 fw-semibold position-relative`}
              style={{
                color: activeBlogCategory === tab.key ? 'var(--nb-primary)' : 'var(--nb-muted)',
                transition: 'all 0.3s ease'
              }}
              onClick={() => setActiveBlogCategory(tab.key as any)}
            >
              {tab.label}
              {activeBlogCategory === tab.key && (
                <div 
                  className="position-absolute bottom-0 start-0 w-100" 
                  style={{ height: '3px', backgroundColor: 'var(--nb-primary)', borderRadius: '3px 3px 0 0', transform: 'translateY(2px)' }} 
                />
              )}
            </button>
          ))}
        </div>
      </div>

      {/* Tab Content */}
      <div className="row g-4">
        {loadingBlogs ? (
          Array.from({ length: 4 }).map((_, i) => (
            <div key={i} className="col-lg-3 col-md-4 col-sm-6 col-12 placeholder-glow">
              <div className="card border-0 rounded-4 overflow-hidden h-100 shadow-sm" style={{ border: '1px solid var(--nb-card-border)' }}>
                <div className="placeholder w-100" style={{ height: '180px', backgroundColor: '#e2e8f0' }}></div>
                <div className="card-body p-3">
                  <div className="placeholder col-8 mb-2"></div>
                  <div className="placeholder col-12"></div>
                </div>
              </div>
            </div>
          ))
        ) : (() => {
          const filtered = blogs.filter((blog) => {
            const cat = (blog.category || '').toLowerCase();
            const name = (blog.name || '').toLowerCase();
            
            if (activeBlogCategory === 'news') {
              return cat.includes('news') || (!cat.includes('tax') && !cat.includes('legal') && !cat.includes('guide') && !cat.includes('help') && !cat.includes('invest'));
            }
            if (activeBlogCategory === 'tax') {
              return cat.includes('tax') || cat.includes('legal') || name.includes('tax') || name.includes('legal') || name.includes('regist') || name.includes('rera') || name.includes('stamp');
            }
            if (activeBlogCategory === 'guide') {
              return cat.includes('guide') || cat.includes('help') || name.includes('guide') || name.includes('checklist') || name.includes('tips') || name.includes('how to') || name.includes('beginners');
            }
            if (activeBlogCategory === 'investment') {
              return cat.includes('invest') || name.includes('invest') || name.includes('construction') || name.includes('buy') || name.includes('market') || name.includes('cost');
            }
            return true;
          });

          const displayBlogs = filtered.length > 0 ? filtered.slice(0, 4) : blogs.slice(0, 4);

          if (displayBlogs.length === 0) {
            return (
              <div className="col-12 text-center py-5">
                <div className="text-muted mb-2">No articles found in this category.</div>
                <p className="small">Please check back later or explore other topics.</p>
              </div>
            );
          }

          return displayBlogs.map((blog) => {
            const image = blog.image ? toFrontendAssetUrl(blog.image) : 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=400&q=80';
            const formattedDate = blog.date
              ? new Date(blog.date).toLocaleDateString('en-IN', { month: 'short', day: '2-digit', year: 'numeric' })
              : 'Jun 2026';
            return (
              <div key={blog.id} className="col-lg-3 col-md-4 col-sm-6 col-12">
                <Link href={`/blog/${blog.id}`} className="text-decoration-none text-dark d-block h-100">
                  <div 
                    className="card border-0 rounded-4 overflow-hidden h-100 bg-white" 
                    style={{ 
                      boxShadow: '0 4px 15px rgba(0, 0, 0, 0.05)', 
                      transition: 'all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1)',
                      border: '1px solid var(--nb-card-border)'
                    }}
                    onMouseEnter={(e) => {
                      e.currentTarget.style.transform = 'translateY(-5px)';
                      e.currentTarget.style.boxShadow = '0 15px 30px rgba(12, 35, 64, 0.12)';
                    }}
                    onMouseLeave={(e) => {
                      e.currentTarget.style.transform = 'none';
                      e.currentTarget.style.boxShadow = '0 4px 15px rgba(0, 0, 0, 0.05)';
                    }}
                  >
                    <div className="position-relative" style={{ height: '180px', overflow: 'hidden' }}>
                      <img
                        src={image}
                        alt={blog.name}
                        className="w-100 h-100 object-fit-cover"
                        style={{ transition: 'transform 0.5s ease' }}
                        onMouseEnter={(e) => e.currentTarget.style.transform = 'scale(1.05)'}
                        onMouseLeave={(e) => e.currentTarget.style.transform = 'scale(1)'}
                      />
                      <span className="position-absolute top-0 start-0 m-3 badge rounded-pill" style={{ backgroundColor: 'rgba(11, 44, 86, 0.85)', color: '#fff', fontSize: '0.7rem', fontWeight: 600, padding: '0.4rem 0.8rem', backdropFilter: 'blur(4px)' }}>
                        {blog.category || activeBlogCategory}
                      </span>
                    </div>
                    <div className="p-3 d-flex flex-column justify-content-between flex-grow-1">
                      <h3 className="h6 fw-bold mb-3 text-dark line-clamp-2" style={{ fontSize: '0.95rem', lineHeight: '1.5', minHeight: '2.85rem' }} title={blog.name}>
                        {blog.name}
                      </h3>
                      <div className="d-flex align-items-center text-muted gap-2" style={{ fontSize: '0.8rem' }}>
                        <div className="rounded-circle bg-light d-flex align-items-center justify-content-center" style={{ width: '24px', height: '24px' }}>
                           <span style={{ fontSize: '0.65rem', color: 'var(--nb-primary)' }}>NB</span>
                        </div>
                        <span className="fw-medium">NoBroker</span>
                        <span className="mx-1">•</span>
                        <span>{formattedDate}</span>
                      </div>
                    </div>
                  </div>
                </Link>
              </div>
            );
          });
        })()}
      </div>
    </div>
  );
};

export default BlogsSection;
