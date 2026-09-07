'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { getBlogs, getBlogById } from '@/lib/frontendApi';
import { toFrontendAssetUrl } from '@/lib/cityImages';
import {
  getBlogCategoryLabel,
  getBlogFallbackImage,
  getBlogReadTime
} from '@/lib/blogUtils';
import { useAuth } from '@/hooks/useAuth';
import {
  ArrowLeft,
  User,
  Calendar,
  Clock,
  Share2,
  Check,
  BookOpen,
  ArrowRight,
  Send,
  MessageSquare
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

interface Comment {
  id: string;
  name: string;
  email: string;
  content: string;
  date: string;
}

export default function BlogPostDetail({ id }: { id: string }) {
  const { user } = useAuth();

  const [blog, setBlog] = useState<Blog | null>(null);
  const [blogsList, setBlogsList] = useState<Blog[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Reading progress bar
  const [scrollProgress, setScrollProgress] = useState(0);
  const [isCopied, setIsCopied] = useState(false);

  // Comments state
  const [comments, setComments] = useState<Comment[]>([]);
  const [commentName, setCommentName] = useState('');
  const [commentEmail, setCommentEmail] = useState('');
  const [commentText, setCommentText] = useState('');
  const [commentSuccess, setCommentSuccess] = useState(false);

  // Selected gallery image modal
  const [activeGalleryImage, setActiveGalleryImage] = useState<string | null>(null);

  // Scroll listener for reading progress bar
  useEffect(() => {
    const handleScroll = () => {
      const totalScroll = document.documentElement.scrollTop || document.body.scrollTop;
      const windowHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
      if (windowHeight > 0) {
        setScrollProgress(Math.min(100, Math.max(0, (totalScroll / windowHeight) * 100)));
      }
    };
    window.addEventListener('scroll', handleScroll, { passive: true });
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  // Fetch blog data
  useEffect(() => {
    if (!id) return;
    setLoading(true);

    getBlogById(id)
      .then((res) => {
        if (res.data) {
          setBlog(res.data);
        } else {
          setError('Blog post not found.');
        }
      })
      .catch((e) => {
        console.warn('Could not fetch blog details', e);
        setError('Error loading blog post.');
      })
      .finally(() => setLoading(false));

    // Fetch other blogs for sidebar
    getBlogs()
      .then((res) => {
        if (Array.isArray(res.data)) {
          setBlogsList(res.data);
        }
      })
      .catch((e) => console.warn('Could not fetch blogs list', e));
  }, [id]);

  // Load article-specific comments from localStorage
  useEffect(() => {
    if (id) {
      const stored = localStorage.getItem(`blog-comments-${id}`);
      if (stored) {
        try {
          setComments(JSON.parse(stored));
        } catch (e) {
          console.warn('Failed to parse comments', e);
        }
      }
    }
  }, [id]);

  const handleCopyLink = () => {
    if (typeof window !== 'undefined') {
      navigator.clipboard.writeText(window.location.href);
      setIsCopied(true);
      setTimeout(() => setIsCopied(false), 2500);
    }
  };

  const handleCommentSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!commentText.trim()) return;

    const newComment: Comment = {
      id: `comment-${Date.now()}`,
      name: commentName.trim() || (user ? user.name : 'Reader'),
      email: commentEmail.trim() || (user ? user.email : ''),
      content: commentText.trim(),
      date: new Date().toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })
    };

    const updated = [newComment, ...comments];
    setComments(updated);
    if (id) {
      localStorage.setItem(`blog-comments-${id}`, JSON.stringify(updated));
    }

    setCommentText('');
    if (!user) {
      setCommentName('');
      setCommentEmail('');
    }
    setCommentSuccess(true);
    setTimeout(() => setCommentSuccess(false), 3000);
  };

  if (loading) {
    return (
      <div style={{ background: '#f8fafc', minHeight: '100vh', padding: '4rem 0' }}>
        <div className="container placeholder-glow" style={{ maxWidth: '850px' }}>
          <div className="placeholder col-3 mb-4 rounded" style={{ height: '24px' }}></div>
          <div className="placeholder col-10 mb-3 rounded" style={{ height: '42px' }}></div>
          <div className="placeholder col-5 mb-4 rounded" style={{ height: '20px' }}></div>
          <div className="placeholder w-100 rounded-4 mb-4" style={{ height: '380px', backgroundColor: '#e2e8f0' }}></div>
          <div className="placeholder col-12 mb-2"></div>
          <div className="placeholder col-12 mb-2"></div>
          <div className="placeholder col-8 mb-4"></div>
        </div>
      </div>
    );
  }

  if (error || !blog) {
    return (
      <div style={{ background: '#f8fafc', minHeight: '100vh', padding: '6rem 0' }}>
        <div className="container text-center classic-fade-in" style={{ maxWidth: '560px' }}>
          <div className="d-inline-flex align-items-center justify-content-center p-3 rounded-circle bg-danger bg-opacity-10 text-danger mb-3">
            <BookOpen size={40} />
          </div>
          <h2 className="fw-bold text-dark mb-2">Article Not Found</h2>
          <p className="text-secondary mb-4">
            {error || 'The requested article could not be loaded. It may have been moved or updated.'}
          </p>
          <Link href="/blog" className="btn btn-primary rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <ArrowLeft size={16} />
            <span>Return to Blogs</span>
          </Link>
        </div>
      </div>
    );
  }

  const fallbackImg = getBlogFallbackImage(blog.category, blog.name);
  const mainImage = blog.image
    ? toFrontendAssetUrl(blog.image)
    : fallbackImg;

  const formattedDate = blog.date
    ? new Date(blog.date).toLocaleDateString('en-IN', { month: 'long', day: '2-digit', year: 'numeric' })
    : '';

  const categoryLabel = getBlogCategoryLabel(blog.category, blog.name);
  const authorName = blog.author && blog.author.toLowerCase() !== 'nobroker' ? blog.author : 'Editorial Desk';
  const readingTime = getBlogReadTime(blog.description || blog.short_notes);

  // Recent blogs excluding current one
  const recentArticles = blogsList.filter(b => b.id !== blog.id).slice(0, 4);

  // Split description by paragraphs if plain text
  const rawText = (blog.description || '').replace(/<[^>]*>/g, ' ');
  const paragraphs = rawText
    .split(/\n\n+|\r\n\r\n+/)
    .map(p => p.trim())
    .filter(Boolean);

  const shareUrl = typeof window !== 'undefined' ? window.location.href : '';
  const shareTitle = encodeURIComponent(blog.name);

  return (
    <div style={{ background: '#f8fafc', minHeight: '100vh', paddingBottom: '5rem' }}>

      {/* Reading Progress Indicator */}
      <div
        className="classic-blog-progress-bar"
        style={{ width: `${scrollProgress}%` }}
        aria-hidden="true"
      />

      <div className="container pt-4 pt-md-5">

        {/* Breadcrumb Navigation & Back Link */}
        <div className="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 pb-2 border-bottom border-light">
          <Link
            href="/blog"
            className="text-decoration-none text-secondary fw-semibold small d-inline-flex align-items-center gap-1.5 py-1.5 px-3 rounded-pill bg-white border shadow-none"
            style={{ transition: 'all 0.2s ease' }}
          >
            <ArrowLeft size={15} />
            <span>All Articles</span>
          </Link>

          <nav aria-label="breadcrumb">
            <ol className="breadcrumb small m-0 text-muted">
              <li className="breadcrumb-item"><Link href="/" className="text-decoration-none text-secondary">Home</Link></li>
              <li className="breadcrumb-item"><Link href="/blog" className="text-decoration-none text-secondary">Blog</Link></li>
              <li className="breadcrumb-item active text-dark fw-medium text-truncate" aria-current="page" style={{ maxWidth: '240px' }}>
                {blog.name}
              </li>
            </ol>
          </nav>
        </div>

        {/* Main Article Grid */}
        <div className="row g-4 g-lg-5">

          {/* Core Article Column (Left) */}
          <div className="col-lg-8">
            <article className="classic-blog-article-card classic-fade-in bg-white p-4 p-md-5 rounded-4 shadow-sm" style={{ border: '1px solid #e2e8f0' }}>

              {/* Category & Meta Header */}
              <div className="d-flex flex-wrap align-items-center gap-3 mb-3">
                <span
                  className="badge rounded-pill text-uppercase px-3 py-1.5 fw-bold"
                  style={{
                    backgroundColor: 'rgba(2, 132, 199, 0.1)',
                    color: '#0284c7',
                    fontSize: '0.72rem',
                    letterSpacing: '0.5px'
                  }}
                >
                  {categoryLabel}
                </span>

                <div className="d-flex align-items-center gap-3 text-muted small">
                  <span className="d-flex align-items-center gap-1.5 text-secondary">
                    <User size={13} />
                    <span className="fw-medium">{authorName}</span>
                  </span>

                  {formattedDate && (
                    <span className="d-flex align-items-center gap-1.5">
                      <Calendar size={13} />
                      <span>{formattedDate}</span>
                    </span>
                  )}

                  <span className="d-flex align-items-center gap-1.5">
                    <Clock size={13} />
                    <span>{readingTime} min read</span>
                  </span>
                </div>
              </div>

              {/* Headline */}
              <h1
                className="h2 fw-bold mb-4"
                style={{
                  color: '#0f172a',
                  lineHeight: '1.3',
                  letterSpacing: '-0.5px'
                }}
              >
                {blog.name}
              </h1>

              {/* Hero Image */}
              <div className="position-relative overflow-hidden rounded-4 mb-4" style={{ height: '380px', backgroundColor: '#0f172a' }}>
                <img
                  src={mainImage}
                  alt={blog.name}
                  className="w-100 h-100 object-fit-cover"
                  onError={(e) => {
                    const target = e.currentTarget as HTMLImageElement;
                    if (target.src !== fallbackImg) {
                      target.src = fallbackImg;
                    }
                  }}
                />
              </div>

              {/* Lead Excerpt */}
              {blog.short_notes && (
                <div
                  className="p-3.5 mb-4 rounded-3 text-dark fw-medium"
                  style={{
                    backgroundColor: '#f1f5f9',
                    borderLeft: '4px solid #0284c7',
                    fontSize: '1.05rem',
                    lineHeight: '1.6'
                  }}
                >
                  "{blog.short_notes}"
                </div>
              )}

              {/* Article Content Body */}
              <div className="classic-blog-content" style={{ fontSize: '1.02rem', lineHeight: '1.8', color: '#334155' }}>
                {paragraphs.length > 0 ? (
                  paragraphs.map((para, idx) => (
                    <p key={idx} className="mb-4">{para}</p>
                  ))
                ) : (
                  <p className="mb-4">{blog.description}</p>
                )}
              </div>

              {/* Photo Gallery Showcase */}
              {blog.gallery && blog.gallery.length > 1 && (
                <div className="mt-5 pt-4 border-top border-light">
                  <h4 className="h6 fw-bold text-dark mb-3">Post Gallery & Visual Documentation</h4>
                  <div className="row g-3">
                    {blog.gallery.map((imgUrl, i) => {
                      const fullUrl = toFrontendAssetUrl(imgUrl);
                      return (
                        <div key={i} className="col-6 col-md-4">
                          <div
                            className="position-relative rounded-3 overflow-hidden"
                            style={{ height: '140px', cursor: 'pointer', backgroundColor: '#0f172a' }}
                            onClick={() => setActiveGalleryImage(fullUrl)}
                          >
                            <img
                              src={fullUrl}
                              alt={`Gallery ${i + 1}`}
                              className="w-100 h-100 object-fit-cover"
                              onError={(e) => {
                                (e.currentTarget as HTMLImageElement).src = fallbackImg;
                              }}
                            />
                          </div>
                        </div>
                      );
                    })}
                  </div>
                </div>
              )}

              {/* Social Sharing & Interaction Bar */}
              <div className="mt-5 pt-4 border-top d-flex flex-wrap align-items-center justify-content-between gap-3" style={{ borderColor: '#e2e8f0' }}>
                <div className="d-flex align-items-center gap-2">
                  <span className="small fw-semibold text-secondary">Share this article:</span>
                  <button
                    type="button"
                    className="btn btn-sm btn-outline-secondary rounded-pill px-3 d-inline-flex align-items-center gap-1.5"
                    onClick={handleCopyLink}
                  >
                    {isCopied ? <Check size={14} className="text-success" /> : <Share2 size={14} />}
                    <span>{isCopied ? 'Copied!' : 'Copy Link'}</span>
                  </button>

                  <a
                    href={`https://api.whatsapp.com/send?text=${shareTitle}%20${encodeURIComponent(shareUrl)}`}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="btn btn-sm btn-outline-success rounded-pill px-3"
                  >
                    WhatsApp
                  </a>

                  <a
                    href={`https://twitter.com/intent/tweet?text=${shareTitle}&url=${encodeURIComponent(shareUrl)}`}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="btn btn-sm btn-outline-info rounded-pill px-3"
                  >
                    Twitter / X
                  </a>
                </div>

                <Link
                  href="/blog"
                  className="btn btn-sm btn-light rounded-pill px-3 fw-medium text-secondary"
                >
                  More Articles →
                </Link>
              </div>

              {/* Comments Section */}
              <div className="mt-5 pt-4 border-top" style={{ borderColor: '#e2e8f0' }}>
                <div className="d-flex align-items-center justify-content-between mb-4">
                  <h3 className="h5 fw-bold text-dark m-0 d-flex align-items-center gap-2">
                    <MessageSquare size={18} className="text-primary" />
                    <span>Reader Discussion ({comments.length})</span>
                  </h3>
                </div>

                {commentSuccess && (
                  <div className="alert alert-success py-2 px-3 small rounded-3 mb-4">
                    Thank you! Your comment has been posted.
                  </div>
                )}

                {/* Comment Input Form */}
                <form onSubmit={handleCommentSubmit} className="mb-4">
                  {!user && (
                    <div className="row g-2 mb-2">
                      <div className="col-md-6">
                        <input
                          type="text"
                          className="form-control form-control-sm rounded-3"
                          placeholder="Your Name"
                          value={commentName}
                          onChange={(e) => setCommentName(e.target.value)}
                        />
                      </div>
                      <div className="col-md-6">
                        <input
                          type="email"
                          className="form-control form-control-sm rounded-3"
                          placeholder="Your Email (optional)"
                          value={commentEmail}
                          onChange={(e) => setCommentEmail(e.target.value)}
                        />
                      </div>
                    </div>
                  )}

                  <div className="mb-2">
                    <textarea
                      rows={3}
                      className="form-control rounded-3"
                      placeholder="Add to the discussion or ask a property query..."
                      value={commentText}
                      onChange={(e) => setCommentText(e.target.value)}
                      required
                      style={{ fontSize: '0.9rem' }}
                    />
                  </div>

                  <button
                    type="submit"
                    className="btn btn-primary btn-sm rounded-pill px-4 fw-semibold d-inline-flex align-items-center gap-1.5"
                  >
                    <Send size={13} />
                    <span>Post Comment</span>
                  </button>
                </form>

                {/* Existing Comments List */}
                {comments.length > 0 ? (
                  <div className="d-flex flex-column gap-3">
                    {comments.map((c) => (
                      <div key={c.id} className="p-3 bg-light rounded-3">
                        <div className="d-flex justify-content-between align-items-center mb-1">
                          <span className="fw-bold small text-dark">{c.name}</span>
                          <span className="text-muted" style={{ fontSize: '0.75rem' }}>{c.date}</span>
                        </div>
                        <p className="m-0 text-secondary small" style={{ lineHeight: '1.5' }}>
                          {c.content}
                        </p>
                      </div>
                    ))}
                  </div>
                ) : (
                  <p className="text-muted small text-center py-3 m-0">
                    No comments yet. Be the first to share your thoughts on this topic!
                  </p>
                )}
              </div>

            </article>
          </div>

          {/* Classic Sidebar Column (Right) */}
          <div className="col-lg-4">
            <div className="d-flex flex-column gap-4" style={{ position: 'sticky', top: '90px' }}>

              {/* Recent Articles Card */}
              <div className="bg-white p-4 rounded-4 shadow-sm" style={{ border: '1px solid #e2e8f0' }}>
                <h3 className="h6 fw-bold text-dark pb-2 mb-3 border-bottom" style={{ letterSpacing: '-0.3px' }}>
                  Recent Articles
                </h3>

                <div className="d-flex flex-column gap-3">
                  {recentArticles.length > 0 ? (
                    recentArticles.map((item) => {
                      const itemFallback = getBlogFallbackImage(item.category, item.name);
                      const thumbUrl = item.image
                        ? toFrontendAssetUrl(item.image)
                        : itemFallback;
                      const itemDate = item.date
                        ? new Date(item.date).toLocaleDateString('en-IN', { month: 'short', day: '2-digit' })
                        : '';
                      return (
                        <Link
                          key={item.id}
                          href={`/blog/${item.id}`}
                          className="text-decoration-none d-flex align-items-center gap-3 p-2 rounded-3 text-dark"
                          style={{ transition: 'background-color 0.2s ease' }}
                        >
                          <div className="rounded-3 overflow-hidden flex-shrink-0" style={{ width: '64px', height: '64px', backgroundColor: '#0f172a' }}>
                            <img
                              src={thumbUrl}
                              alt={item.name}
                              className="w-100 h-100 object-fit-cover"
                              onError={(e) => {
                                (e.currentTarget as HTMLImageElement).src = itemFallback;
                              }}
                            />
                          </div>
                          <div className="min-w-0 flex-grow-1">
                            <h4
                              className="fw-bold text-dark m-0 line-clamp-2"
                              style={{ fontSize: '0.86rem', lineHeight: '1.4' }}
                              title={item.name}
                            >
                              {item.name}
                            </h4>
                            {itemDate && (
                              <span className="text-muted" style={{ fontSize: '0.74rem' }}>
                                {itemDate}
                              </span>
                            )}
                          </div>
                        </Link>
                      );
                    })
                  ) : (
                    <p className="text-muted small m-0">No other articles available.</p>
                  )}
                </div>
              </div>

              {/* Explore Topics Pill List */}
              <div className="bg-white p-4 rounded-4 shadow-sm" style={{ border: '1px solid #e2e8f0' }}>
                <h3 className="h6 fw-bold text-dark pb-2 mb-3 border-bottom" style={{ letterSpacing: '-0.3px' }}>
                  Explore Topics
                </h3>
                <div className="d-flex flex-wrap gap-2">
                  {[
                    { label: 'All Articles', href: '/blog' },
                    { label: 'Market News', href: '/blog' },
                    { label: 'Tax & Legal', href: '/blog' },
                    { label: 'Help Guides', href: '/blog' },
                    { label: 'Investment & Trends', href: '/blog' }
                  ].map((topic, i) => (
                    <Link
                      key={i}
                      href={topic.href}
                      className="badge rounded-pill text-decoration-none text-secondary bg-light border px-3 py-2 fw-medium"
                      style={{ fontSize: '0.78rem', transition: 'all 0.2s ease' }}
                    >
                      {topic.label}
                    </Link>
                  ))}
                </div>
              </div>

              {/* Real Estate Property Search Advisory */}
              <div
                className="rounded-4 text-white p-4 shadow-sm"
                style={{
                  background: 'linear-gradient(135deg, #0c2340 0%, #173d6d 100%)'
                }}
              >
                <div className="small text-uppercase fw-bold text-warning mb-2" style={{ letterSpacing: '0.8px', fontSize: '0.7rem' }}>
                  Verified Listings
                </div>
                <h4 className="fw-bold mb-2 h6" style={{ lineHeight: '1.4' }}>
                  Find Verified Homes & Commercial Properties
                </h4>
                <p className="small text-white-50 mb-3" style={{ fontSize: '0.82rem', lineHeight: '1.5' }}>
                  Explore thousands of verified properties with direct owner contact and zero brokerage.
                </p>
                <Link
                  href="/search"
                  className="btn btn-warning btn-sm rounded-pill fw-bold text-dark px-3.5 py-1.5 d-inline-flex align-items-center gap-1"
                  style={{ fontSize: '0.82rem' }}
                >
                  <span>Browse Properties</span>
                  <ArrowRight size={13} />
                </Link>
              </div>

            </div>
          </div>

        </div>

      </div>

      {/* Lightbox Modal for Gallery Images */}
      {activeGalleryImage && (
        <div
          className="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center p-3"
          style={{ backgroundColor: 'rgba(0,0,0,0.85)', zIndex: 10000 }}
          onClick={() => setActiveGalleryImage(null)}
        >
          <div className="position-relative" style={{ maxWidth: '90vw', maxHeight: '90vh' }}>
            <img
              src={activeGalleryImage}
              alt="Expanded view"
              className="rounded-4 img-fluid shadow-lg"
              style={{ maxHeight: '85vh', objectFit: 'contain' }}
            />
          </div>
        </div>
      )}

    </div>
  );
}
