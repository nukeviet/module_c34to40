<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Thu, 09 Jan 2014 10:18:48 GMT
 */

if( ! defined( 'NV_IS_MOD_C34TO40' ) )
	die( 'Stop!!!' );

/**
 * nv_theme_c34to40_main()
 *
 * @param mixed $array_data
 * @return
 */
function nv_theme_c34to40_main( $data )
{
	global $global_config, $module_name, $module_file, $lang_module, $module_config, $module_info, $op;

	$xtpl = new XTemplate( $op . '.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file );
	$xtpl->assign( 'LANG', $lang_module );
	$xtpl->assign( 'DATA', $data );

	$xtpl->parse( 'main' );
	return $xtpl->text( 'main' );
}
?>