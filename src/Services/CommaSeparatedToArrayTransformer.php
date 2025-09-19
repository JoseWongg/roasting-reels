<?php

namespace App\Services;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * This class is used to transform a comma-separated string to an array and vice versa.
 */
class CommaSeparatedToArrayTransformer implements DataTransformerInterface

{
    /**
     * @param $array
     * @return mixed|string|null
     */
    public function transform($array): mixed
    {
        if (null === $array) {
            return '';
        }

        return implode(', ', $array);
    }

    /**
     * @param $string
     * @return array|mixed|null
     */
    public function reverseTransform($string): mixed
    {
        if (!$string) {
            return [];
        }

        return array_map('trim', explode(',', $string));
    }
}