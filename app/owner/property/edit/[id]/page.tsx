'use client';

import React, { useState, useEffect, use } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '../../../../../components/AuthContext';
import PropertyForm from '../../../../../components/PropertyForm';
import api from '../../../../../lib/api';
import Link from 'next/link';
import { ArrowLeft } from 'lucide-react';

interface PageProps {
  params: Promise<{ id: string }>;
}

export default function EditPropertyPage({ params }: PageProps) {
  const { id } = use(params);
  const { user, loading: authLoading } = useAuth();
  const router = useRouter();

  // Data states
  const [property, setProperty] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Authenticate user
  useEffect(() => {
    if (!authLoading && (!user || user.role !== 'owner')) {
      router.push('/login');
    }
  }, [user, authLoading, router]);

  // Fetch property details
  useEffect(() => {
    if (user && id) {
      setLoading(true);
      setError(null);
      api.get(`/api/properties/${id}`)
        .then((res) => {
          if (res.data?.success && res.data.property) {
            const p = res.data.property;
            // Verify ownership
            if (Number(p.owner_id) !== Number(user.id)) {
              setError('You are not authorized to edit this listing.');
            } else {
              setProperty(p);
            }
          } else {
            setError('Property not found.');
          }
        })
        .catch((err) => {
          console.error('Error fetching property for edit:', err);
          setError(err.response?.data?.message || 'Property loading failed.');
        })
        .finally(() => setLoading(false));
    }
  }, [id, user]);

  if (authLoading || loading) {
    return (
      <div className="text-center py-5 my-5">
        <div className="spinner-border text-primary" role="status">
          <span className="visually-hidden">Loading property data...</span>
        </div>
      </div>
    );
  }

  if (error || !property) {
    return (
      <div className="container py-5 text-center">
        <div className="alert alert-warning py-4 my-5 shadow-sm">
          <h2 className="fw-bold mb-2">Error</h2>
          <p className="text-muted">{error}</p>
          <Link href="/owner/listings" className="btn btn-danger rounded-pill px-4 fw-semibold text-dark mt-2">
            Back to Listings
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="container py-5 mt-5">
      <div className="row justify-content-center">
        <div className="col-lg-10">
          <Link href="/owner/listings" className="btn btn-link text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-4 p-0">
            <ArrowLeft size={14} />
            <span>Back to Listings</span>
          </Link>

          <h1 className="h3 fw-bold text-dark mb-4">Edit Property Listing</h1>
          <PropertyForm initialData={property} isEdit={true} />
        </div>
      </div>
    </div>
  );
}
