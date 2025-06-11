<?php

namespace App\Service;

class TextService
{
    public const int MODE_RU_EN = 1;
    public const int MODE_EN_RU = 2;
    public const int MODE_BOTH  = 3;
    public const string DEFAULT_PATTERN = '/[^0-9A-Za-zА-Яа-яЁё-]/u';

    protected static array $baseReplaceTable = [
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D',
        'Е' => 'E', 'Ё' => 'YO', 'Ж' => 'ZH', 'З' => 'Z', 'И' => 'I',
        'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N',
        'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
        'У' => 'U', 'Ф' => 'F', 'Х' => 'KH', 'Ц' => 'TS', 'Ч' => 'CH',
        'Ш' => 'SH', 'Щ' => 'SHCH', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '',
        'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA'
    ];

    protected static array $replaceOverrides = [
        self::MODE_EN_RU => [
            'Y' => 'И', 'E' => 'Е', 'C' => 'К', 'H' => 'Х', 'J' => 'Ж',
            'Q' => 'К', 'W' => 'В', 'X' => 'Кс', 'cisco' => 'циско'
        ]
    ];

    protected static array $replaceTable = [];

    /**
     * Подготовить таблицу транслитерации (если ещё не готова)
     */
    protected static function prepareReplaceTable(): void
    {
        if (!empty(self::$replaceTable)) {
            return;
        }

        // RU -> EN
        foreach (self::$baseReplaceTable as $rus => $en) {
            self::$replaceTable[self::MODE_RU_EN][$rus] = $en;
            self::$replaceTable[self::MODE_RU_EN][mb_strtolower($rus)] = mb_strtolower($en);
        }

        // EN -> RU
        $array = array_filter(self::$replaceTable[self::MODE_RU_EN]);
        self::$replaceTable[self::MODE_EN_RU] = array_flip($array);

        $overrides = self::$replaceOverrides[self::MODE_EN_RU];
        foreach ($overrides as $key => $value) {
            self::$replaceTable[self::MODE_EN_RU][$key] = $value;
            self::$replaceTable[self::MODE_EN_RU][mb_strtolower($key)] = mb_strtolower($value);
        }

        // Для EN_RU важен порядок (длинные замены сначала)
        uksort(self::$replaceTable[self::MODE_EN_RU], function ($a, $b) {
            return strlen($b) <=> strlen($a);
        });

        // BOTH
        self::$replaceTable[self::MODE_BOTH] = array_merge(
            self::$replaceTable[self::MODE_RU_EN],
            self::$replaceTable[self::MODE_EN_RU]
        );
    }

    /**
     * Основной метод транслитерации (универсальный)
     */
    public static function transliterate(
        string $string,
        int $mode = self::MODE_RU_EN,
        ?string $pattern = self::DEFAULT_PATTERN,
        bool $lowercase = true,
    ): string {
        self::prepareReplaceTable();

        if ($lowercase) {
            $string = mb_strtolower($string);
        }

        $string = strtr($string, self::$replaceTable[$mode]);
        $string = preg_replace($pattern, '-', $string);

        return preg_replace('/-+/', '-', $string);
    }
}
