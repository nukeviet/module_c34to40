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

$page_title = $module_info['custom_title'];
$key_words = $module_info['keywords'];

$contents = '';
if( is_dir( NV_ROOTDIR . '/tmp/module-nukeviet3.4' ) )
{
	$contents = nv_fomat_dir( 'tmp/module-nukeviet3.4', $contents );
	$contents = nv_theme_c34to40_main( $contents );
}
else
{
	$contents = 'Bạn cần copy module nukeviet3.4 đã giải nén vào thư mục tmp/module-nukeviet3.4, sau đó chạy lại module này ';
}

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

				$output_data = str_replace( 'include ( NV_ROOTDIR . "/includes/header.php" );', 'include NV_ROOTDIR . \'/includes/header.php\';', $contents_file );
				$output_data = str_replace( 'include ( NV_ROOTDIR . "/includes/footer.php" );', 'include NV_ROOTDIR . \'/includes/footer.php\';', $output_data );

				$output_data = str_replace( 'include NV_ROOTDIR . "/includes/header.php";', 'include NV_ROOTDIR . \'/includes/header.php\';', $contents_file );
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

				$output_data = preg_replace( '/\$([a-zA-Z0-9\_]+) \= \$db\-\>sql\_fetchrow\( \$([a-zA-Z0-9\_]+) \)/', '$\\1 = $\\2->fetch()', $output_data );

				// Thay thế sql_fetchrow(query)
				$output_data = preg_replace( '/\$([a-zA-Z0-9\_]+) \= \$db\-\>sql\_fetch\_assoc\( \$([a-zA-Z0-9\_]+) \)/', '$\\1 = $\\2->fetch()', $output_data );
				$output_data = preg_replace( '/\$db\-\>sql\_fetch\_assoc\( \$db\-\>query\(([^\;]+)\) \)\;/', '$db->query(\\1)->fetch();', $output_data );
				$output_data = preg_replace( '/\$db\-\>sql\_fetchrow\( \$db\-\>query\(([^\;]+)\)\, 2 \)\;/', '$db->query(\\1)->fetch();', $output_data );

				$output_data = preg_replace( '/list\(([^\)]+)\) \= \$db\-\>sql\_fetchrow\( \$([a-zA-Z0-9\_]+) \)/', 'list(\\1) = $\\2->fetch( 3 )', $output_data );
				$output_data = preg_replace( '/list\( \$([a-zA-Z0-9\_\[\]\']+) \) \= \$db\-\>sql\_fetchrow\( \$db\-\>query\(([^\;]+)\) \)\;/', '$\\1 = $db->query(\\2)->fetchColumn();', $output_data );
				$output_data = preg_replace( '/list\( \$([a-zA-Z0-9\_\[\]\']+) \) \= \$db\-\>sql\_fetchrow\( \$db\-\>query\(([^\;]+)\)\, 1 \)\;/', '$\\1 = $db->query(\\2)->fetchColumn();', $output_data );
				$output_data = preg_replace( '/list\(([a-zA-Z0-9\_\$\s\,\[\]\']+)\) \= \$db\-\>sql\_fetchrow\( \$db\-\>query\(([^\;]+)\) \)\;/', 'list(\\1) = $db->query(\\2)->fetch( 3 );', $output_data );

				$output_data = preg_replace( '/\$db\-\>sql\_fetchrow\( \$([a-zA-Z0-9\_]+)\, 1 \)/', '$\\1->fetch( 3 )', $output_data );
				$output_data = preg_replace( '/\$db\-\>sql\_fetchrow\( \$([a-zA-Z0-9\_]+)\, 2 \)/', '$\\1->fetch()', $output_data );

				//sql_freeresult
				$output_data = preg_replace( '/\$db\-\>sql\_freeresult\( \$([a-zA-Z0-9\_]+) \)/', '$\\1->closeCursor()', $output_data );

				$output_data = preg_replace( '/\$([a-zA-Z0-9\_]+) \= \$db\-\>sql\_fetchrow\( \$db\-\>query\(([^\;]+)\) \)\;/', '$\\1 = $db->query(\\2)->fetch();', $output_data );

				$output_data = preg_replace( '/\$db\-\>sql\_numrows\( \$([a-zA-Z0-9\_]+)\ \)/', '$\\1->rowCount()', $output_data );
				$output_data = preg_replace( '/\$db\-\>sql\_numrows\( \$db\-\>query\(([^\;|\n]+)\) \)\;/', '$db->query(\\1)->rowCount();', $output_data );
				$output_data = preg_replace( '/\$db\-\>sql\_numrows\( \$db\-\>query\(([^\n]+)\) \) /', '$db->query(\\1)->rowCount() ', $output_data );

				$output_data = preg_replace( '/list\( \$([a-zA-Z0-9_]+) \) = \$([a-zA-Z0-9_]+)\-\>fetch\( 3 \)\;/', '$\\1 = $\\2->fetchColumn();', $output_data );

				if( preg_match_all( "/filter\_text\_input\(([^\n]+)\)\;/", $output_data ) AND preg_match_all( "/filter\_text\_input\(([^\;|^\n]+)\)\;/", $output_data, $m ) )
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
							die( $string );
						}
						$string_new = str_replace( "_get_post_", "get,post", $string_new );
						$string_new = str_replace( "_post_get_", "get,post", $string_new );
						$output_data = str_replace( $m[0][$key], $string_new, $output_data );
					}
				}

				if( preg_match_all( "/filter\_text\_input\(([^\(]+)\)\,/", $output_data ) AND preg_match_all( "/filter\_text\_input\(([^\)]+)\)\,/", $output_data ) AND preg_match_all( "/filter\_text\_input\(([^\n]+)\)\,/", $output_data, $m ) )
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
							die( '---------' . $string );
						}
						$string_new = str_replace( "_get_post_", "get,post", $string_new );
						$string_new = str_replace( "_post_get_", "get,post", $string_new );
						$output_data = str_replace( $m[0][$key], $string_new, $output_data );
					}
				}

				if( preg_match_all( "/filter\_text\_input\(([^\(]+)\)\s\=\=\s/", $output_data ) AND preg_match_all( "/filter\_text\_input\(([^\)]+)\)\s\=\=\s/", $output_data ) AND preg_match_all( "/filter\_text\_input\(([^\=|^\n]+)\)\s\=\=\s/", $output_data, $m ) )
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
							die( '----yyyyyyyy-----' . $string );
						}
						$string_new = str_replace( "_get_post_", "get,post", $string_new );
						$string_new = str_replace( "_post_get_", "get,post", $string_new );
						$output_data = str_replace( $m[0][$key], $string_new, $output_data );
					}
				}
				//filter_text_textarea
				if( preg_match_all( "/filter\_text\_textarea\(([^\n]+)\)\;/", $output_data, $m0 ) AND preg_match_all( "/filter\_text\_textarea\(([^\;]+)\)\;/", $output_data, $m ) )
				{
					foreach( $m[1] as $key => $string )
					{
						$value_arr = explode( ',', $string );
						$value_arr = array_map( 'trim', $value_arr );
						if( sizeof( $value_arr ) > 4 )
						{
							print_r( $m );
							die( $string );
						}
						$string_new = '$nv_Request->get_textarea( ' . implode( ', ', $value_arr ) . ' );';
						$output_data = str_replace( $m[0][$key], $string_new, $output_data );
					}
				}

				//nv_editor_filter_textarea
				if( preg_match_all( "/nv\_editor\_filter\_textarea\(([^\n]+)\)\;/", $output_data, $m0 ) AND preg_match_all( "/nv\_editor\_filter\_textarea\(([^\;]+)\)\;/", $output_data, $m ) )
				{
					foreach( $m[1] as $key => $string )
					{
						$value_arr = explode( ',', $string );
						$value_arr = array_map( 'trim', $value_arr );
						if( sizeof( $value_arr ) > 4 )
						{
							print_r( $m );
							die( $string );
						}
						$string_new = '$nv_Request->get_editor( ' . implode( ', ', $value_arr ) . ' );';
						$output_data = str_replace( $m[0][$key], $string_new, $output_data );
					}
				}

				if( strpos( $output_data, 'filter_text_input' ) )
				{
					die( $dirname . '/' . $file . '--------------ERROR: filter_text_input--------<br><br>' . $output_data );
				}

				if( strpos( $output_data, 'filter_text_textarea' ) )
				{
					die( $dirname . '/' . $file . '--------------ERROR: filter_text_textarea--------<br><br>' . $output_data );
				}

				if( strpos( $output_data, 'nv_editor_filter_textarea' ) )
				{
					die( $dirname . '/' . $file . '--------------ERROR: nv_editor_filter_textarea--------<br><br>' . $output_data );
				}

				$output_data = str_replace( '$db->sql_freeresult();', '//$xxx->closeCursor();', trim( $output_data ) );

				// Loại bỏ khoảng trắng ()
				$output_data = preg_replace( '/\([\s]+\)/', '()', $output_data );
				$output_data = preg_replace( "/[ ]+/", " ", $output_data );

				if( $output_data != $contents_file )
				{
					file_put_contents( NV_ROOTDIR . '/' . $dirname . '/' . $file, trim( $output_data ), LOCK_EX );
					$contents .= $dirname . '/' . $file . '--------------change--------<br>';
				}
				else
				{
					$contents .= $dirname . '/' . $file . '<br>';
				}

				if( strpos( $output_data, '//$xxx->closeCursor()' ) )
				{
					$contents .= '--------------//$xxx->closeCursor()--------<br>';
				}
				if( strpos( $output_data, '$db->sql_affectedrows()' ) )
				{
					$contents .= '--------------$db->sql_affectedrows()--------<br>';
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
?>