<?php

/**
 * Class with security functions
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

require_once dirname(__FILE__) . '/../utils/htmLawed.php';

/**
 * Security functions
 */
class NVLL_Security
{
    /**
     * Disable HTML images
     * @param string $body HTML body
     * @return string HTML body without images
     * @static
     */
    public static function disableHtmlImages($body)
    {
        $body = preg_replace('|src[\s]*=[\s]*["\']?[\s]*[A-Za-z]+://[^<>\s]+[A-Za-z0-9/][\s]*["\']?|i', 'src="none"', $body); //src = "xyz" OR src = 'xyz' OR src = xyz
        $body = preg_replace('|background[\s]*=[\s]*["\']?[\s]*[A-Za-z]+://[^<>\s]+[A-Za-z0-9/][\s]*["\']?|i', 'background="none"', $body); //background = "xyz" OR background = 'xyz' OR background = xyz
        $body = preg_replace('|url[\s]*\([\s]*\'?[\s]*[A-Za-z]+://[^<>\s]+[A-Za-z0-9/][\s]*\'?[\s]*\)|i', 'url(none)', $body); //url ( xzy ) OR url ( 'xyz' )

        return $body;
    }

    /**
     * Has disabled HTML images?
     * @param string $body HTML body
     * @return bool Has disabled HTML images?
     * @static
     */
    public static function hasDisabledHtmlImages($body)
    {
        if ($body != null) {
            //if src="none", background="none", url(none)...
            if (preg_match('/src="none"|background="none"|url\(none\)/i', $body)) return true;
        }
        return false;
    }

    /**
     * Remove JS event handler properties and code, e.g. onclick=...
     * @param string $body HTML body
     * @return string Cleaned HTML body
     * @static
     */
    public static function removeJsEventHandler($body)
    {
        $matches = array();
        while (preg_match("/<.*\s(on[a-z]+\s*=\s*[\"\'].*[\"\']).*>/si", $body, $matches)) {
            $body = str_ireplace($matches[1], "", $body);
            $matches = array();
        }
        $clean_body = $body;
        return $clean_body;
    }

    /**
     * Clean HTML body (strip <HTML>, <HEAD> and other tags)
     * @param string $body HTML body
     * @return string Cleaned HTML body
     * @static
     */
    public static function cleanHtmlBody($body)
    {
        $dirtyTags = array(
            "'<\?xml.*?\?>'si",
            "'<!doctype[^>]*>'si",
            "'<html[^>]*>'si",
            "'</html>'si",
            "'<body[^>]*>'si",
            "'</body>'si",
            //TODO: Make problems with <head\n>!?
            "'<head[^>]*>.*?</head>'si",
            "'<head\s*?/>'si",
            "'<style[^>]*>.*?</style>'si",
            "'<script[^>]*>.*?</script>'si",
            "'<object[^>]*>.*?</object>'si",
            "'<embed[^>]*>.*?</embed>'si",
            "'<applet[^>]*>.*?</applet>'si",
            "'<mocha[^>]*>.*?</mocha>'si",
            "'<meta[^>]*>'si",
            "'<o:p[^>]*>.*?</o:p>'si", //Outlook
        );
        $cleanBody = preg_replace($dirtyTags, '', $body);
        return trim($cleanBody);
    }

    /**
     * Purify HTML body (ensure that HTML code is standard-compliant and does not introduce security vulnerabilities)
     * @param string $body HTML body
     * @return string Purified HTML body
     * @static
     */
    public static function purifyHtml($body)
    {
        //copy&paste from word creates html like <![if !supportLists]> and <![endif]>
        // we make proper comments (<!-- -->) of them
        $body = preg_replace("/<!\[if(.*?)>/", '<!--[if$1-->', $body);
        $body = preg_replace("/<!\[endif(.*?)>/", '<!--[endif$1-->', $body);
        $config = array(
            'keep_bad' => 0,
            'schemes' => 'href:aim,feed,file,ftp,gopher,http,https,irc,mailto,news,nntp,sftp,ssh,telnet; src:cid,http,https; style:!; *:file,http,https',
            'valid_xhtml' => 1,
            'comment' => 2,
            'elements' => '*+div',
            'balance' => 0
        );
        return htmLawed($body, $config);
    }

