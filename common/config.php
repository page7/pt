<?php
/**
 +------------------------------------------------------------------------------
 * Convention Config
 +------------------------------------------------------------------------------
 * @category    Pt
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 +------------------------------------------------------------------------------
 */

if (!defined('PT_PATH')) exit();

return  array(
    'build_dir_secure'      =>  true,       // Auto create index.html in dir
    'time_zone'             =>  'PRC',      // Time zone
    'autoload_path'         =>  '',         // Autoload class path, use "," to set multiple dir
    'ajax_var'              =>  'ajax',     // AJAX request parameter by get
    'reflesh_var'           =>  'r',        // resflesh browser catch
    'i18n'                  =>  true,       // i18n support
);