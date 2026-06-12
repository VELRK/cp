'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '../../../components/AuthContext';
import api from '../../../lib/api';
import { Bell, ArrowLeft, Calendar, Video, Image as ImageIcon } from 'lucide-react';

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

  // Authenticate user
  useEffect(() => {
    if (!authLoading && !user) {
      router.push('/login');
    }
  }, [user, authLoading, router]);

  // Fetch notifications
  useEffect(() => {
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
  }, [user]);

  if (authLoading || loading) {
    return (
      <div className="text-center py-5 my-5">
        <div className="spinner-border text-primary" role="status">
          <span className="visually-hidden">Loading live updates...</span>
        </div>
      </div>
    );
  }

  return (
    <div className="container py-5 mt-5">
      <div className="row justify-content-center">
        <div className="col-lg-8">
          <Link href="/" className="btn btn-link text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-4 p-0">
            <ArrowLeft size={14} />
            <span>Back to Home</span>
          </Link>

          <div className="d-flex align-items-center gap-2 mb-4">
            <Bell size={24} className="text-primary" />
            <h1 className="h3 fw-bold text-dark m-0">Live Updates &amp; Announcements</h1>
          </div>

          <div className="d-flex flex-column gap-3">
            {updates.map((update) => (
              <div key={update.id} className="card border-0 shadow-sm bg-white p-4">
                <div className="d-flex justify-content-between align-items-start gap-2 mb-2 flex-wrap">
                  <h2 className="h6 fw-bold m-0 text-dark">{update.title}</h2>
                  <span className="text-muted small d-flex align-items-center gap-1">
                    <Calendar size={12} />
                    {new Date(update.created_at).toLocaleDateString('en-IN')}
                  </span>
                </div>

                <p className="text-secondary small mb-3">{update.description}</p>

                {/* Optional Media previews */}
                <div className="d-flex gap-2">
                  {update.image_url && (
                    <a
                      href={update.image_url}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="btn btn-sm btn-light border small d-inline-flex align-items-center gap-1"
                    >
                      <ImageIcon size={12} />
                      <span>View Image Attachment</span>
                    </a>
                  )}
                  {update.video_url && (
                    <a
                      href={update.video_url}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="btn btn-sm btn-light border small d-inline-flex align-items-center gap-1"
                    >
                      <Video size={12} />
                      <span>Watch Video</span>
                    </a>
                  )}
                </div>
              </div>
            ))}

            {updates.length === 0 && (
              <div className="text-center py-5 border bg-white rounded-3 my-2 text-muted">
                <Bell size={36} className="mx-auto mb-2 opacity-50" />
                <p className="mb-0">No updates or announcements listed yet.</p>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
