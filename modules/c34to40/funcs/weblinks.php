<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Thu, 09 Jan 2014 10:18:48 GMT
 */

if (!defined('NV_IS_MOD_C34TO40'))
    die('Stop!!!');

if ($nv_Request->isset_request('mod_name', 'post')) {
    $mod_name = $nv_Request->get_string('mod_name', 'post');
    $mod_data3 = $nv_Request->get_string('nv3_news', 'post');
    if (isset($site_mods[$mod_name])) {
        $mod_data = $site_mods[$mod_name]['module_data'];
        define('NV_PREFIXLANG3', NV3_PREFIX . '_' . NV_LANG_DATA);

        // Xóa chủ đề 4
        $db->query("TRUNCATE " . NV_PREFIXLANG . "_" . $mod_data . "_cat");

        // Copy nguyên chủ đề bản 3 sang
        $db->query("INSERT INTO " . NV_PREFIXLANG . "_" . $mod_data . "_cat (
            catid, parentid, title, catimage, alias, description, weight, inhome, keywords, add_time, edit_time
        ) SELECT
            catid, parentid, title, catimage, alias, description, weight, inhome, keywords, add_time, edit_time
        FROM " . NV_PREFIXLANG3 . "_" . $mod_data3 . "_cat");

        // Xóa báo lỗi
        $db->query("TRUNCATE " . NV_PREFIXLANG . "_" . $mod_data . "_report");

        // Copy nguyên báo lỗi bản 3 sang
        $db->query("INSERT INTO " . NV_PREFIXLANG . "_" . $mod_data . "_report (
            id, type, report_time, report_ip, report_browse_key, report_browse_name, report_os_key, report_os_name, report_note
        ) SELECT
            id, type, report_time, report_ip, report_browse_key, report_browse_name, report_os_key, report_os_name, report_note
        FROM " . NV_PREFIXLANG3 . "_" . $mod_data3 . "_report");

        // Xóa các liên kết
        $db->query("TRUNCATE " . NV_PREFIXLANG . "_" . $mod_data . "_rows");

        // Copy nguyên liên kết bản 3 sang
        $db->query("INSERT INTO " . NV_PREFIXLANG . "_" . $mod_data . "_rows (
            id, catid, title, alias, url, urlimg, description, add_time, edit_time, hits_total, status
        ) SELECT
            id, catid, title, alias, url, urlimg, description, add_time, edit_time, hits_total, status
        FROM " . NV_PREFIXLANG3 . "_" . $mod_data3 . "_rows");

        $nv_Cache->delMod($mod_name);
        Header('Location: ' . nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $mod_name, true));
        die();
    }
} else {
    $array_nv3_download = array();
    $noNv3DB = false;
    try {
        $result = $db->query('SELECT title, module_data, custom_title FROM ' . NV3_PREFIX . '_' . NV_LANG_DATA . '_modules WHERE module_file="weblinks"');
        $array_nv3_download = $result->fetchAll();
    } catch (PDOException $e) {
        $noNv3DB = true;
    }

    $my_head .= '<link href="' . NV_BASE_SITEURL . NV_EDITORSDIR . '/ckeditor/plugins/codesnippet/lib/highlight/styles/github.css" rel="stylesheet">';
    $my_head .= '<script src="' . NV_BASE_SITEURL . NV_EDITORSDIR . '/ckeditor/plugins/codesnippet/lib/highlight/highlight.pack.js"></script>';
    $my_head .= '<script>hljs.initHighlightingOnLoad();</script>';

    $xtpl = new XTemplate($op . '.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
    $xtpl->assign('LANG', $lang_module);
    $xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
    $xtpl->assign('NV_NAME_VARIABLE', NV_NAME_VARIABLE);
    $xtpl->assign('NV_OP_VARIABLE', NV_OP_VARIABLE);
    $xtpl->assign('MODULE_NAME', $module_name);
    $xtpl->assign('OP', $op);

    foreach ($site_mods as $mod_name => $mod_data) {
        if ($mod_data['module_file'] == 'weblinks') {
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

    if ($noNv3DB) {
        $xtpl->parse('main.error_modulenv3');
    }

    $xtpl->parse('main');
    $contents = $xtpl->text('main');
}

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
