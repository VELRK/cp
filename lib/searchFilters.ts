/** Slugs used as Buy/Rent tabs (not nb_properties.property_type values). */
const LISTING_INTENT_SLUGS: Record<string, 'sale' | 'rent'> = {
  buy: 'sale',
  sale: 'sale',
  sell: 'sale',
  rent: 'rent',
  lease: 'rent',
  pg: 'rent',
};

export function listingTypeFromSlug(slug: string | null | undefined): 'sale' | 'rent' | null {
  if (!slug) return null;
  return LISTING_INTENT_SLUGS[slug.trim().toLowerCase()] ?? null;
}

export function isListingIntentSlug(slug: string | null | undefined): boolean {
  return listingTypeFromSlug(slug) !== null;
}

export function normalizeListingTypeParam(
  value: string | null | undefined
): 'sale' | 'rent' | '' {
  const mapped = listingTypeFromSlug(value);
  if (mapped) return mapped;
  const v = (value || '').trim().toLowerCase();
  if (v === 'sale' || v === 'rent') return v;
  return '';
}

/**
 * Resolve listing_type + property_type from URL params.
 * Buy/Rent tab slugs in property_type become listing_type for the API.
 */
export function resolveSearchFilterParams(sp: URLSearchParams): {
  listing_type: string;
  property_type: string;
} {
  let listing_type = normalizeListingTypeParam(sp.get('listing_type'));
  let property_type = (sp.get('property_type') || '').trim();

  if (!listing_type && property_type) {
    const fromProperty = listingTypeFromSlug(property_type);
    if (fromProperty) {
      listing_type = fromProperty;
      property_type = '';
    }
  }

  return { listing_type, property_type };
}

export function buildSearchApiParams(
  sp: URLSearchParams,
  page = 1,
  limit = 12
): Record<string, string | number> {
  const params: Record<string, string | number> = { page, limit };
  const { listing_type, property_type } = resolveSearchFilterParams(sp);

  const passthrough = [
    'city_id',
    'q',
    'min_price',
    'max_price',
    'bedrooms',
    'sort',
  ] as const;

  passthrough.forEach((key) => {
    const value = sp.get(key);
    if (value) params[key] = value;
  });

  if (listing_type) params.listing_type = listing_type;
  if (property_type) params.property_type = property_type;

  if (sp.get('is_recommended')) params.is_recommended = 1;
  if (sp.get('is_newly_launched')) params.is_newly_launched = 1;
  if (sp.get('is_verified_property') || sp.get('verified')) params.is_verified_property = 1;
  if (sp.get('posted_by_owner') || sp.get('owner_only') || sp.get('owner')) {
    params.posted_by_owner = 1;
  }
  if (sp.get('has_video') || sp.get('video')) params.has_video = 1;
  if (sp.get('ready_to_move')) params.ready_to_move = 1;
  if (sp.get('under_construction')) params.under_construction = 1;

  return params;
}

/** Build /search URL query from home/search form state. */
export function buildSearchUrlParams(input: {
  mainTypeSlug?: string;
  subTypeSlug?: string;
  propertyType?: string;
  listingType?: string;
  cityId?: string;
  q?: string;
  minPrice?: string;
  maxPrice?: string;
  bedrooms?: string;
  sort?: string;
}): URLSearchParams {
  const qp = new URLSearchParams();
  const mainListing = listingTypeFromSlug(input.mainTypeSlug);
  const explicitListing = normalizeListingTypeParam(input.listingType);
  const listing_type = explicitListing || mainListing;

  if (listing_type) {
    qp.set('listing_type', listing_type);
  }

  const sub = (input.subTypeSlug || '').trim();
  const prop = (input.propertyType || '').trim();
  if (sub) {
    qp.set('property_type', sub);
  } else if (prop && !listingTypeFromSlug(prop)) {
    qp.set('property_type', prop);
  }

  if (input.cityId) qp.set('city_id', input.cityId);
  if (input.q?.trim()) qp.set('q', input.q.trim());
  if (input.minPrice) qp.set('min_price', input.minPrice);
  if (input.maxPrice) qp.set('max_price', input.maxPrice);
  if (input.bedrooms) qp.set('bedrooms', input.bedrooms);
  if (input.sort) qp.set('sort', input.sort);

  return qp;
}
