<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Thu, 09 Jan 2014 10:18:48 GMT
 */

if( !defined( 'NV_IS_MOD_C34TO40' ) ) die( 'Stop!!!' );

$page_title = $module_info['custom_title'];
$key_words = $module_info['keywords'];

$_check_exit = false;
if( is_dir( NV_ROOTDIR . '/tmp/module-convert' ) )
{
	$modules_exit = nv_scandir( NV_ROOTDIR . '/tmp/module-convert/modules', $global_config['check_module'] );
	foreach( $modules_exit as $mod_file )
	{
		$version_file = NV_ROOTDIR . '/tmp/module-convert/modules/' . $mod_file . '/version.php';
		if( file_exists( $version_file ) )
		{
			$_check_exit = true;
			break;
		}
	}
}

$contents = '';
if( $_check_exit )
{
	$contents = nv_fomat_dir( 'tmp/module-convert', $contents );
	$contents = nv_theme_c34to40_main( $contents );
}
else
{
	$contents = 'Bạn cần copy module nukeviet3.4 đã giải nén vào thư mục <b>tmp/module-convert</b>, sau đó chạy lại module này<br><br>Ví dụ: Tôi nâng cấp module music, thì khi đó phải tồn tại file: tmp/module-convert/modules/music/version.php';
}

/**
 * nv_fomat_dir()
 *
 * @param mixed $dirname
 * @param mixed $contents
 * @return
 */
