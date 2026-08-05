<?php

declare(strict_types=1);

final class CodeGenerator
{
    public static function generate(string $prefix, ?string $lastCode): string
    {
        if (empty($lastCode)) {
            return $prefix . '00001';
        }

        $number = (int) substr($lastCode, strlen($prefix));

        $number++;

        return sprintf(
            '%s%05d',
            $prefix,
            $number
        );
    }
}