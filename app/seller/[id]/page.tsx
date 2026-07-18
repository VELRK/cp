import React from 'react';
import SellerDetailsClient from '@/components/seller/SellerDetailsClient';

export default function SellerPage({ params }: { params: { id: string } }) {
  return <SellerDetailsClient id={params.id} />;
}
