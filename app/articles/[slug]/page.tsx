import ArticleDetailClient from './ArticleDetailClient';
import { getBuildArticleSlugs } from '@/lib/staticBuildParams';

interface PageProps {
  params: Promise<{ slug: string }>;
}

export function generateStaticParams() {
  const slugs = getBuildArticleSlugs();
  return slugs.length > 0 ? slugs : [{ slug: 'sample' }];
}

export default async function ArticleDetailPage({ params }: PageProps) {
  const { slug } = await params;
  return <ArticleDetailClient slug={slug} />;
}
