'use client';

import React from 'react';
import type { PropertyTypeItem } from '@/lib/propertyTypes';

interface PropertyTypeSelectsProps {
  mainTypes: PropertyTypeItem[];
  mainTypeSlug: string;
  subTypeSlug: string;
  subTypes: PropertyTypeItem[];
  onMainChange: (slug: string) => void;
  onSubChange: (slug: string) => void;
  loading?: boolean;
  /** Tailwind/bootstrap select class */
  selectClassName?: string;
  mainWidth?: string;
  subWidth?: string;
  showMain?: boolean;
  mainLabel?: string;
  subLabel?: string;
}

const PropertyTypeSelects: React.FC<PropertyTypeSelectsProps> = ({
  mainTypes,
  mainTypeSlug,
  subTypeSlug,
  subTypes,
  onMainChange,
  onSubChange,
  loading = false,
  selectClassName = 'form-select',
  mainWidth = '160px',
  subWidth = '170px',
  showMain = true,
  mainLabel = 'Main type',
  subLabel = 'Sub type',
}) => {
  const showSub = Boolean(mainTypeSlug) && subTypes.length > 0;

  return (
    <>
      {showMain && (
        <div className="nb-search-select-premium-wrap" style={{ width: mainWidth }}>
          <select
            className={selectClassName}
            value={mainTypeSlug}
            onChange={(e) => onMainChange(e.target.value)}
            disabled={loading}
            aria-label="Main property type"
          >
            <option value="">{mainLabel}</option>
            {mainTypes.map((m) => (
              <option key={m.id} value={m.slug}>
                {m.name}
              </option>
            ))}
          </select>
        </div>
      )}

      {showSub && (
        <div className="nb-search-select-premium-wrap" style={{ width: subWidth }}>
          <select
            className={selectClassName}
            value={subTypeSlug}
            onChange={(e) => onSubChange(e.target.value)}
            disabled={loading}
            aria-label="Sub property type"
          >
            <option value="">{subLabel}</option>
            {subTypes.map((s) => (
              <option key={s.id} value={s.slug}>
                {s.name}
              </option>
            ))}
          </select>
        </div>
      )}
    </>
  );
};

/** Stacked filters for sidebar forms (search page). */
export const PropertyTypeFilterFields: React.FC<
  Omit<PropertyTypeSelectsProps, 'mainWidth' | 'subWidth' | 'showMain'>
> = ({
  mainTypes,
  mainTypeSlug,
  subTypeSlug,
  subTypes,
  onMainChange,
  onSubChange,
  loading,
  selectClassName = 'form-select form-select-sm nb-filter-control',
  mainLabel = 'Any main type',
  subLabel = 'Any sub type',
}) => {
  const showSub = Boolean(mainTypeSlug) && subTypes.length > 0;

  return (
    <>
      <div className="mb-3">
        <label className="form-label nb-filter-label">Main property type</label>
        <select
          className={selectClassName}
          value={mainTypeSlug}
          onChange={(e) => onMainChange(e.target.value)}
          disabled={loading}
        >
          <option value="">{mainLabel}</option>
          {mainTypes.map((m) => (
            <option key={m.id} value={m.slug}>
              {m.name}
            </option>
          ))}
        </select>
      </div>

      {showSub && (
        <div className="mb-3">
          <label className="form-label nb-filter-label">Sub property type</label>
          <select
            className={selectClassName}
            value={subTypeSlug}
            onChange={(e) => onSubChange(e.target.value)}
            disabled={loading}
          >
            <option value="">{subLabel}</option>
            {subTypes.map((s) => (
              <option key={s.id} value={s.slug}>
                {s.name}
              </option>
            ))}
          </select>
        </div>
      )}
    </>
  );
};

export default PropertyTypeSelects;
