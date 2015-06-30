<?php
/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Thu, 09 Jan 2014 10:18:48 GMT
 */
if( !defined( 'NV_IS_MOD_C34TO40' ) )
	die( 'Stop!!!' );

include_once NV_ROOTDIR . '/includes/class/image.class.php';
$typeflag = array( );
$typeflag[1] = 'gif';
$typeflag[2] = 'jpg';
$typeflag[3] = 'png';
$typeflag[4] = 'swf';
$typeflag[5] = 'psd';
$typeflag[6] = 'bmp';
$typeflag[7] = 'tiff';
$typeflag[8] = 'tiff';
$typeflag[9] = 'jpc';
$typeflag[10] = 'jp2';
$typeflag[11] = 'jpf';
$typeflag[12] = 'jb2';
$typeflag[13] = 'swc';
$typeflag[14] = 'aiff';
$typeflag[15] = 'wbmp';
$typeflag[16] = 'xbm';

function nv_get_viewImage( $fileName )
{
	global $array_thumb_config;
	if( preg_match( '/^' . nv_preg_quote( NV_UPLOADS_DIR ) . '\/(([a-z0-9\-\_\/]+\/)*([a-z0-9\-\_\.]+)(\.(gif|jpg|jpeg|png)))$/i', $fileName, $m ) )
	{
		$viewFile = NV_FILES_DIR . '/' . $m[1];
		if( file_exists( NV_ROOTDIR . '/' . $viewFile ) )
		{
			$size = @getimagesize( NV_ROOTDIR . '/' . $viewFile );
			return array(
				$viewFile,
				$size[0],
				$size[1]
			);
		}
		else
		{
			$m[2] = rtrim( $m[2], '/' );
			if( isset( $array_thumb_config[NV_UPLOADS_DIR . '/' . $m[2]] ) )
			{
				$thumb_config = $array_thumb_config[NV_UPLOADS_DIR . '/' . $m[2]];
			}
			else
			{
				$thumb_config = $array_thumb_config[''];
				$_arr_path = explode( '/', NV_UPLOADS_DIR . '/' . $m[2] );
				while( sizeof( $_arr_path ) > 1 )
				{
					array_pop( $_arr_path );
					$_path = implode( '/', $_arr_path );
					if( isset( $array_thumb_config[$_path] ) )
					{
						$thumb_config = $array_thumb_config[$_path];
						break;
					}
				}
			}
			$viewDir = NV_FILES_DIR;
			if( !empty( $m[2] ) )
			{
				if( !is_dir( NV_ROOTDIR . '/' . $m[2] ) )
				{
					$e = explode( '/', $m[2] );
					$cp = NV_FILES_DIR;
					foreach( $e as $p )
					{
						if( is_dir( NV_ROOTDIR . '/' . $cp . '/' . $p ) )
						{
							$viewDir .= '/' . $p;
						}
						else
						{
							$mk = nv_mkdir( NV_ROOTDIR . '/' . $cp, $p );
							if( $mk[0] > 0 )
							{
								$viewDir .= '/' . $p;
							}
						}
						$cp .= '/' . $p;
					}
				}
			}
			$image = new image( NV_ROOTDIR . '/' . $fileName, NV_MAX_WIDTH, NV_MAX_HEIGHT );
			if( $thumb_config['thumb_type'] == 4 )
			{
				$thumb_width = $thumb_config['thumb_width'];
				$thumb_height = $thumb_config['thumb_height'];
				$maxwh = max( $thumb_width, $thumb_height );
				if( $image->fileinfo['width'] > $image->fileinfo['height'] )
				{
					$thumb_config['thumb_width'] = 0;
					$thumb_config['thumb_height'] = $maxwh;
				}
				else
				{
					$thumb_config['thumb_width'] = $maxwh;
					$thumb_config['thumb_height'] = 0;
				}
			}
			$image->resizeXY( $thumb_config['thumb_width'], $thumb_config['thumb_height'] );
			if( $thumb_config['thumb_type'] == 4 )
			{
				$image->cropFromCenter( $thumb_width, $thumb_height );
			}
			$image->save( NV_ROOTDIR . '/' . $viewDir, $m[3] . $m[4], $thumb_config['thumb_quality'] );
			$create_Image_info = $image->create_Image_info;
			$error = $image->error;
			$image->close( );
			if( empty( $error ) )
			{
				return array(
					$viewDir . '/' . basename( $create_Image_info['src'] ),
					$create_Image_info['width'],
					$create_Image_info['height']
				);
			}
		}
	}
	else
	{
		$size = @getimagesize( NV_ROOTDIR . '/' . $fileName );
		return array(
			$viewFile,
			$size[0],
			$size[1]
		);
	}
	return false;
}

