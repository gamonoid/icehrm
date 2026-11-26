<?php

namespace DiscussionsUser;

use Classes\BaseService;
use Employees\Common\Model\Employee;
use Users\Common\Model\User;

/**
 * @mentions in post content.
 *
 * A mention is stored inline in the block HTML as
 *
 *     <span class="ice-mention" data-employee="42">@Jane Doe</span>
 *
 * The client may send anything; nothing it sends is trusted. canonicalise() keeps
 * only the employee id out of each mention span and REBUILDS the element from the
 * database — so the displayed name is always the real one, and no attribute,
 * handler or nested markup a caller invented can survive into stored content.
 * A mention naming an unknown or inactive employee degrades to plain text.
 *
 * Renderers whitelist exactly this shape (class + data-employee), which is why the
 * markup is fixed here rather than left to the composer.
 */
class Mentions
{
    /** A mention element, however the client happened to write it. */
    const MENTION_RE = '/<span\b[^>]*\bdata-employee\s*=\s*["\'](\d+)["\'][^>]*>.*?<\/span>/is';

    /** Guards against a post that tries to notify the whole company. */
    const MAX_MENTIONS = 25;

    /**
     * Rewrite every mention in $content canonically and report who was mentioned.
     *
     * @param  array $content EditorJS content (modified in place)
     * @return array employee ids actually mentioned, de-duplicated
     */
    public static function canonicalise(array &$content)
    {
        $found = array();
        $names = array();

        $rewrite = function ($html) use (&$found, &$names) {
            return preg_replace_callback(
                self::MENTION_RE,
                function ($m) use (&$found, &$names) {
                    $id = (int) $m[1];
                    // Whatever the span contained, as plain text — used when the
                    // mention cannot be honoured, so the sentence still reads.
                    $plain = trim(strip_tags($m[0]));
                    if ($id <= 0) {
                        return htmlspecialchars($plain, ENT_QUOTES, 'UTF-8');
                    }
                    if (!array_key_exists($id, $names)) {
                        $names[$id] = self::employeeName($id);
                    }
                    if ($names[$id] === null) {
                        // Unknown / inactive employee: keep the text, drop the link.
                        return htmlspecialchars($plain, ENT_QUOTES, 'UTF-8');
                    }
                    if (!in_array($id, $found, true) && count($found) < self::MAX_MENTIONS) {
                        $found[] = $id;
                    }
                    return '<span class="ice-mention" data-employee="' . $id . '">@'
                        . htmlspecialchars($names[$id], ENT_QUOTES, 'UTF-8')
                        . '</span>';
                },
                (string) $html
            );
        };

        $content = self::mapText($content, $rewrite);
        return $found;
    }

    /** Employee ids mentioned in stored content, without rewriting it. */
    public static function extract(array $content)
    {
        $ids = array();
        self::mapText($content, function ($html) use (&$ids) {
            if (preg_match_all(self::MENTION_RE, (string) $html, $m)) {
                foreach ($m[1] as $raw) {
                    $id = (int) $raw;
                    if ($id > 0 && !in_array($id, $ids, true)) {
                        $ids[] = $id;
                    }
                }
            }
            return $html;
        });
        return $ids;
    }

    /**
     * Apply $fn to every text-bearing string in the content — the same fields the
     * composer can put inline markup into.
     */
    private static function mapText(array $content, $fn)
    {
        if (empty($content['blocks']) || !is_array($content['blocks'])) {
            return $content;
        }
        foreach ($content['blocks'] as $i => $block) {
            if (!is_array($block) || empty($block['data']) || !is_array($block['data'])) {
                continue;
            }
            $data = $block['data'];

            foreach (array('text', 'caption') as $key) {
                if (isset($data[$key]) && is_string($data[$key])) {
                    $data[$key] = $fn($data[$key]);
                }
            }

            // list / checklist items: a plain string, or {text|content, …}
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $j => $item) {
                    if (is_string($item)) {
                        $data['items'][$j] = $fn($item);
                    } elseif (is_array($item)) {
                        foreach (array('text', 'content') as $key) {
                            if (isset($item[$key]) && is_string($item[$key])) {
                                $data['items'][$j][$key] = $fn($item[$key]);
                            }
                        }
                    }
                }
            }

            // table cells
            if (isset($data['content']) && is_array($data['content'])) {
                foreach ($data['content'] as $r => $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    foreach ($row as $c => $cell) {
                        if (is_string($cell)) {
                            $data['content'][$r][$c] = $fn($cell);
                        }
                    }
                }
            }

            $content['blocks'][$i]['data'] = $data;
        }
        return $content;
    }

    /** Display name of an ACTIVE employee, or null. */
    private static function employeeName($id)
    {
        try {
            $emp = new Employee();
            $emp->Load('id = ? AND status = ?', array($id, 'Active'));
            if (empty($emp->id)) {
                return null;
            }
            $name = trim($emp->first_name . ' ' . $emp->last_name);
            return $name === '' ? null : $name;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Tell each newly mentioned person, naming who mentioned them and where.
     *
     * Only NEW mentions are notified, so editing a post does not ping everyone
     * again; the author is never notified about their own mention; and anyone who
     * cannot read the channel is skipped, so a mention can never leak the existence
     * of a post they are not allowed to see.
     *
     * @param array  $ids       employee ids mentioned now
     * @param array  $alreadyIds employee ids mentioned before this save
     */
    public static function notify(array $ids, array $alreadyIds, $channel, $post, $user, $authorEmployeeId)
    {
        $fresh = array_diff($ids, $alreadyIds);
        if (empty($fresh)) {
            return;
        }

        $who = self::employeeName((int) $authorEmployeeId);
        if ($who === null) {
            $who = !empty($user->first_name) ? $user->first_name : $user->email;
        }
        $channelName = isset($channel->name) ? $channel->name : '';
        $message = $who . ' mentioned you in ' . ($channelName === '' ? 'a post' : '#' . $channelName);
        // Where clicking the notification lands. Home shows the built-in "Public"
        // channel, so a mention there sends the reader to their Home feed; a
        // mention in any other channel goes to Discussions, which is the only
        // place that post is actually visible. The g=…&n=… form is what
        // Notifications.jsx routes on to open a module inside the SPA.
        $isHomeFeed = !empty($channel->is_system)
            && isset($channel->name)
            && $channel->name === 'Public';
        $target = $isHomeFeed
            ? 'g=modules&n=home'
            : 'g=extension&n=discussions|user&postId=' . (int) $post->id;
        $url = (defined('CLIENT_BASE_URL') ? CLIENT_BASE_URL : '') . '?' . $target;
        $action = json_encode(array('type' => 'url', 'url' => $url));

        foreach ($fresh as $employeeId) {
            $employeeId = (int) $employeeId;
            if ($employeeId <= 0 || $employeeId === (int) $authorEmployeeId) {
                continue;
            }
            try {
                $reader = new User();
                $reader->Load('employee = ?', array($employeeId));
                if (empty($reader->id) || !ChannelAccess::canRead($channel, $reader)) {
                    continue;
                }
                // In-app notification only — the trailing false is $sendEmail.
                // A mention is a nudge inside the app, not something worth an
                // email every time somebody types an @.
                BaseService::getInstance()->notificationManager->addNotification(
                    $employeeId,
                    $message,
                    $action,
                    'Discussions',
                    null,   // toUserId — resolved from the employee
                    false,  // fromSystem: this is from a person
                    false   // sendEmail
                );
            } catch (\Throwable $e) {
                \Utils\LogManager::getInstance()->error(
                    'Mention notification error: ' . $e->getMessage()
                );
            }
        }
    }
}
