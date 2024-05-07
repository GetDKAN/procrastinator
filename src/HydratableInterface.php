<?php

namespace Procrastinator;

/**
 * Interface to hydrate JSON into PHP variables.
 *
 * This interface is similar to \JsonSerializable in that we can 'hydrate' JSON
 * into PHP structures.
 *
 * @see \Procrastinator\HydratableTrait
 * @see \JsonSerializable
 */
interface HydratableInterface extends \JsonSerializable
{
    /**
     * Hydrate some JSON into a PHP object or array or other variable.
     *
     * @param string $json
     *   The JSON to process.
     * @param $instance
     *   (Optional) Create a new instance without invoking the constructor.
     *
     * @return mixed
     *   The hydrated data structure.
     */
    public static function hydrate(string $json, $instance = null);
}
