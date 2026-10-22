<?php

if (! function_exists('brl')) {
    function brl(float|int|string|null $value): string
    {
        return 'R$ '.number_format((float) $value, 2, ',', '.');
    }
}

if (! function_exists('parse_money')) {
    /** Aceita "1.299,90", "1299,90" ou "1299.90". */
    function parse_money(mixed $value): mixed
    {
        if (! is_string($value) || trim($value) === '') {
            return $value;
        }
        $value = str_replace(['R$', ' '], '', $value);
        if (str_contains($value, ',')) {
            $value = str_replace(['.', ','], ['', '.'], $value);
        }

        return $value;
    }
}
