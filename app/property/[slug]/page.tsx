import PropertyDetailClient from '@/components/property/PropertyDetailClient';
import { getBuildPropertySlugs } from '@/lib/staticBuildParams';

interface PageProps {
  params: Promise<{ slug: string }>;
}

export function generateStaticParams() {
  return getBuildPropertySlugs();
}

// Static export forbids dynamicParams: true. Unknown slugs use property/__build_placeholder__.
export const dynamicParams = false;

export default async function PropertyPage({ params }: PageProps) {
  const { slug } = await params;
  return <PropertyDetailClient slug={slug} />;
}
