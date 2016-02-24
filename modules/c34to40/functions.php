<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Thu, 09 Jan 2014 10:18:48 GMT
 */

if( !defined( 'NV_SYSTEM' ) )
	die( 'Stop!!!' );

define( 'NV3_PREFIX', 'nv3' );
define( 'NV4_PREFIX', $db_config['prefix'] );

if ($sys_info['allowed_set_time_limit']) {
    set_time_limit(1200);
}

/**
 * coppy_lang()
 * Coppy các bảng dữ liệu tiếng việt, chuyển sang bảng tiếng anh, dữ liệu của bảng config
 * @return
 */
function coppy_lang( )
{
	global $db_config, $db;
	
	$result = $db->query( "SHOW TABLE STATUS LIKE '" . $db_config['prefix'] . "\_%'" );
	while( $item = $result->fetch( ) )
	{
		if( strpos( $item['name'], NV4_PREFIX . '_vi_' ) !== false )
		{
			echo $item['name'] . '<br>' . strpos( NV4_PREFIX . '_', $item['name'] ) . '<br>';
			$table_en = str_replace( NV4_PREFIX . '_vi_', NV4_PREFIX . '_en_', $item['name'] );
			$db->query( 'DROP TABLE IF EXISTS ' . $table_en );
			$db->query( 'CREATE TABLE ' . $table_en . ' LIKE ' . $item['name'] );

			$db->query( 'INSERT INTO ' . $table_en . ' SELECT * FROM ' . $item['name'] );

		}
	}
	$db->query( "DELETE FROM `" . NV4_PREFIX . "_config` WHERE `lang`='en'" );

	//config
	$_sql = "SELECT * FROM `" . NV4_PREFIX . "_config` WHERE `lang`='vi'";
	$_query = $db->query( $_sql );
	while( $row = $_query->fetch( ) )
	{
		$db->query( "INSERT INTO `" . NV4_PREFIX . "_config`(`lang`, `module`, `config_name`, `config_value`) VALUES ('en'," . $db->quote( $row['module'] ) . "," . $db->quote( $row['config_name'] ) . "," . $db->quote( $row['config_value'] ) . ")" );
	}
}

function nv_file_table( $table )
{
	global $db_config, $db;
	$lang_value = nv_list_lang( );
	$arrfield = array( );
	$result = $db->query( 'SHOW COLUMNS FROM ' . $table );
	while( list( $field ) = $result->fetch( 3 ) )
	{
		$tmp = explode( '_', $field );
		foreach( $lang_value as $lang_i )
		{
			  if (! empty($tmp[0]) && ! empty($tmp[1])) {
                if ($tmp[0] == $lang_i) {
                	$tmp[1]=(isset($tmp[2])) ? $tmp[1].'_'.$tmp[2] : $tmp[1];
                    $arrfield[] = array( $tmp[0], $tmp[1] );
                    break;
                }
            }
		}
	}
	return $arrfield;
}

function nv_list_lang( )
{
	global $db_config, $db;
	$re = $db->query( 'SELECT lang FROM ' . $db_config['prefix'] . '_setup_language WHERE setup=1' );
	$lang_value = array( );
	while( list( $lang_i ) = $re->fetch( 3 ) )
	{
		$lang_value[] = $lang_i;
	}
	return $lang_value;
}

function unfixdb( $value )
{
	$value = preg_replace( array(
		"/(se)\-(lect)/i",
		"/(uni)\-(on)/i",
		"/(con)\-(cat)/i",
		"/(c)\-(har)/i",
		"/(out)\-(file)/i",
		"/(al)\-(ter)/i",
		"/(in)\-(sert)/i",
		"/(d)\-(rop)/i",
		"/(f)\-(rom)/i",
		"/(whe)\-(re)/i",
		"/(up)\-(date)/i",
		"/(de)\-(lete)/i",
		"/(cre)\-(ate)/i"
	), "$1$2", $value );
	return $value;
}

// xóa các bảng dữ liệu nv3 đi
function del_nv3( )
{
	global $db;

	$err = '';
	$result = $db->query( "SHOW TABLE STATUS LIKE '" . NV3_PREFIX . "\_%'" );
	while( $item = $result->fetch( ) )
	{
		$db->query( "DROP TABLE " . $item['name'] );
		$err .= "deltete " . $item['name'] . " </br>";
	}
	return $err;
}

define( 'NV_IS_MOD_C34TO40', true );
