<?php 

namespace Wpjscc\Docs\Classes;

use League\CommonMark\Normalizer\TextNormalizerInterface;
use Overtrue\Pinyin\Pinyin;

class PinYinSlug implements TextNormalizerInterface
{
    public function normalize(string $text, $context = null): string
    {
        return strtolower(Pinyin::permalink($text));
    }
}