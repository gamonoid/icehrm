<?php

namespace Classes\Email;

use Classes\SettingsManager;

/**
 * Building blocks for email bodies, so every email IceHrm sends looks and reads the
 * same. Each method returns HTML with inline styles (email clients ignore most CSS),
 * and everything that comes from data is escaped here: pass plain text, not HTML,
 * unless a method says otherwise.
 *
 * A typical body:
 *   EmailParts::greeting($firstName)
 *   . EmailParts::text('Your leave request was approved.')
 *   . EmailParts::details(array('Dates' => '2 Dec 2026 to 4 Dec 2026', 'Days' => '3'))
 *   . EmailParts::button($url, 'View request')
 *   . EmailParts::fallbackLink($url)
 *
 * The body is then wrapped in core/templates/email/emailBody.html by
 * EmailSender::sendEmail() (logo, card, footer).
 */
class EmailParts
{
    const TEXT = '#1f2937';
    const MUTED = '#6b7280';
    const PRIMARY = '#346CB0';
    const BORDER = '#e5e7eb';
    const SOFT = '#f9fafb';
    const FONT = "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif";

    /** Escape plain text for HTML. */
    public static function esc($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * The company name for emails: the "Company: Name" setting, or "IceHrm" when it
     * has not been set (or still has the installer's placeholder).
     */
    public static function companyName()
    {
        $name = trim((string) SettingsManager::getInstance()->getSetting('Company: Name'));
        if ($name === '' || in_array($name, array('Sample Company Pvt Ltd', 'Your Company'), true)) {
            return 'IceHrm';
        }
        return $name;
    }

    /** "Hi Anna," (or "Hello," when there is no name). */
    public static function greeting($name)
    {
        $name = trim((string) $name);
        return self::paragraph($name === '' ? 'Hello,' : 'Hi ' . self::esc($name) . ',');
    }

    /** A paragraph of plain text (escaped; line breaks kept). */
    public static function text($plain)
    {
        return self::paragraph(nl2br(self::esc($plain)));
    }

    /** A paragraph of trusted HTML (build it with esc() / strong()). */
    public static function paragraph($html)
    {
        return '<p style="margin:0 0 16px;font-family:' . self::FONT . ';font-size:15px;line-height:1.6;color:' . self::TEXT . ';">'
            . $html . '</p>';
    }

    /** Bold, escaped text for use inside paragraph(). */
    public static function strong($plain)
    {
        return '<strong style="color:' . self::TEXT . ';">' . self::esc($plain) . '</strong>';
    }

    /** A small heading line. */
    public static function heading($plain)
    {
        return '<p style="margin:0 0 12px;font-family:' . self::FONT . ';font-size:18px;line-height:1.4;font-weight:600;color:' . self::TEXT . ';">'
            . self::esc($plain) . '</p>';
    }

    /**
     * Label/value rows, e.g. array('Leave type' => 'Annual', 'Days' => '2').
     * Rows with an empty value are left out. Values are plain text.
     */
    public static function details(array $rows)
    {
        $html = '';
        foreach ($rows as $label => $value) {
            if ($value === null || trim((string) $value) === '') {
                continue;
            }
            $html .= '<tr>'
                . '<td style="padding:8px 12px 8px 0;font-family:' . self::FONT . ';font-size:14px;color:' . self::MUTED . ';vertical-align:top;white-space:nowrap;">'
                . self::esc($label) . '</td>'
                . '<td style="padding:8px 0;font-family:' . self::FONT . ';font-size:14px;color:' . self::TEXT . ';vertical-align:top;">'
                . nl2br(self::esc($value)) . '</td>'
                . '</tr>';
        }
        if ($html === '') {
            return '';
        }
        return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" '
            . 'style="margin:0 0 20px;border-top:1px solid ' . self::BORDER . ';border-bottom:1px solid ' . self::BORDER . ';">'
            . $html . '</table>';
    }

    /**
     * A table with a header row, e.g. leave days. $rows is a list of lists of plain
     * text; $headers a list of plain text.
     */
    public static function table(array $headers, array $rows)
    {
        if (empty($rows)) {
            return '';
        }
        $cell = 'padding:8px 10px;font-family:' . self::FONT . ';font-size:14px;text-align:left;border-bottom:1px solid ' . self::BORDER . ';';
        $html = '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:0 0 20px;border-collapse:collapse;">';
        $html .= '<tr>';
        foreach ($headers as $header) {
            $html .= '<th style="' . $cell . 'color:' . self::MUTED . ';font-weight:600;background:' . self::SOFT . ';">' . self::esc($header) . '</th>';
        }
        $html .= '</tr>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $value) {
                $html .= '<td style="' . $cell . 'color:' . self::TEXT . ';">' . self::esc($value) . '</td>';
            }
            $html .= '</tr>';
        }
        return $html . '</table>';
    }

