import { getPropertyTypes } from './frontendApi';
import { fetchRealDataFacets, realPropertyTypesToItems, DEFAULT_REAL_FACETS } from './realDataFilters';

export interface PropertyTypeItem {
  id: number;
  parent_id: number | null;
  type_level: 'main' | 'sub';
  name: string;
  slug: string;
  sort_order: number;
  is_active: number;
  sub_types?: PropertyTypeItem[];
  count?: number;
}

let cachedPropertyTypes: PropertyTypeItem[] | null = null;

/** Load active main types with nested sub_types from API, optionally filtered to real data only. */
export async function fetchActivePropertyTypes(realDataOnly = false): Promise<PropertyTypeItem[]> {
  if (realDataOnly) {
    try {
      const facets = await fetchRealDataFacets();
      if (facets.propertyTypes.length > 0) {
        return realPropertyTypesToItems(facets.propertyTypes);
      }
    } catch (err) {
      console.warn('Using default real property types', err);
    }
    return realPropertyTypesToItems(DEFAULT_REAL_FACETS.propertyTypes);
  }

  if (cachedPropertyTypes && cachedPropertyTypes.length > 0) {
    return cachedPropertyTypes;
  }

  try {
    const res = await getPropertyTypes();
    if (res.data?.success && Array.isArray(res.data.items) && res.data.items.length > 0) {
      cachedPropertyTypes = res.data.items as PropertyTypeItem[];
      return cachedPropertyTypes;
    }
  } catch (err) {
    console.warn('Could not fetch property types from API, using real data fallback', err);
  }

  return realPropertyTypesToItems(DEFAULT_REAL_FACETS.propertyTypes);
}

/** Resolve main + sub slugs from a stored property_type slug. */
export function splitPropertyTypeSlug(
  slug: string,
  mainTypes: PropertyTypeItem[]
): { mainSlug: string; subSlug: string } {
  if (!slug) {
    return { mainSlug: '', subSlug: '' };
  }
  for (const main of mainTypes) {
    if (main.slug === slug) {
      return { mainSlug: slug, subSlug: '' };
    }
    for (const sub of main.sub_types || []) {
      if (sub.slug === slug) {
        return { mainSlug: main.slug, subSlug: sub.slug };
      }
    }
  }
  return { mainSlug: slug, subSlug: '' };
}

export function effectivePropertyTypeSlug(mainSlug: string, subSlug: string): string {
  return subSlug || mainSlug || '';
}

export function subTypesForMain(
  mainTypes: PropertyTypeItem[],
  mainSlug: string
): PropertyTypeItem[] {
  const main = mainTypes.find((m) => m.slug === mainSlug);
  return main?.sub_types || [];
}
