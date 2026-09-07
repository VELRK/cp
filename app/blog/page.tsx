'use client';

import React, { useState, useEffect, useMemo } from 'react';
import Link from 'next/link';
import { getBlogs } from '@/lib/frontendApi';
import { toFrontendAssetUrl } from '@/lib/cityImages';
import {
  getBlogCategoryLabel,
  getBlogFallbackImage,
  getBlogReadTime
} from '@/lib/blogUtils';
import {
  BookOpen,
  User,
  Calendar,
  Clock,
  ArrowLeft,
  ArrowRight,
  Search,
  Sparkles,
  TrendingUp,
  FileText,
  HelpCircle,
  Briefcase,
  X,
  Share2,
  Bookmark
} from 'lucide-react';

interface Blog {
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

export default function BlogIndex() {
  const [blogs, setBlogs] = useState<Blog[]>([]);
  const [loading, setLoading] = useState(true);
  const [activeCategory, setActiveCategory] = useState<'all' | 'news' | 'tax' | 'guide' | 'investment'>('all');
  const [searchQuery, setSearchQuery] = useState('');

  useEffect(() => {
    getBlogs()
      .then((res) => {
        if (Array.isArray(res.data)) {
          setBlogs(res.data);
        }
      })
      .catch((e) => console.warn('Could not fetch blogs', e))
      .finally(() => setLoading(false));
  }, []);

  // Filter blogs based on activeCategory and searchQuery
  const filteredBlogs = useMemo(() => {
    return blogs.filter((blog) => {
      const cat = (blog.category || '').toLowerCase();
      const name = (blog.name || '').toLowerCase();
      const desc = (blog.description || '').toLowerCase();
      const notes = (blog.short_notes || '').toLowerCase();
      const query = searchQuery.trim().toLowerCase();

      // Search match
      if (query && !name.includes(query) && !desc.includes(query) && !notes.includes(query) && !cat.includes(query)) {
        return false;
      }

      // Category match
      if (activeCategory === 'all') return true;
      if (activeCategory === 'news') {
        return cat.includes('news') || (!cat.includes('tax') && !cat.includes('legal') && !cat.includes('guide') && !cat.includes('help') && !cat.includes('invest'));
      }
      if (activeCategory === 'tax') {
        return cat.includes('tax') || cat.includes('legal') || name.includes('tax') || name.includes('legal') || name.includes('regist') || name.includes('rera') || name.includes('stamp');
      }
      if (activeCategory === 'guide') {
        return cat.includes('guide') || cat.includes('help') || name.includes('guide') || name.includes('checklist') || name.includes('tips') || name.includes('how to') || name.includes('beginners');
      }
      if (activeCategory === 'investment') {
        return cat.includes('invest') || name.includes('invest') || name.includes('construction') || name.includes('buy') || name.includes('market') || name.includes('cost');
      }
      return true;
    });
  }, [blogs, activeCategory, searchQuery]);

  // Spotlight featured blog (first blog in list if no active search)
  const featuredBlog = useMemo(() => {
    if (filteredBlogs.length > 0 && activeCategory === 'all' && !searchQuery.trim()) {
      return filteredBlogs[0];
    }
    return null;
  }, [filteredBlogs, activeCategory, searchQuery]);

  // Grid blogs (excluding the featured one when spotlight is shown)
  const gridBlogs = useMemo(() => {
    if (featuredBlog) {
      return filteredBlogs.slice(1);
    }
    return filteredBlogs;
  }, [filteredBlogs, featuredBlog]);

  return (
    <div style={{ background: '#f8fafc', minHeight: '100vh', paddingBottom: '6rem' }}>

      {/* 1. Classic Luxury Hero Header */}
      <section className="classic-blog-hero-section position-relative overflow-hidden" style={{ background: 'linear-gradient(135deg, #0b1d3a 0%, #102a54 50%, #0d3b66 100%)', color: '#ffffff', padding: '3.5rem 0 4rem' }}>
        {/* Subtle Decorative Ambient Lighting */}
        <div className="position-absolute top-0 start-50 translate-middle-x pointer-events-none" style={{ width: '800px', height: '350px', background: 'radial-gradient(circle, rgba(0, 180, 216, 0.15) 0%, transparent 70%)', filter: 'blur(40px)', zIndex: 0 }} />

        <div className="container position-relative z-1">
          {/* Navigation and Breadcrumbs */}
          <div className="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <Link
              href="/"
              className="text-white-50 text-decoration-none small d-inline-flex align-items-center gap-1.5 py-1 px-3 rounded-pill"
              style={{ backgroundColor: 'rgba(255, 255, 255, 0.1)', backdropFilter: 'blur(8px)', border: '1px solid rgba(255, 255, 255, 0.15)', transition: 'all 0.2s ease' }}
            >
              <ArrowLeft size={14} />
              <span>Back to Home</span>
            </Link>

            <nav aria-label="breadcrumb">
              <ol className="breadcrumb small m-0 text-white-50">
                <li className="breadcrumb-item"><Link href="/" className="text-white-50 text-decoration-none">Home</Link></li>
                <li className="breadcrumb-item active text-white" aria-current="page">Realty Journal</li>
              </ol>
            </nav>
          </div>

          <div className="row align-items-center g-4">
            <div className="col-lg-7">
              <div className="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill mb-3" style={{ backgroundColor: 'rgba(0, 180, 216, 0.18)', border: '1px solid rgba(0, 180, 216, 0.35)' }}>
                <Sparkles size={14} className="text-warning" />
                <span className="small fw-bold text-white text-uppercase" style={{ letterSpacing: '0.8px', fontSize: '0.72rem' }}>
                  Realty Journal & Expert Guides
                </span>
              </div>

              <h1 className="display-6 fw-bold text-white mb-3" style={{ lineHeight: '1.25', letterSpacing: '-0.5px' }}>
                Real Estate Insights, Market Trends & Guides
              </h1>

              <p className="text-white-50 lead mb-0" style={{ fontSize: '1rem', lineHeight: '1.6', maxWidth: '620px' }}>
                Verified property documentation advice, local circle rates, market analysis, and investment strategies from our real estate research team.
              </p>
            </div>

            {/* Quick Search Box */}
            <div className="col-lg-5 d-flex justify-content-lg-end">
              <div className="position-relative w-100" style={{ maxWidth: '420px' }}>
                <Search size={18} className="position-absolute text-white-50" style={{ top: '50%', transform: 'translateY(-50%)', left: '16px', pointerEvents: 'none' }} />
                <input
                  type="text"
                  className="form-control text-white"
                  placeholder="Search articles, legal guides, topics..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  style={{
                    backgroundColor: 'rgba(255, 255, 255, 0.12)',
                    backdropFilter: 'blur(12px)',
                    border: '1px solid rgba(255, 255, 255, 0.25)',
                    borderRadius: '50px',
                    padding: '0.75rem 2.8rem 0.75rem 2.75rem',
                    color: '#ffffff',
                    fontSize: '0.9rem'
                  }}
                />
                {searchQuery && (
                  <button
                    type="button"
                    className="btn btn-link position-absolute text-white-50 p-0"
                    style={{ top: '50%', transform: 'translateY(-50%)', right: '16px' }}
                    onClick={() => setSearchQuery('')}
                  >
                    <X size={16} />
                  </button>
                )}
              </div>
            </div>
          </div>
        </div>
      </section>

