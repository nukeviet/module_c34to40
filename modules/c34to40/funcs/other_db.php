<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC.
 * All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 31/05/2010, 00:36
 */

if (!defined('NV_IS_MOD_C34TO40')) die('Stop!!!');

$xtpl = new XTemplate($op . '.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
$xtpl->assign('NV_NAME_VARIABLE', NV_NAME_VARIABLE);
$xtpl->assign('NV_OP_VARIABLE', NV_OP_VARIABLE);
$xtpl->assign('MODULE_NAME', $module_name);
$xtpl->assign('OP', $op);

if ($nv_Request->isset_request('save', 'post')) {
    other();
    //del_nv3();
    $xtpl->assign('ERR', 'Thực hiện xong!');
}
$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';

// chuyển đổi 1 số bảng khác
function other()
{
    global $global_config, $db, $nv_Cache;
    try {
        // bảng nv3_vi_counter
        $_sql = 'SELECT c_type, c_val, last_update, c_count, vi_count FROM ' . NV4_PREFIX . '_counter';
        $_query = $db->query($_sql);
        
        while ($row = $_query->fetch()) {
            $nv_Cache->delAll();
            $sqlnv3 = 'SELECT c_type, c_val, c_count, last_update FROM ' . NV3_PREFIX . '_' . NV_LANG_DATA . '_counter';
            $result = $db->query($sqlnv3);
            
            while ($row3 = $result->fetch()) {
                if ($row['c_type'] == $row3['c_type'] and $row['c_val'] == $row3['c_val']) {
                    $row['c_count'] = $row3['c_count'];
                    $row['last_update'] = $row3['last_update'];
                    
                    $sql_up = 'UPDATE ' . NV4_PREFIX . '_counter SET
					last_update=' . $db->quote($row['last_update']) . ',
					c_count=' . $db->quote($row['c_count']) . ',
					vi_count=' . $db->quote($row['c_count']) . '
					WHERE c_type=' . $db->quote($row['c_type']) . ' AND c_val=' . $db->quote($row['c_val']) . '';
                    $db->query($sql_up);
                }
            }
        }
        
        // bảng nv3_vi_searchkeys
        $db->query('TRUNCATE ' . NV4_PREFIX . '_' . NV_LANG_DATA . '_searchkeys');
        $_sql = 'SELECT `id`, `keys`, `total`, `search_engine` FROM `' . NV3_PREFIX . '_' . NV_LANG_DATA . '_searchkeys`';
        $_query = $db->query($_sql);
        
        while ($row = $_query->fetch()) {
            
            $sql = "INSERT INTO " . NV4_PREFIX . '_' . NV_LANG_DATA . "_searchkeys(id, `skey`, total, search_engine)
		      VALUES (:id,:skey,:total,:search_engine)";
            
            $data_insert = array();
            $data_insert['id'] = $row['id'];
            $data_insert['skey'] = $row['keys'];
            $data_insert['total'] = $row['total'];
            $data_insert['search_engine'] = $row['search_engine'];
            
            $id = intval($db->insert_id($sql, 'id', $data_insert));
        }
        
        // bảng nv3_vi_referer_stats
        $db->query('TRUNCATE ' . NV4_PREFIX . '_' . NV_LANG_DATA . '_referer_stats');
        $_sql = 'SELECT `host`, `total`, `month01`, `month02`, `month03`, `month04`, `month05`, `month06`, `month07`, `month08`, `month09`, `month10`, `month11`, `month12`, `last_update` FROM `' . NV3_PREFIX . '_' . NV_LANG_DATA . '_referer_stats`';
        $_query = $db->query($_sql);
        
        while ($row = $_query->fetch()) {
            
            $sql = "INSERT INTO " . NV4_PREFIX . '_' . NV_LANG_DATA . "_referer_stats
		(host, total, month01, month02, month03, month04,
		month05, month06, month07, month08, month09, month10,
		month11, month12, last_update)
		VALUES (
		" . $db->quote($row['host']) . ",
		" . $db->quote($row['total']) . ",
		" . $db->quote($row['month01']) . ",
		" . $db->quote($row['month02']) . ",
		" . $db->quote($row['month03']) . ",
		" . $db->quote($row['month04']) . ",
		" . $db->quote($row['month05']) . ",
		" . $db->quote($row['month06']) . ",
		" . $db->quote($row['month07']) . ",
		" . $db->quote($row['month08']) . ",
		" . $db->quote($row['month09']) . ",
		" . $db->quote($row['month10']) . ",
		" . $db->quote($row['month11']) . ",
		" . $db->quote($row['month12']) . ",
		" . $db->quote($row['last_update']) . ")";
            $db->query($sql);
        }
    } catch (PDOException $e) {
        trigger_error($e->getMessage());
    }
}