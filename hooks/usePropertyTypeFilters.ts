'use client';

import { useState, useEffect, useCallback, useMemo } from 'react';
import {
  fetchActivePropertyTypes,
  splitPropertyTypeSlug,
  effectivePropertyTypeSlug,
  subTypesForMain,
  type PropertyTypeItem,
} from '@/lib/propertyTypes';

export function usePropertyTypeFilters(initialSlug = '') {
  const [mainTypes, setMainTypes] = useState<PropertyTypeItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [mainTypeSlug, setMainTypeSlug] = useState('');
  const [subTypeSlug, setSubTypeSlug] = useState('');

  useEffect(() => {
    let cancelled = false;
    setLoading(true);
    fetchActivePropertyTypes()
      .then((items) => {
        if (cancelled) return;
        setMainTypes(items);
        const seed = initialSlug || '';
        if (seed) {
          const { mainSlug, subSlug } = splitPropertyTypeSlug(seed, items);
          setMainTypeSlug(mainSlug);
          setSubTypeSlug(subSlug);
        } else if (items.length > 0) {
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
  }, [initialSlug]);

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
