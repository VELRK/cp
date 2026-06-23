import BlogDetailClient from './BlogDetailClient';
import { getBuildBlogIds } from '@/lib/staticBuildParams';

interface PageProps {
  params: Promise<{ id: string }>;
}

export function generateStaticParams() {
  const ids = getBuildBlogIds();
  return ids.length > 0 ? ids : [{ id: '1' }];
}

export default async function BlogDetailPage({ params }: PageProps) {
  const { id } = await params;
  return <BlogDetailClient id={id} />;
}
