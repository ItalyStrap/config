<?php

declare(strict_types=1);

namespace ItalyStrap\Config;

trait AccessValueInArrayWithNotationTrait
{
    /**
     * @param array<array-key, mixed> $array
     * @param array<array-key, string> $levels
     * @param mixed $default
     * @return mixed
     */
    private function findValue(array $array, array $levels, $default = null)
    {
        foreach ($levels as $level) {
            if (!\is_array($array)) {
                $array = (array)$array;
            }

            if (!\array_key_exists($level, $array)) {
                return $default;
            }

            /** @psalm-suppress MixedAssignment */
            $array = $array[$level];
        }

        return $array;
    }

    /**
     * @param array<array-key, mixed> $array
     * @param array<array-key, string> $levels
     * @param mixed $value
     */
    private function insertValue(array &$array, array $levels, $value): bool
    {
        $key = \array_shift($levels);
        /**
         * If the $key is empty string '' return false
         * If the key is 0 or '0' return true because is a valid key for an array
         */
        if (empty($key) && !\is_numeric($key)) {
            return false;
        }

        if ($levels === []) {
            /** @psalm-suppress MixedAssignment */
            $array[$key] = $value;
            return true;
        }

        if (!\array_key_exists($key, $array) || !\is_array($array[$key])) {
            $array[$key] = [];
        }

        return $this->insertValue($array[$key], $levels, $value);
    }

    /**
     * @param array<array-key, mixed> $array
     * @param array<array-key, string> $levels
     */
    private function deleteValue(array &$array, array $levels): bool
    {
        $key = \array_shift($levels);
        if (empty($key) && !\is_numeric($key)) {
            return false;
        }

        if (!\array_key_exists($key, $array)) {
            return true;
        }

        if ($levels !== [] && \is_array($array[$key])) {
            return $this->deleteValue($array[$key], $levels);
        }

        unset($array[$key]);
        return true;
    }
}
