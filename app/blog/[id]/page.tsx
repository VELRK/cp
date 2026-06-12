'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import api from '../../../lib/api';
import { ArrowLeft, User, Calendar, BookOpen, Image as ImageIcon } from 'lucide-react';

interface Blog {
  id: number;
  name: string;
  author: string;
  date: string;
  short_notes: string;
  description: string;
  gallery: string[];
  image: string | null;
}

export default function BlogPostDetail({ params }: { params: Promise<{ id: string }> }) {
  const { id } = React.use(params);
  const [blog, setBlog] = useState<Blog | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!id) return;
    
    api.get(`/api/blogs?id=${id}`)
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
  }, [id]);

  if (loading) {
    return (
      <div style={{ background: '#f8fafc', minHeight: '100vh', padding: '3rem 0' }}>
        <div className="container placeholder-glow" style={{ maxWidth: '800px' }}>
          <div className="placeholder col-4 mb-4" style={{ height: '20px' }}></div>
          <div className="placeholder col-12 mb-2" style={{ height: '40px' }}></div>
          <div className="placeholder col-8 mb-4" style={{ height: '30px' }}></div>
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
    ? new Date(blog.date).toLocaleDateString('en-US', { month: 'long', day: '2-digit', year: 'numeric' })
    : '';

  return (
    <div style={{ background: '#f8fafc', minHeight: '100vh', padding: '3rem 0' }}>
      <div className="container" style={{ maxWidth: '850px' }}>
        
        {/* Navigation Breadcrumb */}
        <div className="mb-4">
          <Link href="/blog" className="btn btn-link text-decoration-none text-secondary p-0 d-flex align-items-center gap-2 fw-semibold">
            <ArrowLeft size={16} />
            <span>Back to Insights</span>
          </Link>
        </div>

        {/* Blog Article Wrap */}
        <article className="bg-white border rounded-4 shadow-sm p-4 p-md-5 overflow-hidden">
          
          {/* Category/Author/Date Info */}
          <div className="d-flex flex-wrap align-items-center gap-3 text-muted small mb-3">
            <span className="badge bg-primary bg-opacity-10 text-primary px-3 py-1.5 rounded-pill fw-bold text-uppercase" style={{ fontSize: '0.7rem', letterSpacing: '0.5px' }}>
              Realty Guide
            </span>
            <span className="d-flex align-items-center gap-1.5">
              <User size={14} className="text-secondary" />
              <span className="fw-semibold">{blog.author || 'Dream Villa Makers'}</span>
            </span>
            <span className="d-flex align-items-center gap-1.5">
              <Calendar size={14} className="text-secondary" />
              <span>{formattedDate}</span>
            </span>
          </div>

          {/* Title */}
          <h1 className="display-6 fw-extrabold text-dark mb-4" style={{ lineHeight: '1.25' }}>
            {blog.name}
          </h1>

          {/* Short Excerpt Summary Box */}
          {blog.short_notes && (
            <div className="p-3 border-start border-primary border-4 bg-light rounded-end mb-4" style={{ fontStyle: 'italic', color: '#4b5563', fontSize: '1.05rem', lineHeight: '1.5' }}>
              {blog.short_notes}
            </div>
          )}

          {/* Main Cover Photo */}
          <div className="rounded-4 overflow-hidden mb-4 shadow-sm" style={{ maxHeight: '450px' }}>
            <img
              src={mainImage}
              alt={blog.name}
              className="w-100 h-100 object-fit-cover"
            />
          </div>

          {/* Core Text Content Body */}
          <div className="text-dark fs-6 mt-4" style={{ lineHeight: '1.8', whiteSpace: 'pre-line' }}>
            {blog.description}
          </div>

          {/* Gallery Showcase if more than 1 image */}
          {blog.gallery && blog.gallery.length > 1 && (
            <div className="mt-5 pt-4 border-top">
              <h3 className="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <ImageIcon size={18} className="text-primary" />
                <span>Gallery Images</span>
              </h3>
              <div className="row g-3">
                {blog.gallery.map((imgUrl, i) => (
                  <div key={i} className="col-6 col-md-4">
                    <div className="rounded-3 overflow-hidden shadow-sm" style={{ height: '140px', cursor: 'pointer' }}>
                      <img
                        src={imgUrl}
                        alt={`Gallery photo ${i + 1}`}
                        className="w-100 h-100 object-fit-cover hover-zoom"
                        onClick={() => window.open(imgUrl, '_blank')}
                      />
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}
        </article>

      </div>
    </div>
  );
}
