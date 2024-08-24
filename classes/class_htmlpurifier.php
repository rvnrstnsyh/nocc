<?php

class HTMLPurifier_URIScheme_cid extends HTMLPurifier_URIScheme
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
