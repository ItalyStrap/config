<?php

declare(strict_types=1);

namespace ItalyStrap\Config;

trait AccessValueInArrayWithNotationTrait
{

    /**
     * @param array<array-key, mixed> $array
     * @param array<array-key> $levels
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
     * @param array<array-key> $levels
     * @param mixed $value
     * @return bool
     */
    private function appendValue(array &$array, array $levels, $value): bool
    {
        $key = (string)\array_shift($levels);
        if (!empty($levels)) {
            if (!isset($array[$key]) || !\is_array($array[$key])) {
                $array[$key] = [];
            }
            return $this->appendValue($array[$key], $levels, $value);
        }

        /** @psalm-suppress MixedAssignment */
        $array[$key] = $value;

        return true;
    }

    /**
     * @param array<array-key, mixed> $array
     * @param array<array-key> $levels
     * @return bool
     */
    private function deleteValue(array &$array, array $levels): bool
    {
        $key = (string)\array_shift($levels);

        if (!\array_key_exists($key, $array)) {
            return false;
        }

        if (!empty($levels) && \is_array($array[$key])) {
            return $this->deleteValue($array[$key], $levels);
        }

        unset($array[$key]);

        return true;
    }
}
