<?php
/**
 * Class to cache weather lookup results.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2018-2022 Lee Garner <lee@leegarner.com>
 * @package     weather
 * @version     v2.0.3
 * @since       v1.1.4
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Weather;


/**
 * Class for Weather Cache.
 * @package weather
 */
class Cache
{
    /** Tag added to every cached item.
     * @const string */
    const TAG = 'weather';


    /**
     * Update the cache.
     * Adds an array of tags including the plugin name
     *
     * @param   string  $key    Item key
     * @param   mixed   $data   Data, typically an array
     * @param   mixed   $tag    Tag, or array of tags.
     * @param   integer $cache_mins Cache minutes
     * @return  boolean     True on success, False on error
     */
    public static function set($key, $data, $tag='', $cache_mins=0)
    {
        global $_CONF_WEATHER;

        // Debugging tool to skip cache usage
        if (isset($_CONF_WEATHER['nocache'])) return;

        // Set cache minutes if not provided, and make sure it's reasonable.
        if ($cache_mins == 0) {
            $cache_mins = (int)$_CONF_WEATHER['cache_minutes'];
        }
        if ($cache_mins < 10) {
            $cache_mins = 30;
        }

        $ttl = (int)$cache_mins * 60;   // convert to seconds
        // Always make sure the base tag is included
        $tags = array(self::TAG);
        if (!empty($tag)) {
            if (!is_array($tag)) {
                $tag = array($tag);
            }
            $tags = array_merge($tags, $tag);
        }
        $key = self::makeKey($key);
        return \glFusion\Cache\Cache::getInstance()->set($key, $data, $tags, $ttl);
    }


    /**
     * Delete a single item from the cache by key.
     *
     * @param   string  $key    Base key, e.g. item ID
     * @return  boolean     True on success, False on error
     */
    public static function delete($key)
    {
        $key = self::makeKey($key);
        return \glFusion\Cache\Cache::getInstance()->delete($key);
    }


    /**
     * Completely clear the cache.
     * Called after upgrade.
     *
     * @param   array   $tag    Optional array of tags, base tag used if undefined
     * @return  boolean     True on success, False on error
     */
    public static function clear($tag = array())
    {
        $tags = array(self::TAG);
        if (!empty($tag)) {
            if (!is_array($tag)) {
                $tag = array($tag);
            }
            $tags = array_merge($tags, $tag);
        }
        return \glFusion\Cache\Cache::getInstance()->deleteItemsByTagsAll($tags);
    }


    /**
     * Create a unique cache key.
     * Intended for internal use, but public in case it is needed.
     *
     * @param   string  $key    Base key, e.g. Item ID
     * @return  string          Encoded key string to use as a cache ID
     */
    public static function makeKey($key)
    {
        return strtolower(COM_sanitizeId($key, false));
    }


    /**
     * Get an item from cache.
     *
     * @param   string  $key    Key to retrieve
     * @return  mixed       Value of key, or NULL if not found
     */
    public static function get($key)
    {
        $retval = NULL;
        $key = self::makeKey($key);
        if (\glFusion\Cache\Cache::getInstance()->has($key)) {
            $retval = \glFusion\Cache\Cache::getInstance()->get($key);
        }
        return $retval;
    }

}

