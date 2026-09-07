/**
 * Blog Utilities for Curation, Fallback Imagery, Category Badges, and Reading Time Calculation
 */

export const BLOG_FALLBACK_IMAGES: Record<string, string> = {
  market: 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80', // Modern glass towers
  tips: 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=1200&q=80',   // Warm modern interior
  legal: 'https://images.unsplash.com/photo-1450133064473-71024230f91b?auto=format&fit=crop&w=1200&q=80',  // Architecture & documentation desk
  investment: 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=1200&q=80', // Luxury villa & landscape
  news: 'https://images.unsplash.com/photo-1577495508048-b635879837f1?auto=format&fit=crop&w=1200&q=80', // Urban skyline
  default: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80', // Contemporary residence
};

export function getBlogCategoryKey(category?: string, title?: string): 'news' | 'tax' | 'guide' | 'investment' {
  const cat = (category || '').toLowerCase();
  const name = (title || '').toLowerCase();

  if (cat.includes('tax') || cat.includes('legal') || name.includes('tax') || name.includes('legal') || name.includes('regist') || name.includes('rera') || name.includes('stamp') || name.includes('deed')) {
    return 'tax';
  }
  if (cat.includes('guide') || cat.includes('help') || cat.includes('tips') || name.includes('guide') || name.includes('checklist') || name.includes('tips') || name.includes('how to') || name.includes('beginners')) {
    return 'guide';
  }
  if (cat.includes('invest') || name.includes('invest') || name.includes('construction') || name.includes('buy') || name.includes('market') || name.includes('cost') || name.includes('suburb') || name.includes('metro')) {
    return 'investment';
  }
  return 'news';
}

export function getBlogCategoryLabel(category?: string, title?: string): string {
  const key = getBlogCategoryKey(category, title);
  switch (key) {
    case 'tax':
      return 'Tax & Legal';
    case 'guide':
      return 'Help Guides';
    case 'investment':
      return 'Investment & Trends';
    case 'news':
    default:
      return 'Market News';
  }
}

export function getBlogFallbackImage(category?: string, title?: string): string {
  const key = getBlogCategoryKey(category, title);
  if (key === 'tax') return BLOG_FALLBACK_IMAGES.legal;
  if (key === 'guide') return BLOG_FALLBACK_IMAGES.tips;
  if (key === 'investment') return BLOG_FALLBACK_IMAGES.investment;
  return BLOG_FALLBACK_IMAGES.market;
}

export function getBlogReadTime(content?: string, defaultMinutes = 3): number {
  if (!content) return defaultMinutes;
  const words = content.replace(/<[^>]*>/g, ' ').trim().split(/\s+/).length;
  const readTime = Math.ceil(words / 180);
  return Math.max(1, Math.min(readTime, 15));
}