if( $nv_Request->isset_request( 'mod_name', 'post' ) )
{
	$mod_name = $nv_Request->get_string( 'mod_name', 'post' );
	if( isset( $site_mods[$mod_name] ) )
	{
		$mod_name = $nv_Request->get_string( 'mod_name', 'post' );
		$mod_data3 = $nv_Request->get_string( 'nv3_news', 'post' );

		$mod_data = $site_mods[$mod_name]['module_data'];
		define( 'NV_PREFIXLANG3', 'nv3_' . NV_LANG_DATA );

		// Xóa dữ liệu module hiện tại
		try
		{
			$_query = $db->query( "SELECT `catid` FROM `" . NV_PREFIXLANG . "_" . $mod_data . "_cat` WHERE 1" );
			while( $row = $_query->fetch( ) )
			{
				$db->query( "DROP TABLE IF EXISTS `" . NV_PREFIXLANG . "_" . $mod_data . "_" . $row['catid'] . "`" );
			}
			$result = $db->query( 'SHOW TABLE STATUS LIKE ' . $db->quote( $db_config['prefix'] . '\_' . NV_LANG_DATA . '\_' . $mod_data . '\_bodyhtml\_%' ) );
			while( $item = $result->fetch( ) )
			{
				$db->query( 'DROP TABLE IF EXISTS ' . $item['name'] );
			}
			$result = $db->query( 'SHOW TABLE STATUS LIKE ' . $db->quote( NV_PREFIXLANG . '\_' . $mod_data . '\_%' ) );
			while( $item = $result->fetch( ) )
			{
				$db->query( 'TRUNCATE ' . $item['name'] );
			}
		}
		catch( PDOException $e )
		{
			die( $e->getMessage( ) );
		}
		// Copy dữ liệu chủ đề
		require_once NV_ROOTDIR . '/includes/action_' . $db->dbtype . '.php';
		$error = 'OK';
		try
		{
			$db->query( "INSERT " . NV_PREFIXLANG . "_" . $mod_data . "_cat (`catid`, `parentid`, `title`, `titlesite`, `alias`, `description`, `image`, `weight`, `sort`, `lev`, `viewcat`, `numsubcat`, `subcatid`, `inhome`, `numlinks`, `keywords`, `admins`, `add_time`, `edit_time`, `groups_view`) SELECT `catid`, `parentid`, `title`, `titlesite`, `alias`, `description`, `image`, `weight`, `order`, `lev`, `viewcat`, `numsubcat`, `subcatid`, `inhome`, `numlinks`, `keywords`, `admins`, `add_time`, `edit_time`, `groups_view` FROM " . NV_PREFIXLANG3 . "_" . $mod_data3 . "_cat" );
			$db->query( "INSERT " . NV_PREFIXLANG . "_" . $mod_data . "_admins ( `userid`, `catid`, `admin`, `add_content`, `pub_content`, `edit_content`, `del_content`) SELECT  `userid`, `catid`, `admin`, `add_content`, `pub_content`, `edit_content`, `del_content` FROM " . NV_PREFIXLANG3 . "_" . $mod_data3 . "_admins" );
			// Fetch Assoc
			$_sql = 'SELECT * FROM ' . NV_PREFIXLANG3 . '_' . $mod_data3 . '_cat';
			$_query = $db->query( $_sql );
			while( $row = $_query->fetch( ) )
			{
				if( $row['who_view'] == 1 )
				{
					$groups_view = '2';
				}
				if( $row['who_view'] == 2 )
				{
					$groups_view = '3';
				}
				else
				{
					$groups_view = '6';
				}
				$db->query( "UPDATE " . NV_PREFIXLANG . "_" . $mod_data . "_cat SET `viewdescription` = 0, `newday` = 3, `groups_view` = " . $groups_view . " WHERE catid=" . $row['catid'] );
				nv_copy_structure_table( NV_PREFIXLANG . '_' . $mod_data . '_' . $row['catid'], NV_PREFIXLANG . '_' . $mod_data . '_rows' );
			}
		}
		catch( PDOException $e )
		{
			die( $e->getMessage( ) );
		}
		try
		{
			$array_thumb_config = array( );
			$array_thumb_config[''] = array(
				'did' => 0,
				'dirname' => '',
				'time' => 0,
				'thumb_type' => 3,
				'thumb_width' => 100,
				'thumb_height' => 150,
				'thumb_quality' => 90,
			);
			$aray_table_bodyhtml = array( );
			$_query = $db->query( 'SELECT * FROM ' . NV_PREFIXLANG3 . '_' . $mod_data3 . '_rows' );
			while( $item = $_query->fetch( ) )
			{
				$array_img = (!empty( $item['homeimgthumb'] )) ? explode( "|", $item['homeimgthumb'] ) : $array_img = array(
					"",
					""
				);
				$homeimgthumb = 0;
				if( $item['homeimgfile'] != "" and file_exists( NV_UPLOADS_REAL_DIR . '/' . $mod_name . '/' . $item['homeimgfile'] ) and $array_img[0] != "" and file_exists( NV_ROOTDIR . '/' . NV_FILES_DIR . '/' . $mod_name . '/' . $array_img[0] ) )
				{
					$path = dirname( $item['homeimgfile'] );
					if( !empty( $path ) )
					{
						if( !is_dir( NV_ROOTDIR . '/' . NV_FILES_DIR . '/' . $mod_name . '/' . $path ) )
						{
							$e = explode( "/", $path );
							$cp = NV_FILES_DIR . '/' . $mod_name;
							foreach( $e as $p )
							{
								if( is_dir( NV_ROOTDIR . '/' . $cp . '/' . $p ) )
								{
									$viewDir .= '/' . $p;
								}
								else
								{
									$mk = nv_mkdir( NV_ROOTDIR . '/' . $cp, $p );
									if( $mk[0] > 0 )
									{
										$viewDir .= '/' . $p;
									}
								}
								$cp .= '/' . $p;
							}
						}
					}
					if( @rename( NV_ROOTDIR . '/' . NV_FILES_DIR . '/' . $mod_name . '/' . $array_img[0], NV_ROOTDIR . '/' . NV_FILES_DIR . '/' . $mod_name . '/' . $item['homeimgfile'] ) )
					{
						$homeimgthumb = 1;
					}
					else
					{
						$homeimgthumb = 2;
					}
				}
				elseif( nv_is_url( $item['homeimgfile'] ) )
				{
					$homeimgthumb = 3;
				}
				elseif( $item['homeimgfile'] != '' and file_exists( NV_UPLOADS_REAL_DIR . '/' . $mod_name . '/' . $item['homeimgfile'] ) )
				{
					$homeimgthumb = 2;
					$imagesize = getimagesize( NV_UPLOADS_REAL_DIR . '/' . $mod_name . '/' . $item['homeimgfile'] );
					if( !empty( $imagesize ) )
					{
						$ext1 = strtolower( array_pop( explode( '.', $item['homeimgfile'] ) ) );
						$ext2 = $typeflag[$imagesize[2]];
						if( $ext1 != $ext2 )
						{
							$homeimgfile = preg_replace( "/." . $ext1 . "$/", '.' . $ext2, $item['homeimgfile'] );
							rename( NV_UPLOADS_REAL_DIR . '/' . $mod_name . '/' . $item['homeimgfile'], NV_UPLOADS_REAL_DIR . '/' . $mod_name . '/' . $homeimgfile );
							$item['homeimgfile'] = $homeimgfile;
						}
					}
					$arrimg = nv_get_viewImage( NV_UPLOADS_DIR . '/' . $mod_name . '/' . $item['homeimgfile'] );
					if( !empty( $arrimg ) )
					{
						$homeimgthumb = 1;
					}
				}
				else
				{
					$item['homeimgfile'] = '';
				}
				$item['homeimgthumb'] = $homeimgthumb;
				$stmt = $db->prepare( "INSERT INTO " . NV_PREFIXLANG . "_" . $mod_data . "_rows
				(id, catid, listcatid, topicid, admin_id, author, sourceid, addtime, edittime, status, publtime, exptime, archive, title, alias, hometext, homeimgfile, homeimgalt, homeimgthumb, inhome, allowed_comm, allowed_rating, hitstotal, hitscm, total_rating, click_rating) VALUES
				(:id,:catid,:listcatid,:topicid,:admin_id,:author,:sourceid,:addtime,:edittime,:status,:publtime,:exptime,:archive,:title,:alias,:hometext,:homeimgfile,:homeimgalt,:homeimgthumb,:inhome,:allowed_comm,:allowed_rating,:hitstotal,:hitscm,:total_rating,:click_rating)" );
				$stmt->bindParam( ':id', $item['id'], PDO::PARAM_INT );
				$stmt->bindParam( ':catid', $item['catid'], PDO::PARAM_INT );
				$stmt->bindParam( ':listcatid', $item['listcatid'], PDO::PARAM_STR );
				$stmt->bindParam( ':topicid', $item['topicid'], PDO::PARAM_INT );
				$stmt->bindParam( ':admin_id', $item['admin_id'], PDO::PARAM_INT );
				$stmt->bindParam( ':author', $item['author'], PDO::PARAM_STR );
				$stmt->bindParam( ':sourceid', $item['sourceid'], PDO::PARAM_INT );
				$stmt->bindParam( ':addtime', $item['addtime'], PDO::PARAM_INT );
				$stmt->bindParam( ':edittime', $item['edittime'], PDO::PARAM_INT );
				$stmt->bindParam( ':status', $item['status'], PDO::PARAM_INT );
				$stmt->bindParam( ':publtime', $item['publtime'], PDO::PARAM_INT );
				$stmt->bindParam( ':exptime', $item['exptime'], PDO::PARAM_INT );
				$stmt->bindParam( ':archive', $item['archive'], PDO::PARAM_INT );
				$stmt->bindParam( ':title', $item['title'], PDO::PARAM_STR );
				$stmt->bindParam( ':alias', $item['alias'], PDO::PARAM_STR );
				$stmt->bindParam( ':hometext', $item['hometext'], PDO::PARAM_STR, strlen( $item['hometext'] ) );
				$stmt->bindParam( ':homeimgfile', $item['homeimgfile'], PDO::PARAM_STR );
				$stmt->bindParam( ':homeimgalt', $item['homeimgalt'], PDO::PARAM_STR );
				$stmt->bindParam( ':homeimgthumb', $item['homeimgthumb'], PDO::PARAM_INT );
				$stmt->bindParam( ':inhome', $item['inhome'], PDO::PARAM_INT );
				$stmt->bindParam( ':allowed_comm', $item['allowed_comm'], PDO::PARAM_STR );
				$stmt->bindParam( ':allowed_rating', $item['allowed_rating'], PDO::PARAM_INT );
				$stmt->bindParam( ':hitstotal', $item['hitstotal'], PDO::PARAM_INT );
				$stmt->bindParam( ':hitscm', $item['hitscm'], PDO::PARAM_INT );
				$stmt->bindParam( ':total_rating', $item['total_rating'], PDO::PARAM_INT );
				$stmt->bindParam( ':click_rating', $item['click_rating'], PDO::PARAM_INT );
				$ec = $stmt->execute( );
				if( $ec )
				{
					$tbhtml = ceil( $item['id'] / 2000 );
					if( !in_array( $tbhtml, $aray_table_bodyhtml ) )
					{
						$aray_table_bodyhtml[] = $tbhtml;
						$db->query( "CREATE TABLE IF NOT EXISTS " . NV_PREFIXLANG . "_" . $mod_data . "_bodyhtml_" . $tbhtml . " (id int(11) unsigned NOT NULL, bodyhtml longtext NOT NULL, sourcetext varchar(255) NOT NULL default '', imgposition tinyint(1) NOT NULL default '1', copyright tinyint(1) NOT NULL default '0', allowed_send tinyint(1) NOT NULL default '0', allowed_print tinyint(1) NOT NULL default '0', allowed_save tinyint(1) NOT NULL default '0', gid mediumint(9) NOT NULL DEFAULT '0', PRIMARY KEY (id)) ENGINE=MyISAM" );
						$db->query( "INSERT " . NV_PREFIXLANG . "_" . $mod_data . "_bodyhtml_" . $tbhtml . " (`id`, `bodyhtml`, `sourcetext`, `imgposition`, `copyright`, `allowed_send`, `allowed_print`, `allowed_save`) SELECT `id`, `bodyhtml`, `sourcetext`, `imgposition`, `copyright`, `allowed_send`, `allowed_print`, `allowed_save` FROM " . NV_PREFIXLANG3 . "_" . $mod_data3 . "_bodyhtml_" . $tbhtml );
					}
					$catids = explode( ',', $item['listcatid'] );
					foreach( $catids as $catid )
					{
						$db->exec( 'INSERT INTO ' . NV_PREFIXLANG . '_' . $mod_data . '_' . $catid . ' SELECT * FROM ' . NV_PREFIXLANG . '_' . $mod_data . '_rows WHERE id=' . $item['id'] );
					}
					$keywords = explode( ',', nv_strtolower( $item['keywords'] ) );
					$keywords = array_map( 'strip_punctuation', $keywords );
					$keywords = array_map( 'trim', $keywords );
					$keywords = array_diff( $keywords, array( '' ) );
					$keywords = array_unique( $keywords );
					foreach( $keywords as $keyword )
					{
						if( !empty( $keyword ) )
						{
							$alias_i = ($module_config[$mod_name]['tags_alias']) ? change_alias( $keyword ) : str_replace( ' ', '-', $keyword );
							$sth = $db->prepare( 'SELECT tid, alias, description, keywords FROM ' . NV_PREFIXLANG . '_' . $mod_data . '_tags where alias= :alias OR FIND_IN_SET(:keyword, keywords)>0' );
							$sth->bindParam( ':alias', $alias_i, PDO::PARAM_STR );
							$sth->bindParam( ':keyword', $keyword, PDO::PARAM_STR );
							$sth->execute( );
							list( $tid, $alias, $keywords_i ) = $sth->fetch( 3 );
							if( empty( $tid ) )
							{
								$array_insert = array( );
								$array_insert['alias'] = $alias_i;
								$array_insert['keyword'] = $keyword;
								$tid = $db->insert_id( "INSERT INTO " . NV_PREFIXLANG . "_" . $mod_data . "_tags (numnews, alias, description, image, keywords) VALUES (1, :alias, '', '', :keyword)", "tid", $array_insert );
							}
							else
							{
								if( $alias != $alias_i )
								{
									if( !empty( $keywords_i ) )
									{
										$keyword_arr = explode( ',', $keywords_i );
										$keyword_arr[] = $keyword;
										$keywords_i2 = implode( ',', array_unique( $keyword_arr ) );
									}
									else
									{
										$keywords_i2 = $keyword;
									}
									if( $keywords_i != $keywords_i2 )
									{
										$sth = $db->prepare( 'UPDATE ' . NV_PREFIXLANG . '_' . $mod_data . '_tags SET keywords= :keywords WHERE tid =' . $tid );
										$sth->bindParam( ':keywords', $keywords_i2, PDO::PARAM_STR );
										$sth->execute( );
									}
								}
								$db->query( 'UPDATE ' . NV_PREFIXLANG . '_' . $mod_data . '_tags SET numnews = numnews+1 WHERE tid = ' . $tid );
							}
							$sql = 'INSERT INTO ' . NV_PREFIXLANG . '_' . $mod_data . '_tags_id (id, tid, keyword) VALUES (' . $item['id'] . ', ' . $tid . ', :keyword)';
							$data_insert = array( );
							$data_insert['keyword'] = $keyword;
							$db->insert_id( $sql, 'id', $data_insert );
						}
					}
				}
			}
		}
		catch( PDOException $e )
		{
			die( $e->getMessage( ) );
		}
		$db->query( "INSERT " . NV_PREFIXLANG . "_" . $mod_data . "_bodytext (`id`, `bodytext`) SELECT `id`, `bodytext` FROM " . NV_PREFIXLANG3 . "_" . $mod_data3 . "_bodytext" );
		// bảng nv3_vi_news_sources
		$db->query( "TRUNCATE " . NV_PREFIXLANG . "_" . $mod_data . "_sources" );
		$_sql = 'SELECT sourceid, title, link,logo,weight,add_time, edit_time FROM ' . NV_PREFIXLANG3 . '_' . $mod_data3 . '_sources';
		$_query = $db->query( $_sql );
		while( $row = $_query->fetch( ) )
		{
			$sql = "INSERT INTO " . NV_PREFIXLANG . "_" . $mod_data . "_sources( sourceid,title, link, logo, weight, add_time, edit_time)
				VALUES (:sourceid,:title,:link,:logo,:weight,:add_time,:edit_time)";
			$data_insert = array( );
			$data_insert['sourceid'] = $row['sourceid'];
			$data_insert['title'] = $row['title'];
			$data_insert['link'] = $row['link'];
			$data_insert['logo'] = $row['logo'];
			$data_insert['weight'] = $row['weight'];
			$data_insert['add_time'] = $row['add_time'];
			$data_insert['edit_time'] = $row['edit_time'];
			$sourceid = intval( $db->insert_id( $sql, 'sourceid', $data_insert ) );
		}
		// bảng nv3_vi_news_block
		$db->query( "TRUNCATE " . NV_PREFIXLANG . "_" . $mod_data . "_block" );
		$_sql = 'SELECT bid, id, weight FROM ' . NV_PREFIXLANG3 . '_' . $mod_data3 . '_block';
		$_query = $db->query( $_sql );
		while( $row = $_query->fetch( ) )
		{
			$sql = "INSERT INTO " . NV_PREFIXLANG . "_" . $mod_data . "_block(bid, id, weight) VALUES (:bid,:id,:weight)";
			$data_insert = array( );
			$data_insert['bid'] = $row['bid'];
			$data_insert['id'] = $row['id'];
			$data_insert['weight'] = $row['weight'];
			$bid = intval( $db->insert_id( $sql, 'bid', $data_insert ) );
		}
		// bảng nv3_vi_news_block_cat
		$db->query( "TRUNCATE " . NV_PREFIXLANG . "_" . $mod_data . "_block_cat" );
		$_sql = 'SELECT bid, adddefault, number, title, alias, image, thumbnail, description, weight, keywords, add_time, edit_time FROM ' . NV_PREFIXLANG3 . '_' . $mod_data3 . '_block_cat';
		$_query = $db->query( $_sql );
		while( $row = $_query->fetch( ) )
		{
			$sql = "INSERT INTO " . NV_PREFIXLANG . "_" . $mod_data . "_block_cat(bid, adddefault, numbers, title, alias, image, description, weight, keywords, add_time, edit_time)
				VALUES (:bid,:adddefault,:numbers,:title,:alias,:image,:description,:weight,:keywords,:add_time,:edit_time)";
			$data_insert = array( );
			$data_insert['bid'] = $row['bid'];
			$data_insert['adddefault'] = $row['adddefault'];
			$data_insert['numbers'] = $row['number'];
			$data_insert['title'] = $row['title'];
			$data_insert['alias'] = $row['alias'];
			$data_insert['image'] = $row['image'];
			$data_insert['description'] = $row['description'];
			$data_insert['weight'] = $row['weight'];
			$data_insert['keywords'] = $row['keywords'];
			$data_insert['add_time'] = $row['add_time'];
			$data_insert['edit_time'] = $row['edit_time'];
			$bid = intval( $db->insert_id( $sql, 'bid', $data_insert ) );
		}
		nv_del_moduleCache( $mod_name );
		nv_insert_logs( NV_LANG_DATA, $mod_name, 'Convert', '', $admin_info['userid'] );
		Header( 'Location: ' . nv_url_rewrite( NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $mod_name, true ) );
		die( );
	}
}
else
{
	$result = $db->query( 'SELECT title, module_data, custom_title FROM ' . NV3_PREFIX . '_' . NV_LANG_DATA . '_modules WHERE module_file="news"' );
	$array_nv3_news = $result->fetchAll( );

	$xtpl = new XTemplate( $op . '.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file );
	$xtpl->assign( 'LANG', $lang_module );
	$xtpl->assign( 'NV_BASE_SITEURL', NV_BASE_SITEURL );
	$xtpl->assign( 'NV_NAME_VARIABLE', NV_NAME_VARIABLE );
	$xtpl->assign( 'NV_OP_VARIABLE', NV_OP_VARIABLE );
	$xtpl->assign( 'MODULE_NAME', $module_name );
	$xtpl->assign( 'OP', $op );

	foreach( $site_mods as $mod_name => $mod_data )
	{
		if( $mod_data['module_file'] == 'news' )
		{
			$mod_data['value'] = $mod_name;
			$xtpl->assign( 'MOD_DATA', $mod_data );
			$xtpl->parse( 'main.mod_data' );
		}
	}

	if( !empty( $array_nv3_news ) )
	{
		foreach( $array_nv3_news as $nv3_news )
		{
			$xtpl->assign( 'NV3_NEWS', $nv3_news );
			$xtpl->parse( 'main.nv3_news' );
		}
	}

	$xtpl->parse( 'main' );
	$contents = $xtpl->text( 'main' );
}
include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme( $contents );
include NV_ROOTDIR . '/includes/footer.php';