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

foreach ($site_mods as $mod_name => $mod_data) {
    if ($mod_data['module_file'] == 'contact') {
        $mod_data['value'] = $mod_name;
        $xtpl->assign('MOD_DATA', $mod_data);
        $xtpl->parse('main.mod_data');
    }
}

if ($nv_Request->isset_request('mod_name', 'post')) {
    $mod_name = $nv_Request->get_string('mod_name', 'post');
	define('NV_PREFIXLANG3', NV3_PREFIX. '_' .NV_LANG_DATA);
	
    if (isset($site_mods[$mod_name])) {
        try{
	    	$db->exec('TRUNCATE TABLE ' . NV_PREFIXLANG .'_'. $mod_name . '_department');
	    
	        $_sql = 'SELECT * FROM ' . NV_PREFIXLANG3 .'_'. $mod_name . '_rows';
	        $_query = $db->query($_sql);
	        $i=1;
	        while ($row = $_query->fetch()) {
	            $sql = "INSERT INTO " . NV_PREFIXLANG .'_'. $mod_name . "_department
	            	(id, full_name, alias, image, phone, fax, email, address, note, 
	            	others, cats, admins, act, weight, is_default) 
	            	VALUES (:id,:full_name,:alias,:image,:phone,:fax,:email,:address,
	            	:note,:others,:cats,:admins,:act,:weight,:is_default)";
	            $data_insert = array();
	            $data_insert['id'] = $row['id'];
	            $data_insert['full_name'] = $row['full_name'];
	            $data_insert['alias'] = change_alias($row['full_name']);
	            $data_insert['image'] = '';
	            $data_insert['phone'] = $row['phone'];
	            $data_insert['fax'] = $row['fax'];
	            $data_insert['email'] = $row['email'];
	            $data_insert['address'] = '';
	            $data_insert['note'] = $row['note'];
	            $data_insert['others'] = '';
	            $data_insert['cats'] = '';
	            $data_insert['admins'] = $row['admins'];
	            $data_insert['act'] = $row['act'];
	            $data_insert['weight'] = $i;
	            $data_insert['is_default'] = 0;
	            $id = intval($db->insert_id($sql, 'id', $data_insert));
	            
	            if (empty($id)) {
	                $err .= "Lỗi insert bộ phận: " . $id . " </br>";
	            }
	            $i++;
	    	}
        }
        catch( PDOException $e )
        {
          trigger_error( $e->getMessage() );
        }
		
		try{
	    	$db->exec('TRUNCATE TABLE ' . NV_PREFIXLANG .'_'. $mod_name . '_send');
	    
	        $_sql = 'SELECT * FROM ' . NV_PREFIXLANG3 .'_'. $mod_name . '_send';
	        $_query = $db->query($_sql);
	        while ($row = $_query->fetch()) {
	            $sql = "INSERT INTO " . NV_PREFIXLANG .'_'. $mod_name . "_send
	            	(id, cid, cat, title, content, send_time, sender_id, sender_name, sender_email, 
	            	sender_phone, sender_ip, is_read, is_reply) 
	            	VALUES (:id,:cid,:cat,:title,:content,:send_time,:sender_id,:sender_name,
	            	:sender_email,:sender_phone,:sender_ip,:is_read,:is_reply)";
	            $data_insert = array();
	            $data_insert['id'] = $row['id'];
	            $data_insert['cid'] = $row['cid'];
	            $data_insert['cat'] = '';
	            $data_insert['title'] = $row['title'];
	            $data_insert['content'] = $row['content'];
	            $data_insert['send_time'] = $row['send_time'];
	            $data_insert['sender_id'] = $row['sender_id'];
	            $data_insert['sender_name'] = $row['sender_name'];
	            $data_insert['sender_email'] = $row['sender_email'];
	            $data_insert['sender_phone'] = $row['sender_phone'];
	            $data_insert['sender_ip'] = $row['sender_ip'];
	            $data_insert['is_read'] = $row['is_read'];
	            $data_insert['is_reply'] = $row['is_reply'];
	            $id = intval($db->insert_id($sql, 'id', $data_insert));
	            
	            $sql = "INSERT INTO " . NV_PREFIXLANG .'_'. $mod_name . "_reply
	            	(id, reply_content, reply_time, reply_aid) 
	            	VALUES (:id,:reply_content,:reply_time,:reply_aid)";
	            $data_insert = array();
	            $data_insert['id'] = $row['id'];
	            $data_insert['reply_content'] = $row['reply_content'];
	            $data_insert['reply_time'] = $row['title'];
	            $data_insert['reply_aid'] = $row['content'];
	            $id = intval($db->insert_id($sql, 'rid', $data_insert));
	    	}
        }
        catch( PDOException $e )
        {
          trigger_error( $e->getMessage() );
        }
        if ($err != null) {
            $xtpl->assign('ERR', $err);
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
