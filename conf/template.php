<?php
/**
 * template config
 +-----------------------------------------
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 */

return array(
    //'path'        => '',
    'dir'           => 'template',
    'vars'          => array(),
    'callback'      => array(
        'display'       =>  function($debug)
                            {
                                if ($debug)
                                    \pt\framework\debug\console::show();
                            },
    ),
);