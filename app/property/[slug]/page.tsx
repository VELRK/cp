import PropertyDetailClient from '@/components/property/PropertyDetailClient';
import { getBuildPropertySlugs } from '@/lib/staticBuildParams';

interface PageProps {
  params: Promise<{ slug: string }>;
}

export function generateStaticParams() {
  return getBuildPropertySlugs();
}

export default async function PropertyPage({ params }: PageProps) {
  const { slug } = await params;
  return <PropertyDetailClient slug={slug} />;
}
