<?php 

namespace Wpjscc\Docs\Classes;

use League\CommonMark\Normalizer\TextNormalizerInterface;

class HashSlug implements TextNormalizerInterface
{
    public function normalize(string $text, $context = null): string
    {
        return md5($text);
    }
}