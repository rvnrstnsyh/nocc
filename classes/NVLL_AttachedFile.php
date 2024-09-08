<?php

/**
 * Class for wrapping a attached file
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

/**
 * Wrapping a attached file
 */
class NVLL_AttachedFile
{
    /**
     * Temp file path
     * @var string
     */
    private $tmpFile = '';
    /**
     * File name
     * @var string 
     */
    private $name = '';
    /**
     * Bytes
     * @var integer
     */
    private $bytes = 0;
    /**
     * MIME type
     * @var string 
     */
    private $mimeType = '';

    /**
     * ...
     * @param string $tmpFile Temp file path
     * @param string $name File name
     * @param integer $bytes File size in bytes
     * @param string $mimeType MIME type
     */
    public function __construct($tmpFile, $name, $bytes, $mimeType)
    {
        $this->tmpFile = $tmpFile;
        $this->name = $name;
        $this->bytes = $bytes;
        $this->mimeType = $mimeType;

        if (empty($mimeType)) $this->mimeType = trim(`file -b $tmpFile`);
    }

    /**
     * Get the temp file path from the attached file
     * @return string Temp file path
     */
    public function getTmpFile()
    {
        return $this->tmpFile;
    }

    /**
     * Get the name from the attached file
     * @return string File name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the number of bytes from the attached file
     * @return integer Number of bytes
     */
    public function getBytes()
    {
        return $this->bytes;
    }

    /**
     * Get the size from the attached file in kilobyte
     * @return integer Size in kilobyte
     */
    public function getSize()
    {
        //if more then 1024 bytes...
        if ($this->bytes > 1024) return ceil($this->bytes / 1024);
        return 1;
    }

    /**
     * Get the MIME type from the attached file
     * @return type MIME type
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * ...
     * @return bool Exists?
     */
    public function exists()
    {
        return file_exists($this->tmpFile);
    }

    /**
     * ...
     * @return string Content
     */
    public function getContent()
    {
        if ($this->exists()) {
            // Check if the file size is 0!
            if (filesize($this->tmpFile) === 0) return '';

            $fp = fopen($this->tmpFile, 'rb');
            $content = fread($fp, $this->bytes);
            fclose($fp);

            return $content;
        }
        return '';
    }

    /**
     * ...
     */
    public function delete()
    {
        @unlink($this->tmpFile);
    }
}
