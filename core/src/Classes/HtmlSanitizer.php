<?php

namespace Classes;

/**
 * Allow-list sanitiser for rich text that must keep its formatting.
 *
 * Built for the public job pages: recruiter-authored descriptions are stored as
 * markdown and rendered to HTML with cebe/markdown, whose block\HtmlTrait passes raw
 * HTML straight through. The result is echoed on an unauthenticated page, so anyone
 * who can edit a job posting could otherwise script every visitor.
 *
 * Escaping the whole value would be simpler but would visibly mangle existing
 * listings — the field has always rendered as HTML, so some contain intentional
 * markup. This keeps the safe subset and removes the rest:
 *
 *   - elements outside the allow-list are unwrapped, so their text survives but the
 *     tag does not;
 *   - script/style/iframe/object/embed/form and friends are dropped entirely,
 *     including their contents, because their text is code rather than prose;
 *   - every attribute outside the per-tag allow-list is dropped, which removes all
 *     on* event handlers and style;
 *   - href/src values are restricted to http, https and mailto, plus relative paths,
 *     so javascript: and data: URLs cannot survive;
 *   - comments and processing instructions are removed.
 *
 * For plain values that merely need displaying, escape with htmlspecialchars instead —
 * escaping is stricter and cheaper than sanitising.
 *
 * PHP 7.3 compatible.
 */
class HtmlSanitizer
{
    /** tag => list of permitted attributes */
    private static $allowedTags = array(
        'p' => array(),
        'br' => array(),
        'hr' => array(),
        'h1' => array(), 'h2' => array(), 'h3' => array(),
        'h4' => array(), 'h5' => array(), 'h6' => array(),
        'strong' => array(), 'b' => array(),
        'em' => array(), 'i' => array(),
        'u' => array(), 's' => array(), 'del' => array(), 'ins' => array(),
        'sub' => array(), 'sup' => array(), 'mark' => array(), 'small' => array(),
        'blockquote' => array(),
        'pre' => array(),
        'code' => array(),
        'ul' => array(), 'ol' => array('start'), 'li' => array(),
        'dl' => array(), 'dt' => array(), 'dd' => array(),
        'span' => array(), 'div' => array(),
        'a' => array('href', 'title', 'target', 'rel'),
        'img' => array('src', 'alt', 'title', 'width', 'height'),
        'table' => array(), 'thead' => array(), 'tbody' => array(), 'tfoot' => array(),
        'tr' => array(), 'th' => array('colspan', 'rowspan'), 'td' => array('colspan', 'rowspan'),
        'figure' => array(), 'figcaption' => array(),
    );

    /**
     * Elements removed with their contents. Everything else that is not allowed is
     * unwrapped instead, so prose is never silently lost.
     */
    private static $strippedTags = array(
        'script', 'style', 'iframe', 'object', 'embed', 'applet', 'form',
        'input', 'button', 'select', 'option', 'textarea', 'link', 'meta',
        'base', 'noscript', 'template', 'svg', 'math',
    );

    /** Attributes carrying a URL, checked against the scheme allow-list. */
    private static $urlAttributes = array('href', 'src');

    private static $allowedSchemes = array('http', 'https', 'mailto');

    /**
     * @param string $html
     * @return string sanitised HTML, safe to echo into a page
     */
    public static function richText($html)
    {
        $html = (string) $html;
        if (trim($html) === '') {
            return '';
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');

        $previousErrors = libxml_use_internal_errors(true);
        $previousLoader = null;
        if (PHP_VERSION_ID < 80000) {
            // PHP 7.3 loads external entities by default. LIBXML_NOENT is deliberately
            // not passed — it enables the substitution an XXE payload relies on.
            $previousLoader = libxml_disable_entity_loader(true);
        }

        // The numeric entity pins the encoding without relying on a meta tag, and
        // NOIMPLIED/NODEFDTD stop libxml wrapping the fragment in html/body.
        $loaded = $dom->loadHTML(
            '<?xml encoding="UTF-8"?><div id="icehrm-sanitize-root">'.$html.'</div>',
            LIBXML_NONET | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        if (PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader($previousLoader);
        }
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        if (!$loaded) {
            // Unparseable: fall back to escaping rather than emitting anything raw.
            return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
        }

        $root = $dom->getElementById('icehrm-sanitize-root');
        if ($root === null) {
            return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
        }

        self::sanitizeChildren($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return $out;
    }

    /**
     * Depth-first over a static snapshot of the child list, because the tree is
     * modified while walking it.
     *
     * @param \DOMNode $node
     */
    private static function sanitizeChildren($node)
    {
        $children = array();
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            self::sanitizeNode($child);
        }
    }

    /**
     * @param \DOMNode $node
     */
    private static function sanitizeNode($node)
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            return; // text is escaped on serialisation
        }

        if ($node->nodeType === XML_COMMENT_NODE || $node->nodeType === XML_PI_NODE) {
            $node->parentNode->removeChild($node);
            return;
        }

        if ($node->nodeType !== XML_ELEMENT_NODE) {
            $node->parentNode->removeChild($node);
            return;
        }

        $tag = strtolower($node->nodeName);

        if (in_array($tag, self::$strippedTags, true)) {
            $node->parentNode->removeChild($node);
            return;
        }

        if (!isset(self::$allowedTags[$tag])) {
            // Unwrap: sanitise the contents, then splice them in where the tag was.
            self::sanitizeChildren($node);
            $parent = $node->parentNode;
            while ($node->firstChild !== null) {
                $parent->insertBefore($node->firstChild, $node);
            }
            $parent->removeChild($node);
            return;
        }

        self::sanitizeAttributes($node, self::$allowedTags[$tag]);
        self::sanitizeChildren($node);
    }

    /**
     * @param \DOMElement $element
     * @param array $allowed
     */
    private static function sanitizeAttributes($element, array $allowed)
    {
        $attributes = array();
        foreach ($element->attributes as $attribute) {
            $attributes[] = $attribute->nodeName;
        }

        foreach ($attributes as $name) {
            $lower = strtolower($name);

            if (!in_array($lower, $allowed, true)) {
                // Drops every on* handler and style along with anything else unlisted.
                $element->removeAttribute($name);
                continue;
            }

            if (in_array($lower, self::$urlAttributes, true)
                && !self::isSafeUrl($element->getAttribute($name))
            ) {
                $element->removeAttribute($name);
            }
        }

        // Anchors that open a new tab must not hand the opener over to the target.
        if (strtolower($element->nodeName) === 'a' && $element->getAttribute('target') !== '') {
            $element->setAttribute('rel', 'noopener noreferrer');
        }
    }

    /**
     * Relative URLs are allowed; absolute ones must use an allow-listed scheme.
     *
     * @param string $url
     * @return bool
     */
    private static function isSafeUrl($url)
    {
        $url = trim($url);
        if ($url === '') {
            return false;
        }

        // Strip characters a browser ignores when resolving the scheme, so
        // "java\0script:" and "java\tscript:" cannot slip past.
        $probe = strtolower(preg_replace('/[\x00-\x20]/', '', $url));

        if (strpos($probe, '#') === 0 || strpos($probe, '/') === 0) {
            return true; // fragment or root-relative
        }

        $colon = strpos($probe, ':');
        if ($colon === false) {
            return true; // relative path
        }

        // A colon after the first path separator is part of the path, not a scheme.
        $slash = strpos($probe, '/');
        if ($slash !== false && $slash < $colon) {
            return true;
        }

        return in_array(substr($probe, 0, $colon), self::$allowedSchemes, true);
    }
}
