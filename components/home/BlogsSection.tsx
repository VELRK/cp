'use client';

import React from 'react';
import Link from 'next/link';
import { ArrowRight, User, Calendar, Clock, Sparkles } from 'lucide-react';
import { toFrontendAssetUrl } from '@/lib/cityImages';
import {
  getBlogCategoryLabel,
  getBlogFallbackImage,
  getBlogReadTime
} from '@/lib/blogUtils';

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
  slug?: string;
}

interface BlogsSectionProps {
  blogs: Blog[];
  loadingBlogs: boolean;
  activeBlogCategory: 'all' | 'news' | 'tax' | 'guide' | 'investment';
  setActiveBlogCategory: (val: 'all' | 'news' | 'tax' | 'guide' | 'investment') => void;
}

const BlogsSection: React.FC<BlogsSectionProps> = ({
  blogs,
  loadingBlogs,
  activeBlogCategory,
  setActiveBlogCategory
}) => {
  const filtered = blogs.filter((blog) => {
    if (activeBlogCategory === 'all') return true;
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

  const displayBlogs = filtered.length > 0 ? filtered.slice(0, 4) : (activeBlogCategory === 'all' ? blogs.slice(0, 4) : []);

  return (
    <section className="classic-home-blog-section p-3 p-md-4 rounded-4 shadow-sm bg-white mb-4" style={{ border: '1px solid #e2e8f0' }}>
      {/* Header Row */}
      <div className="d-flex justify-content-between align-items-sm-end align-items-start mb-3 flex-wrap gap-2">
        <div>
          <div className="d-inline-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill mb-1.5" style={{ backgroundColor: '#eff6ff', border: '1px solid #bfdbfe' }}>
            <Sparkles size={12} className="text-primary" />
            <span className="fw-bold text-primary text-uppercase" style={{ fontSize: '0.68rem', letterSpacing: '0.5px' }}>
              Insights & Market Guides
            </span>
          </div>
          <h2 className="fw-bold m-0 text-dark" style={{ fontSize: 'clamp(1.2rem, 1.8vw, 1.45rem)', letterSpacing: '-0.025em', color: '#0f172a' }}>
            From Our Blog
          </h2>
          <p className="text-muted m-0 mt-1" style={{ fontSize: 'clamp(0.8rem, 1vw, 0.86rem)' }}>
            Expert insights, verified property documentation tips, and local market trends.
          </p>
        </div>

        <Link
          href="/blog"
          className="btn btn-outline-primary btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-none"
          style={{ fontSize: '0.8rem', transition: 'all 0.2s ease' }}
        >
          <span>View All Articles</span>
          <ArrowRight size={14} />
        </Link>
      </div>

      {/* Filter Tabs */}
      <div className="mb-3 pb-1 border-bottom d-flex gap-2 overflow-auto" style={{ scrollbarWidth: 'none' }}>
        {[
          { key: 'all', label: 'All Articles' },
          { key: 'news', label: 'Market News' },
          { key: 'tax', label: 'Tax & Legal' },
          { key: 'guide', label: 'Help Guides' },
          { key: 'investment', label: 'Investment' }
        ].map((tab) => {
          const isActive = activeBlogCategory === tab.key;
          return (
            <button
              key={tab.key}
              type="button"
              className={`btn btn-sm rounded-pill px-3 py-1.5 fw-semibold text-nowrap transition-all ${
                isActive
                  ? 'btn-primary text-white shadow-sm'
                  : 'btn-light bg-light text-secondary border-0'
              }`}
              style={{ fontSize: '0.8rem' }}
              onClick={() => setActiveBlogCategory(tab.key as any)}
            >
              {tab.label}
            </button>
          );
        })}
      </div>

      {/* Grid of Cards */}
      <div className="row g-3">
        {loadingBlogs ? (
          Array.from({ length: 4 }).map((_, i) => (
            <div key={i} className="col-lg-3 col-sm-6 col-12 placeholder-glow">
              <div className="card border-0 rounded-3 overflow-hidden h-100 shadow-sm" style={{ border: '1px solid #e2e8f0' }}>
                <div className="placeholder w-100" style={{ height: '160px', backgroundColor: '#e2e8f0' }}></div>
                <div className="card-body p-3">
                  <div className="placeholder col-6 mb-2"></div>
                  <div className="placeholder col-12 mb-2" style={{ height: '20px' }}></div>
                  <div className="placeholder col-9 mb-3"></div>
                  <div className="placeholder col-5"></div>
                </div>
              </div>
            </div>
          ))
        ) : displayBlogs.length === 0 ? (
          <div className="col-12 text-center py-5">
            <p className="text-muted small mb-2">No articles found in this category.</p>
            <button
              type="button"
              className="btn btn-sm btn-outline-primary rounded-pill px-3"
              onClick={() => setActiveBlogCategory('all')}
            >
              View all articles
            </button>
          </div>
        ) : (
          displayBlogs.map((blog) => {
            const fallbackImg = getBlogFallbackImage(blog.category, blog.name);
            const initialImg = blog.image ? toFrontendAssetUrl(blog.image) : fallbackImg;
            const categoryLabel = getBlogCategoryLabel(blog.category, blog.name);
            const readTime = getBlogReadTime(blog.description || blog.short_notes);
            const formattedDate = blog.date
              ? new Date(blog.date).toLocaleDateString('en-IN', { month: 'short', day: '2-digit', year: 'numeric' })
              : '';
            const author = blog.author && blog.author.toLowerCase() !== 'nobroker' ? blog.author : 'Editorial Desk';
            
            // Prefer rich article description for excerpt; fallback to short_notes or cleanDesc
            const cleanDesc = blog.description ? blog.description.replace(/<[^>]*>/g, '').trim() : '';
            const excerpt = cleanDesc.length > 20 ? cleanDesc : (blog.short_notes || cleanDesc);

            return (
              <div key={blog.id} className="col-lg-3 col-sm-6 col-12">
                <Link
                  href={`/blog/${blog.id}`}
                  className="text-decoration-none d-block h-100 classic-home-blog-card"
                >
                  <div
                    className="card h-100 border-0 rounded-3 overflow-hidden bg-white d-flex flex-column"
                    style={{
                      border: '1px solid #e2e8f0',
                      boxShadow: '0 1px 3px rgba(0,0,0,0.04)',
                      transition: 'all 0.3s cubic-bezier(0.16, 1, 0.3, 1)'
                    }}
                  >
                    {/* Card Image */}
                    <div className="position-relative overflow-hidden" style={{ height: '160px', backgroundColor: '#0f172a' }}>
                      <img
                        src={initialImg}
                        alt={blog.name}
                        className="w-100 h-100 object-fit-cover classic-card-img"
                        loading="lazy"
                        onError={(e) => {
                          const target = e.currentTarget as HTMLImageElement;
                          if (target.src !== fallbackImg) {
                            target.src = fallbackImg;
                          }
                        }}
                      />
                      {/* Dark Gradient Overlay at bottom of image */}
                      <div
                        className="position-absolute bottom-0 start-0 w-100"
                        style={{
                          height: '45%',
                          background: 'linear-gradient(to top, rgba(15, 23, 42, 0.65), transparent)',
                          pointerEvents: 'none'
                        }}
                      />

                      {/* Top Category Badge */}
                      <span
                        className="position-absolute top-0 start-0 m-2 badge rounded-pill text-truncate"
                        style={{
                          maxWidth: '60%',
                          backgroundColor: 'rgba(15, 23, 42, 0.82)',
                          color: '#ffffff',
                          fontSize: '0.66rem',
                          fontWeight: 600,
                          padding: '0.28rem 0.58rem',
                          backdropFilter: 'blur(6px)',
                          letterSpacing: '0.02em',
                          border: '1px solid rgba(255,255,255,0.18)'
                        }}
                        title={categoryLabel}
                      >
                        {categoryLabel}
                      </span>

                      {/* Top-Right Read Time */}
                      <span
                        className="position-absolute top-0 end-0 m-2 badge rounded-pill d-inline-flex align-items-center gap-1 flex-shrink-0"
                        style={{
                          backgroundColor: 'rgba(15, 23, 42, 0.75)',
                          color: '#ffffff',
                          fontSize: '0.64rem',
                          fontWeight: 500,
                          padding: '0.28rem 0.52rem',
                          backdropFilter: 'blur(6px)',
                          border: '1px solid rgba(255,255,255,0.15)'
                        }}
                      >
                        <Clock size={10} />
                        <span>{readTime} min</span>
                      </span>
                    </div>

                    {/* Card Body */}
                    <div className="p-3 d-flex flex-column flex-grow-1">
                      {/* Meta info: Author on left, Date on right - Guaranteed no awkward wrapping */}
                      <div
                        className="d-flex align-items-center justify-content-between text-muted mb-2 gap-1"
                        style={{ fontSize: '0.72rem', minWidth: 0 }}
                      >
                        <span
                          className="d-inline-flex align-items-center gap-1.5 text-secondary fw-medium text-truncate"
                          style={{ minWidth: 0, maxWidth: '60%' }}
                          title={author}
                        >
                          <User size={11} className="text-muted flex-shrink-0" />
                          <span className="text-truncate">{author}</span>
                        </span>
                        {formattedDate && (
                          <span
                            className="d-inline-flex align-items-center gap-1 text-muted text-nowrap flex-shrink-0"
                            style={{ fontSize: '0.71rem' }}
                          >
                            <Calendar size={11} className="text-muted flex-shrink-0" />
                            <span>{formattedDate}</span>
                          </span>
                        )}
                      </div>

                      {/* Title - Consistent 2-line height */}
                      <h3
                        className="fw-bold mb-2 text-dark classic-blog-title"
                        style={{
                          fontSize: 'clamp(0.86rem, 0.82rem + 0.15vw, 0.92rem)',
                          lineHeight: '1.38',
                          height: '2.55rem',
                          display: '-webkit-box',
                          WebkitLineClamp: 2,
                          WebkitBoxOrient: 'vertical',
                          overflow: 'hidden',
                          color: '#0f172a'
                        }}
                        title={blog.name}
                      >
                        {blog.name}
                      </h3>

                      {/* Excerpt - Consistent 2-line height */}
                      {excerpt ? (
                        <p
                          className="text-secondary small mb-3"
                          style={{
                            fontSize: '0.78rem',
                            lineHeight: '1.45',
                            height: '2.3rem',
                            display: '-webkit-box',
                            WebkitLineClamp: 2,
                            WebkitBoxOrient: 'vertical',
                            overflow: 'hidden',
                            color: '#64748b'
                          }}
                          title={excerpt}
                        >
                          {excerpt}
                        </p>
                      ) : (
                        <div style={{ height: '2.3rem' }} className="mb-3" />
                      )}

                      {/* Card Footer CTA - Pinned to bottom cleanly */}
                      <div className="pt-2.5 mt-auto border-top d-flex align-items-center justify-content-between" style={{ borderColor: '#f1f5f9' }}>
                        <span
                          className="fw-bold small d-inline-flex align-items-center gap-1 classic-blog-cta"
                          style={{ color: '#0284c7', fontSize: '0.78rem' }}
                        >
                          <span>Read Full Story</span>
                          <ArrowRight size={13} className="classic-arrow-icon" />
                        </span>
                      </div>
                    </div>
                  </div>
                </Link>
              </div>
            );
          })
        )}
      </div>
    </section>
  );
};

export default BlogsSection;
