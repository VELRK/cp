import { getPropertyTypes } from './frontendApi';

export interface PropertyTypeItem {
  id: number;
  parent_id: number | null;
  type_level: 'main' | 'sub';
  name: string;
  slug: string;
  sort_order: number;
  is_active: number;
  sub_types?: PropertyTypeItem[];
}

/** Load active main types with nested sub_types from API. */
export async function fetchActivePropertyTypes(): Promise<PropertyTypeItem[]> {
  const res = await getPropertyTypes();
  if (res.data?.success && Array.isArray(res.data.items)) {
    return res.data.items as PropertyTypeItem[];
  }
  return [];
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