    /** A highlighted quote, e.g. a note from a manager. Plain text. */
    public static function note($plain, $label = null)
    {
        if (trim((string) $plain) === '') {
            return '';
        }
        $title = $label === null ? '' : '<div style="font-size:13px;color:' . self::MUTED . ';margin-bottom:4px;">' . self::esc($label) . '</div>';
        return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:0 0 20px;">'
            . '<tr><td style="padding:12px 16px;background:' . self::SOFT . ';border-left:3px solid ' . self::PRIMARY . ';border-radius:4px;'
            . 'font-family:' . self::FONT . ';font-size:14px;line-height:1.6;color:' . self::TEXT . ';">'
            . $title . nl2br(self::esc($plain)) . '</td></tr></table>';
    }

    /** A bulleted list of plain-text items. */
    public static function bullets(array $items)
    {
        if (empty($items)) {
            return '';
        }
        $html = '<ul style="margin:0 0 16px;padding-left:20px;font-family:' . self::FONT . ';font-size:15px;line-height:1.6;color:' . self::TEXT . ';">';
        foreach ($items as $item) {
            $html .= '<li style="margin:0 0 6px;">' . self::esc($item) . '</li>';
        }
        return $html . '</ul>';
    }

    /** A call-to-action button that works in Outlook as well (table-based). */
    public static function button($url, $label)
    {
        return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:8px 0 24px;">'
            . '<tr><td align="center" bgcolor="' . self::PRIMARY . '" style="border-radius:6px;">'
            . '<a href="' . self::esc($url) . '" target="_blank" '
            . 'style="display:inline-block;padding:12px 24px;font-family:' . self::FONT . ';font-size:15px;font-weight:600;'
            . 'color:#ffffff;text-decoration:none;border-radius:6px;background:' . self::PRIMARY . ';">'
            . self::esc($label) . '</a></td></tr></table>';
    }

    /** "If the button doesn't work, copy this link …" in small print. */
    public static function fallbackLink($url)
    {
        return '<p style="margin:0 0 16px;font-family:' . self::FONT . ';font-size:12px;line-height:1.5;color:' . self::MUTED . ';">'
            . 'If the button does not work, copy this link into your browser:<br/>'
            . '<a href="' . self::esc($url) . '" style="color:' . self::PRIMARY . ';word-break:break-all;">' . self::esc($url) . '</a></p>';
    }

    /** Small print, e.g. "This link expires on …". Plain text. */
    public static function small($plain)
    {
        return '<p style="margin:0 0 16px;font-family:' . self::FONT . ';font-size:13px;line-height:1.5;color:' . self::MUTED . ';">'
            . nl2br(self::esc($plain)) . '</p>';
    }

    /** "Thanks,<br/>The Acme team" style sign-off. */
    public static function signOff($from = null)
    {
        $from = $from === null ? 'The ' . self::companyName() . ' team' : $from;
        return self::paragraph('Thanks,<br/>' . self::esc($from));
    }

    /** "Mon, 2 Dec 2026" from "2026-12-02" (anything unparsable is returned as is). */
    public static function date($value)
    {
        $time = empty($value) ? false : strtotime((string) $value);
        return $time === false ? (string) $value : date('D, j M Y', $time);
    }

    /** "Mon, 2 Dec 2026 at 14:30" from a date-time. */
    public static function dateTime($value)
    {
        $time = empty($value) ? false : strtotime((string) $value);
        return $time === false ? (string) $value : date('D, j M Y \a\t H:i', $time);
    }

    /** A full app URL from a legacy "g=modules&n=…#tab" route or an absolute URL. */
    public static function appUrl($route = '')
    {
        $route = (string) $route;
        if ($route === '') {
            return CLIENT_BASE_URL;
        }
        if (preg_match('#^https?://#i', $route)) {
            return $route;
        }
        return CLIENT_BASE_URL . '?' . ltrim($route, '?');
    }
}
