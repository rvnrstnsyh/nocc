<?php

/**
 * Class for wrapping the languages
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

/**
 * Wrapping the languages
 */
class NVLL_Languages
{
    /**
     * Languages
     * @var array
     * @access private
     */
    private $_languages;

    /**
     * Default language ID
     * @var string
     * @access private
     */
    private $_defaultLangId;

    /**
     * Selected language ID
     * @var string
     * @access private
     */
    private $_selectedLangId;

    private function loadLanguageFile($filePath)
    {
        if (file_exists($filePath)) return include $filePath;
        return [];
    }

    /**
     * Initialize the languages wrapper
     * @param string $path Languages path (relative)
     * @param string $defaultLangId Default language ID
     */
    public function __construct($path, $defaultLangId = 'en', $allowedLanguages = null)
    {
        $this->_languages = array();
        $this->_defaultLangId = 'en';
        $this->_selectedLangId = 'en';

        if (isset($path) && is_string($path) && !empty($path)) {
            if (is_dir($path)) {
                if (substr($path, -1) != '/') $path .= '/';
                $files = glob($path . '[a-z][a-z].php');
                foreach ($files as $file) {
                    $basename = strtolower(basename($file, '.php'));
                    if ($allowedLanguages === null || in_array($basename, $allowedLanguages)) $this->_languages[$basename] = $this->loadLanguageFile($file);
                }

                if ($this->exists($defaultLangId)) {
                    $this->_defaultLangId = strtolower($defaultLangId);
                    $this->_selectedLangId = $this->_defaultLangId;
                }

                if (!isset($this->_languages['default'])) $this->_languages['default'] = $this->_languages[$this->_defaultLangId];
            }
        }
    }

    /**
     * Get the count from the languages
     * @return int Count
     */
    public function count()
    {
        return count($this->_languages);
    }

    /**
     * Exists the language?
     * @param string $langId Language ID
     * @return bool Exists?
     */
    public function exists($langId)
    {
        //if language ID is set...
        if (isset($langId) && is_string($langId) && !empty($langId)) {
            $langId = strtolower($langId);
            return array_key_exists($langId, $this->_languages);
        }
        return false;
    }

    /**
     * Detect the language from the browser...
     * @return string Language ID
     */
    public function detectFromBrowser()
    {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) { //if the "Accept-Language" header is set...
            $acceptedLanguages = $this->parseAcceptLanguageHeader($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach ($acceptedLanguages as $fullLangId => $langQuality) { //for all accepted languages...
                $langId = explode('-', $fullLangId); //Split language and country ID, if exist!
                if ($this->exists($langId[0])) { //if the language exists...
                    $this->_selectedLangId = $langId[0];
                    return $langId[0];
                }
            }
        }
        return $this->_defaultLangId;
    }

    /**
     * Get the default language ID
     * @return string Default language ID
     */
    public function getDefaultLangId()
    {
        return $this->_defaultLangId;
    }

    /**
     * Set the default language ID
     * @param string $langId Default language ID
     * @return bool Successful?
     */
    public function setDefaultLangId($langId)
    {
        if ($this->exists($langId) && isset($this->_languages[strtolower($langId)])) {
            $this->_defaultLangId = strtolower($langId);
            return true;
        }
        return false;
    }

    /**
     * Get the selected language ID
     * @return string Selected language ID
     */
    public function getSelectedLangId()
    {
        //if a language is selected...
        if (!empty($this->_selectedLangId)) return $this->_selectedLangId;
        return $this->_defaultLangId;
    }

    /**
     * Set the selected language ID
     * @param string $langId Selected language ID
     * @return bool Successful?
     */
    public function setSelectedLangId($langId)
    {
        if ($this->exists($langId) && isset($this->_languages[strtolower($langId)])) {
            $this->_selectedLangId = strtolower($langId);
            return true;
        }
        return false;
    }

    /**
     * Parce the "Accept-Language" header...
     * @param string $acceptLanguageHeader "Accept-Language" header
     * @return array Accepted languages
     * @static
     */
    public static function parseAcceptLanguageHeader($acceptLanguageHeader)
    {
        $languages = array();
        if (isset($acceptLanguageHeader) && is_string($acceptLanguageHeader) && !empty($acceptLanguageHeader)) { //if the "Accept-Language" header is set...
            $acceptLanguageHeader = strtolower($acceptLanguageHeader);
            $acceptLanguageHeader = str_replace(' ', '', $acceptLanguageHeader);
            $acceptLanguageHeader = str_replace('q=', '', $acceptLanguageHeader);
            $langQuality = '1.0';
            $acceptedLanguages = explode(',', $acceptLanguageHeader);

            foreach ($acceptedLanguages as $acceptedLanguage) { //for all accepted languages...
                $tmp = explode(';', $acceptedLanguage);
                if (isset($tmp[0]) && !empty($tmp[0])) { //if found language ID...
                    $lang_id = $tmp[0];
                    //if found language quality...
                    if (isset($tmp[1]) && !empty($tmp[1])) $langQuality = $tmp[1];
                    $languages[$lang_id] = $langQuality;
                }
            }
        }
        return $languages;
    }
}
