'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import api from '../../lib/api';
import { BookOpen, User, Calendar, ArrowLeft, ArrowRight } from 'lucide-react';

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

export default function BlogIndex() {
  const [blogs, setBlogs] = useState<Blog[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.get('/api/blogs')
      .then((res) => {
        if (Array.isArray(res.data)) {
          setBlogs(res.data);
        }
      })
      .catch((e) => console.warn('Could not fetch blogs', e))
      .finally(() => setLoading(false));
  }, []);

  return (
    <div style={{ background: '#f8fafc', minHeight: '100vh', padding: '3rem 0' }}>
      <div className="container">
        
        {/* Navigation Breadcrumb */}
        <div className="mb-4">
          <Link href="/" className="btn btn-link text-decoration-none text-secondary p-0 d-flex align-items-center gap-2 fw-semibold">
            <ArrowLeft size={16} />
            <span>Back to Home</span>
          </Link>
        </div>

        {/* Page Title & Intro */}
        <div className="mb-5 text-center">
          <div className="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary p-3 rounded-circle mb-3">
            <BookOpen size={32} />
          </div>
          <h1 className="display-5 fw-extrabold text-dark mb-2">Realty Insights & Guides</h1>
          <p className="text-secondary mx-auto" style={{ maxWidth: '600px', fontSize: '1.05rem' }}>
            Stay updated with the latest real estate trends, policy changes, house construction costs, and property documentation advice from our experts.
          </p>
        </div>

        {loading ? (
          <div className="row g-4">
            {Array.from({ length: 6 }).map((_, i) => (
              <div key={i} className="col-md-4 placeholder-glow">
                <div className="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                  <div className="placeholder w-100" style={{ height: '200px', backgroundColor: '#e2e8f0' }}></div>
                  <div className="card-body p-4">
                    <div className="placeholder col-8 mb-3"></div>
                    <div className="placeholder col-12 mb-2"></div>
                    <div className="placeholder col-10 mb-4"></div>
                    <div className="placeholder col-4"></div>
                  </div>
                </div>
              </div>
            ))}
          </div>
        ) : blogs.length > 0 ? (
          <div className="row g-4">
            {blogs.map((blog) => {
              const image = blog.image || 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=500&q=80';
              const formattedDate = blog.date
                ? new Date(blog.date).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })
                : '';
              
              return (
                <div key={blog.id} className="col-md-4">
                  <div className="card border-0 shadow-sm rounded-4 overflow-hidden h-100 nb-insight-card-hover d-flex flex-column" style={{ transition: 'transform 0.2s ease, box-shadow 0.2s ease' }}>
                    <div style={{ height: '220px', overflow: 'hidden', position: 'relative' }}>
                      <img
                        src={image}
                        alt={blog.name}
                        className="w-100 h-100 object-fit-cover"
                      />
                    </div>
                    <div className="card-body p-4 d-flex flex-column flex-grow-1">
                      <div className="d-flex align-items-center gap-3 mb-3 text-muted small">
                        <span className="d-flex align-items-center gap-1">
                          <User size={14} />
                          <span>{blog.author || 'Admin'}</span>
                        </span>
                        <span className="d-flex align-items-center gap-1">
                          <Calendar size={14} />
                          <span>{formattedDate}</span>
                        </span>
                      </div>
                      
                      <h3 className="h5 fw-bold text-dark mb-2 line-clamp-2" style={{ lineHeight: '1.4' }}>
                        {blog.name}
                      </h3>
                      
                      <p className="text-secondary small mb-4 line-clamp-3" style={{ lineHeight: '1.5' }}>
                        {blog.short_notes}
                      </p>
                      
                      <Link
                        href={`/blog/${blog.id}`}
                        className="btn btn-outline-primary btn-sm rounded-pill px-4 py-2 mt-auto fw-bold d-inline-flex align-items-center gap-1.5 align-self-start"
                      >
                        <span>Read Full Post</span>
                        <ArrowRight size={14} />
                      </Link>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        ) : (
          <div className="text-center py-5 bg-white border rounded-4 shadow-sm">
            <BookOpen size={48} className="text-muted mb-3" />
            <h3 className="h5 fw-bold text-dark">No Articles Found</h3>
            <p className="text-secondary small mb-0">Check back later for exciting insights and realty news.</p>
          </div>
        )}
      </div>
    </div>
  );
}
