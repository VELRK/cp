import React from 'react';
import SellerDetailsClient from '@/components/seller/SellerDetailsClient';

interface PageProps {
  params: Promise<{ id: string }>;
}

export function generateStaticParams() {
  return [{ id: '__build_placeholder__' }];
}

export default async function SellerPage({ params }: PageProps) {
  const { id } = await params;
  return <SellerDetailsClient id={id} />;
}
