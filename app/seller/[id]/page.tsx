import React from 'react';
import SellerDetailsClient from '@/components/seller/SellerDetailsClient';

export default async function SellerPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  return <SellerDetailsClient id={id} />;
}
