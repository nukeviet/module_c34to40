<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 31/05/2010, 00:36
 */

if (!defined('NV_IS_MOD_C34TO40'))
    die('Stop!!!');

$noNv3DB = false;
$array_nv3_shop = array();
try {
    $result = $db->query('SELECT title, module_data, custom_title FROM ' . NV3_PREFIX . '_' . NV_LANG_DATA . '_modules WHERE module_file="shops"');
    $array_nv3_shop = $result->fetchAll();
} catch (PDOException $e) {
    $noNv3DB = true;
}

$xtpl = new XTemplate($op . '.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
$xtpl->assign('NV_NAME_VARIABLE', NV_NAME_VARIABLE);
$xtpl->assign('NV_OP_VARIABLE', NV_OP_VARIABLE);
$xtpl->assign('MODULE_NAME', $module_name);
$xtpl->assign('OP', $op);

foreach ($site_mods as $mod_name => $mod_data) {
    if ($mod_data['module_file'] == 'shops') {
        $mod_data['value'] = $mod_name;
        $xtpl->assign('MOD_DATA', $mod_data);
        $xtpl->parse('main.mod_data');
    }
}

if (!empty($array_nv3_shop)) {
    foreach ($array_nv3_shop as $nv3_shop) {
        $xtpl->assign('NV3_SHOP', $nv3_shop);
        $xtpl->parse('main.nv3_shop');
    }
}

if ($nv_Request->isset_request('mod_name', 'post')) {
    $mod_name = $nv_Request->get_string('mod_name', 'post');
    $nv3_shop = $nv_Request->get_string('nv3_shop', 'post');

    if (isset($site_mods[$mod_name])) {
        $result = c_shop($mod_name, $nv3_shop);
        if ($result != null) {
            $xtpl->assign('ERR', $result);
        } else {
            $xtpl->assign('ERR', 'Thực hiện xong!');
        }
    }
}

if ($noNv3DB) {
    $xtpl->parse('main.error_modulenv3');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';

function c_shop($mod_name, $nv3_shop)
{
    global $global_config, $db;
    try {
        $err = "";
        $nv3_table_name = NV3_PREFIX . '_' . $nv3_shop;
        $nv4_table_name = NV4_PREFIX . '_' . $mod_name;

        $field_lang = nv_file_table($nv4_table_name . '_catalogs');
        $listfield = '';
        $listvalue = '';
        foreach ($field_lang as $field_lang_i) {
            list($flang, $fname) = $field_lang_i;
            $listfield .= ', ' . $flang . '_' . $fname;
            $listvalue .= ', :' . $flang . '_' . $fname;
        }

        $db->query('TRUNCATE ' . $nv4_table_name . '_catalogs');
        $_query = $db->query('SELECT * FROM ' . $nv3_table_name . '_catalogs');
        while ($data = $_query->fetch()) {
            $data = array_map('unfixdb', $data);
            $data['newday'] = 3;
            $data['typeprice'] = 1;
            $data['form'] = '';
            $data['group_price'] = '';
            $data['cat_allow_point'] = 0;
            $data['cat_number_point'] = 0;
            $data['cat_number_product'] = 0;

            $sql = "INSERT INTO " . $nv4_table_name . '_catalogs' . "
			(catid, parentid, image, weight, sort, lev, viewcat, numsubcat, subcatid, inhome, numlinks, newday, typeprice,
			form, group_price,viewdescriptionhtml, admins, add_time, edit_time, groups_view, cat_allow_point, cat_number_point,
			cat_number_product " . $listfield . " )
			VALUES (" . $data['catid'] . ", :parentid, :image," . $data['weight'] . ", " . $data['order'] . ", " . $data['lev'] . ",:viewcat, " . $data['numsubcat'] . ", :subcatid, '1', '4', :newday, :typeprice, :form, :group_price,:viewdescriptionhtml,:admins, " . NV_CURRENTTIME . ", " . NV_CURRENTTIME . ", :groups_view, :cat_allow_point,:cat_number_point, :cat_number_product" . $listvalue . ")";
            $data_insert = array();
            $data_insert['parentid'] = $data['parentid'];
            $data_insert['image'] = $data['image'];
            $data_insert['subcatid'] = $data['subcatid'];
            $data_insert['viewcat'] = 'viewcat_page_list';
            $data_insert['newday'] = $data['newday'];
            $data_insert['typeprice'] = $data['typeprice'];
            $data_insert['form'] = $data['form'];
            $data_insert['group_price'] = $data['group_price'];
            $data_insert['viewdescriptionhtml'] = '';
            $data_insert['admins'] = $data['admins'];

            if ($data['who_view'] == 1) {
                $data_insert['groups_view'] = '2';
            }
            if ($data['who_view'] == 2) {
                $data_insert['groups_view'] = '3';
            } else {
                $data_insert['groups_view'] = '6';
            }

            $data_insert['cat_allow_point'] = $data['cat_allow_point'];
            $data_insert['cat_number_point'] = $data['cat_number_point'];
            $data_insert['cat_number_product'] = $data['cat_number_product'];
            foreach ($field_lang as $field_lang_i) {
                list($flang, $fname) = $field_lang_i;
                if ($fname == 'descriptionhtml') {
                    $data_insert[$flang . '_' . $fname] = '';
                } elseif ($fname == 'title_custom') {
                    $data_insert[$flang . '_' . $fname] = $data[$flang . '_title'];
                } else {
                    if (!isset($data[$flang . '_' . $fname])) {
                        $data_insert[$flang . '_' . $fname] = $data['vi' . '_' . $fname];
                    } else {
                        $data_insert[$flang . '_' . $fname] = $data[$flang . '_' . $fname];
                    }
                }
            }

            $newcatid = intval($db->insert_id($sql, 'catid', $data_insert));
            if (empty($newcatid)) {
                $err .= "Lỗi insert: " . $nv4_table_name . '_catalogs' . " </br>";
            }
        }

        // bang nv3_shops_units
        $db->query('TRUNCATE ' . $nv4_table_name . '_units');
        $field_lang = nv_file_table($nv4_table_name . '_units');

        $listfield = '';
        $listvalue = '';
        foreach ($field_lang as $field_lang_i) {
            list($flang, $fname) = $field_lang_i;
            $listfield .= ', ' . $flang . '_' . $fname;
            $listvalue .= ', :' . $flang . '_' . $fname;
        }

        $_query = $db->query('SELECT id, vi_title, vi_note FROM ' . $nv3_table_name . '_units');
        while ($data = $_query->fetch()) {
            $data['vi_note'] = '';
            $sql = "INSERT INTO " . $nv4_table_name . '_units' . " (id " . $listfield . ") VALUES (" . $data['id'] . $listvalue . ")";
            $data_insert = array();
            foreach ($field_lang as $field_lang_i) {
                list($flang, $fname) = $field_lang_i;
                if (!isset($data[$flang . '_' . $fname])) {
                    $data_insert[$flang . '_' . $fname] = $data['vi' . '_' . $fname];
                } else {
                    $data_insert[$flang . '_' . $fname] = $data[$flang . '_' . $fname];
                }
            }
            $newcatid = intval($db->insert_id($sql, 'id', $data_insert));
            if (empty($newcatid)) {
                $err .= "Lỗi insert" . $nv4_table_name . '_units' . " </br>";
            }
        }

        // bang nv3_shops_rows
        $db->query('TRUNCATE ' . $nv4_table_name . '_rows');
        $field_lang = nv_file_table($nv4_table_name . '_rows');

        $listfield = '';
        $listvalue = '';
        foreach ($field_lang as $field_lang_i) {

            list($flang, $fname) = $field_lang_i;
            if ($fname == 'gift') {
                $listfield .= ',' . $flang . '_' . $fname . '_content';
                $listvalue .= ',:' . $flang . '_' . $fname . '_content';
            } else {
                $listfield .= ',' . $flang . '_' . $fname;
                $listvalue .= ',:' . $flang . '_' . $fname;
            }
        }

        $_query = $db->query("SELECT id, listcatid, group_id, user_id, source_id, addtime, edittime, status, publtime, exptime, archive, product_number, product_price, product_discounts, money_unit, product_unit, homeimgfile, homeimgthumb, homeimgalt, otherimage, imgposition, copyright, inhome, allowed_comm, allowed_rating, ratingdetail, allowed_send, allowed_print, allowed_save, hitstotal, hitscm, hitslm, showprice, vi_title, vi_alias, vi_description, vi_keywords, vi_note, vi_hometext, vi_bodytext, vi_address, vi_warranty, vi_promotional FROM " . $nv3_table_name . "_rows");
        while ($row = $_query->fetch()) {
            $row['vi_tag_title'] = '';
            $row['vi_tag_description'] = '';
            $sql = "INSERT INTO " . $nv4_table_name . '_rows' . " (id, listcatid, user_id, addtime, edittime,
		status, publtime, exptime, archive, product_code, product_number, product_price,
		price_config, money_unit, product_unit, product_weight, weight_unit, discount_id,
		homeimgfile, homeimgthumb, homeimgalt,otherimage,imgposition, copyright,gift_from,gift_to,
		inhome,allowed_comm, allowed_rating, ratingdetail, allowed_send, allowed_print,
		allowed_save, hitstotal, hitscm, hitslm, showprice " . $listfield . ")
			VALUES ( " . $row['id'] . " ,
			:listcatid,
			" . intval($row['user_id']) . ",
			" . intval($row['addtime']) . ",
			" . intval($row['edittime']) . ",
			" . intval($row['status']) . ",
			" . intval($row['publtime']) . ",
			" . intval($row['exptime']) . ",
			" . intval($row['archive']) . ",
			:product_code,
			" . intval($row['product_number']) . ",
			:product_price,
			:price_config,
			:money_unit,
			" . intval($row['product_unit']) . ",
			:product_weight,
			:weight_unit,
			" . intval(0) . ",
			:homeimgfile,
			:homeimgthumb,
			:homeimgalt,
			:otherimage,
			" . intval($row['imgposition']) . ",
			" . intval($row['copyright']) . ",
			" . intval(NV_CURRENTTIME) . ",
			" . intval(NV_CURRENTTIME) . ",
			" . intval($row['inhome']) . ",
			:allowed_comm,
			" . intval($row['allowed_rating']) . ",
			:ratingdetail,
			" . intval($row['allowed_send']) . ",
			" . intval($row['allowed_print']) . ",
			" . intval($row['allowed_save']) . ",
			" . intval($row['hitstotal']) . ",
			" . intval($row['hitscm']) . ",
			" . intval($row['hitslm']) . ",
			" . intval($row['showprice']) . "
			" . $listvalue . "
			)";

            $data_insert = array();
            $data_insert['listcatid'] = $row['listcatid'];
            $data_insert['product_code'] = '';
            $data_insert['product_price'] = $row['product_price'];
            $data_insert['price_config'] = '';
            $data_insert['product_weight'] = 0;
            $data_insert['weight_unit'] = '';
            $data_insert['money_unit'] = $row['money_unit'];
            $data_insert['homeimgfile'] = $row['homeimgfile'];
            $data_insert['homeimgalt'] = $row['homeimgalt'];
            $data_insert['otherimage'] = $row['otherimage'];
            $data_insert['ratingdetail'] = $row['ratingdetail'];
            $data_insert['allowed_comm'] = 6;

            //kiểm tra ảnh thumb
            $data_insert['homeimgthumb'] = 0;
            if (!empty($row['homeimgfile']) and !nv_is_url($row['homeimgfile']) and is_file(NV_DOCUMENT_ROOT . '/uploads/' . $mod_name . '/' . $row['homeimgfile'])) {
                if (file_exists(NV_ROOTDIR . '/' . NV_FILES_DIR . '/' . $mod_name . '/' . $row['homeimgfile'])) {
                    $data_insert['homeimgthumb'] = 1;
                } else {
                    $data_insert['homeimgthumb'] = 2;
                }
            } elseif (!empty($row['homeimgfile']) and nv_is_url($row['homeimgfile'])) {
                $data_insert['homeimgthumb'] = 3;
            } else {
                $row['homeimgfile'] = '';
            }

            foreach ($field_lang as $field_lang_i) {
                list($flang, $fname) = $field_lang_i;
                if ($fname == 'gift') // kiem tra neu la truong gift thi them content vao sau
                    {
                    $fname = $fname . '_content';
                }

                if ($fname == 'gift_content') {
                    $data_insert[$flang . '_' . $fname] = '';
                } else {
                    if (!isset($data[$flang . '_' . $fname])) {
                        $data_insert[$flang . '_' . $fname] = $row['vi' . '_' . $fname];
                    } else {
                        $data_insert[$flang . '_' . $fname] = $row[$flang . '_' . $fname];
                    }
                }
            }
            $contentid = $db->insert_id($sql, 'id', $data_insert);
            if (empty($contentid)) {
                $err .= "Lỗi insert" . $nv4_table_name . '_rows' . " </br>";
            }
        }

        //bảng nv3_shops_block
        $db->query('TRUNCATE ' . $nv4_table_name . '_block');

        // Fetch Assoc
        $_sql = 'SELECT bid, id, weight FROM ' . $nv3_table_name . '_block';
        $_query = $db->query($_sql);

        while ($row = $_query->fetch()) {
            $sql = "INSERT INTO " . $nv4_table_name . '_block' . " (bid, id, weight) VALUES (:bid,:id,:weight)";
            $data_insert = array();
            $data_insert['bid'] = $row['bid'];
            $data_insert['id'] = $row['id'];
            $data_insert['weight'] = $row['weight'];
            $contentid = $db->insert_id($sql, 'bid', $data_insert);
        }

        //bang nv3_shops_block_cat
        $db->query('TRUNCATE ' . $nv4_table_name . '_block_cat');
        $field_lang = nv_file_table($nv4_table_name . '_block_cat');

        $listfield = '';
        $listvalue = '';
        foreach ($field_lang as $field_lang_i) {
            list($flang, $fname) = $field_lang_i;
            $listfield .= ', ' . $flang . '_' . $fname;
            $listvalue .= ', :' . $flang . '_' . $fname;
        }
        $_sql = 'SELECT bid, adddefault, image, thumbnail, weight, add_time, edit_time, vi_title, vi_alias, vi_description, vi_keywords FROM ' . $nv3_table_name . '_block_cat';
        $_query = $db->query($_sql);

        while ($row = $_query->fetch()) {
            $sql = "INSERT INTO " . $nv4_table_name . "_block_cat(bid, adddefault, image, weight, add_time, edit_time " . $listfield . ")
				VALUES (:bid,:adddefault,:image,:weight,:add_time,:edit_time" . $listvalue . ")";
            $data_insert = array();
            $data_insert['bid'] = $row['bid'];
            $data_insert['adddefault'] = $row['adddefault'];
            $data_insert['image'] = $row['image'];
            $data_insert['weight'] = $row['weight'];
            $data_insert['add_time'] = $row['add_time'];
            $data_insert['edit_time'] = $row['edit_time'];

            foreach ($field_lang as $field_lang_i) {
                list($flang, $fname) = $field_lang_i;
                if (!isset($data[$flang . '_' . $fname])) {
                    $data_insert[$flang . '_' . $fname] = $row['vi' . '_' . $fname];
                } else {
                    $data_insert[$flang . '_' . $fname] = $row[$flang . '_' . $fname];
                }
            }
            $contentid = $db->insert_id($sql, 'bid', $data_insert);
            if (empty($contentid)) {
                $err .= "Lỗi insert" . $nv4_table_name . '_block_cat' . " </br>";
            }
        }

        //bảng nv3_shops_group
        $db->query('TRUNCATE ' . $nv4_table_name . '_group');
        $field_lang = nv_file_table($nv4_table_name . '_group');

        $listfield = '';
        $listvalue = '';
        foreach ($field_lang as $field_lang_i) {
            list($flang, $fname) = $field_lang_i;
            $listfield .= ', ' . $flang . '_' . $fname;
            $listvalue .= ', :' . $flang . '_' . $fname;
        }

        // Fetch Assoc
        $_sql = 'SELECT * FROM ' . $nv3_table_name . '_group';
        $_query = $db->query($_sql);

        while ($row = $_query->fetch()) {
            $sql = "INSERT INTO " . $nv4_table_name . '_group' . "(groupid, parentid, image, weight, sort, lev, viewgroup, numsubgroup, subgroupid, inhome, indetail, add_time, edit_time, numpro, in_order, is_require" . $listfield . ")
				VALUES (:groupid,:parentid,:image,:weight,:sort,:lev,:viewgroup,:numsubgroup,:subgroupid,:inhome,:indetail,:add_time,:edit_time,:numpro,:in_order,:is_require" . $listvalue . ")";

            $data_insert = array();
            $data_insert['groupid'] = $row['groupid'];
            $data_insert['parentid'] = $row['parentid'];
            $data_insert['image'] = $row['image'];
            $data_insert['weight'] = $row['weight'];
            $data_insert['sort'] = 0;
            $data_insert['lev'] = $row['lev'];
            $data_insert['viewgroup'] = $row['viewgroup'];
            $data_insert['numsubgroup'] = $row['numsubgroup'];
            $data_insert['subgroupid'] = $row['subgroupid'];
            $data_insert['inhome'] = $row['inhome'];
            $data_insert['indetail'] = 1;
            $data_insert['add_time'] = $row['add_time'];
            $data_insert['edit_time'] = $row['edit_time'];
            $data_insert['numpro'] = $row['numpro'];
            $data_insert['in_order'] = 1;
            $data_insert['is_require'] = '';

            foreach ($field_lang as $field_lang_i) {
                list($flang, $fname) = $field_lang_i;
                if (!isset($data[$flang . '_' . $fname])) {
                    $data_insert[$flang . '_' . $fname] = $row['vi' . '_' . $fname];
                } else {
                    $data_insert[$flang . '_' . $fname] = $row[$flang . '_' . $fname];
                }
            }
            $contentid = $db->insert_id($sql, 'bid', $data_insert);
            if (empty($contentid)) {
                $err .= "Lỗi insert" . $nv4_table_name . "_group </br>";
            }
        }

        //bảng nv3_shops_orders
        $db->query('TRUNCATE ' . $nv4_table_name . '_orders');

        // Fetch Assoc
        $_sql = 'SELECT * FROM ' . $nv3_table_name . '_orders';
        $_query = $db->query($_sql);

        while ($row = $_query->fetch()) {
            $sql = "INSERT INTO " . $nv4_table_name . '_orders' . " (order_id, order_code, lang,
				order_name, order_email, order_phone, order_note, user_id, admin_id,
				shop_id, who_is, unit_total, order_total, order_time, edit_time,
				postip, order_view, transaction_status, transaction_id, transaction_count
				) VALUES (
				:order_id, :order_code,:lang, :order_name, :order_email, :order_phone, :order_note,
				:user_id, :admin_id, :shop_id,:who_is, :unit_total, :order_total,
				:order_time, :edit_time, :postip, :order_view, :transaction_status, :transaction_id,:transaction_count
				)";
            $data_insert = array();
            $data_insert['order_id'] = $row['order_id'];
            $data_insert['order_code'] = $row['order_code'];
            $data_insert['lang'] = $row['lang'];
            $data_insert['order_name'] = $row['order_name'];
            $data_insert['order_email'] = $row['order_email'];
            $data_insert['order_phone'] = $row['order_phone'];
            $data_insert['order_note'] = $row['order_note'];
            $data_insert['user_id'] = ($row['user_id']);
            $data_insert['admin_id'] = ($row['admin_id']);
            $data_insert['shop_id'] = ($row['shop_id']);
            $data_insert['who_is'] = ($row['who_is']);
            $data_insert['unit_total'] = $row['unit_total'];
            $data_insert['order_total'] = ($row['order_total']);
            $data_insert['order_time'] = ($row['order_time']);
            $data_insert['edit_time'] = 0;
            $data_insert['postip'] = $row['postip'];
            $data_insert['order_view'] = ($row['view']);
            $data_insert['transaction_status'] = ($row['transaction_status']);
            $data_insert['transaction_id'] = ($row['transaction_id']);
            $data_insert['transaction_count'] = ($row['transaction_count']);

            $order_id = $db->insert_id($sql, 'order_id', $data_insert);
            if (!empty($order_id)) {
                // insert vào bảng thông tin đơn hàng
                $proid = explode('|', $row['listid']);
                $listnum = explode('|', $row['listnum']);
                $listprice = explode('|', $row['listprice']);
                foreach ($proid as $i => $pid) {
                    $listnum_item = $listnum[$i];
                    $listprice_item = $listprice[$i];
                    $sql_d = 'INSERT INTO ' . $nv4_table_name . '_orders_id( order_id, proid, num, price, discount_id )
					  VALUES ( :order_id, :proid, :num, :price, :discount_id )';
                    $data_insert_d = array();
                    $data_insert_d['order_id'] = $row['order_id'];
                    $data_insert_d['proid'] = $pid;
                    $data_insert_d['num'] = $listnum_item;
                    $data_insert_d['price'] = $listprice_item;
                    $data_insert_d['discount_id'] = 0;
                    $order_i = $db->insert_id($sql_d, 'id', $data_insert_d);
                }
            } else {
                $err .= "Lỗi insert" . $nv4_table_name . "_orders </br>";
            }

        }

        // bảng nv3_shops_payment
        $db->query('TRUNCATE ' . $nv4_table_name . '_payment');

        // Fetch Assoc
        $_sql = 'SELECT * FROM ' . $nv3_table_name . '_payment';
        $_query = $db->query($_sql);

        while ($row = $_query->fetch()) {

            $sql = "INSERT INTO " . $nv4_table_name . '_payment' . "
		(payment, paymentname, domain, active, weight, config, images_button)
		VALUES (:payment,:paymentname,:domain,:active,:weight,:config,:images_button)";
            $data_insert = array();
            $data_insert['payment'] = $row['payment'];
            $data_insert['paymentname'] = $row['paymentname'];
            $data_insert['domain'] = $row['domain'];
            $data_insert['active'] = $row['active'];
            $data_insert['weight'] = $row['weight'];
            $data_insert['config'] = $row['config'];
            $data_insert['images_button'] = $row['images_button'];

            $contentid = $db->insert_id($sql, 'order_id', $data_insert);
        }

        //bảng nv3_shops_transaction
        $db->query('TRUNCATE ' . $nv4_table_name . '_transaction');

        // Fetch Assoc
        $_sql = 'SELECT * FROM ' . $nv3_table_name . '_transaction';
        $_query = $db->query($_sql);

        while ($row = $_query->fetch()) {

            $sql = "INSERT INTO " . $nv4_table_name . '_transaction' . "
		(transaction_id, transaction_time, transaction_status,
		order_id, userid, payment, payment_id, payment_time,
		payment_amount, payment_data)
		VALUES (:transaction_id,:transaction_time,:transaction_status,:order_id,
		:userid,:payment,:payment_id,:payment_time,:payment_amount,:payment_data)";
            $data_insert = array();
            $data_insert['transaction_id'] = $row['transaction_id'];
            $data_insert['transaction_time'] = $row['transaction_time'];
            $data_insert['transaction_status'] = $row['transaction_status'];
            $data_insert['order_id'] = $row['order_id'];
            $data_insert['userid'] = $row['userid'];
            $data_insert['payment'] = $row['payment'];
            $data_insert['payment_id'] = $row['payment_id'];
            $data_insert['payment_time'] = $row['payment_time'];
            $data_insert['payment_amount'] = $row['payment_amount'];
            $data_insert['payment_data'] = $row['payment_data'];

            $transaction_id = $db->insert_id($sql, 'transaction_id', $data_insert);
            if (empty($transaction_id)) {
                $err .= "Lỗi insert" . $nv4_table_name . "_transaction </br>";
            }
        }
    } catch (PDOException $e) {
        $err .= $e->getMessage();
        //trigger_error( $e->getMessage( ) );
    }

    return $err;
}