    /**
     * Convert HTML to plain text (UTF-8)
     * @param string $string HTML
     * @return string Plain text (UTF-8)
     * @static
     * @todo Remove empty lines from Outlook HTML mails.
     */
    public static function convertHtmlToPlainText($string, $mime = 'text/html')
    {
        $crlf = "\r\n";
        $string = str_replace("\r\n", "\n", $string);
        $string = str_replace("\r", "\n", $string);
        $string = str_replace("\n", $crlf, $string);
        // Replace image tags with their alt text, adding a line break before.
        $string = preg_replace_callback('/<img[^>]+alt=([\'"])(.*?)\1[^>]*>/i', function ($matches) use ($crlf) {
            return $matches[2] ? $crlf . '[' . $matches[2] . ']' : '';
        }, $string);
        // Cleanup <pre> tags and other unnecessary tags.
        $string = preg_replace("/^<pre>/Ui", "", $string);
        $string = preg_replace("/<\/pre>$/Ui", "", $string);
        // Handle paragraphs and line breaks.
        $string = preg_replace("/" . $crlf . "\s*<p/Ui", "<p", $string);
        $string = preg_replace("/(<p.*>)/Ui", $crlf . "$1", $string);
        $string = preg_replace("/<br\s*>/Ui", "<br />", $string);
        $string = preg_replace("/<br\s*\/\s*>/Ui", "<br />", $string);
        $string = preg_replace("/(\S+)\s*" . $crlf . "\s*<br \/>/Ui", "$1<br />", $string);
        $string = preg_replace("/<br \/>\s*" . $crlf . "/Ui", "<br />", $string);
        $string = preg_replace("/<br \/>/Ui", "<br />" . $crlf, $string);
        $string = preg_replace("/<p[^>]*>\s*?(.*)\s*?<\/p>/Uis", "<p>$1</p>", $string);
        // Process blockquote tags.
        $new_string = $string;
        do {
            $string = $new_string;
            $match = array();
            if (1 === preg_match("/^(.*?)<blockquote([^>]*?)>(.*)<\/blockquote>(.*)$/si", $new_string, $match)) {
                $left = $match[1];
                $attributes = $match[2];
                $inner = $match[3];
                $right = $match[4];
                $new_inner = "";
                $lines = explode($crlf, $inner);
                foreach ($lines as $line) {
                    // Only add "> " to non-empty lines inside blockquote.
                    if (trim($line) !== "") {
                        $new_inner .= "> " . $line . "<br />" . $crlf;
                    } else {
                        $new_inner .= "<br />" . $crlf;
                    }
                }
                $new_outer = rtrim($new_inner, "<br />" . $crlf); // Remove unnecessary last <br /> at the end.
                $new_string = $left . $new_outer . $right;
            }
        } while ($new_string != $string);

        // Strip remaining HTML tags.
        $tmp_string = strip_tags($string);
        if ($mime == "text/html") {
            $lines = preg_split("/" . $crlf . "/", $tmp_string);
            $tmp_string = "";
            foreach ($lines as $line) {
                $new_line = preg_replace("/^\s*/", "", $line);
                $tmp_string .= $new_line . $crlf;
            }
        }

        // Final decoding of HTML entities.
        $string = $tmp_string;
        return html_entity_decode($string, ENT_COMPAT, 'UTF-8');
    }

    /**
     * Is supported image type?
     * @param string $internetMediaType Internet media type (MIME type)
     * @return bool Supported image type?
     * TODO: Better name?
     * TODO: Move to other place?
     */
    public static function isSupportedImageType($internetMediaType)
    {
        $types = explode('/', $internetMediaType);
        if (count($types) == 2) { //if valid MIME type...
            if (strtolower($types[0]) == 'image') { //if image MIME type...
                //if PJP(E)G, JP(E)G, GIF, PNG, BMP...
                if (preg_match('/^PJPE?G$|^JPE?G$|^GIF$|^PNG|^BMP$/i', $types[1])) return true;
            }
        }
        return false;
    }
}
