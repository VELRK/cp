'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/components/AuthContext';
import api from '@/lib/api';
import LiveUpdateModal from '@/components/common/LiveUpdateModal';
import { Bell, ArrowLeft, Calendar, Video, Image as ImageIcon, Plus } from 'lucide-react';

interface Notification {
  id: number;
  title: string;
  description: string;
  image_url: string | null;
  video_url: string | null;
  created_at: string;
}

export default function LiveUpdatesPage() {
  const { user, loading: authLoading } = useAuth();
  const router = useRouter();

  // Data states
  const [updates, setUpdates] = useState<Notification[]>([]);
  const [loading, setLoading] = useState(true);
  const [showLiveUpdateModal, setShowLiveUpdateModal] = useState(false);

  // Authenticate user
  useEffect(() => {
    if (!authLoading && !user) {
      router.push('/login');
    }
  }, [user, authLoading, router]);

  // Fetch notifications
  const fetchUpdates = () => {
    if (user) {
      api.get('/api/nb/notifications')
        .then((res) => {
          if (res.data?.success && Array.isArray(res.data.notifications)) {
            setUpdates(res.data.notifications);
          }
        })
        .catch((err) => console.error('Error fetching notifications:', err))
        .finally(() => setLoading(false));
    }
  };

  useEffect(() => {
    fetchUpdates();
  }, [user]);

  if (authLoading || loading) {
    return (
      <div className="text-center py-5 my-5 d-flex flex-column align-items-center justify-content-center" style={{ minHeight: '60vh' }}>
        <div className="spinner-border text-primary" role="status" style={{ width: '3rem', height: '3rem' }}>
          <span className="visually-hidden">Loading live updates...</span>
        </div>
        <p className="mt-3 text-muted fw-medium fade-in">Fetching the latest updates...</p>
        <style>{`
          .fade-in { animation: fadeIn 1.5s infinite alternate; }
          @keyframes fadeIn { from { opacity: 0.5; } to { opacity: 1; } }
        `}</style>
      </div>
    );
  }

  return (
    <div className="container py-5 mt-5">
      <style>{`
        .fade-in-up {
          animation: fadeInUp 0.6s ease-out forwards;
          opacity: 0;
          transform: translateY(20px);
        }
        @keyframes fadeInUp {
          to {
            opacity: 1;
            transform: translateY(0);
          }
        }
        .update-card {
          transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
          border: 1px solid rgba(0,0,0,0.05) !important;
          border-left: 4px solid transparent !important;
          border-radius: 12px;
        }
        .update-card:hover {
          transform: translateY(-5px);
          box-shadow: 0 15px 30px rgba(0,0,0,0.08) !important;
          border-left: 4px solid #0d6efd !important;
        }
        .classic-title {
          font-family: 'Georgia', serif;
          letter-spacing: 0.3px;
          color: #2c3e50;
        }
        .pulse-icon {
          animation: pulseIcon 2s infinite;
        }
        @keyframes pulseIcon {
          0% { transform: scale(1); }
          50% { transform: scale(1.15); color: #0d6efd; }
          100% { transform: scale(1); }
        }
        .header-underline {
          position: relative;
          padding-bottom: 12px;
        }
        .header-underline::after {
          content: '';
          position: absolute;
          bottom: 0;
          left: 0;
          width: 60px;
          height: 4px;
          background: linear-gradient(90deg, #0d6efd, #6ea8fe);
          border-radius: 4px;
        }
        .btn-custom-add {
          background: linear-gradient(135deg, #dc3545, #fd7e14);
          border: none;
          transition: all 0.3s ease;
        }
        .btn-custom-add:hover {
          transform: translateY(-2px);
          box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
        }
      `}</style>

      <div className="row justify-content-center">
        <div className="col-lg-8">
          <Link href="/" className="btn btn-link text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-4 p-0" style={{ transition: 'color 0.2s' }} onMouseOver={(e) => e.currentTarget.style.color = '#0d6efd'} onMouseOut={(e) => e.currentTarget.style.color = 'inherit'}>
            <ArrowLeft size={14} />
            <span>Back to Home</span>
          </Link>

          <div className="d-flex justify-content-between align-items-center mb-5 header-underline fade-in-up" style={{ animationDelay: '0.1s' }}>
            <div className="d-flex align-items-center gap-3">
              <div className="bg-primary bg-opacity-10 p-3 rounded-circle">
                <Bell size={28} className="text-primary pulse-icon" />
              </div>
              <h1 className="h3 fw-bold m-0 classic-title">Live Updates & Announcements</h1>
            </div>
            <button
              className="btn btn-primary btn-custom-add btn-sm d-flex align-items-center gap-1 fw-bold text-white px-3 py-2 rounded-pill"
              onClick={() => setShowLiveUpdateModal(true)}
            >
              <Plus size={16} /> Add Update
            </button>
          </div>

          <div className="d-flex flex-column gap-4">
            {updates.map((update, index) => (
              <div
                key={update.id}
                className="card update-card bg-white p-4 fade-in-up"
                style={{ animationDelay: `${0.15 + index * 0.1}s` }}
              >
                <div className="d-flex justify-content-between align-items-start gap-3 mb-3 flex-wrap">
                  <h2 className="h5 fw-bold m-0 text-dark" style={{ lineHeight: '1.4' }}>{update.title}</h2>
                  <span className="badge bg-light text-secondary border d-flex align-items-center gap-1 px-2 py-1">
                    <Calendar size={12} />
                    {new Date(update.created_at).toLocaleDateString('en-IN', {
                      day: 'numeric',
                      month: 'short',
                      year: 'numeric'
                    })}
                  </span>
                </div>

                <p className="text-secondary mb-4" style={{ fontSize: '1.05rem', lineHeight: '1.6' }}>{update.description}</p>

                {/* Optional Media previews */}
                {(update.image_url || update.video_url) && (
                  <div className="d-flex gap-3 pt-3 border-top">
                    {update.image_url && (
                      <a
                        href={update.image_url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2 rounded-pill px-3"
                      >
                        <ImageIcon size={14} />
                        <span className="fw-medium">View Image</span>
                      </a>
                    )}
                    {update.video_url && (
                      <a
                        href={update.video_url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-2 rounded-pill px-3"
                      >
                        <Video size={14} />
                        <span className="fw-medium">Watch Video</span>
                      </a>
                    )}
                  </div>
                )}
              </div>
            ))}

            {updates.length === 0 && (
              <div className="text-center py-5 border bg-white rounded-3 my-2 text-muted fade-in-up" style={{ animationDelay: '0.2s', borderStyle: 'dashed !important' }}>
                <div className="bg-light d-inline-block p-4 rounded-circle mb-3">
                  <Bell size={40} className="text-secondary opacity-50" />
                </div>
                <h3 className="h5 fw-bold text-dark">No updates available</h3>
                <p className="mb-0 text-secondary">There are currently no live announcements to display.</p>
              </div>
            )}
          </div>
        </div>
      </div>

      <LiveUpdateModal
        show={showLiveUpdateModal}
        onClose={() => setShowLiveUpdateModal(false)}
        onSuccess={fetchUpdates}
      />
    </div>
  );
}
