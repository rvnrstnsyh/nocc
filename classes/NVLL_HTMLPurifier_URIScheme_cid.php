<?php

/**
 * This class, NVLL_HTMLPurifier_URIScheme_cid,
 * extends the HTMLPurifier_URIScheme class and defines
 * settings for handling URIs with the cid
 *
 * Copyright 2024 Rivane Rasetiansyah <re@nvll.me>
 *
 * This file is part of NVLL. NVLL is free software under the terms of the
 * GNU General Public License. You should have received a copy of the license
 * along with NVLL. If not, see <http://www.gnu.org/licenses>.
 */

class NVLL_HTMLPurifier_URIScheme_cid extends HTMLPurifier_URIScheme
{
  public $browsable = true;
  public $allowed_types = array(
    'image/jpeg' => true,
    'image/gif' => true,
    'image/png' => true,
    'application/octet-stream' => true,
  );
  public $may_omit_host = true;
  public function doValidate(&$uri, $config, $context)
  {
    return true;
  }
}