      <div className="container mt-4">

        {/* 2. Category Filter Tabs */}
        <div className="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-2 border-bottom">
          <div className="d-flex gap-2 overflow-auto pb-1 flex-grow-1" style={{ scrollbarWidth: 'none' }}>
            {[
              { key: 'all', label: 'All Articles', icon: BookOpen },
              { key: 'news', label: 'Market News', icon: TrendingUp },
              { key: 'tax', label: 'Tax & Legal', icon: FileText },
              { key: 'guide', label: 'Help Guides', icon: HelpCircle },
              { key: 'investment', label: 'Investment', icon: Briefcase }
            ].map((tab) => {
              const Icon = tab.icon;
              const isActive = activeCategory === tab.key;
              return (
                <button
                  key={tab.key}
                  type="button"
                  className={`btn btn-sm rounded-pill px-3.5 py-2 fw-semibold d-inline-flex align-items-center gap-1.5 text-nowrap transition-all ${
                    isActive
                      ? 'btn-primary text-white shadow-sm'
                      : 'btn-white bg-white text-secondary border'
                  }`}
                  style={{
                    fontSize: '0.85rem',
                    transition: 'all 0.25s ease'
                  }}
                  onClick={() => setActiveCategory(tab.key as any)}
                >
                  <Icon size={14} />
                  <span>{tab.label}</span>
                </button>
              );
            })}
          </div>

