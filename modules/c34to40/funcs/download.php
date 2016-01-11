<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC.
 * All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Thu, 09 Jan 2014 10:18:48 GMT
 */

if (!defined('NV_IS_MOD_C34TO40')) die('Stop!!!');

$typeflag = array();
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

if ($nv_Request->isset_request('mod_name', 'post')) {
    $mod_name = $nv_Request->get_string('mod_name', 'post');
    $mod_data3 = $nv_Request->get_string('nv3_news', 'post');
    if (isset($site_mods[$mod_name])) {
        $mod_data = $site_mods[$mod_name]['module_data'];
        define('NV_PREFIXLANG3', NV3_PREFIX . '_' . NV_LANG_DATA);
        
        try {

        } catch (PDOException $e) {
            die($e->getMessage());
        }
        
        $db->query("TRUNCATE " . NV_PREFIXLANG . "_" . $mod_data . "_categories");
        $db->query("TRUNCATE " . NV_PREFIXLANG . "_" . $mod_data);
        $groups_view = '6';
        $groups_download = '6';
        
        try {
            // Fetch Assoc
            $_sql = 'SELECT * FROM ' . NV_PREFIXLANG3 . '_' . $mod_data3;
            $_query = $db->query($_sql);
            while ($row = $_query->fetch()) {
                if ($row['who_view'] == 1) {
                    $groups_comment = '2';
                } elseif ($row['who_view'] == 2) {
                    $groups_comment = '3';
                } elseif ($row['who_view'] == 3) {
                    $groups_comment = $row['groups_comment'];
                } else {
                    $groups_comment = '6';
                }
                
                $db->query("INSERT Into " . NV_PREFIXLANG . "_" . $mod_data . "(id, catid, title, alias, description, introtext, uploadtime, updatetime, user_id, user_name, author_name, author_email, author_url, fileupload, linkdirect, version, filesize, fileimage, status, copyright, view_hits, download_hits, groups_comment, groups_view, groups_download, comment_hits, rating_detail)
		                                                          SELECT id, catid, title, alias, description, introtext, uploadtime, updatetime, user_id, user_name, author_name, author_email, author_url, fileupload, linkdirect, version, filesize, fileimage, status, copyright, view_hits, download_hits," . $groups_comment . ", " . $groups_view . "," . $groups_download . ",comment_hits, rating_detail FROM " . NV_PREFIXLANG3 . "_" . $mod_data3 . " Where id = " . $row['id']);
            }
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        
        try {
            // Fetch Assoc
            $_sql = 'SELECT * FROM ' . NV_PREFIXLANG3 . '_' . $mod_data3 . '_categories';
            $_query = $db->query($_sql);
            while ($row = $_query->fetch()) {
                if ($row['who_view'] == 1) {
                    $groups_view = '2';
                }
                if ($row['who_view'] == 2) {
                    $groups_view = '3';
                } else {
                    $groups_view = '6';
                }
                
                if ($row['who_download'] == 1) {
                    $groups_download = '2';
                }
                if ($row['who_download'] == 2) {
                    $groups_download = '3';
                } else {
                    $groups_download = '6';
                }
                $db->query("INSERT " . NV_PREFIXLANG . "_" . $mod_data . "_categories (id, parentid, title, alias, description, groups_view, groups_download, weight, status) SELECT id, parentid, title, alias, description, " . $groups_view . " , " . $groups_download . ", weight, status  FROM " . NV_PREFIXLANG3 . "_" . $mod_data3 . "_categories Where id = " . $row['id']);
            }
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        
        $db->query("INSERT " . NV_PREFIXLANG . "_" . $mod_data . "_tmp  SELECT  * FROM " . NV_PREFIXLANG3 . "_" . $mod_data3 . "_tmp");
        $db->query("INSERT " . NV_PREFIXLANG . "_" . $mod_data . "_report  SELECT  * FROM " . NV_PREFIXLANG3 . "_" . $mod_data3 . "_report");
        
        $nv_Cache->delMod($mod_name);
        nv_insert_logs(NV_LANG_DATA, $mod_name, 'Convert', '', $admin_info['userid']);
        Header('Location: ' . nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $mod_name, true));
        die();
    }
} else {
    $result = $db->query('SELECT title, module_data, custom_title FROM ' . NV3_PREFIX . '_' . NV_LANG_DATA . '_modules WHERE module_file="download"');
    $array_nv3_download = $result->fetchAll();
    
    $xtpl = new XTemplate($op . '.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
    $xtpl->assign('LANG', $lang_module);
    $xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
    $xtpl->assign('NV_NAME_VARIABLE', NV_NAME_VARIABLE);
    $xtpl->assign('NV_OP_VARIABLE', NV_OP_VARIABLE);
    $xtpl->assign('MODULE_NAME', $module_name);
    $xtpl->assign('OP', $op);
    
    foreach ($site_mods as $mod_name => $mod_data) {
        if ($mod_data['module_file'] == 'download') {
            $mod_data['value'] = $mod_name;
            $xtpl->assign('MOD_DATA', $mod_data);
            $xtpl->parse('main.mod_data');
        }
    }
    
    if (!empty($array_nv3_download)) {
        foreach ($array_nv3_download as $nv3_download) {
            $xtpl->assign('NV3_DOWNLOAD', $nv3_download);
            $xtpl->parse('main.nv3_download');
        }
    }
    
    $xtpl->parse('main');
    $contents = $xtpl->text('main');
}

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
