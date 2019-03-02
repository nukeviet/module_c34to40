<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Thu, 09 Jan 2014 10:18:48 GMT
 */

if (!defined('NV_IS_MOD_C34TO40')) {
    die('Stop!!!');
}

if (!defined('NV_IS_GODADMIN') and !defined('NV_IS_SPADMIN')) {
    $contents = nv_theme_alert('Yêu cầu quyền truy cập', 'Vui lòng đăng nhập quản trị tối cao hoặc điều hành chung để sử dụng chức năng này', 'warning');

    include NV_ROOTDIR . '/includes/header.php';
    echo nv_site_theme($contents);
    include NV_ROOTDIR . '/includes/footer.php';
}

$my_head .= '<link href="' . NV_BASE_SITEURL . NV_EDITORSDIR . '/ckeditor/plugins/codesnippet/lib/highlight/styles/github.css" rel="stylesheet">';
$my_head .= '<script src="' . NV_BASE_SITEURL . NV_EDITORSDIR . '/ckeditor/plugins/codesnippet/lib/highlight/highlight.pack.js"></script>';
$my_head .= '<script>hljs.initHighlightingOnLoad();</script>';

if ($nv_Request->isset_request('save', 'post')) {
    try {
        // Xóa dữ liệu click bản 4
        $db->query("TRUNCATE " . $db_config['prefix'] . "_banners_click");

        // Copy nguyên dữ liệu click bản 3 sang
        $db->query("INSERT INTO " . $db_config['prefix'] . "_banners_click SELECT * FROM " . NV3_PREFIX . "_banners_click");

        // Xóa các khối quảng cáo bảng 4
        $db->query("TRUNCATE " . $db_config['prefix'] . "_banners_plans");

        // Copy các khối quảng cáo bản 3 sang
        $sql = "SELECT * FROM " . NV3_PREFIX . "_banners_plans";
        $result = $db->query($sql);
        while ($row = $result->fetch()) {
            $sql = "INSERT INTO " . $db_config['prefix'] . "_banners_plans (
                id, blang, title, description, form, width, height, act, require_image, uploadtype, uploadgroup, exp_time
            ) VALUES (
                " . $row['id'] .  ", " . $db->quote($row['blang']) . ", " . $db->quote($row['title']) . ", " . $db->quote($row['description']) . ",
                " . $db->quote($row['form']) . ", " . $row['width'] .  ", " . $row['height'] .  ", " . $row['act'] .  ", 1, 'images,flash', '', 0
            )";
            $db->query($sql);
        }

        // Xóa các quảng cáo bản 4
        $db->query("TRUNCATE " . $db_config['prefix'] . "_banners_rows");

        // Copy các quảng cáo bản 3 sang
        $sql = "SELECT * FROM " . NV3_PREFIX . "_banners_rows";
        $result = $db->query($sql);
        while ($row = $result->fetch()) {
            $sql = "INSERT INTO " . $db_config['prefix'] . "_banners_rows (
                id, title, pid, clid, file_name, file_ext, file_mime, width, height, file_alt, imageforswf, click_url, target, bannerhtml,
                add_time, publ_time, exp_time, hits_total, act, weight
            ) VALUES (
                " . $row['id'] .  ", " . $db->quote($row['title']) . ", " . $row['pid'] .  ", 1, " . $db->quote($row['file_name']) . ", " . $db->quote($row['file_ext']) . ",
                " . $db->quote($row['file_mime']) . ", " . $row['width'] .  ", " . $row['height'] .  ", " . $db->quote($row['file_alt']) . ", '',
                " . $db->quote($row['click_url']) . ", '', '', " . $row['add_time'] .  ", " . $row['publ_time'] .  ", " . $row['exp_time'] .  ", " . $row['hits_total'] .  ",
                " . $row['act'] .  ", " . $row['weight'] .  "
            )";
            $db->query($sql);
        }

        // Xóa hết các file XML của bản 4 hiện có
        //$xml_files = nv_scandir(NV_ROOTDIR . '/' . NV_DATADIR, '/^bpl\_([0-9]+)\.xml$/s');
        //foreach ($xml_files as $xml_file) {
        //    nv_deletefile(NV_ROOTDIR . '/' . NV_DATADIR . '/' . $xml_file);
        //}

        // Tạo lại các file XML
        define('NV_ADMIN', true);
        if (!defined('NV_IS_MODADMIN')) {
            define('NV_IS_MODADMIN', true);
        }

        require NV_ROOTDIR . '/modules/banners/language/admin_' . NV_LANG_DATA . '.php';
        require NV_ROOTDIR . '/modules/banners/admin.functions.php';
        nv_CreateXML_bannerPlan();

        $url_back = nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op, true);
        $contents = nv_theme_alert('Chuyển đổi thành công', 'Dữ liệu đã được chuyển đổi thành công', 'info', $url_back, 'Quay lại');

        include NV_ROOTDIR . '/includes/header.php';
        echo nv_site_theme($contents);
        include NV_ROOTDIR . '/includes/footer.php';
    } catch (Exception $ext) {
        echo '<pre><code>';
        print_r($ext);
        die('</code></pre>');
    }
}

$xtpl = new XTemplate($op . '.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
$xtpl->assign('NV_NAME_VARIABLE', NV_NAME_VARIABLE);
$xtpl->assign('NV_OP_VARIABLE', NV_OP_VARIABLE);
$xtpl->assign('MODULE_NAME', $module_name);
$xtpl->assign('OP', $op);

$noNv3DB = false;
try {
    $result = $db->query('SELECT title, module_data, custom_title FROM ' . NV3_PREFIX . '_' . NV_LANG_DATA . '_modules WHERE module_file="banners"');
    $array_nv3_download = $result->fetchAll();
} catch (PDOException $e) {
    $noNv3DB = true;
}

if ($noNv3DB) {
    $xtpl->parse('main.error_modulenv3');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
