'use client';

import { useEffect } from 'react';
import { useRouter } from 'next/navigation';

export default function ViewedPropertiesPage() {
  const router = useRouter();

  useEffect(() => {
    router.replace('/owner/listings');
  }, [router]);

  return (
    <div className="container py-5 my-5 text-center">
      <div className="spinner-border text-primary" role="status">
        <span className="visually-hidden">Loading My Properties...</span>
      </div>
      <p className="text-muted mt-3">Loading your properties list...</p>
    </div>
  );
}
