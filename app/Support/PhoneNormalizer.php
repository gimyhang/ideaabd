<?php

namespace App\Support;

class PhoneNormalizer
{
    /**
     * Universal Phone Normalizer for Bangladesh and International numbers.
     *
     * Handles:
     * - Standard BD 11-digit local format: 017XXXXXXXX
     * - BD 10-digit format without leading 0: 17XXXXXXXX -> 017XXXXXXXX
     * - BD 12-digit bug format (country code 88 appended to 10 digits): 8817XXXXXXXX -> 017XXXXXXXX
     * - BD 13-digit format: 88017XXXXXXXX -> 017XXXXXXXX
     * - BD E.164 with plus: +88017XXXXXXXX -> 017XXXXXXXX / +88017XXXXXXXX
     * - Accidental +88 with 11 digits: +88017XXXXXXXX
     * - Bengali numerals: ০১৭২৬৯৭৬৯৮২ -> 01726976982
     * - Spaces, dashes, parentheses: +88 (017) 26-976982 -> 01726976982
     * - Foreign numbers (India +91, UK +44, USA +1, etc.): Preserved accurately
     */
    public static function normalize(?string $rawPhone, ?string $countryCode = '+880'): array
    {
        $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        $str = str_replace($bn, $en, trim((string) $rawPhone));
        $digits = preg_replace('/[^\d]/', '', $str);

        $code = trim((string) ($countryCode ?: ''));
        $codeDigits = preg_replace('/[^\d]/', '', $code);

        // Check if raw input explicitly starts with a non-BD plus country code (e.g. +91, +44, +1)
        $explicitForeignPlus = str_starts_with($str, '+') && !str_starts_with($str, '+88');

        // Explicit non-BD country code chosen in selector
        $explicitForeignCode = !empty($codeDigits) && !in_array($codeDigits, ['880', '88'], true);

        if ($explicitForeignPlus || $explicitForeignCode) {
            $isBd = false;
        } else {
            // Detect if Bangladesh:
            // 1. Country code is +880, 880, +88, 88, or empty
            // 2. OR raw input starts with +880, +88, 880, 881, 01, or 1 (when 10-11 digits)
            $isBdCode = empty($codeDigits) || in_array($codeDigits, ['880', '88'], true);
            $looksLikeBdDigits = str_starts_with($digits, '8801')
                || (str_starts_with($digits, '881') && strlen($digits) === 12)
                || (str_starts_with($digits, '01') && (strlen($digits) === 11 || strlen($digits) === 13))
                || (str_starts_with($digits, '1') && strlen($digits) === 10);

            $isBd = $isBdCode || $looksLikeBdDigits;
        }

        if ($isBd) {
            $working = $digits;

            // Strip leading 880 (e.g. 88017XXXXXXXX -> 17XXXXXXXX or 017XXXXXXXX)
            if (str_starts_with($working, '880')) {
                $working = substr($working, 3);
            }
            // Strip leading 88 if it's the 12-digit format (8817XXXXXXXX -> 17XXXXXXXX)
            elseif (str_starts_with($working, '88') && strlen($working) === 12 && str_starts_with(substr($working, 2), '1')) {
                $working = substr($working, 2);
            }
            // Strip leading 88 if someone wrote 8801... but it became 8801... (already caught) or 88 + 01... (13 digits)
            elseif (str_starts_with($working, '88') && strlen($working) === 13 && str_starts_with(substr($working, 2), '01')) {
                $working = substr($working, 2);
            }

            // Strip leading zeros
            $core = ltrim($working, '0');

            // If the number ends with a valid 10-digit BD mobile number starting with 1 (e.g. 13-19)
            if (strlen($core) > 10 && str_starts_with(substr($core, -10), '1')) {
                $core = substr($core, -10);
            } elseif (strlen($core) > 10) {
                $core = substr($core, 0, 10);
            }

            $localPhone = '0' . $core;             // Always 11 digits: 01XXXXXXXXX
            $formattedPhone = '+880' . $core;      // Always 14 chars: +8801XXXXXXXXX
            $e164Digits = '880' . $core;           // Always 13 digits: 8801XXXXXXXXX

            // All possible formats that could exist in DB or search queries
            $queryPhones = array_values(array_unique(array_filter([
                $localPhone,
                $formattedPhone,
                $e164Digits,
                '88' . $localPhone,       // 8801XXXXXXXXX (13 digits)
                '+88' . $localPhone,      // +8801XXXXXXXXX
                '88' . $core,             // 881XXXXXXXXX (12-digit conflict representation)
                '+88' . $core,            // +881XXXXXXXXX
                $core,                    // 1XXXXXXXXX (10 digits)
                $rawPhone,
                $str,
            ])));

            return [
                'is_bd'           => true,
                'core'            => $core,
                'local_phone'     => $localPhone,
                'formatted_phone' => $formattedPhone,
                'e164'            => $e164Digits,
                'country_code'    => '+880',
                'query_phones'    => $queryPhones,
            ];
        }

        // International numbers
        if ($explicitForeignPlus) {
            $fullPhone = '+' . $digits;
            $prefix = '+' . substr($digits, 0, 2); // approximate
        } else {
            $prefix = str_starts_with($code, '+') ? $code : '+' . $code;
            $cleanIntl = ltrim($digits, '0');
            $fullPhone = $prefix . $cleanIntl;
        }
        $plainDigits = preg_replace('/[^\d]/', '', $fullPhone);
        $cleanIntl = ltrim($digits, '0');

        $queryPhones = array_values(array_unique(array_filter([
            $fullPhone,
            $plainDigits,
            $cleanIntl,
            $rawPhone,
            $str,
        ])));

        return [
            'is_bd'           => false,
            'core'            => $cleanIntl,
            'local_phone'     => $fullPhone,
            'formatted_phone' => $fullPhone,
            'e164'            => $plainDigits,
            'country_code'    => $prefix,
            'query_phones'    => $queryPhones,
        ];
    }

    /**
     * Get clean local phone (e.g. 017XXXXXXXX for BD).
     */
    public static function toLocal(?string $rawPhone, ?string $countryCode = '+880'): string
    {
        return static::normalize($rawPhone, $countryCode)['local_phone'];
    }

    /**
     * Get formatted international phone with plus (e.g. +88017XXXXXXXX).
     */
    public static function toInternational(?string $rawPhone, ?string $countryCode = '+880'): string
    {
        return static::normalize($rawPhone, $countryCode)['formatted_phone'];
    }

    /**
     * Get E.164 digits without plus (e.g. 88017XXXXXXXX for SMS gateways).
     */
    public static function toE164(?string $rawPhone, ?string $countryCode = '+880'): string
    {
        return static::normalize($rawPhone, $countryCode)['e164'];
    }
}
