<?php

namespace Reactions;

use Classes\BaseService;
use Model\Reaction;

/**
 * Reads and toggles emoji reactions on any target (see the Reactions migration).
 *
 * target_type is validated against TARGET_TYPES: a reaction may only decorate a
 * model this list knows about, so a client cannot attach rows to arbitrary names.
 * Add a type here (and nothing else) when another model adopts reactions.
 */
class ReactionService
{
    const TARGET_TYPES = array('Kudos', 'DiscussionPost');

    const MAX_EMOJI_CHARS = 16;

    public static function isValidTargetType($type)
    {
        return is_string($type) && in_array($type, self::TARGET_TYPES, true);
    }

    /**
     * The value stored in Reactions.employee for the caller: their Employees id, or
     * — for a user with no profile, as admins may be — the negated Users id.
     *
     * @return int|null null when there is no signed-in user
     */
    public static function reactorToken($user)
    {
        if (empty($user) || empty($user->id)) {
            return null;
        }
        $employeeId = BaseService::getInstance()->getCurrentProfileId();
        if (!empty($employeeId)) {
            return (int) $employeeId;
        }
        return -((int) $user->id);
    }

    /**
     * Normalise an emoji as sent by the client. Anything empty or implausibly long
     * is refused (null) rather than stored.
     */
    public static function cleanEmoji($emoji)
    {
        if (!is_string($emoji)) {
            return null;
        }
        $emoji = trim($emoji);
        if ($emoji === '' || mb_strlen($emoji) > self::MAX_EMOJI_CHARS) {
            return null;
        }
        // No control characters, no markup.
        if (preg_match('/[\x00-\x1F<>&"\']/', $emoji)) {
            return null;
        }
        return $emoji;
    }

    /**
     * Insert the caller's reaction, or remove it when it already exists.
     *
     * @return array summary for the target after the change: {counts:{emoji:n}, mine:[emoji]}
     */
    public static function toggle($targetType, $targetId, $emoji, $reactorToken)
    {
        $existing = new Reaction();
        $existing->Load(
            'target_type = ? AND target_id = ? AND employee = ? AND emoji = ?',
            array($targetType, (int) $targetId, (int) $reactorToken, $emoji)
        );
        if (!empty($existing->id)) {
            $existing->Delete();
        } else {
            $reaction = new Reaction();
            $reaction->target_type = $targetType;
            $reaction->target_id = (int) $targetId;
            $reaction->employee = (int) $reactorToken;
            $reaction->emoji = $emoji;
            $reaction->created = date('Y-m-d H:i:s');
            $reaction->Save();
        }
        $summary = self::summarize($targetType, array((int) $targetId), $reactorToken);
        return isset($summary[(int) $targetId]) ? $summary[(int) $targetId] : self::emptySummary();
    }

    /**
     * Per-emoji counts and the caller's own emojis for many targets in two queries —
     * for feeds, which would otherwise query once per card.
     *
     * @param  string   $targetType
     * @param  int[]    $targetIds
     * @param  int|null $reactorToken the caller's token, or null for counts only
     * @return array    targetId => {counts:{emoji:n}, mine:[emoji]}
     */
    public static function summarize($targetType, array $targetIds, $reactorToken = null)
    {
        $out = array();
        $ids = array();
        foreach ($targetIds as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $ids[] = $id;
                $out[$id] = self::emptySummary();
            }
        }
        if (empty($ids)) {
            return $out;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $db = BaseService::getInstance()->getDB();

        // MyORM's Execute() returns an array of assoc rows.
        $rows = $db->Execute(
            'SELECT target_id, emoji, COUNT(*) AS n FROM Reactions '
            . 'WHERE target_type = ? AND target_id IN (' . $placeholders . ') '
            . 'GROUP BY target_id, emoji',
            array_merge(array($targetType), $ids)
        );
        if (is_array($rows)) {
            foreach ($rows as $row) {
                $tid = (int) $row['target_id'];
                if (isset($out[$tid])) {
                    $out[$tid]['counts'][$row['emoji']] = (int) $row['n'];
                }
            }
        }

        if ($reactorToken !== null) {
            $mine = $db->Execute(
                'SELECT target_id, emoji FROM Reactions '
                . 'WHERE target_type = ? AND employee = ? AND target_id IN (' . $placeholders . ')',
                array_merge(array($targetType, (int) $reactorToken), $ids)
            );
            if (is_array($mine)) {
                foreach ($mine as $row) {
                    $tid = (int) $row['target_id'];
                    if (isset($out[$tid])) {
                        $out[$tid]['mine'][] = $row['emoji'];
                    }
                }
            }
        }

        // An empty counts map must serialise as {} not [], so the client can treat
        // it as an object uniformly.
        foreach ($out as $tid => $summary) {
            if (empty($summary['counts'])) {
                $out[$tid]['counts'] = new \stdClass();
            }
        }

        return $out;
    }

    /** Remove every reaction on a target — for when the target row is deleted. */
    public static function deleteForTarget($targetType, $targetId)
    {
        BaseService::getInstance()->getDB()->Execute(
            'DELETE FROM Reactions WHERE target_type = ? AND target_id = ?',
            array($targetType, (int) $targetId)
        );
    }

    private static function emptySummary()
    {
        // counts is an array while being filled; summarize() turns an empty one
        // into an object before returning.
        return array('counts' => array(), 'mine' => array());
    }
}
