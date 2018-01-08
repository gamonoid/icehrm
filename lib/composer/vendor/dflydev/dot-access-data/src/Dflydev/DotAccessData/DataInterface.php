<?php

/*
 * This file is a part of dflydev/dot-access-data.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dflydev\DotAccessData;

interface DataInterface
{
    /**
     * Append a value to a key (assumes key refers to an array value)
     *
     * @param string $key
     * @param mixed  $value
     */
    public function append($key, $value = null);

    /**
     * Set a value for a key
     *
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value = null);

    /**
     * Remove a key
     *
     * @param string $key
     */
    public function remove($key);

    /**
     * Get the raw value for a key
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Check if the key exists
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key);

    /**
     * Get a data instance for a key
     *
     * @param string $key
     *
     * @return DataInterface
     */
    public function getData($key);

    /**
     * Import data into existing data
     *
     * @param array $data
     * @param bool  $clobber
     */
    public function import(array $data, $clobber = true);

    /**
     * Import data from an external data into existing data
     *
     * @param DataInterface $data
     * @param bool          $clobber
     */
    public function importData(DataInterface $data, $clobber = true);

    /**
     * Export data as raw data
     *
     * @return array
     */
    public function export();
}
