'use client';

import React from 'react';
import Link from 'next/link';
import { ArrowRight } from 'lucide-react';

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
    <div className="nb-blogs-section-wrapper p-4 bg-white rounded-4 shadow-sm border border-light">
      <div className="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
        <div>
          <h2 className="h4 fw-bold text-dark m-0" style={{ color: '#0b2c56' }}>Top articles on home buying</h2>
          <p className="text-muted small m-0">Expert guides, tax regulations, and latest real estate news</p>
        </div>
        <Link href="/blog" className="text-decoration-none fw-bold small d-flex align-items-center gap-1" style={{ color: '#0076de' }}>
          <span>Read realty news, guides & articles</span>
          <ArrowRight size={16} />
        </Link>
      </div>

      {/* Tab List */}
      <div className="nb-blogs-tabs-scroll-wrap mb-4">
        <div className="nb-blogs-tabs-row">
          {[
            { key: 'news', label: 'News' },
            { key: 'tax', label: 'Tax & Legal' },
            { key: 'guide', label: 'Help Guides' },
            { key: 'investment', label: 'Investment' }
          ].map((tab) => (
            <button
              key={tab.key}
              type="button"
              className={`nb-blog-tab-btn ${activeBlogCategory === tab.key ? 'active' : ''}`}
              onClick={() => setActiveBlogCategory(tab.key as any)}
            >
              {tab.label}
            </button>
          ))}
        </div>
      </div>

      {/* Tab Content */}
      <div className="row g-4">
        {loadingBlogs ? (
          Array.from({ length: 4 }).map((_, i) => (
            <div key={i} className="col-md-3 col-sm-6 col-12 placeholder-glow">
              <div className="card border-0 rounded-3 overflow-hidden h-100 shadow-sm">
                <div className="placeholder w-100" style={{ height: '160px', backgroundColor: '#e2e8f0' }}></div>
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
              <div className="col-12 text-center py-4 text-muted small">
                No articles found in this category.
              </div>
            );
          }

          return displayBlogs.map((blog) => {
            const image = blog.image || 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=400&q=80';
            const formattedDate = blog.date
              ? new Date(blog.date).toLocaleDateString('en-IN', { month: 'short', day: '2-digit', year: 'numeric' })
              : 'Jun 2026';
            return (
              <div key={blog.id} className="col-lg-3 col-md-4 col-sm-6 col-12">
                <Link href={`/blog/${blog.id}`} className="text-decoration-none text-dark d-block h-100">
                  <div className="card border-0 rounded-4 overflow-hidden h-100 shadow-sm nb-insight-card-hover" style={{ backgroundColor: '#fff' }}>
                    <div className="position-relative" style={{ height: '160px', overflow: 'hidden' }}>
                      <img
                        src={image}
                        alt={blog.name}
                        className="w-100 h-100 object-fit-cover"
                      />
                      <span className="position-absolute top-0 start-0 m-2 badge rounded-pill bg-dark bg-opacity-75 small text-uppercase" style={{ fontSize: '0.65rem', letterSpacing: '0.5px' }}>
                        {blog.category || activeBlogCategory}
                      </span>
                    </div>
                    <div className="p-3 d-flex flex-column justify-content-between flex-grow-1">
                      <h3 className="h6 fw-bold mb-2 text-dark line-clamp-2" style={{ fontSize: '0.9rem', lineHeight: '1.4', minHeight: '2.8rem' }} title={blog.name}>
                        {blog.name}
                      </h3>
                      <div className="text-muted" style={{ fontSize: '0.75rem' }}>
                        {formattedDate}
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
