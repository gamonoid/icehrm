<?php

namespace DiscussionsUser;

/**
 * Who may do what in a channel.
 *
 * Each channel carries three comma-separated lists of user levels — read_levels,
 * post_levels, reply_levels. A user is allowed when their level is in the list.
 * Administrators are allowed everything regardless; a channel cannot be configured
 * to lock them out (they configure the channels).
 *
 * The "Restricted …" levels are on their way out; they are treated as their
 * unrestricted counterpart so a Restricted Manager sees what a Manager sees.
 */
class ChannelAccess
{
    const LEVELS = array('Admin', 'Manager', 'Employee');

    /** 'Restricted Manager' -> 'Manager'. */
    public static function normalizeLevel($level)
    {
        $level = trim((string) $level);
        if (strpos($level, 'Restricted ') === 0) {
            $level = substr($level, strlen('Restricted '));
        }
        return in_array($level, self::LEVELS, true) ? $level : 'Employee';
    }

    /** "Admin, Manager" -> ['Admin','Manager'], unknown names dropped. */
    public static function parseLevels($csv)
    {
        $out = array();
        foreach (explode(',', (string) $csv) as $part) {
            $part = trim($part);
            if (in_array($part, self::LEVELS, true) && !in_array($part, $out, true)) {
                $out[] = $part;
            }
        }
        return $out;
    }

    /** Array of levels -> canonical CSV, always including Admin. */
    public static function toCsv($levels)
    {
        $clean = array('Admin');
        foreach ((array) $levels as $level) {
            $level = self::normalizeLevel($level);
            if (!in_array($level, $clean, true)) {
                $clean[] = $level;
            }
        }
        // Keep the canonical order so two equal sets serialise identically.
        $ordered = array();
        foreach (self::LEVELS as $level) {
            if (in_array($level, $clean, true)) {
                $ordered[] = $level;
            }
        }
        return implode(',', $ordered);
    }

    public static function isAdmin($user)
    {
        return !empty($user) && self::normalizeLevel($user->user_level) === 'Admin';
    }

    private static function allows($csv, $user)
    {
        if (empty($user)) {
            return false;
        }
        if (self::isAdmin($user)) {
            return true;
        }
        return in_array(self::normalizeLevel($user->user_level), self::parseLevels($csv), true);
    }

    public static function canRead($channel, $user)
    {
        return self::allows($channel->read_levels, $user);
    }

    public static function canPost($channel, $user)
    {
        return self::canRead($channel, $user) && self::allows($channel->post_levels, $user);
    }

    public static function canReply($channel, $user)
    {
        return self::canRead($channel, $user) && self::allows($channel->reply_levels, $user);
    }
}
