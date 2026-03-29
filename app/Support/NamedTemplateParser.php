<?php

namespace App\Support;

use App\Interfaces\TemplateParser;

class NamedTemplateParser implements TemplateParser
{
    public function parse(string $template, array $context): string
    {
        return preg_replace_callback('/{{\s*(.*?)\s*}}/', function (array $matches) use ($context) {
            $path  = explode('.', (string) $matches[1]);
            $value = $context;

            foreach ($path as $segment) {
                if (is_array($value) && isset($value[$segment])) {
                    $value = $value[$segment];
                } elseif (is_object($value) && isset($value->{$segment})) {
                    $value = $value->{$segment};
                } else {
                    return $matches[0];
                }
            }

            return $value;
        }, $template);
    }
}