function nv_fomat_dir( $dirname, &$contents )
{
	$dh = opendir( NV_ROOTDIR . '/' . $dirname );
	if( $dh )
	{
		while( ($file = readdir( $dh )) !== false )
		{
			if( preg_match( '/^([a-zA-Z0-9\-\_\/\.]+)\.php$/', $file ) )
			{

				$contents_file = file_get_contents( NV_ROOTDIR . '/' . $dirname . '/' . $file );

				$output_data = str_replace( 'include ( NV_ROOTDIR . \'/includes/header.php\' );', 'include NV_ROOTDIR . \'/includes/header.php\';', $contents_file );
				$output_data = str_replace( 'include ( NV_ROOTDIR . \'/includes/footer.php\' );', 'include NV_ROOTDIR . \'/includes/footer.php\';', $output_data );

				$output_data = str_replace( 'include ( NV_ROOTDIR . "/includes/header.php" );', 'include NV_ROOTDIR . \'/includes/header.php\';', $output_data );
				$output_data = str_replace( 'include ( NV_ROOTDIR . "/includes/footer.php" );', 'include NV_ROOTDIR . \'/includes/footer.php\';', $output_data );

				$output_data = str_replace( 'include NV_ROOTDIR . "/includes/header.php";', 'include NV_ROOTDIR . \'/includes/header.php\';', $output_data );
				$output_data = str_replace( 'include NV_ROOTDIR . "/includes/footer.php";', 'include NV_ROOTDIR . \'/includes/footer.php\';', $output_data );

				$output_data = str_replace( 'die( "OK" )', "die( 'OK' )", trim( $output_data ) );
				$output_data = str_replace( 'die( "NO" )', "die( 'NO' )", trim( $output_data ) );

				// Thay thế sử dụng PDO
				$output_data = str_replace( '$db->sql_query(', '$db->query(', trim( $output_data ) );
				$output_data = str_replace( '$db->sql_query_insert_id(', '$db->insert_id(', trim( $output_data ) );
				$output_data = str_replace( '$db->dbescape_string(', '$db->quote(', trim( $output_data ) );
				$output_data = str_replace( '$db->dbescape(', '$db->quote(', trim( $output_data ) );
				$output_data = str_replace( 'mysql_query(', '$db->query(', trim( $output_data ) );

				$output_data = preg_replace( '/\$db\-\>unfixdb\( \$([^\)]+) \)/', '$\\1', $output_data );

				// Thay thế sql_fetchrow

				$output_data = preg_replace( '/\$([a-zA-Z0-9\_]+)\s*\=\s*\$db\-\>sql\_fetchrow\(\s*\$([a-zA-Z0-9\_]+)\s*\)/', '$\\1 = $\\2->fetch()', $output_data );

				// Thay thế sql_fetchrow(query)
				$output_data = preg_replace( '/\$([a-zA-Z0-9\_]+)\s*\=\s*\$db\-\>sql\_fetch\_assoc\(\s*\$([a-zA-Z0-9\_]+)\s*\)/', '$\\1 = $\\2->fetch()', $output_data );
				$output_data = preg_replace( '/\$db\-\>sql\_fetch\_assoc\(\s*\$db\-\>query\(([^\;]+)\)\s*\)\;/', '$db->query(\\1)->fetch();', $output_data );
				$output_data = preg_replace( '/\$db\-\>sql\_fetchrow\(\s*\$db\-\>query\(([^\;]+)\s*\)\, 2 \)\;/', '$db->query(\\1)->fetch();', $output_data );

				$output_data = preg_replace( '/list\(([^\)]+)\)\s*\=\s*\$db\-\>sql\_fetchrow\(\s*\$([a-zA-Z0-9\_]+)\s*\)/', 'list(\\1) = $\\2->fetch( 3 )', $output_data );
				$output_data = preg_replace( '/list\(\s*\$([a-zA-Z0-9\_\[\]\']+)\s*\)\s*\=\s*\$db\-\>sql\_fetchrow\(\s*\$db\-\>query\(([^\;]+)\)\s*\)\;/', '$\\1 = $db->query(\\2)->fetchColumn();', $output_data );
				$output_data = preg_replace( '/list\(\s*\$([a-zA-Z0-9\_\[\]\']+)\s*\) \= \$db\-\>sql\_fetchrow\(\s*\$db\-\>query\(([^\;]+)\)\, 1\s*\)\;/', '$\\1 = $db->query(\\2)->fetchColumn();', $output_data );
				$output_data = preg_replace( '/list\(([a-zA-Z0-9\_\$\s\,\[\]\']+)\)\s*\=\s*\$db\-\>sql\_fetchrow\(\s*\$db\-\>query\(([^\;]+)\)\s*\)\;/', 'list(\\1) = $db->query(\\2)->fetch( 3 );', $output_data );
				$output_data = preg_replace( '/list\(\s*\$([a-zA-Z0-9_]+)\s*\)\s*=\s*\$([a-zA-Z0-9_]+)\-\>fetch\(\s*3\s*\)\;/', '$\\1 = $\\2->fetchColumn();', $output_data );

				$output_data = preg_replace( '/\$db\-\>sql\_fetchrow\(\s*\$([a-zA-Z0-9\_]+)\, 1\s*\)/', '$\\1->fetch( 3 )', $output_data );
				$output_data = preg_replace( '/\$db\-\>sql\_fetchrow\(\s*\$([a-zA-Z0-9\_]+)\, 2\s*\)/', '$\\1->fetch()', $output_data );

				$output_data = preg_replace( '/\$([a-zA-Z0-9\_]+)\s*\=\s*\$db\-\>sql\_fetchrow\s*\(\s*\$db\-\>query\s*\(([^\;]+)\)\s*\)\;/', '$\\1 = $db->query(\\2)->fetch();', $output_data );
				$output_data = preg_replace( '/\$([a-zA-Z0-9\_]+)\s*\=\s*\$db\-\>sql\_fetchrow\s*\(\s*\$db\-\>query\s*\(([^\;]+)\)\s*,\s*2\s*\)\;/', '$\\1 = $db->query(\\2)->fetch();', $output_data );

				//sql_freeresult
				$output_data = preg_replace( '/\$db\-\>sql\_freeresult\(\s*\$([a-zA-Z0-9\_]+)\s*\)/', '$\\1->closeCursor()', $output_data );

				$output_data = preg_replace( '/\$db\-\>sql\_numrows\(\s*\$([a-zA-Z0-9\_]+)\s*\)/', '$\\1->rowCount()', $output_data );
				$output_data = preg_replace( '/\$db\-\>sql\_numrows\(\s*\$db\-\>query\(([^\;|\n]+)\)\s*\)\;/', '$db->query(\\1)->rowCount();', $output_data );
				$output_data = preg_replace( '/\$db\-\>sql\_numrows\(\s*\$db\-\>query\(([^\n]+)\)\s*\) /', '$db->query(\\1)->rowCount() ', $output_data );

				//filter_text_input
				if( preg_match_all( "/filter\_text\_input\(([^\n]+)\)\;/", $output_data, $m1 ) and preg_match_all( "/filter\_text\_input\(([^\;|^\n]+)\)\;/", $output_data, $m ) )
				{
					foreach( $m[1] as $key => $string )
					{
						$string = str_replace( "'get,post'", "'_get_post_'", $string );
						$string = str_replace( "'get, post'", "'_get_post_'", $string );
						$string = str_replace( "'get ,post'", "'_get_post_'", $string );

						$string = str_replace( '"get , post"', '"_get_post_"', $string );
						$string = str_replace( '"get, post"', '"_get_post_"', $string );
						$string = str_replace( '"get ,post"', '"_get_post_"', $string );

						$string = str_replace( "'post,get'", "'_post_get_'", $string );
						$string = str_replace( "'post, get'", "'_post_get_'", $string );
						$string = str_replace( "'post ,get'", "'_post_get_'", $string );

						$string = str_replace( '"post , get"', '"_post_get_"', $string );
						$string = str_replace( '"post, get"', '"_post_get_"', $string );
						$string = str_replace( '"post ,get"', '"_post_get_"', $string );

						$value_arr = explode( ',', $string );
						$value_arr = array_map( 'trim', $value_arr );
						if( sizeof( $value_arr ) <= 4 )
						{
							$string_new = '$nv_Request->get_title( ' . implode( ', ', $value_arr ) . ' );';
						}
						elseif( sizeof( $value_arr ) == 5 )
						{
							$maxlength = array_pop( $value_arr );
							$string_new = 'nv_substr( $nv_Request->get_title( ' . implode( ', ', $value_arr ) . ' ), 0, ' . $maxlength . ');';
						}
						elseif( sizeof( $value_arr ) == 6 )
						{
							$preg_replace = array_pop( $value_arr );
							$maxlength = array_pop( $value_arr );
							$value_arr[] = $preg_replace;
							$string_new = 'nv_substr( $nv_Request->get_title( ' . implode( ', ', $value_arr ) . ' ), 0, ' . $maxlength . ');';
						}
						else
						{
							print_r( $value_arr );
							die( '<H1> ERROR: </H1>' . $string );
						}
						$string_new = str_replace( "_get_post_", "get,post", $string_new );
						$string_new = str_replace( "_post_get_", "get,post", $string_new );
						$output_data = str_replace( $m[0][$key], $string_new, $output_data );
					}
				}

				if( preg_match_all( "/filter\_text\_input\(([^\(]+)\)\,/", $output_data, $m1 ) and preg_match_all( "/filter\_text\_input\(([^\)]+)\)\,/", $output_data, $m2 ) and preg_match_all( "/filter\_text\_input\(([^\n]+)\)\,/", $output_data, $m ) )
				{
					foreach( $m[1] as $key => $string )
					{
						$string = str_replace( "'get,post'", "'_get_post_'", $string );
						$string = str_replace( "'get, post'", "'_get_post_'", $string );
						$string = str_replace( "'get ,post'", "'_get_post_'", $string );

						$string = str_replace( '"get , post"', '"_get_post_"', $string );
						$string = str_replace( '"get, post"', '"_get_post_"', $string );
						$string = str_replace( '"get ,post"', '"_get_post_"', $string );

						$string = str_replace( "'post,get'", "'_post_get_'", $string );
						$string = str_replace( "'post, get'", "'_post_get_'", $string );
						$string = str_replace( "'post ,get'", "'_post_get_'", $string );

						$string = str_replace( '"post , get"', '"_post_get_"', $string );
						$string = str_replace( '"post, get"', '"_post_get_"', $string );
						$string = str_replace( '"post ,get"', '"_post_get_"', $string );

						$value_arr = explode( ',', $string );
						$value_arr = array_map( 'trim', $value_arr );
						if( sizeof( $value_arr ) <= 4 )
						{
							$string_new = '$nv_Request->get_title( ' . implode( ', ', $value_arr ) . ' ),';
						}
						elseif( sizeof( $value_arr ) == 5 )
						{
							$maxlength = array_pop( $value_arr );
							$string_new = 'nv_substr( $nv_Request->get_title( ' . implode( ', ', $value_arr ) . ' ), 0, ' . $maxlength . '),';
						}
						elseif( sizeof( $value_arr ) == 6 )
						{
							$preg_replace = array_pop( $value_arr );
							$maxlength = array_pop( $value_arr );
							$value_arr[] = $preg_replace;
							$string_new = 'nv_substr( $nv_Request->get_title( ' . implode( ', ', $value_arr ) . ' ), 0, ' . $maxlength . '),';
						}
						else
						{
							print_r( $value_arr );
							die( '<H1> ERROR: </H1>' . $string );
						}
						$string_new = str_replace( "_get_post_", "get,post", $string_new );
						$string_new = str_replace( "_post_get_", "get,post", $string_new );
						$output_data = str_replace( $m[0][$key], $string_new, $output_data );
					}
				}

				if( preg_match_all( "/filter\_text\_input\(([^\(]+)\)\s\=\=\s/", $output_data, $m1 ) and preg_match_all( "/filter\_text\_input\(([^\)]+)\)\s\=\=\s/", $output_data, $m2 ) and preg_match_all( "/filter\_text\_input\(([^\=|^\n]+)\)\s\=\=\s/", $output_data, $m ) )
				{
					foreach( $m[1] as $key => $string )
					{
						$string = str_replace( "'get,post'", "'_get_post_'", $string );
						$string = str_replace( "'get, post'", "'_get_post_'", $string );
						$string = str_replace( "'get ,post'", "'_get_post_'", $string );

						$string = str_replace( '"get , post"', '"_get_post_"', $string );
						$string = str_replace( '"get, post"', '"_get_post_"', $string );
						$string = str_replace( '"get ,post"', '"_get_post_"', $string );

						$string = str_replace( "'post,get'", "'_post_get_'", $string );
						$string = str_replace( "'post, get'", "'_post_get_'", $string );
						$string = str_replace( "'post ,get'", "'_post_get_'", $string );

						$string = str_replace( '"post , get"', '"_post_get_"', $string );
						$string = str_replace( '"post, get"', '"_post_get_"', $string );
						$string = str_replace( '"post ,get"', '"_post_get_"', $string );

						$value_arr = explode( ',', $string );
						$value_arr = array_map( 'trim', $value_arr );
						if( sizeof( $value_arr ) <= 4 )
						{
							$string_new = '$nv_Request->get_title( ' . implode( ', ', $value_arr ) . ' ) == ';
						}
						elseif( sizeof( $value_arr ) == 5 )
						{
							$maxlength = array_pop( $value_arr );
							$string_new = 'nv_substr( $nv_Request->get_title( ' . implode( ', ', $value_arr ) . ' ), 0, ' . $maxlength . ') == ';
						}
						elseif( sizeof( $value_arr ) == 6 )
						{
							$preg_replace = array_pop( $value_arr );
							$maxlength = array_pop( $value_arr );
							$value_arr[] = $preg_replace;
							$string_new = 'nv_substr( $nv_Request->get_title( ' . implode( ', ', $value_arr ) . ' ), 0, ' . $maxlength . ') == ';
						}
						else
						{
							print_r( $value_arr );
							die( '<H1> ERROR: </H1>' . $string );
						}
						$string_new = str_replace( "_get_post_", "get,post", $string_new );
						$string_new = str_replace( "_post_get_", "get,post", $string_new );
						$output_data = str_replace( $m[0][$key], $string_new, $output_data );
					}
				}

				if( preg_match_all( "/filter\_text\_input\(([^\n]+)\)\s*\/\//", $output_data, $m1 ) and preg_match_all( "/filter\_text\_input\(([^\;|^\n]+)\)\s*\/\//", $output_data, $m ) )
				{
					foreach( $m[1] as $key => $string )
					{
						$string = str_replace( "'get,post'", "'_get_post_'", $string );
						$string = str_replace( "'get, post'", "'_get_post_'", $string );
						$string = str_replace( "'get ,post'", "'_get_post_'", $string );

						$string = str_replace( '"get , post"', '"_get_post_"', $string );
						$string = str_replace( '"get, post"', '"_get_post_"', $string );
						$string = str_replace( '"get ,post"', '"_get_post_"', $string );

						$string = str_replace( "'post,get'", "'_post_get_'", $string );
						$string = str_replace( "'post, get'", "'_post_get_'", $string );
						$string = str_replace( "'post ,get'", "'_post_get_'", $string );

						$string = str_replace( '"post , get"', '"_post_get_"', $string );
						$string = str_replace( '"post, get"', '"_post_get_"', $string );
						$string = str_replace( '"post ,get"', '"_post_get_"', $string );

						$value_arr = explode( ',', $string );
						$value_arr = array_map( 'trim', $value_arr );
						if( sizeof( $value_arr ) <= 4 )
						{
							$string_new = '$nv_Request->get_title( ' . implode( ', ', $value_arr ) . ' ) // ';
						}
						elseif( sizeof( $value_arr ) == 5 )
						{
							$maxlength = array_pop( $value_arr );
							$string_new = 'nv_substr( $nv_Request->get_title( ' . implode( ', ', $value_arr ) . ' ), 0, ' . $maxlength . ') // ';
						}
						elseif( sizeof( $value_arr ) == 6 )
						{
							$preg_replace = array_pop( $value_arr );
							$maxlength = array_pop( $value_arr );
							$value_arr[] = $preg_replace;
							$string_new = 'nv_substr( $nv_Request->get_title( ' . implode( ', ', $value_arr ) . ' ), 0, ' . $maxlength . ') // ';
						}
						else
						{
							print_r( $value_arr );
							die( '<H1> ERROR: </H1>' . $string );
						}
						$string_new = str_replace( "_get_post_", "get,post", $string_new );
						$string_new = str_replace( "_post_get_", "get,post", $string_new );
						$output_data = str_replace( $m[0][$key], $string_new, $output_data );
					}
				}
				//filter_text_textarea
				if( preg_match_all( "/filter\_text\_textarea\(([^\n]+)\)\;/", $output_data, $m0 ) and preg_match_all( "/filter\_text\_textarea\(([^\;]+)\)\;/", $output_data, $m ) )
				{
					foreach( $m[1] as $key => $string )
					{
						$value_arr = explode( ',', $string );
						$value_arr = array_map( 'trim', $value_arr );
						if( sizeof( $value_arr ) > 4 )
						{
							print_r( $m );
							die( '<H1> ERROR: </H1>' . $string );
						}
						$string_new = '$nv_Request->get_textarea( ' . implode( ', ', $value_arr ) . ' );';
						$output_data = str_replace( $m[0][$key], $string_new, $output_data );
					}
				}

				//nv_editor_filter_textarea
				if( preg_match_all( "/nv\_editor\_filter\_textarea\(([^\n]+)\)\;/", $output_data, $m0 ) and preg_match_all( "/nv\_editor\_filter\_textarea\(([^\;]+)\)\;/", $output_data, $m ) )
				{
					foreach( $m[1] as $key => $string )
					{
						$value_arr = explode( ',', $string );
						$value_arr = array_map( 'trim', $value_arr );
						if( sizeof( $value_arr ) > 4 )
						{
							print_r( $m );
							die( '<H1> ERROR: </H1>' . $string );
						}
						$string_new = '$nv_Request->get_editor( ' . implode( ', ', $value_arr ) . ' );';
						$output_data = str_replace( $m[0][$key], $string_new, $output_data );
					}
				}

				if( strpos( $output_data, 'filter_text_input' ) )
				{
					die( '<H1> ERROR: </H1>' . $dirname . '/' . $file . '--------------ERROR: filter_text_input--------<br><br>' . $output_data );
				}

				if( strpos( $output_data, 'filter_text_textarea' ) )
				{
					die( '<H1> ERROR: </H1>' . $dirname . '/' . $file . '--------------ERROR: filter_text_textarea--------<br><br>' . $output_data );
				}

				if( strpos( $output_data, 'nv_editor_filter_textarea' ) )
				{
					die( '<H1> ERROR: </H1>' . $dirname . '/' . $file . '--------------ERROR: nv_editor_filter_textarea--------<br><br>' . $output_data );
				}

				$output_data = str_replace( '$db->sql_freeresult();', '//$xxx->closeCursor();', trim( $output_data ) );

				if( preg_match( "/^\<\?php[\s\t\r\n]+\/\*\*[\s\t\r\n]+([^\#]+)[\s\t\r\n]+\*\/[\s\t\r\n]+/", $output_data, $m ) )
				{
					$new = str_replace( 'createdate', 'Createdate', $m[0] );
					$new = str_replace( "@Project NUKEVIET\n", "@Project NUKEVIET 3.x\n", $new );
					$new = str_replace( "Project NUKEVIET 3.0", "Project NUKEVIET 3.x", $new );
					$new = str_replace( "Project NUKEVIET 3.1", "Project NUKEVIET 3.x", $new );
					$new = str_replace( "Project NUKEVIET 3.2", "Project NUKEVIET 3.x", $new );
					$new = str_replace( "Project NUKEVIET 3.3", "Project NUKEVIET 3.x", $new );
					$new = str_replace( "Project NUKEVIET 3.4", "Project NUKEVIET 3.x", $new );
					$new = str_replace( "2009 VINADES", "2014 VINADES", $new );
					$new = str_replace( "2010 VINADES", "2014 VINADES", $new );
					$new = str_replace( "2011 VINADES", "2014 VINADES", $new );
					$new = str_replace( "2012 VINADES", "2014 VINADES", $new );
					$new = str_replace( "2013 VINADES", "2014 VINADES", $new );

					if( strpos( $new, '@Project NUKEVIET 3.x' ) and strpos( $new, '* @Createdate' ) )
					{
						$new = str_replace( '@Project NUKEVIET 3.x', '@Project NUKEVIET 4.x', $new );
						if( strpos( $new, '@Language' ) )
						{
							$new = str_replace( '* @Createdate', "* @License CC BY-SA (http://creativecommons.org/licenses/by-sa/4.0/)\n * @Createdate", $new );
						}
						else
						{
							$new = str_replace( '* @Createdate', "* @License GNU/GPL version 2 or any later version\n * @Createdate", $new );
						}
						$output_data = str_replace( $m[0], $new, $output_data );
					}
				}

				if( preg_match( '/\/modules\/([a-zA-Z0-9\-\_]+)\/admin\.functions\.php$/', $dirname . '/' . $file ) )
				{
					if( !file_exists( NV_ROOTDIR . '/' . $dirname . '/admin.menu.php' ) )
					{
						$array_sub_menu = array();
						if( preg_match_all( '/\$submenu\[\'([a-zA-Z0-9\-\_]+)\'][\s\t\r]*\=[\s\t\r]*\$lang_module\[\'([a-zA-Z0-9\-\_]+)\'][\s\t\r]*\;/', $output_data, $m ) )
						{
							foreach( $m[0] as $key => $value )
							{
								$array_sub_menu[] = trim( $value );
								$output_data = str_replace( $value, '//' . $value, $output_data );
							}
							$c_menu_admin = "<?php\n\n";
							$c_menu_admin .= NV_FILEHEAD;
							$c_menu_admin .= "\n\nif( ! defined( 'NV_ADMIN' ) ) die( 'Stop!!!' );\n\n";
							$c_menu_admin .= implode( "\n", $array_sub_menu );
							$c_menu_admin .= "\n\n?>";
							file_put_contents( NV_ROOTDIR . '/' . $dirname . '/admin.menu.php', trim( $c_menu_admin ), LOCK_EX );
						}
					}
				}

				$output_data = str_replace( "Header( 'Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_NAME_VARIABLE . '=' . \$module_name );", "Header( 'Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . \$module_name );", $output_data );
				$output_data = str_replace( 'Header( "Location: " . NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name );', "Header( 'Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . \$module_name );", $output_data );

				$output_data = str_replace( "NV_BASE_ADMINURL . 'index.php?' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;", "NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;", $output_data );
				$output_data = str_replace( 'NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name . "&amp;', 'NV_BASE_ADMINURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&amp;" . NV_NAME_VARIABLE . "=" . $module_name . "&amp;', $output_data );

				$output_data = str_replace( "NV_BASE_ADMINURL . 'index.php?' . NV_NAME_VARIABLE . '=' . \$module_name", "NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . \$module_name", $output_data );
				$output_data = str_replace( 'NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name', 'NV_BASE_ADMINURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&" . NV_NAME_VARIABLE . "=" . $module_name', $output_data );

				// Loại bỏ khoảng trắng ()
				$output_data = preg_replace( '/\([\s]+\)/', '()', $output_data );
				
				// Xóa thẻ đóng php
				$output_data = preg_replace( '/(\s*\?>\s*)\z$/', '', $output_data );

				if( $output_data != $contents_file )
				{
					file_put_contents( NV_ROOTDIR . '/' . $dirname . '/' . $file, trim( $output_data ), LOCK_EX );
					$contents .= $dirname . '/' . $file . "--------------change--------\n";
				}
				else
				{
					$contents .= $dirname . '/' . $file . "\n";
				}

				if( strpos( $output_data, '//$xxx-&gt;closeCursor()' ) )
				{
					$contents .= "--------------//$xxx-&gt;closeCursor()--------\n";
				}

				if( strpos( $output_data, '$db-&gt;sql_affectedrows()' ) )
				{
					$contents .= "--------------$db-&gt;sql_affectedrows()--------\n";
				}

				if( preg_match( '/\/modules\/([a-zA-Z0-9\-\_]+)\/action\.php$/', $dirname . '/' . $file ) )
				{
					rename( NV_ROOTDIR . '/' . $dirname . '/' . $file, NV_ROOTDIR . '/' . $dirname . '/action_mysql.php' );
				}
			}
			elseif( preg_match( '/^([a-zA-Z0-9\-\_\/\.]+)\.js$/', $file ) OR preg_match( '/^([a-zA-Z0-9\-\_\/\.]+)\.css/', $file ) )
			{
				$contents_file = file_get_contents( NV_ROOTDIR . '/' . $dirname . '/' . $file );
				$output_data = trim( $contents_file );

				if( preg_match( "/^\/\*\*[\s\t\r\n]+([^\#]+)[\s\t\r\n]+\*\/[\s\t\r\n]+/", $output_data, $m ) )
				{
					$new = str_replace( 'createdate', 'Createdate', $m[0] );
					$new = str_replace( "@Project NUKEVIET\n", "@Project NUKEVIET 3.x\n", $new );
					$new = str_replace( "Project NUKEVIET 3.0", "Project NUKEVIET 3.x", $new );
					$new = str_replace( "Project NUKEVIET 3.1", "Project NUKEVIET 3.x", $new );
					$new = str_replace( "Project NUKEVIET 3.2", "Project NUKEVIET 3.x", $new );
					$new = str_replace( "Project NUKEVIET 3.3", "Project NUKEVIET 3.x", $new );
					$new = str_replace( "Project NUKEVIET 3.4", "Project NUKEVIET 3.x", $new );
					$new = str_replace( "2009 VINADES", "2014 VINADES", $new );
					$new = str_replace( "2010 VINADES", "2014 VINADES", $new );
					$new = str_replace( "2011 VINADES", "2014 VINADES", $new );
					$new = str_replace( "2012 VINADES", "2014 VINADES", $new );
					$new = str_replace( "2013 VINADES", "2014 VINADES", $new );

					if( strpos( $new, '@Project NUKEVIET 3.x' ) and strpos( $new, '* @Createdate' ) )
					{
						$new = str_replace( '@Project NUKEVIET 3.x', '@Project NUKEVIET 4.x', $new );

						if( strpos( $new, '@Language' ) )
						{
							$new = str_replace( '* @Createdate', "* @License CC BY-SA (http://creativecommons.org/licenses/by-sa/4.0/)\n * @Createdate", $new );
						}
						else
						{
							$new = str_replace( '* @Createdate', "* @License GNU/GPL version 2 or any later version\n * @Createdate", $new );
						}

						$output_data = str_replace( $m[0], $new, $output_data );
						$output_data = str_replace( $m[0], $new, $output_data );
						$output_data = trim( $output_data );
						$output_data = rtrim( $output_data, '?>' );
						$output_data = trim( $output_data );

						if( $output_data != $contents_file )
						{
							file_put_contents( NV_ROOTDIR . '/' . $dirname . '/' . $file, trim( $output_data ), LOCK_EX );
							$contents .= $dirname . '/' . $file . "--------------change--------\n";
						}
						else
						{
							$contents .= $dirname . '/' . $file . "\n";
						}
					}
				}
			}
			elseif( preg_match( '/^([a-zA-Z0-9\-\_\/\.]+)\.tpl/', $file ) )
			{
				$contents_file = file_get_contents( NV_ROOTDIR . '/' . $dirname . '/' . $file );
				$output_data = trim( $contents_file );
				$output_data = str_replace( ' summary=""', '', $output_data );
				$output_data = str_replace( 'nv_open_browse_file', 'nv_open_browse', $output_data );

				$output_data = preg_replace( "/\<\!\-\- BEGIN\: ([a-zA-Z0-9\-\_\/]+) \-\-\>([\s\t\r\n]+)\<tbody([^\>]+)\>/", '<tbody>\\2<!-- BEGIN: \\1 -->', $output_data );
				$output_data = preg_replace( "/\<\/tbody\>([\s\t\r\n]+)<\!\-\- END\: ([a-zA-Z0-9\-\_\/]+) \-\-\>/", '<!-- END: \\2 -->\\1<tbody>', $output_data );
				$output_data = preg_replace( "/([\s\t\r\n]+)\<\/tbody\>([\s\t\r\n]+)\<tbody class\=\"second\"\>/", "", $output_data );
				$output_data = preg_replace( "/([\s\t\r\n]+)\<\/tbody\>([\s\t\r\n]+)\<tbody\>/", "", $output_data );
				$output_data = str_replace( '<tbody class="second">', '<tbody>', $output_data );

				$output_data = preg_replace( "/\<\/tr>([\s\t\r\n]+)\<tbody\>/", '</tr>', $output_data );
				$output_data = preg_replace( "/\<\/tbody>([\s\t\r\n]+)\<tr\>/", '<tr>', $output_data );

				$output_data = trim( preg_replace( '/\>\s*\<textarea\s*/', '><textarea ', $output_data ) );
				$output_data = str_replace( '<td></td>', '<td>&nbsp;</td>', $output_data );
				$check_table1 = false;
				if( preg_match_all( "/\n([\t]*)<table([^>]+)>(.*)<\/table>/is", $output_data, $m ) )
				{
					foreach( $m[2] as $key => $in_class )
					{
						if( strpos( $m[3][$key], '<table' ) )
						{
							continue;
						}

						$in_class = trim( $in_class );
						$in_class = str_replace( 'class="tab1 fixtab"', 'class="tab1"', $in_class );

						$tab_begin = $m[1][$key];
						if( $in_class == 'class="tab1"' )
						{
							$str_in_table = '';
							$arr_in_table = explode( "\n", $m[3][$key] );
							foreach( $arr_in_table as $value )
							{
								$str_in_table .= "\t" . $value . "\n";
							}
							if( preg_match_all( "/<thead>(.*)<\/thead>/is", $str_in_table, $m2 ) )
							{
								$in_thead = $m2[0][0];
								$in_thead = str_replace( '<td', '<th', $in_thead );
								$in_thead = str_replace( '</td>', '</th>', $in_thead );
								$str_in_table = str_replace( $m2[0][0], $in_thead, $str_in_table );
							}
							$str_in_table = trim( $str_in_table );
							$output_data = str_replace( $m[0][$key], "\n" . $tab_begin . "<div class=\"table-responsive\">\n" . $tab_begin . "\t<table class=\"table table-striped table-bordered table-hover\">\n" . $tab_begin . "\t\t" . $str_in_table . "\n" . $tab_begin . "\t</table>\n" . $tab_begin . "</div>", $output_data );
						}
					}

				}

				if( preg_match_all( "/<input([^>]+)>/", $output_data, $m ) )
				{
					foreach( $m[0] as $key => $value )
					{
						if( strpos( $value, 'type="text"' ) or strpos( $value, 'type="password"' ) )
						{
							if( !strpos( $value, 'form-control' ) )
							{
								if( preg_match( '/class="([^\"]+)"/', $m[1][$key], $m2 ) )
								{
									$value2 = str_replace( $m2[0], 'class="form-control ' . $m2[1] . '"', $value );
								}
								else
								{
									$value2 = '<input class="form-control"' . $m[1][$key] . '>';
								}
								$output_data = str_replace( $value, $value2, $output_data );
							}
						}
						elseif( strpos( $value, 'type="submit"' ) and strpos( $value, 'class="' ) === false )
						{
							$value2 = '<input class="btn btn-primary"' . $m[1][$key] . '>';
							$output_data = str_replace( $value, $value2, $output_data );
						}
					}
				}

				if( preg_match_all( "/<select([^>]+)>/", $output_data, $m ) )
				{
					foreach( $m[0] as $key => $value )
					{
						if( !strpos( $value, 'form-control' ) )
						{
							if( preg_match( '/class="([^\"]+)"/', $m[1][$key], $m2 ) )
							{
								$value2 = str_replace( $m2[0], 'class="form-control ' . $m2[1] . '"', $value );
							}
							else
							{
								$value2 = '<select class="form-control"' . $m[1][$key] . '>';
							}
							$output_data = str_replace( $value, $value2, $output_data );
						}
					}
				}

				if( preg_match_all( "/<form([^>]+)>/", $output_data, $m ) )
				{
					foreach( $m[0] as $key => $value )
					{
						if( !strpos( $value, 'form-inline' ) )
						{
							if( preg_match( '/class="([^\"]+)"/', $m[1][$key], $m2 ) )
							{
								$value2 = str_replace( $m2[0], 'class="form-inline ' . $m2[1] . '"', $value );
							}
							else
							{
								$value2 = '<form class="form-inline"' . $m[1][$key] . '>';
							}
							$output_data = str_replace( $value, $value2, $output_data );
						}
					}
				}

				if( preg_match_all( "/<div class=\"quote\">[\n\s\t]*<blockquote class=\"([^\"]+)\">[\n\s\t]*<span>[\n\s\t]*([^\<]+)[\n\s\t]*<\/span>[\n\s\t]*<\/blockquote>[\n\s\t]*<\/div>/", $output_data, $m ) )
				{
					foreach( $m[0] as $key => $value )
					{
						$alert_warning = '';
						if( $m[1][$key] == 'error' )
						{
							$alert_warning = 'alert-warning';
						}
						if( !empty( $alert_warning ) )
						{
							$output_data = str_replace( $value, '<div class="alert ' . $alert_warning . '">' . $m[2][$key] . '</div>', $output_data );
						}
					}
				}

				if( preg_match_all( "/<div class=\"quote\">[\n\s\t]*<blockquote>[\n\s\t]*<span>[\n\s\t]*([^\<]+)[\n\s\t]*<\/span>[\n\s\t]*<\/blockquote>[\n\s\t]*<\/div>/", $output_data, $m ) )
				{
					foreach( $m[0] as $key => $value )
					{
						$output_data = str_replace( $value, '<div class="alert alert-warning">' . $m[1][$key] . '</div>', $output_data );
					}
				}
				$output_data = str_replace( '<em class="icon-plus icon-large">&nbsp;</em>', '<i class="fa fa-plus fa-lg">&nbsp;</i>', $output_data );
				$output_data = str_replace( '<em class="icon-plus icon-large"></em>', '<i class="fa fa-plus fa-lg">&nbsp;</i>', $output_data );

				$output_data = str_replace( '<em class="icon-edit icon-large">&nbsp;</em>', '<i class="fa fa-edit fa-lg">&nbsp;</i>', $output_data );
				$output_data = str_replace( '<em class="icon-edit icon-large"></em>', '<i class="fa fa-edit fa-lg">&nbsp;</i>', $output_data );

				$output_data = str_replace( '<em class="icon-trash icon-large">&nbsp;</em>', '<i class="fa fa-trash-o fa-lg">&nbsp;</i>', $output_data );
				$output_data = str_replace( '<em class="icon-trash icon-large"></em>', '<i class="fa fa-trash-o fa-lg">&nbsp;</i>', $output_data );

				$output_data = str_replace( ' class="tab1"', ' class="table table-striped table-bordered table-hover"', $output_data );
				$output_data = str_replace( ' class="center"', ' class="text-center"', $output_data );
				$output_data = str_replace( ' align="center"', ' class="text-center"', $output_data );
				$output_data = str_replace( '<th class="w50">', '<th class="w100">', $output_data );
				$output_data = str_replace( '<th style="width:40px;">', '<th class="w100">', $output_data );
				$output_data = str_replace( '<th style="width:50px;">', '<th class="w100">', $output_data );
				$output_data = str_replace( '<th style="width:100px;">', '<th class="w100">', $output_data );
				$output_data = str_replace( 'class="form-control txt-full"', 'class="form-control"', $output_data );

				$output_data = trim( $output_data );

				if( $output_data != $contents_file )
				{
					file_put_contents( NV_ROOTDIR . '/' . $dirname . '/' . $file, $output_data, LOCK_EX );
					$contents .= $dirname . '/' . $file . "--------------change--------\n";
				}
				else
				{
					$contents .= $dirname . '/' . $file . "\n";
				}
			}
			elseif( preg_match( "/^([a-zA-Z0-9\-\_\/]+)$/", $file ) and is_dir( NV_ROOTDIR . '/' . $dirname . '/' . $file ) )
			{
				nv_fomat_dir( $dirname . '/' . $file, $contents );
			}
		}
	}

	return $contents;
}

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme( $contents );
include NV_ROOTDIR . '/includes/footer.php';