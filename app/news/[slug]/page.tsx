import NewsDetailClient from './NewsDetailClient';
import { getBuildNewsSlugs } from '@/lib/staticBuildParams';

interface PageProps {
  params: Promise<{ slug: string }>;
}

export function generateStaticParams() {
  const slugs = getBuildNewsSlugs();
  return slugs.length > 0 ? slugs : [{ slug: 'sample' }];
}

export default async function NewsDetailPage({ params }: PageProps) {
  const { slug } = await params;
  return <NewsDetailClient slug={slug} />;
}
