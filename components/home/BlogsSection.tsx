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
    <section className="classic-home-blog-section p-4 rounded-4 shadow-sm bg-white mb-4 border">
      {/* Header Row */}
      <div className="d-flex justify-content-between align-items-sm-end align-items-start mb-4 flex-wrap gap-3">
        <div>
          <div className="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill mb-2" style={{ backgroundColor: 'var(--nb-primary-soft)', border: '1px solid var(--nb-mint-deep)' }}>
            <Sparkles size={14} style={{ color: 'var(--nb-primary)' }} />
            <span className="fw-bold text-uppercase" style={{ color: 'var(--nb-primary)', fontSize: '0.75rem', letterSpacing: '0.5px' }}>
              Insights & Market Guides
            </span>
          </div>
          <h2 className="fw-bold m-0 text-dark mb-1" style={{ fontSize: '1.75rem', letterSpacing: '-0.02em' }}>
            From Our Blog
          </h2>
          <p className="text-secondary m-0" style={{ fontSize: '0.95rem' }}>
            Expert insights, verified property documentation tips, and local market trends.
          </p>
        </div>

        <Link
          href="/blog"
          className="btn rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm text-white"
          style={{ backgroundColor: 'var(--nb-primary)', borderColor: 'var(--nb-primary)', fontSize: '0.9rem', transition: 'all 0.2s ease' }}
        >
          <span>View All Articles</span>
          <ArrowRight size={16} />
        </Link>
      </div>

      {/* Filter Tabs */}
      <div className="mb-4 pb-2 border-bottom d-flex gap-2 overflow-auto" style={{ scrollbarWidth: 'none' }}>
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
              className={`btn btn-sm rounded-pill px-4 py-2 fw-semibold text-nowrap transition-all ${
                isActive
                  ? 'text-white shadow-sm'
                  : 'btn-light bg-light text-secondary border-0'
              }`}
              style={{
                fontSize: '0.85rem',
                ...(isActive ? { backgroundColor: 'var(--nb-primary)', borderColor: 'var(--nb-primary)' } : {})
              }}
              onClick={() => setActiveBlogCategory(tab.key as any)}
            >
              {tab.label}
            </button>
          );
        })}
      </div>

      {/* Grid of Cards */}
      <div className="row g-4">
        {loadingBlogs ? (
          Array.from({ length: 4 }).map((_, i) => (
            <div key={i} className="col-lg-3 col-sm-6 col-12 placeholder-glow">
              <div className="card border rounded-4 overflow-hidden h-100 shadow-sm">
                <div className="placeholder w-100" style={{ height: '180px' }}></div>
                <div className="card-body p-4">
                  <div className="placeholder col-6 mb-3"></div>
                  <div className="placeholder col-12 mb-2" style={{ height: '24px' }}></div>
                  <div className="placeholder col-9 mb-4"></div>
                  <div className="placeholder col-5"></div>
                </div>
              </div>
            </div>
          ))
        ) : displayBlogs.length === 0 ? (
          <div className="col-12 text-center py-5">
            <p className="text-muted mb-3">No articles found in this category.</p>
            <button
              type="button"
              className="btn rounded-pill px-4 py-2 fw-semibold"
              style={{ color: 'var(--nb-primary)', border: '1px solid var(--nb-primary)', backgroundColor: 'transparent' }}
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

            const cleanDesc = blog.description ? blog.description.replace(/<[^>]*>/g, '').trim() : '';
            const excerpt = cleanDesc.length > 20 ? cleanDesc : (blog.short_notes || cleanDesc);

            return (
              <div key={blog.id} className="col-lg-3 col-sm-6 col-12">
                <Link
                  href={`/blog/${blog.id}`}
                  className="text-decoration-none d-block h-100 group"
                >
                  <div
                    className="card h-100 border rounded-4 overflow-hidden bg-white d-flex flex-column"
                    style={{
                      boxShadow: '0 4px 12px rgba(0,0,0,0.05)',
                      transition: 'transform 0.3s ease, box-shadow 0.3s ease',
                      cursor: 'pointer'
                    }}
                    onMouseEnter={(e) => {
                      e.currentTarget.style.transform = 'translateY(-5px)';
                      e.currentTarget.style.boxShadow = '0 10px 20px rgba(0,0,0,0.1)';
                    }}
                    onMouseLeave={(e) => {
                      e.currentTarget.style.transform = 'translateY(0)';
                      e.currentTarget.style.boxShadow = '0 4px 12px rgba(0,0,0,0.05)';
                    }}
                  >
                    {/* Card Image */}
                    <div className="position-relative overflow-hidden" style={{ height: '180px' }}>
                      <img
                        src={initialImg}
                        alt={blog.name}
                        className="w-100 h-100 object-fit-cover"
                        style={{ transition: 'transform 0.5s ease' }}
                        onMouseEnter={(e) => e.currentTarget.style.transform = 'scale(1.05)'}
                        onMouseLeave={(e) => e.currentTarget.style.transform = 'scale(1)'}
                        loading="lazy"
                        onError={(e) => {
                          const target = e.currentTarget as HTMLImageElement;
                          if (target.src !== fallbackImg) {
                            target.src = fallbackImg;
                          }
                        }}
                      />
                      {/* Gradient Overlay */}
                      <div
                        className="position-absolute bottom-0 start-0 w-100"
                        style={{
                          height: '60%',
                          background: 'linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent)',
                          pointerEvents: 'none'
                        }}
                      />

                      {/* Top Category Badge */}
                      <span
                        className="position-absolute top-0 start-0 m-3 badge rounded-pill text-white text-truncate shadow-sm"
                        style={{
                          backgroundColor: 'var(--nb-primary)',
                          maxWidth: '70%',
                          fontSize: '0.7rem',
                          fontWeight: 600,
                          padding: '0.35rem 0.75rem',
                          letterSpacing: '0.02em'
                        }}
                        title={categoryLabel}
                      >
                        {categoryLabel}
                      </span>

                      {/* Top-Right Read Time */}
                      <span
                        className="position-absolute top-0 end-0 m-3 badge rounded-pill bg-white text-dark shadow-sm d-inline-flex align-items-center gap-1 flex-shrink-0"
                        style={{
                          fontSize: '0.7rem',
                          fontWeight: 600,
                          padding: '0.35rem 0.75rem'
                        }}
                      >
                        <Clock size={12} style={{ color: 'var(--nb-primary)' }} />
                        <span>{readTime} min</span>
                      </span>
                    </div>

                    {/* Card Body */}
                    <div className="p-4 d-flex flex-column flex-grow-1">
                      {/* Meta info */}
                      <div className="d-flex align-items-center justify-content-between text-secondary mb-3 gap-2" style={{ fontSize: '0.8rem' }}>
                        <span className="d-inline-flex align-items-center gap-1.5 fw-medium text-truncate" title={author}>
                          <User size={14} style={{ color: 'var(--nb-primary)' }} className="flex-shrink-0" />
                          <span className="text-truncate">{author}</span>
                        </span>
                        {formattedDate && (
                          <span className="d-inline-flex align-items-center gap-1.5 text-nowrap flex-shrink-0">
                            <Calendar size={14} style={{ color: 'var(--nb-primary)' }} className="flex-shrink-0" />
                            <span>{formattedDate}</span>
                          </span>
                        )}
                      </div>

                      {/* Title */}
                      <h3
                        className="fw-bold mb-3 text-dark"
                        style={{
                          fontSize: '1.05rem',
                          lineHeight: '1.4',
                          display: '-webkit-box',
                          WebkitLineClamp: 2,
                          WebkitBoxOrient: 'vertical',
                          overflow: 'hidden'
                        }}
                        title={blog.name}
                      >
                        {blog.name}
                      </h3>

                      {/* Excerpt */}
                      {excerpt ? (
                        <p
                          className="text-secondary mb-4 flex-grow-1"
                          style={{
                            fontSize: '0.85rem',
                            lineHeight: '1.5',
                            display: '-webkit-box',
                            WebkitLineClamp: 2,
                            WebkitBoxOrient: 'vertical',
                            overflow: 'hidden'
                          }}
                          title={excerpt}
                        >
                          {excerpt}
                        </p>
                      ) : (
                        <div className="flex-grow-1 mb-4" />
                      )}

                      {/* Card Footer CTA */}
                      <div className="pt-3 mt-auto border-top border-light d-flex align-items-center justify-content-between">
                        <span
                          className="fw-bold d-inline-flex align-items-center gap-2 transition-all"
                          style={{ color: 'var(--nb-primary)', fontSize: '0.85rem' }}
                        >
                          <span>Read Full Story</span>
                          <ArrowRight size={16} />
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
