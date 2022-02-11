<?php

namespace App\Common;

use \NumberFormatter;

// класс хелперов для форматирования печатных форм
class PrintFormFormats
{
    // форматирование денег для печатной формы
    public static function format_money($num)
    {
        $fmt = new NumberFormatter("ru-RU", NumberFormatter::DECIMAL);
        $fmt->setAttribute(NumberFormatter::FRACTION_DIGITS, 2);
        return $fmt->format($num);
    }
    // форматирование количества для печатной формы
    public static function format_kolvo($num)
    {
        $fmt = new NumberFormatter("ru-RU", NumberFormatter::DECIMAL);
        $fmt->setAttribute(NumberFormatter::FRACTION_DIGITS, 3);
        return $fmt->format($num);
    }
    // форматирование целых чисел для печатной формы
    public static function format_int($num)
    {
        $fmt = new NumberFormatter("ru-RU", NumberFormatter::DECIMAL);
        $fmt->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);
        return $fmt->format($num);
    }

    // сумма прописью
    public static function propis($num, $use_number_formatter = false)
    {
        if ($use_number_formatter) {
            $intPart = sprintf('%d', $num);
            $fractionalPart = round(($num - $intPart) * 100);
            $int_propis = new NumberFormatter('ru-RU', NumberFormatter::SPELLOUT);
            $res = $int_propis->format($intPart);
            $res .= ' руб. ';
            $fract_propis = new NumberFormatter('ru-RU', NumberFormatter::SPELLOUT);
            $res .= $fract_propis->format($fractionalPart);
            $res .= ' коп.';
            return self::mb_ucfirst($res);
        } else {
            return self::num2str($num);
        }
    }

    // первая буква заглавная для русского языка в т.ч.
    public static function mb_ucfirst($str, $enc = 'UTF-8')
    {
        return mb_strtoupper(mb_substr($str, 0, 1, $enc), $enc) . mb_substr($str, 1, mb_strlen($str, $enc), $enc);
    }

    // самописка для суммы прописью
    private static function num2str($num)
    {
        $nul = 'ноль';
        $ten = array(
            array('', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'),
            array('', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять')
        );
        $a20 = array('десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать');
        $tens = array(2 => 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто');
        $hundred = array('', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот');
        $unit = array(
            array('копейка', 'копейки',   'копеек',     1),
            array('рубль',    'рубля',     'рублей',     0),
            array('тысяча',   'тысячи',    'тысяч',      1),
            array('миллион',  'миллиона',  'миллионов',  0),
            array('миллиард', 'миллиарда', 'миллиардов', 0),
        );

        list($rub, $kop) = explode('.', sprintf("%015.2f", floatval($num)));
        $out = array();
        if (intval($rub) > 0) {
            foreach (str_split($rub, 3) as $uk => $v) {
                if (!intval($v)) continue;
                $uk = sizeof($unit) - $uk - 1;
                $gender = $unit[$uk][3];
                list($i1, $i2, $i3) = array_map('intval', str_split($v, 1));
                // mega-logic
                $out[] = $hundred[$i1]; // 1xx-9xx
                if ($i2 > 1) $out[] = $tens[$i2] . ' ' . $ten[$gender][$i3]; // 20-99
                else $out[] = $i2 > 0 ? $a20[$i3] : $ten[$gender][$i3]; // 10-19 | 1-9
                // units without rub & kop
                if ($uk > 1) $out[] = self::morph($v, $unit[$uk][0], $unit[$uk][1], $unit[$uk][2]);
            }
        } else {
            $out[] = $nul;
        }
        $out[] = self::morph(intval($rub), $unit[1][0], $unit[1][1], $unit[1][2]); // rub
        $out[] = $kop . ' ' . self::morph($kop, $unit[0][0], $unit[0][1], $unit[0][2]); // kop
        return self::mb_ucfirst(trim(preg_replace('/ {2,}/', ' ', join(' ', $out))));
    }

    // склонение словоформы для суммы прописью
    private static function morph($n, $f1, $f2, $f5)
    {
        $n = abs(intval($n)) % 100;
        if ($n > 10 && $n < 20) return $f5;
        $n = $n % 10;
        if ($n > 1 && $n < 5) return $f2;
        if ($n == 1) return $f1;
        return $f5;
    }
}