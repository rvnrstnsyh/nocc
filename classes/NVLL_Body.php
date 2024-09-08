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
     * @return string Mail body with prepared HMTL links
     * @static
     */
    public static function prepareHtmlLinks($body)
    {
        $placeholder = md5($body);
        $matches = array();

        if ($count = preg_match_all("/\[cid:.*?\]/", $body, $matches)) for ($i = 0; $i < $count; $i++) $body = str_replace($matches[0][$i], $placeholder . "_" . $i, $body);

        $body = preg_replace("|href=\"mailto:([a-zA-Z0-9\+\-=%&:_.~\?@]+[#a-zA-Z0-9\+]*)\"|i", "href=\"api.php?" . NVLL_Session::getUrlGetSession() . "&amp;service=write&amp;mail_to=$1\"", $body);
        $body = preg_replace("|href=mailto:([a-zA-Z0-9\+\-=%&:_.~\?@]+[#a-zA-Z0-9\+]*)|i", "href=\"api.php?" . NVLL_Session::getUrlGetSession() . "&amp;service=write&amp;mail_to=$1\"", $body);

        for ($i = 0; $i < $count; $i++) $body = str_replace($placeholder . "_" . $i, $matches[0][$i], $body);

        $body = preg_replace("|href=\"([a-zA-Z0-9\+\/\;\-=%&:_.~\?]+[#a-zA-Z0-9\+]*)\"|i", "href=\"$1\" target=\"_blank\"", $body);
        $body = preg_replace("|href=([a-zA-Z0-9\+\/\;\-=%&:_.~\?]+[#a-zA-Z0-9\+]*)|i", "href=\"$1\" target=\"_blank\"", $body);

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
        $htmlEntities = array('&quot;', '&lt;', '&gt;');
        $nvllEntities = array('«quot»', '«lt»', '«gt»');
        $body = str_replace($htmlEntities, $nvllEntities, $body);
        $body = preg_replace("{(http|https|ftp)://([a-zA-Z0-9\+\/\;\-=%&:_.~\?]+[#a-zA-Z0-9\+:]*)}i", "<a href=\"$1://$2\" target=\"_blank\">$1://$2</a>", $body);
        $placeholder = md5($body);
        $matches = array();

        if ($count = preg_match_all("/\[cid:.*?\]/", $body, $matches)) for ($i = 0; $i < $count; $i++) $body = str_replace($matches[0][$i], $placeholder . "_" . $i, $body);

        $body = preg_replace("/([0-9a-zA-Z]([-_.]?[0-9a-zA-Z])*@[0-9a-zA-Z]([-.]?[0-9a-zA-Z])*\.[a-zA-Z]{2,})/", "<a href=\"api.php?" . NVLL_Session::getUrlGetSession() . "&amp;service=write&amp;mail_to=\\1\">\\1</a>", $body);
        for ($i = 0; $i < $count; $i++) $body = str_replace($placeholder . "_" . $i, $matches[0][$i], $body);
        $body = str_replace($nvllEntities, $htmlEntities, $body);

        return $body;
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
