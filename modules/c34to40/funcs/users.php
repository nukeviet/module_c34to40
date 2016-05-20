<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC.
 * All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 31/05/2010, 00:36
 */

if (!defined('NV_IS_MOD_C34TO40'))
    die('Stop!!!');

$xtpl = new XTemplate($op . '.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
$xtpl->assign('NV_NAME_VARIABLE', NV_NAME_VARIABLE);
$xtpl->assign('NV_OP_VARIABLE', NV_OP_VARIABLE);
$xtpl->assign('MODULE_NAME', $module_name);
$xtpl->assign('OP', $op);

foreach ($site_mods as $mod_name => $mod_data) {
    if ($mod_data['module_file'] == 'users') {
        $mod_data['value'] = $mod_name;
        $xtpl->assign('MOD_DATA', $mod_data);
        $xtpl->parse('main.mod_data');
    }
}

if ($nv_Request->isset_request('mod_name', 'post')) {
    $mod_name = $nv_Request->get_string('mod_name', 'post');
    if (isset($site_mods[$mod_name])) {
        $result = c_user();
        if ($result != null) {
            $xtpl->assign('ERR', $result);
        } else {
            $xtpl->assign('ERR', 'Thực hiện xong!');
        }
    }
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';

/**
 * c_user()
 * 
 * @return
 */
function c_user()
{
    global $global_config, $db;

    // Kiểm tra xem các trường của bảng nv3 đã được tạo trong bảng nv4_users_field hay chưa
    $_sql = 'SELECT count(*) FROM ' . NV4_PREFIX . '_users_field';
    $err = "";
    
    $num_items = $db->query($_sql)->fetchColumn();
    if ($num_items == 0) {
        // neu chua co thi insert cac truong nay vao
        $sql = "INSERT INTO " . NV4_PREFIX . "_users_field (fid, field, weight, field_type, field_choices, sql_choices, match_type, match_regex, func_callback, min_length, max_length, required, show_register, user_editable, show_profile, class, language, default_value) VALUES
		(1, 'website', 1, 'textbox', '', '', 'none', '', '', 0, 255, 0, 1, 1, 1, 'input', 'a:1:{s:2:\"vi\";a:2:{i:0;s:7:\"website\";i:1;s:0:\"\";}}', ''),
		(2, 'location', 2, 'textbox', '', '', 'none', '', '', 0, 255, 0, 1, 1, 1, 'input', 'a:1:{s:2:\"vi\";a:2:{i:0;s:12:\"Địa chỉ\";i:1;s:0:\"\";}}', ''),
		(3, 'yim', 3, 'textbox', '', '', 'none', '', '', 0, 255, 0, 1, 1, 1, 'input', 'a:1:{s:2:\"vi\";a:2:{i:0;s:5:\"yahoo\";i:1;s:0:\"\";}}', ''),
		(4, 'mobile', 4, 'textbox', '', '', 'none', '', '', 0, 100, 0, 1, 1, 1, 'input', 'a:1:{s:2:\"vi\";a:2:{i:0;s:6:\"mobile\";i:1;s:0:\"\";}}', ''),
		(5, 'fax', 5, 'textbox', '', '', 'none', '', '', 0, 100, 0, 1, 1, 1, 'input', 'a:1:{s:2:\"vi\";a:2:{i:0;s:3:\"fax\";i:1;s:0:\"\";}}', ''),
		(6, 'telephone', 6, 'textbox', '', '', 'none', '', '', 0, 100, 0, 1, 1, 1, 'input', 'a:1:{s:2:\"vi\";a:2:{i:0;s:9:\"telephone\";i:1;s:0:\"\";}}', '')";

        try {
            $db->query($sql);
        } catch (PDOException $e) {
            $err .= "Lỗi bảng " . NV4_PREFIX . "_users_field<br />";
        }

        // insert vao bang nv4_users_field
        // cap nhat vao bang nv4_users_info
        $sql_i = "ALTER TABLE " . NV4_PREFIX . "_users_info ADD website varchar(255) NOT NULL DEFAULT '',";
        $sql_i .= "ADD location varchar(255) NOT NULL DEFAULT '',";
        $sql_i .= "ADD yim varchar(255) NOT NULL DEFAULT '',";
        $sql_i .= "ADD telephone varchar(100) NOT NULL DEFAULT '',";
        $sql_i .= "ADD fax varchar(100) NOT NULL DEFAULT '',";
        $sql_i .= "ADD mobile varchar(100) NOT NULL DEFAULT ''";

        try {
            $db->query($sql_i);
        } catch (PDOException $e) {
            $err .= "Lỗi bảng " . NV4_PREFIX . "_users_info<br />";
        }
    }
    
    $nv3_exists = true;
    try {
        $sql = 'SELECT * FROM ' . NV3_PREFIX . '_users';
        $queryUsers = $db->query($sql);
    } catch (PDOException $e) {
        $nv3_exists = false;
        $err .= "Lỗi: Chưa import CSDL thành viên NukeViet 3 vào";
    }
    
    if ($nv3_exists) {
        $db->exec('TRUNCATE TABLE ' . NV4_PREFIX . '_users_info');
        // Xóa dữ liệu bảng cũ đi
        $db->exec('TRUNCATE TABLE ' . NV4_PREFIX . '_users');
    
        while ($row = $queryUsers->fetch()) {
            $sql = "INSERT INTO " . NV4_PREFIX . "_users(userid, group_id, username, md5username, password,
                email, first_name, last_name, gender, photo, birthday, sig,
                regdate, question, answer, passlostkey, view_mail, remember,
                in_groups, active, checknum, last_login, last_ip, last_agent,
                last_openid, idsite)
                VALUES (:userid,:group_id,:username,:md5username,:password,:email,:first_name,:last_name,
                :gender,:photo,:birthday,:sig,:regdate,:question,:answer,
                :passlostkey,:view_mail,:remember,:in_groups,:active,:checknum,:last_login,
                :last_ip,:last_agent,:last_openid,:idsite
            )";
            
            $data_insert = array();
            $data_insert['userid'] = $row['userid'];
            $data_insert['group_id'] = 0;
            $data_insert['username'] = $row['username'];
            $data_insert['md5username'] = nv_md5safe($row['username']);
            $data_insert['password'] = $row['password'];
            $data_insert['email'] = $row['email'];
            $data_insert['first_name'] = '';
            $data_insert['last_name'] = $row['full_name'];
            $data_insert['gender'] = $row['gender'];
            $data_insert['photo'] = $row['photo'];
            $data_insert['birthday'] = $row['birthday'];
            $data_insert['sig'] = $row['sig'];
            $data_insert['regdate'] = $row['regdate'];
            $data_insert['question'] = $row['question'];
            $data_insert['answer'] = $row['answer'];
            $data_insert['passlostkey'] = $row['passlostkey'];
            $data_insert['view_mail'] = $row['view_mail'];
            $data_insert['remember'] = $row['remember'];
            $data_insert['in_groups'] = $row['in_groups'];
            $data_insert['active'] = $row['active'];
            $data_insert['checknum'] = $row['checknum'];
            $data_insert['last_login'] = $row['last_login'];
            $data_insert['last_ip'] = $row['last_ip'];
            $data_insert['last_agent'] = $row['last_agent'];
            $data_insert['last_openid'] = $row['last_openid'];
            $data_insert['idsite'] = $global_config['idsite'];
            
            try {
                $userid = intval($db->insert_id($sql, 'userid', $data_insert));
            } catch (PDOException $e) {
                $err .= 'Trùng tài khoản đổi tài khoản: ' . $row['username'];
                $data_insert['username'] = $row['username'] . '-' . $userid;
                $data_insert['md5username'] = nv_md5safe($row['username']);
                $userid = intval($db->insert_id($sql, 'userid', $data_insert));
                $err .= '---->' . $row['username'] . '<br>';
            }
            
            if (!empty($userid)) {
                $sql_info = "INSERT INTO " . NV4_PREFIX . "_users_info(
                    userid, website, location, yim, telephone, fax, mobile
                ) VALUES (
                    :userid,:website,:location,:yim,:telephone,:fax,:mobile
                )";
                
                $data_insert_info = array();
                $data_insert_info['userid'] = $userid;
                $data_insert_info['website'] = $row['website'];
                $data_insert_info['location'] = $row['location'];
                $data_insert_info['yim'] = $row['yim'];
                $data_insert_info['telephone'] = $row['telephone'];
                $data_insert_info['fax'] = $row['fax'];
                $data_insert_info['mobile'] = $row['mobile'];
                $userid_info = intval($db->insert_id($sql_info, 'userid', $data_insert_info));
            } else {
                $err .= "Lỗi insert user: " . $userid . " </br>";
            }
        }
    
        // Cập nhật dữ liệu quản trị.
        $db->exec('TRUNCATE TABLE ' . NV4_PREFIX . '_authors');
        
        try {
            $db->query('INSERT ' . NV4_PREFIX . '_authors SELECT * FROM ' . NV3_PREFIX . '_authors');
        } catch (PDOException $e) {
            $err .= "Lỗi không tương thích cấu trúc của bảng quản trị, các tài khoản quản trị không được chuyển, vui lòng xem lại <br />";
        }
    }

    return $err;
}
