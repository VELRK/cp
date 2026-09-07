import { searchProperties } from './frontendApi';
import type { PropertyTypeItem } from './propertyTypes';

export interface RealFilterFacet {
  propertyTypes: { slug: string; name: string; count: number }[];
  cities: { id: number; name: string; count: number }[];
  bedrooms: { bedrooms: number; label: string; count: number }[];
  listingTypes: { type: string; label: string; count: number }[];
  localities: { name: string; count: number }[];
}

/** Default fallback real data based on the database seed to ensure zero delay */
export const DEFAULT_REAL_FACETS: RealFilterFacet = {
  propertyTypes: [
    { slug: 'apartment', name: 'Apartment / Flat', count: 1 },
    { slug: 'plot', name: 'Plot / Land', count: 1 },
  ],
  cities: [
    { id: 8, name: 'Annur', count: 1 },
    { id: 2, name: 'Saravanampatti', count: 1 },
  ],
  bedrooms: [
    { bedrooms: 2, label: '2 BHK', count: 2 },
  ],
  listingTypes: [
    { type: 'sale', label: 'Buy / Sale', count: 2 },
  ],
  localities: [
    { name: 'Saravanampatti', count: 2 },
  ],
};

let cachedFacets: RealFilterFacet | null = null;
let lastFetchTime = 0;
const CACHE_TTL_MS = 60 * 1000; // 1 minute in-memory cache

/** Convert real property types into PropertyTypeItem format */
export function realPropertyTypesToItems(
  realTypes: { slug: string; name: string; count: number }[]
): PropertyTypeItem[] {
  return realTypes.map((t, idx) => ({
    id: 1000 + idx,
    parent_id: null,
    type_level: 'main',
    name: t.name,
    slug: t.slug,
    sort_order: (idx + 1) * 10,
    is_active: 1,
    sub_types: [],
  }));
}

/** Extract real facets from an array of active properties */
export function extractRealFacetsFromProperties(items: any[]): RealFilterFacet {
  if (!Array.isArray(items) || items.length === 0) {
    return DEFAULT_REAL_FACETS;
  }

  const typeMap = new Map<string, { name: string; count: number }>();
  const cityMap = new Map<number, { name: string; count: number }>();
  const bedMap = new Map<number, number>();
  const listMap = new Map<string, number>();
  const locMap = new Map<string, number>();

  items.forEach((p) => {
    // Property type
    const rawType = (p.property_type || '').trim().toLowerCase();
    if (rawType) {
      const label = (p.property_type_label || '').trim() || 
        (rawType === 'apartment' ? 'Apartment / Flat' : 
         rawType === 'plot' ? 'Plot / Land' : 
         rawType === 'villa' ? 'Villa' : 
         rawType.charAt(0).toUpperCase() + rawType.slice(1));
      
      const cur = typeMap.get(rawType) || { name: label, count: 0 };
      cur.count += 1;
      typeMap.set(rawType, cur);
    }

    // City
    const cityId = p.city_id ? Number(p.city_id) : null;
    const cityName = (p.city_name || '').trim();
    if (cityId && cityName) {
      const cur = cityMap.get(cityId) || { name: cityName, count: 0 };
      cur.count += 1;
      cityMap.set(cityId, cur);
    }

    // Bedrooms (BHK)
    const beds = p.bedrooms !== undefined && p.bedrooms !== null ? Number(p.bedrooms) : null;
    if (beds && beds > 0) {
      bedMap.set(beds, (bedMap.get(beds) || 0) + 1);
    }

    // Listing type
    const listing = (p.listing_type || '').trim().toLowerCase();
    if (listing) {
      listMap.set(listing, (listMap.get(listing) || 0) + 1);
    }

    // Locality
    const loc = (p.locality || '').trim();
    if (loc) {
      // Clean up locality e.g. "Saravanampatti, Coimbatore" -> "Saravanampatti"
      const mainLoc = loc.split(',')[0].trim();
      if (mainLoc) {
        locMap.set(mainLoc, (locMap.get(mainLoc) || 0) + 1);
      }
    }
  });

  const propertyTypes = Array.from(typeMap.entries()).map(([slug, data]) => ({
    slug,
    name: data.name,
    count: data.count,
  }));

  const cities = Array.from(cityMap.entries()).map(([id, data]) => ({
    id,
    name: data.name,
    count: data.count,
  }));

  const bedrooms = Array.from(bedMap.entries())
    .sort(([a], [b]) => a - b)
    .map(([beds, count]) => ({
      bedrooms: beds,
      label: `${beds} BHK`,
      count,
    }));

  const listingTypes = Array.from(listMap.entries()).map(([type, count]) => ({
    type,
    label: type === 'sale' ? 'Buy / Sale' : type === 'rent' ? 'Rent' : type,
    count,
  }));

  const localities = Array.from(locMap.entries())
    .sort((a, b) => b[1] - a[1])
    .map(([name, count]) => ({
      name,
      count,
    }));

  return {
    propertyTypes: propertyTypes.length > 0 ? propertyTypes : DEFAULT_REAL_FACETS.propertyTypes,
    cities: cities.length > 0 ? cities : DEFAULT_REAL_FACETS.cities,
    bedrooms: bedrooms.length > 0 ? bedrooms : DEFAULT_REAL_FACETS.bedrooms,
    listingTypes: listingTypes.length > 0 ? listingTypes : DEFAULT_REAL_FACETS.listingTypes,
    localities: localities.length > 0 ? localities : DEFAULT_REAL_FACETS.localities,
  };
}

/** Fetch active properties to extract real facets with live counts */
export async function fetchRealDataFacets(forceRefresh = false): Promise<RealFilterFacet> {
  const now = Date.now();
  if (!forceRefresh && cachedFacets && now - lastFetchTime < CACHE_TTL_MS) {
    return cachedFacets;
  }

  try {
    const res = await searchProperties({ limit: 100, sort: 'new' });
    if (res.data?.success && Array.isArray(res.data.items) && res.data.items.length > 0) {
      cachedFacets = extractRealFacetsFromProperties(res.data.items);
      lastFetchTime = now;
      return cachedFacets;
    }
  } catch (err) {
    console.warn('Could not fetch real data facets from search API, using defaults', err);
  }

  if (cachedFacets) return cachedFacets;
  cachedFacets = DEFAULT_REAL_FACETS;
  return cachedFacets;
}

/** Synchronous getter for current cached facets or default */
export function getCachedRealFacets(): RealFilterFacet {
  return cachedFacets || DEFAULT_REAL_FACETS;
}