          <div className="text-muted small fw-medium text-nowrap">
            Showing <strong className="text-dark">{filteredBlogs.length}</strong> {filteredBlogs.length === 1 ? 'article' : 'articles'}
          </div>
        </div>

        {/* 3. Loading Skeleton */}
        {loading ? (
          <div className="row g-4">
            <div className="col-12 placeholder-glow">
              <div className="card border-0 rounded-4 overflow-hidden shadow-sm p-4 mb-4">
                <div className="row g-4">
                  <div className="col-lg-6">
                    <div className="placeholder w-100 rounded-3" style={{ height: '300px', backgroundColor: '#e2e8f0' }}></div>
                  </div>
                  <div className="col-lg-6 d-flex flex-column justify-content-center">
                    <div className="placeholder col-3 mb-3"></div>
                    <div className="placeholder col-10 mb-2" style={{ height: '28px' }}></div>
                    <div className="placeholder col-12 mb-2"></div>
                    <div className="placeholder col-8 mb-4"></div>
                    <div className="placeholder col-4" style={{ height: '36px' }}></div>
                  </div>
                </div>
              </div>
            </div>
            {Array.from({ length: 3 }).map((_, i) => (
              <div key={i} className="col-lg-4 col-md-6 placeholder-glow">
                <div className="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                  <div className="placeholder w-100" style={{ height: '200px', backgroundColor: '#e2e8f0' }}></div>
                  <div className="card-body p-4">
                    <div className="placeholder col-6 mb-3"></div>
                    <div className="placeholder col-12 mb-2"></div>
                    <div className="placeholder col-9 mb-4"></div>
                    <div className="placeholder col-4"></div>
                  </div>
                </div>
              </div>
            ))}
          </div>
        ) : filteredBlogs.length === 0 ? (
          /* Empty Search/Filter State */
          <div className="text-center py-5 bg-white border rounded-4 shadow-sm my-4 classic-fade-in">
            <div className="d-inline-flex align-items-center justify-content-center p-3 rounded-circle bg-light text-muted mb-3">
              <BookOpen size={36} />
            </div>
            <h3 className="h5 fw-bold text-dark mb-1">No Articles Found</h3>
            <p className="text-secondary small mb-4" style={{ maxWidth: '420px', margin: '0 auto' }}>
              {searchQuery
                ? `No articles matching "${searchQuery}". Try searching with different keywords or resetting your filters.`
                : 'No articles currently listed in this category.'}
            </p>
            <button
              type="button"
              className="btn btn-outline-primary btn-sm rounded-pill px-4 py-2 fw-semibold"
              onClick={() => {
                setActiveCategory('all');
                setSearchQuery('');
              }}
            >
              Reset All Filters
            </button>
          </div>
        ) : (
          <div className="classic-fade-in">

            {/* 4. Featured Spotlight Hero Card */}
            {featuredBlog && (
              <div className="mb-5">
                <Link
                  href={`/blog/${featuredBlog.id}`}
                  className="text-decoration-none d-block classic-featured-story-link"
                >
                  <div className="card border-0 rounded-4 overflow-hidden shadow-sm bg-white" style={{ border: '1px solid #e2e8f0' }}>
                    <div className="row g-0 align-items-stretch">
                      <div className="col-lg-6 position-relative overflow-hidden" style={{ minHeight: '320px', backgroundColor: '#0f172a' }}>
                        {(() => {
                          const featuredFallback = getBlogFallbackImage(featuredBlog.category, featuredBlog.name);
                          const featuredImg = featuredBlog.image ? toFrontendAssetUrl(featuredBlog.image) : featuredFallback;
                          const featuredCat = getBlogCategoryLabel(featuredBlog.category, featuredBlog.name);
                          const featuredReadTime = getBlogReadTime(featuredBlog.description || featuredBlog.short_notes);

                          return (
                            <>
                              <img
                                src={featuredImg}
                                alt={featuredBlog.name}
                                className="w-100 h-100 object-fit-cover classic-featured-img"
                                onError={(e) => {
                                  const target = e.currentTarget as HTMLImageElement;
                                  if (target.src !== featuredFallback) {
                                    target.src = featuredFallback;
                                  }
                                }}
                              />
                              <div
                                className="position-absolute bottom-0 start-0 w-100"
                                style={{
                                  height: '40%',
                                  background: 'linear-gradient(to top, rgba(15, 23, 42, 0.7), transparent)',
                                  pointerEvents: 'none'
                                }}
                              />
                              <span
                                className="position-absolute top-0 start-0 m-3.5 badge rounded-pill"
                                style={{
                                  backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                  color: '#ffffff',
                                  fontSize: '0.72rem',
                                  fontWeight: 700,
                                  padding: '0.45rem 0.85rem',
                                  backdropFilter: 'blur(6px)',
                                  letterSpacing: '0.4px',
                                  border: '1px solid rgba(255,255,255,0.15)'
                                }}
                              >
                                ★ Featured Story · {featuredCat}
                              </span>

                              <span
                                className="position-absolute top-0 end-0 m-3.5 badge rounded-pill d-inline-flex align-items-center gap-1"
                                style={{
                                  backgroundColor: 'rgba(0, 0, 0, 0.65)',
                                  color: '#ffffff',
                                  fontSize: '0.7rem',
                                  fontWeight: 500,
                                  padding: '0.45rem 0.75rem',
                                  backdropFilter: 'blur(4px)'
                                }}
                              >
                                <Clock size={12} />
                                <span>{featuredReadTime} min read</span>
                              </span>
                            </>
                          );
                        })()}
                      </div>

                      <div className="col-lg-6 d-flex flex-column justify-content-between p-4 p-md-5 bg-white">
                        <div>
                          <div className="d-flex align-items-center gap-3 text-muted small mb-3">
                            <span className="d-flex align-items-center gap-1.5 text-secondary">
                              <User size={14} className="text-muted" />
                              <span className="fw-medium">{featuredBlog.author && featuredBlog.author.toLowerCase() !== 'nobroker' ? featuredBlog.author : 'Editorial Desk'}</span>
                            </span>
                            {featuredBlog.date && (
                              <>
                                <span className="text-muted">•</span>
                                <span className="d-flex align-items-center gap-1.5">
                                  <Calendar size={14} className="text-muted" />
                                  <span>{new Date(featuredBlog.date).toLocaleDateString('en-IN', { month: 'short', day: '2-digit', year: 'numeric' })}</span>
                                </span>
                              </>
                            )}
                          </div>

                          <h2
                            className="h3 fw-bold mb-3 classic-featured-title"
                            style={{ lineHeight: '1.35', color: '#0f172a', letterSpacing: '-0.3px' }}
                          >
                            {featuredBlog.name}
                          </h2>

                          <p
                            className="text-secondary mb-4 line-clamp-3"
                            style={{ fontSize: '0.95rem', lineHeight: '1.65', color: '#475569' }}
                          >
                            {featuredBlog.short_notes || (featuredBlog.description ? featuredBlog.description.replace(/<[^>]*>/g, '').slice(0, 160) + '...' : '')}
                          </p>
                        </div>

                        <div className="pt-3 border-top d-flex align-items-center justify-content-between" style={{ borderColor: '#f1f5f9' }}>
                          <span className="fw-bold d-inline-flex align-items-center gap-2 text-primary">
                            <span>Read Full Story</span>
                            <ArrowRight size={16} className="classic-arrow-icon" />
                          </span>

                          <span className="badge rounded-pill bg-light text-secondary border px-3 py-1.5 small fw-normal">
                            Editor's Choice
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </Link>
              </div>
            )}

            {/* 5. Articles Grid */}
            {gridBlogs.length > 0 && (
              <>
                {featuredBlog && (
                  <div className="d-flex align-items-center justify-content-between mb-4">
                    <h3 className="h5 fw-bold text-dark m-0" style={{ color: '#0f172a' }}>
                      Latest Stories & Guides
                    </h3>
                  </div>
                )}

                <div className="row g-4">
                  {gridBlogs.map((blog) => {
                    const fallbackImg = getBlogFallbackImage(blog.category, blog.name);
                    const imageUrl = blog.image ? toFrontendAssetUrl(blog.image) : fallbackImg;
                    const formattedDate = blog.date
                      ? new Date(blog.date).toLocaleDateString('en-IN', { month: 'short', day: '2-digit', year: 'numeric' })
                      : '';
                    const categoryLabel = getBlogCategoryLabel(blog.category, blog.name);
                    const readTime = getBlogReadTime(blog.description || blog.short_notes);
                    const excerpt = blog.short_notes || (blog.description ? blog.description.replace(/<[^>]*>/g, '').slice(0, 110) + '...' : '');
                    const author = blog.author && blog.author.toLowerCase() !== 'nobroker' ? blog.author : 'Editorial Desk';

                    return (
                      <div key={blog.id} className="col-lg-4 col-md-6 col-12">
                        <Link href={`/blog/${blog.id}`} className="text-decoration-none d-block h-100 classic-grid-card-link">
                          <div className="card h-100 border-0 rounded-4 overflow-hidden bg-white d-flex flex-column" style={{ border: '1px solid #e2e8f0', boxShadow: '0 4px 15px rgba(0, 0, 0, 0.04)' }}>
                            {/* Card Image */}
                            <div className="position-relative overflow-hidden" style={{ height: '210px', backgroundColor: '#0f172a' }}>
                              <img
                                src={imageUrl}
                                alt={blog.name}
                                className="w-100 h-100 object-fit-cover classic-grid-img"
                                loading="lazy"
                                onError={(e) => {
                                  const target = e.currentTarget as HTMLImageElement;
                                  if (target.src !== fallbackImg) {
                                    target.src = fallbackImg;
                                  }
                                }}
                              />
                              <div
                                className="position-absolute bottom-0 start-0 w-100"
                                style={{
                                  height: '45%',
                                  background: 'linear-gradient(to top, rgba(15, 23, 42, 0.65), transparent)',
                                  pointerEvents: 'none'
                                }}
                              />

                              <span
                                className="position-absolute top-0 start-0 m-3 badge rounded-pill"
                                style={{
                                  backgroundColor: 'rgba(15, 23, 42, 0.88)',
                                  color: '#ffffff',
                                  fontSize: '0.7rem',
                                  fontWeight: 700,
                                  padding: '0.4rem 0.8rem',
                                  backdropFilter: 'blur(4px)',
                                  letterSpacing: '0.3px',
                                  border: '1px solid rgba(255,255,255,0.15)'
                                }}
                              >
                                {categoryLabel}
                              </span>

                              <span
                                className="position-absolute top-0 end-0 m-3 badge rounded-pill d-inline-flex align-items-center gap-1"
                                style={{
                                  backgroundColor: 'rgba(0, 0, 0, 0.65)',
                                  color: '#ffffff',
                                  fontSize: '0.68rem',
                                  fontWeight: 500,
                                  padding: '0.4rem 0.7rem',
                                  backdropFilter: 'blur(4px)'
                                }}
                              >
                                <Clock size={11} />
                                <span>{readTime} min</span>
                              </span>
                            </div>

                            {/* Card Body */}
                            <div className="p-4 d-flex flex-column justify-content-between flex-grow-1">
                              <div>
                                <div className="d-flex align-items-center text-muted gap-2 mb-2.5" style={{ fontSize: '0.78rem' }}>
                                  <span className="d-flex align-items-center gap-1.5 text-secondary fw-medium">
                                    <User size={13} className="text-muted" />
                                    <span>{author}</span>
                                  </span>
                                  {formattedDate && (
                                    <>
                                      <span className="text-muted">•</span>
                                      <span className="d-flex align-items-center gap-1.5">
                                        <Calendar size={13} className="text-muted" />
                                        <span>{formattedDate}</span>
                                      </span>
                                    </>
                                  )}
                                </div>

                                <h3
                                  className="h6 fw-bold text-dark mb-2.5 line-clamp-2 classic-grid-title"
                                  style={{ fontSize: '1.02rem', lineHeight: '1.45', minHeight: '2.9rem', color: '#0f172a' }}
                                  title={blog.name}
                                >
                                  {blog.name}
                                </h3>

                                {excerpt && (
                                  <p
                                    className="text-secondary small mb-4 line-clamp-2"
                                    style={{ fontSize: '0.85rem', lineHeight: '1.55', minHeight: '2.65rem', color: '#64748b' }}
                                  >
                                    {excerpt}
                                  </p>
                                )}
                              </div>

                              <div className="pt-3 mt-auto border-top d-flex align-items-center justify-content-between" style={{ borderColor: '#f1f5f9' }}>
                                <span className="fw-bold small d-inline-flex align-items-center gap-1.5 text-primary classic-read-link">
                                  <span>Read Full Post</span>
                                  <ArrowRight size={14} className="classic-arrow-icon" />
                                </span>
                              </div>
                            </div>
                          </div>
                        </Link>
                      </div>
                    );
                  })}
                </div>
              </>
            )}

          </div>
        )}

      </div>
    </div>
  );
}
