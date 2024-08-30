<?php

/**
 * Charset configuration for NVLL
 * 
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

class Charset
{
  var $charset = '';
  var $aliases = '';
  var $group = '';
  var $label = '';
}

$charset_array = array();

$i = 0;
$charset_array[$i] = new Charset();
$charset_array[$i]->charset = 'ISO-8859-1';
$charset_array[$i]->aliases = 'ISO8859-1';
$charset_array[$i]->group = 'Western European';
$charset_array[$i]->label = 'Western European, Latin-1 (ISO-8859-1)';

$i++;
$charset_array[$i] = new Charset();
$charset_array[$i]->charset = 'ISO-8859-5';
$charset_array[$i]->aliases = 'ISO8859-5';
$charset_array[$i]->group = 'Cyrillic';
$charset_array[$i]->label = 'Latin/Cyrillic (ISO-8859-5)';

$i++;
$charset_array[$i] = new Charset();
$charset_array[$i]->charset = 'ISO-8859-15';
$charset_array[$i]->aliases = 'ISO8859-15';
$charset_array[$i]->group = 'Western European';
$charset_array[$i]->label = 'Western European, Latin-9 (ISO-8859-15)';

$i++;
$charset_array[$i] = new Charset();
$charset_array[$i]->charset = 'UTF-8';
$charset_array[$i]->aliases = '';
$charset_array[$i]->group = 'Universal';
$charset_array[$i]->label = 'ASCII compatible multi-byte 8-bit Unicode (UTF-8)';

$i++;
$charset_array[$i] = new Charset();
$charset_array[$i]->charset = 'cp866';
$charset_array[$i]->aliases = 'ibm866, 866';
$charset_array[$i]->group = 'Cyrillic';
$charset_array[$i]->label = 'DOS-specific Cyrillic charset (cp866)';

$i++;
$charset_array[$i] = new Charset();
$charset_array[$i]->charset = 'cp1251';
$charset_array[$i]->aliases = 'Windows-1251, win-1251, 1251';
$charset_array[$i]->group = 'Cyrillic';
$charset_array[$i]->label = 'DOS-specific Cyrillic charset (cp1251)';

$i++;
$charset_array[$i] = new Charset();
$charset_array[$i]->charset = 'cp1252';
$charset_array[$i]->aliases = 'Windows-1252, 1252';
$charset_array[$i]->group = 'Western European';
$charset_array[$i]->label = 'DOS-specific Western European (cp1252)';

$i++;
$charset_array[$i] = new Charset();
$charset_array[$i]->charset = 'KOI8-R';
$charset_array[$i]->aliases = 'koi8-ru, koi8r';
$charset_array[$i]->group = 'Cyrillic';
$charset_array[$i]->label = 'Russian (KOI8-R)';

$i++;
$charset_array[$i] = new Charset();
$charset_array[$i]->charset = 'BIG5';
$charset_array[$i]->aliases = '950';
$charset_array[$i]->group = 'Chinese';
$charset_array[$i]->label = 'Traditional Chinese, Taiwan (BIG5)';

$i++;
$charset_array[$i] = new Charset();
$charset_array[$i]->charset = 'GB2312';
$charset_array[$i]->aliases = '936';
$charset_array[$i]->group = 'Chinese';
$charset_array[$i]->label = 'Simplified Chinese, national (GB2312)';

$i++;
$charset_array[$i] = new Charset();
$charset_array[$i]->charset = 'BIG5-HKSCS';
$charset_array[$i]->aliases = '';
$charset_array[$i]->group = 'Chinese';
$charset_array[$i]->label = 'Traditional Chinese, Hong Kong (BIG5-HKSCS)';

$i++;
$charset_array[$i] = new Charset();
$charset_array[$i]->charset = 'Shift_JIS';
$charset_array[$i]->aliases = 'SJIS, SJIS-win, cp932, 932';
$charset_array[$i]->group = 'Japanese';
$charset_array[$i]->label = 'Japanese (Shift_JIS)';

$i++;
$charset_array[$i] = new Charset();
$charset_array[$i]->charset = 'EUC-JP';
$charset_array[$i]->aliases = 'EUCJP, eucJP-win';
$charset_array[$i]->group = 'Japanese';
$charset_array[$i]->label = 'Japanese (EUC-JP)';

$i++;
$charset_array[$i] = new Charset();
$charset_array[$i]->charset = 'MacRoman';
$charset_array[$i]->aliases = '';
$charset_array[$i]->group = 'Western European';
$charset_array[$i]->label = 'Charset that was used by Mac OS (Roman)';

$i++;
$charset_array[$i] = new Charset();
$charset_array[$i]->charset = '';
$charset_array[$i]->aliases = '';
$charset_array[$i]->group = 'Auto-Detect';
$charset_array[$i]->label = 'Script encoding detection (not recommended)';
