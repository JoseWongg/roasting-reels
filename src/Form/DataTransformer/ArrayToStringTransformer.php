<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

// This class transforms an array to a JSON string and back
class ArrayToStringTransformer implements DataTransformerInterface
{
    // Transforms an array to a JSON string
    public function transform($value): string
    {
        // Transform the array to a JSON string
        if (null === $value) {
            return '';
        }
        // Check if the value is an array
        if (!is_array($value)) {
            throw new TransformationFailedException('Expected an array.');
        }
        // Return the JSON encoded value
        return json_encode($value);
    }


    // Transforms a JSON string to an array
    public function reverseTransform($value): array
    {
        // Transform the string back to an array
        if (!$value) {
            // If the value is empty, return an empty array
            return [];
        }

        // Decode the JSON string
        $decoded = json_decode($value, true);

        // If the JSON decoding failed
        if (null === $decoded) {
            // Throw an exception
            throw new TransformationFailedException('Invalid JSON.');
        }
        // Return the decoded value
        return $decoded;
    }
}