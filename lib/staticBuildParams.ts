/**
 * Slugs/ids fetched at build time for static export (see scripts/build-deploy.mjs).
 */
import { PROPERTY_PLACEHOLDER_SLUG } from './propertySlug';

export function getBuildPropertySlugs(): { slug: string }[] {
  const raw = process.env.BUILD_PROPERTY_SLUGS || '';
  const slugs = raw
    .split(',')
    .map((s) => s.trim())
    .filter(Boolean);
  if (!slugs.includes(PROPERTY_PLACEHOLDER_SLUG)) {
    slugs.push(PROPERTY_PLACEHOLDER_SLUG);
  }
  return slugs.map((slug) => ({ slug }));
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
