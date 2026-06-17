'use client';

import React, { useState } from 'react';
import api from '../lib/api';

interface LiveUpdateModalProps {
  show: boolean;
  onClose: () => void;
  onSuccess?: () => void;
}

export default function LiveUpdateModal({ show, onClose, onSuccess }: LiveUpdateModalProps) {
  const [liveUpdateData, setLiveUpdateData] = useState({
    title: '', platform: 'app', status: 'upcoming', url: '', liveTime: '', description: ''
  });
  const [liveUpdateImage, setLiveUpdateImage] = useState<File | null>(null);
  const [liveUpdateSubmitting, setLiveUpdateSubmitting] = useState(false);

  const handleLiveUpdateSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!liveUpdateData.title) return alert('Title is required');
    setLiveUpdateSubmitting(true);
    const formData = new FormData();
    Object.entries(liveUpdateData).forEach(([key, value]) => formData.append(key, value));
    if (liveUpdateImage) formData.append('image_file', liveUpdateImage);

    try {
      const res = await api.post('/api/mobile/live-updates/create', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      if (res.data?.success) {
        alert('Live Update created successfully!');
        setLiveUpdateData({ title: '', platform: 'app', status: 'upcoming', url: '', liveTime: '', description: '' });
        setLiveUpdateImage(null);
        if (onSuccess) onSuccess();
        onClose();
      } else {
        alert(res.data?.message || 'Failed to create live update.');
      }
    } catch (err) {
      console.error(err);
      alert('Error creating live update.');
    } finally {
      setLiveUpdateSubmitting(false);
    }
  };

  if (!show) return null;

  return (
    <div className="modal fade show d-block" tabIndex={-1} style={{ backgroundColor: 'rgba(0,0,0,0.5)', zIndex: 1050 }}>
      <div className="modal-dialog modal-dialog-centered">
        <div className="modal-content border-0 shadow">
          <div className="modal-header border-bottom-0 pb-0">
            <h5 className="modal-title fw-bold text-dark">Add Live Update</h5>
            <button type="button" className="btn-close" onClick={onClose}></button>
          </div>
          <div className="modal-body">
            <form onSubmit={handleLiveUpdateSubmit}>
              <div className="mb-3">
                <label className="form-label small fw-semibold text-secondary">Title *</label>
                <input type="text" className="form-control" required value={liveUpdateData.title} onChange={(e) => setLiveUpdateData({...liveUpdateData, title: e.target.value})} placeholder="e.g. New Project Launch" />
              </div>
              <div className="row g-2 mb-3">
                <div className="col-6">
                  <label className="form-label small fw-semibold text-secondary">Platform *</label>
                  <select className="form-select" value={liveUpdateData.platform} onChange={(e) => setLiveUpdateData({...liveUpdateData, platform: e.target.value})}>
                    <option value="app">App</option>
                    <option value="youtube">YouTube</option>
                    <option value="instagram">Instagram</option>
                  </select>
                </div>
                <div className="col-6">
                  <label className="form-label small fw-semibold text-secondary">Status *</label>
                  <select className="form-select" value={liveUpdateData.status} onChange={(e) => setLiveUpdateData({...liveUpdateData, status: e.target.value})}>
                    <option value="upcoming">Upcoming</option>
                    <option value="live_started">Live Started</option>
                    <option value="reschedule">Reschedule</option>
                    <option value="cancelled">Cancelled</option>
                  </select>
                </div>
              </div>
              <div className="mb-3">
                <label className="form-label small fw-semibold text-secondary">Live Time</label>
                <input type="datetime-local" className="form-control" value={liveUpdateData.liveTime} onChange={(e) => setLiveUpdateData({...liveUpdateData, liveTime: e.target.value})} />
              </div>
              <div className="mb-3">
                <label className="form-label small fw-semibold text-secondary">Link URL</label>
                <input type="url" className="form-control" value={liveUpdateData.url} onChange={(e) => setLiveUpdateData({...liveUpdateData, url: e.target.value})} placeholder="https://" />
              </div>
              <div className="mb-3">
                <label className="form-label small fw-semibold text-secondary">Image Upload</label>
                <input type="file" className="form-control" accept=".jpg,.jpeg,.png,.webp" onChange={(e) => setLiveUpdateImage(e.target.files ? e.target.files[0] : null)} />
              </div>
              <div className="mb-4">
                <label className="form-label small fw-semibold text-secondary">Description</label>
                <textarea className="form-control" rows={3} value={liveUpdateData.description} onChange={(e) => setLiveUpdateData({...liveUpdateData, description: e.target.value})}></textarea>
              </div>
              <button type="submit" className="btn btn-primary w-100 fw-bold py-2" disabled={liveUpdateSubmitting}>
                {liveUpdateSubmitting ? 'Creating...' : 'Create Live Update'}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
}
