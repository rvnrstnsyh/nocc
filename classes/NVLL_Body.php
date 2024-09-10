<?php

/**
 * Class with functions to modify a mail body
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

/**
 * Functions to modify a mail body
 */
class NVLL_Body
{
    /**
     * Prepare HTML links
     * @param string $body Mail body
     * @return string Mail body with prepared HTML links
     * @static
     */
    public static function prepareHtmlLinks($body)
    {
        $placeholder = uniqid('CID_');
        // Handle CID placeholders
        $body = preg_replace_callback(
            '/\[cid:.*?\]/',
            function ($matches) use (&$placeholder) {
                static $i = 0;
                return $placeholder . '_' . $i++;
            },
            $body
        );
        // Process mailto links
        $sessionParams = NVLL_Session::getUrlGetSession();
        $body = preg_replace_callback(
            '/(href=(?:"mailto:|mailto:))([a-zA-Z0-9\+\-=%&:_.~\?@]+[#a-zA-Z0-9\+]*)/i',
            function ($matches) use ($sessionParams) {
                $email = $matches[2];
                return "href=\"api.php?{$sessionParams}&amp;service=write&amp;mail_to={$email}\" target=\"_blank\" rel=\"noopener noreferrer nofollow\"";
            },
            $body
        );
        // Process all other href links
        $body = preg_replace_callback(
            '/(href=(?:"|\'))([^"\']+)(?:"|\')/',
            function ($matches) {
                $url = $matches[2];
                return "href=\"{$url}\" target=\"_blank\" rel=\"noopener noreferrer nofollow\"";
            },
            $body
        );
        // Restore CID placeholders
        $body = preg_replace_callback(
            '/' . preg_quote($placeholder, '/') . '_\d+/',
            function ($matches) use ($body) {
                static $i = 0;
                return preg_replace('/\[cid:.*?\]/', $matches[0], $body, 1);
            },
            $body
        );

        return $body;
    }

    /**
     * Prepare text links
     * @param string $body Mail body (prepared with htmlspecialchars())
     * @return string Mail body with prepared text links
     * @static
     */
    public static function prepareTextLinks($body)
    {
        $entities = ['&quot;' => '«quot»', '&lt;' => '«lt»', '&gt;' => '«gt»'];
        $body = strtr($body, $entities);
        $linkAttributes = " target=\"_blank\" rel=\"noopener noreferrer nofollow\"";
        $body = preg_replace_callback(
            '{(https?|ftp)://([a-zA-Z0-9\+\/\;\-=%&:_.~\?]+[#a-zA-Z0-9\+:]*)}i',
            function ($matches) use ($linkAttributes) {
                return "<a href=\"{$matches[0]}\"{$linkAttributes}>{$matches[0]}</a>";
            },
            $body
        );
        $placeholder = uniqid('CID_');
        // Handle CID placeholders
        $body = preg_replace_callback(
            '/\[cid:.*?\]/',
            function ($matches) use (&$placeholder) {
                static $i = 0;
                return $placeholder . '_' . $i++;
            },
            $body
        );
        // Process all other href links
        $body = preg_replace_callback(
            '/([0-9a-zA-Z]([-_.]?[0-9a-zA-Z])*@[0-9a-zA-Z]([-.]?[0-9a-zA-Z])*\.[a-zA-Z]{2,})/',
            function ($matches) use ($linkAttributes) {
                $email = $matches[1];
                return "<a href=\"api.php?" . NVLL_Session::getUrlGetSession() . "&amp;service=write&amp;mail_to={$email}\"{$linkAttributes}>{$email}</a>";
            },
            $body
        );
        // Restore CID placeholders
        $body = preg_replace_callback(
            '/' . preg_quote($placeholder, '/') . '_\d+/',
            function ($matches) use ($body) {
                static $i = 0;
                return preg_replace('/\[cid:.*?\]/', $matches[0], $body, 1);
            },
            $body
        );

        return strtr($body, array_flip($entities));
    }

    /**
     * Add colored quotes
     * @param string $body Mail body (prepared with htmlspecialchars())
     * @return string Mail body with colored quotes
     * @static
     */
    public static function addColoredQuotes($body)
    {
        $body = preg_replace_callback(
            '/(^|\r?\n)((&gt; *)+)(.*?)(\r?\n|$)/m',
            function ($matches) {
                $level = substr_count($matches[2], '&gt;');
                $class = $level > 50 ? 'quoteLevelDefault' : 'quoteLevel' . $level;
                return $matches[1] . '<span class="' . $class . '">' . $matches[2] . $matches[4] . '</span>' . $matches[5];
            },
            $body
        );
        return $body;
    }

    /**
     * Add structured text
     * @param string $body Mail body
     * @return string Mail body with structured text
     * @static
     */
    public static function addStructuredText($body)
    {
        $body = preg_replace('/(\s)\+\/-/', '\\1&plusmn;', $body); // +/-
        $body = preg_replace('/(\w|\))\^([0-9]+)/', '\\1<sup>\\2</sup>', $body); // 10^6, a^2, (a+b)^2
        $body = preg_replace('/(\s)(\*)([^\s\*]+[^\*\r\n]+)(\*)/', '\\1<strong>\\2\\3\\4</strong>', $body); // *strong*
        $body = preg_replace('/(\s)(\/)([^\s\/]+[^\/\r\n<>]+)(\/)/', '\\1<em>\\2\\3\\4</em>', $body); // /emphasis/
        $body = preg_replace('/(\s)(_)([^\s_]+[^_\r\n]+)(_)/', '\\1<span style="text-decoration:underline">\\2\\3\\4</span>', $body); // _underline_
        $body = preg_replace('/(\s)(\|)([^\s\|]+[^\|\r\n]+)(\|)/', '\\1<code>\\2\\3\\4</code>', $body); // |code|

        return $body;
    }
}
