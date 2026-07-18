'use client';

import React, { useEffect, useState } from 'react';
import { ChevronRight, ChevronLeft, PlayCircle } from 'lucide-react';
import { getVideos } from '@/lib/frontendApi';

interface Video {
  id: number;
  title: string;
  videoUrl: string;
  youtube_id: string | null;
  embed_url: string | null;
  thumbnail: string | null;
  createdAt: string | null;
}

const PropertyVideos: React.FC = () => {
  const [videos, setVideos] = useState<Video[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getVideos()
      .then((res) => {
        if (res.data?.success && Array.isArray(res.data.data)) {
          setVideos(res.data.data);
        }
      })
      .catch((err) => console.error('Error fetching videos:', err))
      .finally(() => setLoading(false));
  }, []);

  if (!loading && videos.length === 0) {
    return null;
  }

  return (
    <div className="mb-5 fade-in-up">
      <div className="d-flex justify-content-between align-items-end mb-3">
        <div>
          <h2 className="h4 fw-bold text-dark m-0">Property Videos</h2>
          <p className="text-muted small m-0">Explore our properties through immersive video tours</p>
        </div>
      </div>

      {loading ? (
        <div className="text-center py-5 bg-white border rounded-4 shadow-sm">
          <div className="spinner-border nb-text-brand" role="status">
            <span className="visually-hidden">Loading videos...</span>
          </div>
        </div>
      ) : (
        <div className="nb-scroll-wrapper">
          <button className="nb-scroll-arrow nb-scroll-arrow-left" aria-label="Scroll left"><ChevronLeft size={24} /></button>
          <button className="nb-scroll-arrow nb-scroll-arrow-right" aria-label="Scroll right"><ChevronRight size={24} /></button>
          <div className="nb-horizontal-scroll">
            {videos.map((video) => (
              <div key={video.id} className="nb-classic-property-card-wrap" style={{ width: '320px', flex: '0 0 auto' }}>
                <div className="nb-classic-card h-100 d-flex flex-column">
                  <div className="nb-classic-card-img-container" style={{ paddingBottom: '56.25%', height: 0 }}>
                    {video.embed_url ? (
                      <iframe
                        src={video.embed_url}
                        title={video.title || 'Property Video'}
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowFullScreen
                        className="position-absolute top-0 start-0 w-100 h-100 border-0"
                      />
                    ) : (
                      <a href={video.videoUrl} target="_blank" rel="noopener noreferrer" className="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark text-white text-decoration-none">
                        {video.thumbnail ? (
                          <img src={video.thumbnail} alt={video.title} className="position-absolute top-0 start-0 w-100 h-100 object-fit-cover opacity-50" />
                        ) : null}
                        <PlayCircle size={48} className="position-relative z-1" />
                      </a>
                    )}
                  </div>
                  <div className="nb-classic-card-body flex-grow-1">
                    <h3 className="nb-classic-card-title text-truncate m-0 py-2" title={video.title || 'Video Tour'}>
                      {video.title || 'Video Tour'}
                    </h3>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
};

export default PropertyVideos;
