'use client';

import { useState, useEffect, useCallback, useMemo } from 'react';
import {
  fetchActivePropertyTypes,
  splitPropertyTypeSlug,
  effectivePropertyTypeSlug,
  subTypesForMain,
  type PropertyTypeItem,
} from '@/lib/propertyTypes';
import { realPropertyTypesToItems, DEFAULT_REAL_FACETS } from '@/lib/realDataFilters';

interface UsePropertyTypeFiltersOptions {
  realDataOnly?: boolean;
  defaultAll?: boolean;
}

export function usePropertyTypeFilters(
  initialSlug = '',
  options: UsePropertyTypeFiltersOptions = {}
) {
  const { realDataOnly = true, defaultAll = true } = options;

  // Initialize with real data fallback so there is never an empty state or layout shift
  const [mainTypes, setMainTypes] = useState<PropertyTypeItem[]>(() =>
    realPropertyTypesToItems(DEFAULT_REAL_FACETS.propertyTypes)
  );
  const [loading, setLoading] = useState(false);
  const [mainTypeSlug, setMainTypeSlug] = useState(initialSlug || '');
  const [subTypeSlug, setSubTypeSlug] = useState('');

  useEffect(() => {
    let cancelled = false;
    fetchActivePropertyTypes(realDataOnly)
      .then((items) => {
        if (cancelled) return;
        if (items.length > 0) {
          setMainTypes(items);
        }
        const seed = initialSlug || '';
        if (seed) {
          const { mainSlug, subSlug } = splitPropertyTypeSlug(seed, items);
          setMainTypeSlug(mainSlug);
          setSubTypeSlug(subSlug);
        } else if (!defaultAll && items.length > 0) {
          setMainTypeSlug(items[0].slug);
        }
      })
      .catch((e) => console.warn('Could not load property types', e))
      .finally(() => {
        if (!cancelled) setLoading(false);
      });
    return () => {
      cancelled = true;
    };
  }, [initialSlug, realDataOnly, defaultAll]);

  const subTypes = useMemo(
    () => subTypesForMain(mainTypes, mainTypeSlug),
    [mainTypes, mainTypeSlug]
  );

  const propertyType = effectivePropertyTypeSlug(mainTypeSlug, subTypeSlug);

  const setMainType = useCallback((slug: string) => {
    setMainTypeSlug(slug);
    setSubTypeSlug('');
  }, []);

  const resetSubType = useCallback(() => setSubTypeSlug(''), []);

  return {
    mainTypes,
    mainTypeSlug,
    subTypeSlug,
    subTypes,
    propertyType,
    loading,
    setMainTypeSlug: setMainType,
    setSubTypeSlug,
    resetSubType,
  };
}
