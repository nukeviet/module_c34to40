<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Thu, 09 Jan 2014 10:18:48 GMT
 */

if( !defined( 'NV_MAINFILE' ) )
	die( 'Stop!!!' );

$module_version = array(
	'name' => 'C34to40',
	'modfuncs' => 'main,news,download,organs,laws,faqs,album,shop,users,other_db, page, contact',
	'submenu' => 'main,news,download,organs,laws,faqs,album,shop,users,other_db, page, contact',
	'is_sysmod' => 0,
	'virtual' => 0,
	'version' => '4.0.0',
	'date' => 'Thu, 9 Jan 2014 10:18:49 GMT',
	'author' => 'VINADES (contact@vinades.vn)',
	'uploads_dir' => array( $module_name ),
	'note' => ''
);