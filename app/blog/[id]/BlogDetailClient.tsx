'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { getBlogs, getBlogById } from '@/lib/frontendApi';
import { useAuth } from '../../../components/AuthContext';
import {
  ArrowLeft,
  User,
  Calendar,
  BookOpen,
  Heart,
  Eye,
  Share2,
  ThumbsUp,
  ChevronDown,
  ChevronUp,
  Plus,
  X,
  Send,
  Smartphone,
  Star,
  MapPin,
  Check
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
}

interface Comment {
  id: string;
  name: string;
  email: string;
  content: string;
  date: string;
}

export default function BlogPostDetail({ id }: { id: string }) {
  const { user, setAuthModalOpen } = useAuth();

  const [blog, setBlog] = useState<Blog | null>(null);
  const [blogsList, setBlogsList] = useState<Blog[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Custom interactive states
  const [likesCount, setLikesCount] = useState(12);
  const [hasLiked, setHasLiked] = useState(false);
  const [sharesCount, setSharesCount] = useState(4);
  const [viewsCount, setViewsCount] = useState(254);
  const [showContents, setShowContents] = useState(true);
  const [isCopied, setIsCopied] = useState(false);

  // Newsletter
  const [newsletterEmail, setNewsletterEmail] = useState('');
  const [newsletterSubscribed, setNewsletterSubscribed] = useState(false);

  // Comments
  const [comments, setComments] = useState<Comment[]>([]);
  const [commentName, setCommentName] = useState('');
  const [commentEmail, setCommentEmail] = useState('');
  const [commentText, setCommentText] = useState('');

  // Scroll triggered popup
  const [showPromoPopup, setShowPromoPopup] = useState(false);

  // Fetch blog data
  useEffect(() => {
    if (!id) return;

    getBlogById(id)
      .then((res) => {
        if (res.data) {
          setBlog(res.data);
          // Set deterministic mock counts based on blog ID
          setLikesCount(12 + (Number(id) % 7) * 4);
          setSharesCount(4 + (Number(id) % 3) * 2);
          setViewsCount(250 + (Number(id) % 10) * 42);
        } else {
          setError('Blog post not found.');
        }
      })
      .catch((e) => {
        console.warn('Could not fetch blog details', e);
        setError('Error loading blog post.');
      })
      .finally(() => setLoading(false));

    // Fetch lists for sidebar widgets
    getBlogs()
      .then((res) => {
        if (Array.isArray(res.data)) {
          setBlogsList(res.data);
        }
      })
      .catch((e) => console.warn('Could not fetch blogs list', e));
  }, [id]);

  // Load comments from localstorage
  useEffect(() => {
    if (id) {
      const stored = localStorage.getItem(`nb-comments-${id}`);
      if (stored) {
        try {
          setComments(jsonDecode(stored));
        } catch (e) {
          console.warn('Failed to parse comments', e);
        }
      } else {
        // Mock default comments
        const mock = [
          {
            id: 'mock-1',
            name: 'Rajesh Kumar',
            email: 'rajesh@example.com',
            content: 'Very informative article! Property tax revisions in Coimbatore were long overdue. The detailed zones breakdown is extremely helpful.',
            date: new Date(Date.now() - 3600000 * 24 * 2).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })
          },
          {
            id: 'mock-2',
            name: 'Priya Sundar',
            email: 'priya@example.com',
            content: 'Is there any online calculator specifically updated for this new regime? Thanks in advance.',
            date: new Date(Date.now() - 3600000 * 12).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })
          }
        ];
        setComments(mock);
        localStorage.setItem(`nb-comments-${id}`, JSON.stringify(mock));
      }
    }
  }, [id]);

  // Helper function to decode JSON list safely
  function jsonDecode(str: string) {
    return JSON.parse(str);
  }

  // Scroll handler for promo popup
  useEffect(() => {
    const handleScroll = () => {
      const scrolled = window.scrollY;
      const docHeight = document.documentElement.scrollHeight - window.innerHeight;
      if (docHeight > 0 && scrolled / docHeight > 0.25) {
        setShowPromoPopup(true);
      }
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
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

  const handleNewsletterSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (newsletterEmail.trim() === '') return;
    setNewsletterSubscribed(true);
    setNewsletterEmail('');
  };

  const handleCommentSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (commentText.trim() === '') return;

    const newComment: Comment = {
      id: `comment-${Date.now()}`,
      name: commentName.trim() || (user ? user.name : 'Guest User'),
      email: commentEmail.trim() || (user ? user.email : 'guest@example.com'),
      content: commentText.trim(),
      date: new Date().toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })
    };

    const updated = [newComment, ...comments];
    setComments(updated);
    if (id) {
      localStorage.setItem(`nb-comments-${id}`, JSON.stringify(updated));
    }

    // Reset inputs
    setCommentText('');
    if (!user) {
      setCommentName('');
      setCommentEmail('');
    }
  };

  if (loading) {
    return (
      <div style={{ background: '#f8fafc', minHeight: '100vh', padding: '3rem 0' }}>
        <div className="container placeholder-glow" style={{ maxWidth: '900px' }}>
          <div className="placeholder col-3 mb-4" style={{ height: '20px' }}></div>
          <div className="placeholder col-12 mb-3" style={{ height: '40px' }}></div>
          <div className="placeholder col-8 mb-4" style={{ height: '25px' }}></div>
          <div className="placeholder w-100 rounded-4 mb-4" style={{ height: '400px', backgroundColor: '#e2e8f0' }}></div>
          <div className="placeholder col-12 mb-2"></div>
          <div className="placeholder col-12 mb-2"></div>
          <div className="placeholder col-10 mb-2"></div>
        </div>
      </div>
    );
  }

  if (error || !blog) {
    return (
      <div style={{ background: '#f8fafc', minHeight: '100vh', padding: '5rem 0' }}>
        <div className="container text-center" style={{ maxWidth: '600px' }}>
          <BookOpen size={48} className="text-danger mb-3" />
          <h2 className="fw-bold text-dark">Failed to Load Article</h2>
          <p className="text-secondary mb-4">{error || 'This article might have been moved or deleted.'}</p>
          <Link href="/blog" className="btn btn-primary rounded-pill px-4">
            Back to Insights
          </Link>
        </div>
      </div>
    );
  }

  const mainImage = blog.image || 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=800&q=80';
  const formattedDate = blog.date
    ? new Date(blog.date).toLocaleDateString('en-IN', { month: 'long', day: '2-digit', year: 'numeric' })
    : 'Jun 18, 2026';

  // Categories list
  const latestArticles = blogsList.filter(b => b.id !== blog.id).slice(0, 3);
  const popularArticles = blogsList.slice(0, 3);
  const relatedArticles = blogsList.filter(b => b.id !== blog.id).slice(1, 4);

  return (
    <div style={{ background: '#f8fafc', minHeight: '100vh', paddingBottom: '5rem' }}>

      {/* Sticky/Fixed Subheader Bar */}
      <div className="nb-blog-sticky-header">
        <div className="container d-flex justify-content-between align-items-center h-100 px-3">
          <div className="text-truncate fw-bold text-dark pe-3" style={{ fontSize: '0.9rem', maxWidth: '75%' }}>
            {blog.name}
          </div>
          <div className="d-flex align-items-center gap-2 flex-shrink-0">
            <button
              type="button"
              className={`btn btn-sm d-flex align-items-center gap-1.5 rounded-pill border ${hasLiked ? 'btn-danger text-white border-danger' : 'btn-light text-secondary bg-white'}`}
              onClick={handleLike}
            >
              <ThumbsUp size={13} />
              <span className="fw-bold" style={{ fontSize: '12px' }}>{likesCount}</span>
            </button>
            <button
              type="button"
              className="btn btn-sm btn-light bg-white text-secondary border d-flex align-items-center gap-1.5 rounded-pill"
              onClick={handleShare}
            >
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
              <li className="breadcrumb-item"><Link href="/blog" className="text-decoration-none text-secondary">Knowledge Centre</Link></li>
              <li className="breadcrumb-item"><span className="text-muted">Real Estate News</span></li>
              <li className="breadcrumb-item active text-dark fw-bold text-truncate" aria-current="page" style={{ maxWidth: '200px' }}>
                Coimbatore
              </li>
            </ol>
          </nav>
        </div>

        {/* Title Block */}
        <div className="mb-4">
          <h1 className="display-6 fw-extrabold text-dark mb-3" style={{ lineHeight: '1.25', color: '#0b2c56' }}>
            {blog.name}
          </h1>

          {/* Social Share & Read info row */}
          <div className="d-flex flex-wrap align-items-center justify-content-between gap-3 text-muted small pb-3 border-bottom">
            <div className="d-flex flex-wrap align-items-center gap-3">
              <span className="badge bg-primary bg-opacity-10 text-primary px-3 py-1.5 rounded-pill fw-bold text-uppercase" style={{ fontSize: '0.7rem', letterSpacing: '0.5px' }}>
                {blog.category || 'Real Estate News'}
              </span>
              <span className="d-flex align-items-center gap-1">
                <User size={13} />
                <span className="fw-semibold">{blog.author || 'Sohina Sharma'}</span>
              </span>
              <span className="d-flex align-items-center gap-1">
                <Calendar size={13} />
                <span>{formattedDate}</span>
              </span>
            </div>
            <div className="d-flex align-items-center gap-2">
              <span className="small bg-light px-2.5 py-1 rounded fw-semibold text-secondary">2 min read</span>
              <span className="small text-muted">Updated: {formattedDate}</span>
            </div>
          </div>

          {/* Hashtag Badges */}
          <div className="d-flex flex-wrap gap-1.5 mt-3">
            {['#Builders', '#Buyers', '#Investors', '#Latest&Greatest', '#OthersCities', '#Owners', '#Residential', '#Taxation'].map((tag) => (
              <span key={tag} className="badge bg-light text-secondary border py-1.5 px-2.5 rounded-pill font-monospace" style={{ fontSize: '0.75rem', fontWeight: 500 }}>
                {tag}
              </span>
            ))}
          </div>
        </div>

        {/* Three Columns Main Grid Layout */}
        <div className="row g-4 mt-1">

          {/* LEFT SIDEBAR COLUMN (Engagement & Ad Widgets) */}
          <div className="col-lg-2 d-none d-lg-block">
            <div className="d-flex flex-column gap-4 sticky-top" style={{ top: '120px', zIndex: 10 }}>

              {/* Vertical Engagement Counter */}
              <div className="bg-white border rounded-4 p-3 text-center shadow-sm">
                <div className="mb-4">
                  <div className="text-secondary small mb-1 fw-bold text-uppercase" style={{ fontSize: '10px' }}>Views</div>
                  <div className="d-flex align-items-center justify-content-center gap-1 text-dark fw-extrabold h5 m-0">
                    <Eye size={16} className="text-muted" />
                    <span>{viewsCount}</span>
                  </div>
                </div>
                <div className="mb-4">
                  <div className="text-secondary small mb-1 fw-bold text-uppercase" style={{ fontSize: '10px' }}>Shares</div>
                  <div className="d-flex align-items-center justify-content-center gap-1 text-dark fw-extrabold h5 m-0">
                    <Share2 size={16} className="text-muted" />
                    <span>{sharesCount}</span>
                  </div>
                </div>
                <div className="mb-2">
                  <div className="text-secondary small mb-1.5 fw-bold text-uppercase" style={{ fontSize: '10px' }}>Likes</div>
                  <button
                    type="button"
                    className={`btn btn-sm w-100 py-1.5 rounded-pill border d-flex align-items-center justify-content-center gap-1.5 ${hasLiked ? 'btn-danger text-white border-danger shadow-sm' : 'btn-light text-secondary bg-white'}`}
                    onClick={handleLike}
                  >
                    <ThumbsUp size={13} fill={hasLiked ? '#fff' : 'none'} />
                    <span className="fw-bold">{likesCount}</span>
                  </button>
                </div>
              </div>

              {/* Newsletter subscribe widget */}
              <div className="bg-white border rounded-4 p-3 shadow-sm">
                <h4 className="fw-bold text-dark mb-2" style={{ fontSize: '0.9rem' }}>Subscribe to Updates</h4>
                <p className="text-muted mb-3" style={{ fontSize: '0.75rem', lineHeight: '1.4' }}>
                  Get real estate updates delivered to your inbox!
                </p>
                {newsletterSubscribed ? (
                  <div className="alert alert-success py-2 px-2.5 small mb-0 rounded-3">
                    ✓ Thank you for subscribing!
                  </div>
                ) : (
                  <form onSubmit={handleNewsletterSubmit} className="d-flex flex-column gap-2">
                    <input
                      type="email"
                      className="form-control form-control-sm rounded-3"
                      placeholder="Email address"
                      value={newsletterEmail}
                      onChange={(e) => setNewsletterEmail(e.target.value)}
                      required
                      style={{ fontSize: '0.8rem' }}
                    />
                    <button type="submit" className="btn btn-sm btn-primary w-100 rounded-3 fw-bold">
                      Subscribe
                    </button>
                  </form>
                )}
              </div>

              {/* Post Property Promo card */}
              <div className="card border-0 rounded-4 text-white p-3 shadow-sm overflow-hidden" style={{ background: 'linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%)' }}>
                <h5 className="fw-extrabold mb-1" style={{ fontSize: '0.95rem' }}>Sell or rent faster!</h5>
                <p className="small mb-3 text-white-50" style={{ fontSize: '0.75rem', lineHeight: '1.4' }}>
                  List your property at the right price directly to verified buyers.
                </p>
                <Link
                  href={user ? '/owner/property/add' : '#'}
                  onClick={(e) => { if (!user) { e.preventDefault(); setAuthModalOpen('login'); } }}
                  className="btn btn-sm btn-warning fw-bold w-100 rounded-pill text-dark"
                  style={{ fontSize: '0.75rem' }}
                >
                  Post Property Free
                </Link>
              </div>

            </div>
          </div>

          {/* MIDDLE COLUMN (Core Article Text, Content Box, Comments) */}
          <div className="col-lg-7 col-md-8 col-12">

            {/* Short notes excerpt summary */}
            {blog.short_notes && (
              <div className="p-3 border-start border-primary border-4 bg-white rounded-3 shadow-sm mb-4" style={{ fontStyle: 'italic', color: '#4b5563', fontSize: '1rem', lineHeight: '1.6' }}>
                {blog.short_notes}
              </div>
            )}

            {/* Contents index box (Accordion) */}
            <div className="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
              <button
                type="button"
                className="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center text-dark fw-bold"
                onClick={() => setShowContents(!showContents)}
                style={{ cursor: 'pointer', outline: 'none' }}
              >
                <span className="h6 fw-bold m-0" style={{ color: '#0b2c56' }}>Contents</span>
                {showContents ? <ChevronUp size={18} /> : <ChevronDown size={18} />}
              </button>
              {showContents && (
                <div className="card-body bg-light bg-opacity-50 pt-0 px-4 pb-4">
                  <ol className="list-group list-group-numbered border-0 bg-transparent mb-0">
                    <li className="list-group-item border-0 bg-transparent px-0 py-1.5 small text-secondary">
                      <a href="#section-1" className="text-decoration-none text-secondary hover-primary fw-semibold">What is the new Coimbatore property tax regime?</a>
                    </li>
                    <li className="list-group-item border-0 bg-transparent px-0 py-1.5 small text-secondary">
                      <a href="#section-2" className="text-decoration-none text-secondary hover-primary fw-semibold">How the zones are divided?</a>
                    </li>
                    <li className="list-group-item border-0 bg-transparent px-0 py-1.5 small text-secondary">
                      <a href="#section-faq" className="text-decoration-none text-secondary hover-primary fw-semibold">Frequently Asked Questions</a>
                    </li>
                  </ol>
                </div>
              )}
            </div>

            {/* Main Cover Image */}
            <div className="rounded-4 overflow-hidden shadow-sm mb-4 bg-white border border-light" style={{ maxHeight: '420px' }}>
              <img
                src={mainImage}
                alt={blog.name}
                className="w-100 h-100 object-fit-cover"
              />
            </div>

            {/* Main Text Body */}
            <article className="bg-white border rounded-4 shadow-sm p-4 p-md-5 mb-4 overflow-hidden text-dark fs-6" style={{ lineHeight: '1.8', whiteSpace: 'pre-line' }}>

              <div id="section-1" className="mb-4">
                <h2 className="h5 fw-extrabold text-dark mb-3" style={{ borderBottom: '1.5px solid #eff6ff', paddingBottom: '8px' }}>
                  1. What is the new Coimbatore property tax regime?
                </h2>
                <p>
                  Coimbatore Municipal Corporation has initiated the process of collecting property tax from newly constructed buildings based on revised guidelines. Under the new resolution, property taxation for buildings is calculated according to the zone classification and a base price per square foot.
                </p>
                <p>
                  This reform is designed to streamline collections, eliminate discrepancy, and improve civic infrastructure financing. Inhabitants in 100 municipal wards will be categorized depending on their localized access to arterial roads and services.
                </p>
              </div>

              <div id="section-2" className="mb-4">
                <h2 className="h5 fw-extrabold text-dark mb-3" style={{ borderBottom: '1.5px solid #eff6ff', paddingBottom: '8px' }}>
                  2. How the zones are divided?
                </h2>
                <p>
                  The municipal limits are segmented into four distinct zones based on commercial density and highway proximity:
                </p>
                <ul>
                  <li><strong>Zone A:</strong> Premium arterial lanes including Avinashi Road, Trichy Road, and D.B. Road. Base rates are higher here due to extensive business development.</li>
                  <li><strong>Zone B:</strong> Proximity to major corridors, residential colonies in Nehru Nagar, Saravanampatti, and Ramanathapuram.</li>
                  <li><strong>Zone C:</strong> Intermediate blocks with standard connectivity and developing infrastructure.</li>
                  <li><strong>Zone D:</strong> Peripheral areas and newly annexed village panchayats.</li>
                </ul>
              </div>

              {/* Dynamic DB description field */}
              <div className="mt-4 pt-3 border-top border-light">
                {blog.description}
              </div>

              {/* FAQ Section */}
              <div id="section-faq" className="mt-4 pt-4 border-top border-light">
                <h2 className="h5 fw-extrabold text-dark mb-3" style={{ borderBottom: '1.5px solid #eff6ff', paddingBottom: '8px' }}>
                  Frequently Asked Questions
                </h2>
                <div className="mb-3">
                  <strong className="d-block text-dark mb-1">Q: What is the deadline to pay property tax in Coimbatore?</strong>
                  <p className="text-secondary small mb-0">The corporation usually requires the half-yearly property tax to be cleared before September 30 and March 31 respectively to avoid penalty interests.</p>
                </div>
                <div>
                  <strong className="d-block text-dark mb-1">Q: Where can I get the Coimbatore property tax receipt?</strong>
                  <p className="text-secondary small mb-0">Taxpayers can download the digital payment receipt directly from the official Tamil Nadu urban local bodies portal after successful transaction completion.</p>
                </div>
              </div>

              {/* Gallery Showcase if more than 1 image */}
              {blog.gallery && blog.gallery.length > 1 && (
                <div className="mt-5 pt-4 border-top border-light">
                  <h3 className="h6 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <span>Gallery Images</span>
                  </h3>
                  <div className="row g-2">
                    {blog.gallery.map((imgUrl, i) => (
                      <div key={i} className="col-4">
                        <div className="rounded-3 overflow-hidden shadow-sm" style={{ height: '90px', cursor: 'pointer' }}>
                          <img
                            src={imgUrl}
                            alt={`Gallery photo ${i + 1}`}
                            className="w-100 h-100 object-fit-cover"
                            onClick={() => window.open(imgUrl, '_blank')}
                          />
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </article>

            {/* In-content App download Banner */}
            <div className="card border-0 rounded-4 p-4 mb-4 text-dark shadow-sm position-relative overflow-hidden" style={{ background: 'linear-gradient(135deg, #fff9e6 0%, #fff0cc 100%)' }}>
              <div className="d-flex align-items-start gap-3 relative z-1" style={{ maxWidth: '75%' }}>
                <div className="bg-warning bg-opacity-20 text-warning p-2.5 rounded-circle d-flex flex-shrink-0">
                  <Smartphone size={24} className="text-dark" />
                </div>
                <div>
                  <h4 className="fw-bold mb-1" style={{ fontSize: '1rem', color: '#0b2c56' }}>Download our app & stay updated</h4>
                  <p className="text-secondary mb-3" style={{ fontSize: '0.8rem', lineHeight: '1.4' }}>
                    4.3★ on Android · 4.2★ on iOS | Real-time alerts, direct owner chat, and personalized recommendations.
                  </p>
                  <div className="d-flex gap-2">
                    <button type="button" className="btn btn-dark btn-sm rounded-pill px-3 py-1.5 fw-bold" style={{ fontSize: '11px' }} onClick={() => alert('Redirecting to Google Play Store')}>
                      Google Play
                    </button>
                    <button type="button" className="btn btn-outline-dark btn-sm rounded-pill px-3 py-1.5 fw-bold" style={{ fontSize: '11px' }} onClick={() => alert('Redirecting to Apple App Store')}>
                      App Store
                    </button>
                  </div>
                </div>
              </div>
              <div className="position-absolute bottom-0 end-0 pe-4 d-none d-sm-block" style={{ width: '130px', height: '140px', opacity: 0.15 }}>
                <Smartphone size={160} className="text-dark" />
              </div>
            </div>

            {/* Interactive Comment Form and List */}
            <div className="bg-white border rounded-4 shadow-sm p-4 mb-4">
              <h3 className="h6 fw-bold text-dark mb-4 border-bottom pb-2" style={{ color: '#0b2c56' }}>
                Comments ({comments.length})
              </h3>

              {/* Form */}
              <form onSubmit={handleCommentSubmit} className="mb-4">
                <div className="row g-2 mb-2">
                  {!user && (
                    <>
                      <div className="col-sm-6">
                        <input
                          type="text"
                          className="form-control form-control-sm rounded-3"
                          placeholder="Name"
                          value={commentName}
                          onChange={(e) => setCommentName(e.target.value)}
                          required
                        />
                      </div>
                      <div className="col-sm-6">
                        <input
                          type="email"
                          className="form-control form-control-sm rounded-3"
                          placeholder="Email Address"
                          value={commentEmail}
                          onChange={(e) => setCommentEmail(e.target.value)}
                          required
                        />
                      </div>
                    </>
                  )}
                </div>
                <div className="mb-3">
                  <textarea
                    className="form-control rounded-3"
                    rows={3}
                    placeholder="Share your thoughts about this article..."
                    value={commentText}
                    onChange={(e) => setCommentText(e.target.value)}
                    required
                    style={{ fontSize: '0.85rem' }}
                  />
                </div>
                <button type="submit" className="btn btn-sm btn-primary px-4 py-2 rounded-pill fw-bold d-inline-flex align-items-center gap-1.5">
                  <Send size={12} />
                  <span>Post a comment</span>
                </button>
              </form>

              {/* List */}
              <div className="d-flex flex-column gap-3" style={{ maxHeight: '400px', overflowY: 'auto' }}>
                {comments.map((comm) => (
                  <div key={comm.id} className="p-3 bg-light bg-opacity-70 rounded-4">
                    <div className="d-flex justify-content-between align-items-center mb-1.5">
                      <strong className="text-dark small">{comm.name}</strong>
                      <span className="text-muted" style={{ fontSize: '11px' }}>{comm.date}</span>
                    </div>
                    <p className="m-0 text-secondary" style={{ fontSize: '0.82rem', lineHeight: '1.5' }}>
                      {comm.content}
                    </p>
                  </div>
                ))}
                {comments.length === 0 && (
                  <div className="text-center py-3 text-muted small">
                    Be the first to share your comment!
                  </div>
                )}
              </div>

            </div>

          </div>

          {/* RIGHT COLUMN (Recommended & Similar Lists, Search Keywords Links) */}
          <div className="col-lg-3 col-md-4 col-12">
            <div className="d-flex flex-column gap-4 sticky-top" style={{ top: '120px', zIndex: 10 }}>

              {/* Latest Articles list */}
              <div className="bg-white border rounded-4 p-3 shadow-sm">
                <h4 className="fw-bold text-dark border-bottom pb-2 mb-3" style={{ fontSize: '0.9rem', color: '#0b2c56' }}>Latest Articles</h4>
                <div className="d-flex flex-column gap-3">
                  {latestArticles.map((art) => {
                    const thumb = art.image || 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=80&q=80';
                    return (
                      <Link key={art.id} href={`/blog/${art.id}`} className="d-flex gap-2 text-decoration-none align-items-center">
                        <div className="rounded overflow-hidden flex-shrink-0" style={{ width: '60px', height: '48px' }}>
                          <img src={thumb} alt={art.name} className="w-100 h-100 object-fit-cover" />
                        </div>
                        <div className="min-w-0">
                          <h5 className="text-dark text-truncate small fw-bold m-0" title={art.name}>{art.name}</h5>
                          <span className="text-muted" style={{ fontSize: '10px' }}>
                            {art.date ? new Date(art.date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short' }) : 'June 2026'}
                          </span>
                        </div>
                      </Link>
                    );
                  })}
                  {latestArticles.length === 0 && (
                    <div className="text-muted small">No other articles listed.</div>
                  )}
                </div>
              </div>

              {/* Popular Articles list */}
              <div className="bg-white border rounded-4 p-3 shadow-sm">
                <h4 className="fw-bold text-dark border-bottom pb-2 mb-3" style={{ fontSize: '0.9rem', color: '#0b2c56' }}>Popular Articles</h4>
                <div className="d-flex flex-column gap-3">
                  {popularArticles.map((art) => {
                    const thumb = art.image || 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=80&q=80';
                    return (
                      <Link key={`pop-${art.id}`} href={`/blog/${art.id}`} className="d-flex gap-2 text-decoration-none align-items-center">
                        <div className="rounded overflow-hidden flex-shrink-0" style={{ width: '60px', height: '48px' }}>
                          <img src={thumb} alt={art.name} className="w-100 h-100 object-fit-cover" />
                        </div>
                        <div className="min-w-0">
                          <h5 className="text-dark text-truncate small fw-bold m-0" title={art.name}>{art.name}</h5>
                          <span className="text-muted" style={{ fontSize: '10px' }}>
                            {art.date ? new Date(art.date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short' }) : 'June 2026'}
                          </span>
                        </div>
                      </Link>
                    );
                  })}
                </div>
              </div>

              {/* What are people searching for (Locality search links) */}
              <div className="bg-white border rounded-4 p-3 shadow-sm">
                <h4 className="fw-bold text-dark border-bottom pb-2 mb-3" style={{ fontSize: '0.9rem', color: '#0b2c56' }}>What are people searching for?</h4>
                <div className="d-flex flex-column gap-2">
                  {[
                    { label: 'Flats in Coimbatore', query: 'Coimbatore' },
                    { label: 'Flats in Saravanampatti', query: 'Saravanampatti' },
                    { label: 'Flats in Kovilpalayam', query: 'Kovilpalayam' },
                    { label: 'Flats in Sulur', query: 'Sulur' },
                    { label: 'Flats in Vilankurichi', query: 'Vilankurichi' }
                  ].map((keyword, idx) => (
                    <Link
                      key={idx}
                      href={`/search?q=${encodeURIComponent(keyword.query)}`}
                      className="text-decoration-none small text-secondary hover-primary d-block py-1"
                    >
                      {keyword.label}
                    </Link>
                  ))}
                </div>
              </div>

            </div>
          </div>

        </div>

      </div>

      {/* Floating Bottom Drawer Promo popup (Triggered on Scroll) */}
      {showPromoPopup && (
        <div className="nb-blog-promo-popup">
          <div className="p-3 d-flex align-items-center justify-content-between text-white relative h-100">
            <button
              type="button"
              className="position-absolute top-0 end-0 m-1.5 btn btn-link text-white-50 p-0"
              onClick={() => setShowPromoPopup(false)}
            >
              <X size={15} />
            </button>
            <div className="pe-4">
              <strong className="d-block text-warning small mb-0.5">Sell or rent faster at the right price!</strong>
              <span style={{ fontSize: '11px', display: 'block', opacity: 0.85, lineHeight: '1.2' }}>
                List your property for free with direct buyer routes.
              </span>
            </div>
            <Link
              href={user ? '/owner/property/add' : '#'}
              onClick={(e) => { if (!user) { e.preventDefault(); setAuthModalOpen('login'); } }}
              className="btn btn-warning btn-sm fw-bold px-3 py-1.5 flex-shrink-0 text-dark rounded-pill"
              style={{ fontSize: '11px' }}
            >
              Post Property, It's FREE
            </Link>
          </div>
        </div>
      )}

    </div>
  );
}
