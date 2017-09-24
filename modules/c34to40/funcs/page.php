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

        $db->query("TRUNCATE " . NV_PREFIXLANG . "_" . $mod_data . "");

        try {
            $_sql = 'SELECT * FROM ' . NV_PREFIXLANG3 . '_' . $mod_data3;
            $_query = $db->query($_sql);
            while ($row = $_query->fetch()) {
                $sql = "INSERT INTO " . NV_PREFIXLANG . "_" . $mod_data . "
				(id, title, alias, image, imagealt,imageposition, description, bodytext, keywords, socialbutton, 
				activecomm, layout_func, gid, weight, admin_id, add_time, edit_time, status)
		        VALUES (:id,:title,:alias,:image,:imagealt,:imageposition,:description,:bodytext,:keywords,:socialbutton, 
				:activecomm,:layout_func,:gid,:weight,:admin_id,:add_time,:edit_time,:status)";

                $data_insert = array();
                $data_insert['id'] = $row['id'];
                $data_insert['title'] = $row['title'];
                $data_insert['alias'] = $row['alias'];
                $data_insert['image'] = '';
                $data_insert['imagealt'] = '';
                $data_insert['imageposition'] = 0;
                $data_insert['description'] = '';
                $data_insert['bodytext'] = $row['bodytext'];
                $data_insert['keywords'] = $row['keywords'];
                $data_insert['socialbutton'] = 0;
                $data_insert['activecomm'] = 4;
                $data_insert['layout_func'] = '';
                $data_insert['gid'] = 0;
                $data_insert['weight'] = $row['weight'];
                $data_insert['admin_id'] = $row['admin_id'];
                $data_insert['add_time'] = $row['add_time'];
                $data_insert['edit_time'] = $row['edit_time'];
                $data_insert['status'] = $row['status'];

                $id = intval($db->insert_id($sql, 'id', $data_insert));
                if (empty($id)) {
                    echo 'Lỗi bài viết id:' . $row['id'];
                }
            }
        } catch (PDOException $e) {
            print_r($e);
            die($e->getMessage());
        }

        $nv_Cache->delMod($mod_name);
        nv_insert_logs(NV_LANG_DATA, $mod_name, 'Convert', '', $admin_info['userid']);
        Header('Location: ' . nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $mod_name, true));
        die();
    }
} else {
    $result = $db->query('SELECT title, module_data, custom_title FROM ' . NV3_PREFIX . '_' . NV_LANG_DATA . '_modules WHERE module_file="about"');
    $array_nv3_download = $result->fetchAll();

    $xtpl = new XTemplate($op . '.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
    $xtpl->assign('LANG', $lang_module);
    $xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
    $xtpl->assign('NV_NAME_VARIABLE', NV_NAME_VARIABLE);
    $xtpl->assign('NV_OP_VARIABLE', NV_OP_VARIABLE);
    $xtpl->assign('MODULE_NAME', $module_name);
    $xtpl->assign('OP', $op);

    foreach ($site_mods as $mod_name => $mod_data) {
        if ($mod_data['module_file'] == 'page') {
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
