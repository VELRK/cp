/**
 * Slugs/ids fetched at build time for static export (see scripts/build-deploy.mjs).
 */
const PLACEHOLDER_SLUG = '__build_placeholder__';

export function getBuildPropertySlugs(): { slug: string }[] {
  const raw = process.env.BUILD_PROPERTY_SLUGS || '';
  const slugs = raw
    .split(',')
    .map((s) => s.trim())
    .filter(Boolean)
    .map((slug) => ({ slug }));
  return slugs.length > 0 ? slugs : [{ slug: PLACEHOLDER_SLUG }];
}

export function getBuildBlogIds(): { id: string }[] {
  const raw = process.env.BUILD_BLOG_IDS || '';
  return raw
    .split(',')
    .map((s) => s.trim())
    .filter(Boolean)
    .map((id) => ({ id }));
}

export function getBuildArticleSlugs(): { slug: string }[] {
  const raw = process.env.BUILD_ARTICLE_SLUGS || '';
  return raw
    .split(',')
    .map((s) => s.trim())
    .filter(Boolean)
    .map((slug) => ({ slug }));
}

export function getBuildNewsSlugs(): { slug: string }[] {
  const raw = process.env.BUILD_NEWS_SLUGS || '';
  return raw
    .split(',')
    .map((s) => s.trim())
    .filter(Boolean)
    .map((slug) => ({ slug }));
}
