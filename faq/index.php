<?php
/* *************************************************************************
  Id: index.php

  Basic security/convenience measure.


  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

if(is_dir('./setup')){
  $osf_goto = './setup/';
}else{
  $osf_goto = '../faq.php';
}

header('Location: ' . $osf_goto);
exit();
?>