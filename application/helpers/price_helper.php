<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('dvm_trim_decimal')) {
    function dvm_trim_decimal($v)
    {
        $s = number_format((float) $v, 2, '.', '');
        $s = rtrim(rtrim($s, '0'), '.');
        return ($s === '') ? '0' : $s;
    }
}

if (!function_exists('dvm_format_price_inr')) {
    /**
     * Format a numeric amount as Indian Lakhs / Crore (e.g. 1000000 → "₹10 Lakhs").
     *
     * @param float|string $amount
     * @param bool         $include_rupee_prefix Include ₹ before the number part
     */
    function dvm_format_price_inr($amount, $include_rupee_prefix = true)
    {
        if ($amount === null || $amount === '') {
            return '';
        }
        $n = (float) $amount;
        if ($n <= 0) {
            return '';
        }

        $crore = 10000000.0;
        $lakh  = 100000.0;
        $rupee = $include_rupee_prefix ? '₹' : '';

        if ($n >= $crore) {
            $v = $n / $crore;
            $s = dvm_trim_decimal($v);
            $unit = (abs((float) $s - 1.0) < 0.0001) ? 'Crore' : 'Crores';
            return $rupee . $s . ' ' . $unit;
        }
        if ($n >= $lakh) {
            $v = $n / $lakh;
            $s = dvm_trim_decimal($v);
            $unit = (abs((float) $s - 1.0) < 0.0001) ? 'Lakh' : 'Lakhs';
            return $rupee . $s . ' ' . $unit;
        }

        return $rupee . number_format($n, 0, '.', ',');
    }
}

if (!function_exists('dvm_property_price_display')) {
    /**
     * Full price line for property cards / detail: formatted INR + optional suffix text.
     *
     * @param array $property keys propertyPriceRange, propertyPriceRangeText
     */
    function dvm_property_price_display($property)
    {
        $raw = isset($property['propertyPriceRange']) ? $property['propertyPriceRange'] : null;
        $suffix = isset($property['propertyPriceRangeText']) ? trim((string) $property['propertyPriceRangeText']) : '';

        if ($raw === null || $raw === '') {
            return $suffix !== '' ? htmlspecialchars($suffix) : '';
        }

        if (is_numeric($raw)) {
            $main = dvm_format_price_inr((float) $raw, true);
            if ($main === '') {
                return $suffix !== '' ? htmlspecialchars($suffix) : '';
            }
            return $main . ($suffix !== '' ? ' ' . htmlspecialchars($suffix) : '');
        }

        return '₹' . htmlspecialchars((string) $raw) . ($suffix !== '' ? ' ' . htmlspecialchars($suffix) : '');
    }
}

if (!function_exists('dvm_property_price_data_attr')) {
    /** Plain text for data-property-price attributes (modals, JS). */
    function dvm_property_price_data_attr($property)
    {
        $raw = isset($property['propertyPriceRange']) ? $property['propertyPriceRange'] : null;
        $suffix = isset($property['propertyPriceRangeText']) ? trim((string) $property['propertyPriceRangeText']) : '';

        if ($raw === null || $raw === '') {
            return $suffix;
        }
        if (is_numeric($raw)) {
            $main = dvm_format_price_inr((float) $raw, true);
            return trim($main . ($suffix !== '' ? ' ' . $suffix : ''));
        }

        return trim('₹' . (string) $raw . ($suffix !== '' ? ' ' . $suffix : ''));
    }
}
