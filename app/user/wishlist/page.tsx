'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/components/AuthContext';
import api from '@/lib/api';
import PropertyCard, { Property } from '@/components/property/PropertyCard';
import { Heart, Search, ArrowLeft } from 'lucide-react';

interface WishlistItem {
  id: string;
  propertyId: string;
  propertyName: string;
  property: Property | null;
}

export default function WishlistPage() {
  const { user, loading: authLoading } = useAuth();
  const router = useRouter();

  // Data states
  const [wishlist, setWishlist] = useState<WishlistItem[]>([]);
  const [loading, setLoading] = useState(true);

  // Authenticate user
  useEffect(() => {
    if (!authLoading && !user) {
      router.push('/login');
    }
  }, [user, authLoading, router]);

  // Fetch wishlist
  useEffect(() => {
    if (user) {
      api.get(`/api/nb/wishlist?userId=${user.id}`)
        .then((res) => {
          if (res.data?.success && Array.isArray(res.data.wishlist)) {
            setWishlist(res.data.wishlist);
          }
        })
        .catch((err) => console.error('Error loading wishlist:', err))
        .finally(() => setLoading(false));
    }
  }, [user]);

  if (authLoading || loading) {
    return (
      <div className="text-center py-5 my-5">
        <div className="spinner-border text-primary" role="status">
          <span className="visually-hidden">Loading wishlist...</span>
        </div>
      </div>
    );
  }

  // Filter out any items where property is null (e.g. deleted properties)
  const validProperties = wishlist
    .map((item) => item.property)
    .filter((p): p is Property => p !== null && p !== undefined);

  return (
    <div className="container py-5 mt-5">
      <div className="row justify-content-center">
        <div className="col-lg-10">
          <Link href="/" className="btn btn-link text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-4 p-0">
            <ArrowLeft size={14} />
            <span>Back to Home</span>
          </Link>

          <div className="d-flex align-items-center gap-2 mb-4">
            <Heart size={24} className="text-danger" fill="#ef4444" />
            <h1 className="h3 fw-bold text-dark m-0">My Saved Properties</h1>
          </div>

          <div className="row g-4 nb-property-grid">
            {validProperties.map((p) => (
              <PropertyCard key={p.id} property={p} />
            ))}

            {validProperties.length === 0 && (
              <div className="col-12">
                <div className="text-center py-5 border bg-white rounded-3 my-2 text-muted">
                  <Search size={36} className="mx-auto mb-2 opacity-50" />
                  <h2 className="h5 fw-bold text-dark mb-1">Your wishlist is empty</h2>
                  <p className="small mb-0">Tap the heart icon on any property card to save it here.</p>
                  <Link href="/search" className="btn btn-sm btn-danger rounded-pill px-4 text-dark mt-3 fw-semibold">
                    Explore Properties
                  </Link>
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
