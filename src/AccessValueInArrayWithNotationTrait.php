<?php

declare(strict_types=1);

namespace ItalyStrap\Config;

trait AccessValueInArrayWithNotationTrait
{

    private function findValue(array $array, array $levels, $default = null)
    {
        foreach ($levels as $level) {
            if (!\is_array($array)) {
                $array = (array)$array;
            }

            if (!\array_key_exists($level, $array)) {
                return $default;
            }

            $array = $array[$level];
        }

        return $array;
    }

    private function appendValue(array &$array, array $levels, $value): bool
    {
        $key = \array_shift($levels);
        if (!empty($levels)) {
            if (!isset($array[$key]) || !\is_array($array[$key])) {
                $array[$key] = [];
            }
            return $this->appendValue($array[$key], $levels, $value);
        }

        $array[$key] = $value;

        return true;
    }

    private function deleteValue(array &$array, array $levels): bool
    {
        $key = \array_shift($levels);

        if (!isset($array[$key])) {
            return false;
        }

        if (!empty($levels)) {
            return $this->deleteValue($array[$key], $levels);
        }

        unset($array[$key]);

        return true;
    }
}
