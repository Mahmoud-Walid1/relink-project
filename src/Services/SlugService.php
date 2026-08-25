<?php

class SlugService {
    /**
     * Generate clean web slug automatically from title or fallback
     */
    public static function createSlug(string $title, ?string $customSlug = null): string {
        if (!empty(trim($customSlug))) {
            return self::sanitizeSlug($customSlug);
        }

        $arabicToLatin = [
            'أ' => 'a', 'إ' => 'a', 'آ' => 'a', 'ا' => 'a', 'ب' => 'b', 'ت' => 't', 'ث' => 'th',
            'ج' => 'j', 'ح' => 'h', 'خ' => 'kh', 'د' => 'd', 'ذ' => 'dh', 'ر' => 'r', 'ز' => 'z',
            'س' => 's', 'ش' => 'sh', 'ص' => 's', 'ض' => 'd', 'ط' => 't', 'ظ' => 'z', 'ع' => 'a',
            'غ' => 'gh', 'ف' => 'f', 'ق' => 'q', 'ك' => 'k', 'ل' => 'l', 'م' => 'm', 'ن' => 'n',
            'ه' => 'h', 'و' => 'w', 'ي' => 'y', 'ى' => 'a', 'ة' => 'h', 'ء' => 'a', 'ئ' => 'y', 'ؤ' => 'w',
            '0' => '0', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9'
        ];

        $slug = mb_strtolower(trim($title), 'UTF-8');
        $slugStr = '';
        $len = mb_strlen($slug, 'UTF-8');

        for ($i = 0; $i < $len; $i++) {
            $char = mb_substr($slug, $i, 1, 'UTF-8');
            if (isset($arabicToLatin[$char])) {
                $slugStr .= $arabicToLatin[$char];
            } elseif (preg_match('/[a-z0-9]/', $char)) {
                $slugStr .= $char;
            } elseif (in_array($char, [' ', '-', '_'])) {
                $slugStr .= '-';
            }
        }

        $slugStr = preg_replace('/-+/', '-', $slugStr);
        $slugStr = trim($slugStr, '-');

        if (empty($slugStr)) {
            $slugStr = 'item-' . substr(md5(uniqid('', true)), 0, 6);
        }

        return $slugStr;
    }

    public static function sanitizeSlug(string $slug): string {
        $slug = mb_strtolower(trim($slug), 'UTF-8');
        $slug = preg_replace('/[^a-z0-9\-_]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}
