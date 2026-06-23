import OwnerPropertyEditClient from './OwnerPropertyEditClient';

interface PageProps {
  params: Promise<{ id: string }>;
}

export function generateStaticParams() {
  return [{ id: '0' }];
}

export default async function OwnerPropertyEditPage({ params }: PageProps) {
  const { id } = await params;
  return <OwnerPropertyEditClient id={id} />;
}
