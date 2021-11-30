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

/**
 * ajax_respon()
 *
 * @param mixed $message
 * @param string $class
 * @param string $url
 * @param string $urlmessage
 * @param integer $complete
 * @return void
 */
function ajax_respon($message, $class = 'text-success', $url = '', $urlmessage = '', $complete = 0) {
    $json = array(
        'complete' => $complete,
        'message' => $message,
        'url' => $url,
        'urlmessage' => $urlmessage,
        'class' => $class
    );
    die(json_encode($json));
}

$respon = array();

if ($nv_Request->isset_request('mod_name', 'post,get')) {
    $mod_name = $nv_Request->get_string('mod_name', 'post,get', '');
    $mod_data3 = $nv_Request->get_string('nv3_nvalbums', 'post,get', '');
    $runstep = $nv_Request->get_int('runstep', 'post,get', 0);
    $runoffset = $nv_Request->get_int('runoffset', 'post,get', 0);

    $nexturl = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op;
    $nexturl .= '&mod_name=' . $mod_name;
    $nexturl .= '&nv3_nvalbums=' . $mod_data3;

    if (isset($site_mods[$mod_name])) {
        $mod_data = $site_mods[$mod_name]['module_data'];
        define('NV_PREFIXLANG3', NV3_PREFIX . '_' . NV_LANG_DATA);

        // Bước 1: Xóa dữ liệu module
        if ($runstep == 0) {
            // Xóa dữ liệu module hiện tại
            try {
                $db->query("TRUNCATE TABLE " . NV_PREFIXLANG . "_" . $mod_data . "_albums");
                $db->query("TRUNCATE TABLE " . NV_PREFIXLANG . "_" . $mod_data . "_pictures");
                $db->query("TRUNCATE TABLE " . NV_PREFIXLANG . "_" . $mod_data . "_pin_album");
                $db->query("TRUNCATE TABLE " . NV_PREFIXLANG . "_" . $mod_data . "_tmp");
            } catch (PDOException $e) {
                trigger_error(print_r($e, true));
                ajax_respon('Lỗi xóa dữ liệu module', 'text-danger');
            }

            $nexturl .= '&runstep=1';
            ajax_respon('Xóa dữ liệu module thành công', 'text-success', $nexturl, 'Copy các album');
        } elseif ($runstep == 1) {
            $limit_row = 500;
            // Copy các album
            try {
                $db->sqlreset()->select('*')->from(NV_PREFIXLANG3 . '_' . $mod_data3 . '_albums')->order('id ASC')->limit($limit_row)->offset($runoffset);
                $sql = $db->sql();
                $result = $db->query($sql);
                $num_rows = $result->rowCount();

                $weightRow = $runoffset;

                while ($item = $result->fetch()) {
                    $weightRow++;

                    // Xử lý lại đường dẫn đến file
                    $mod_upload3 = str_replace('_', '-', $mod_data3);
                    $item['thumb'] = str_replace('uploads/' . $mod_upload3 . '/album/', '', $item['thumb']);

                    // Cắt ngắn bớt các cột
                    /*
                    $keys = [
                        'code' => 50
                    ];
                    foreach ($keys as $field => $length) {
                        $item[$field] = nv_substr($item[$field], 0, $length);
                        $item[$field] = trim($item[$field], '-');
                        $item[$field] = trim($item[$field]);
                    }
                    */

                    $stmt = $db->prepare("INSERT INTO " . NV_PREFIXLANG . "_" . $mod_data . "_albums (
                        id, title, description, thumb, thumb_width, thumb_height, thumb_type,
                        post_id, post_name, post_time, num_views, weight, status
                    ) VALUES (
                        :id, :title, :description, :thumb, 300, 200, 0, :post_id, :post_name, :post_time,
                        :num_views, " . $weightRow . ", :status
                    )");

                    $stmt->bindParam(':id', $item['id'], PDO::PARAM_INT);
                    $stmt->bindParam(':title', $item['title'], PDO::PARAM_STR);
                    $stmt->bindParam(':description', $item['description'], PDO::PARAM_STR, strlen($item['description']));
                    $stmt->bindParam(':thumb', $item['thumb'], PDO::PARAM_STR);
                    $stmt->bindParam(':post_id', $item['post_id'], PDO::PARAM_INT);
                    $stmt->bindParam(':post_name', $item['post_name'], PDO::PARAM_STR);
                    $stmt->bindParam(':post_time', $item['post_time'], PDO::PARAM_INT);
                    $stmt->bindParam(':num_views', $item['num_views'], PDO::PARAM_INT);
                    $stmt->bindParam(':status', $item['status'], PDO::PARAM_INT);

                    $stmt->execute();
                }
            } catch (PDOException $e) {
                trigger_error(print_r($e, true));
                ajax_respon('Lỗi copy album', 'text-danger');
            }

            if ($num_rows < $limit_row) {
                $nexturl .= '&runstep=2';
                ajax_respon('Thành công', 'text-success', $nexturl, 'Copy các ảnh');
            } else {
                $runoffset += $limit_row;
                $nexturl .= '&runstep=1&runoffset=' . $runoffset;
                ajax_respon('Thành công', 'text-success', $nexturl, 'Tiếp tục copy ' . $limit_row . ' albums tiếp theo');
            }
        } elseif ($runstep == 2) {
            $limit_row = 500;
            // Copy các ảnh
            try {
                $db->sqlreset()->select('*')->from(NV_PREFIXLANG3 . '_' . $mod_data3 . '_pictures')->order('id ASC')->limit($limit_row)->offset($runoffset);
                $sql = $db->sql();
                $result = $db->query($sql);
                $num_rows = $result->rowCount();

                while ($item = $result->fetch()) {
                    // Xử lý lại đường dẫn đến file
                    $mod_upload3 = str_replace('_', '-', $mod_data3);
                    $item['link'] = str_replace('uploads/' . $mod_upload3 . '/images/', '', $item['link']);

                    // Cắt ngắn bớt các cột
                    /*
                    $keys = [
                        'code' => 50
                    ];
                    foreach ($keys as $field => $length) {
                        $item[$field] = nv_substr($item[$field], 0, $length);
                        $item[$field] = trim($item[$field], '-');
                        $item[$field] = trim($item[$field]);
                    }
                    */

                    $stmt = $db->prepare("INSERT INTO " . NV_PREFIXLANG . "_" . $mod_data . "_pictures (
                        id, title, link, description, num_views, post_id, post_name, post_time, status
                    ) VALUES (
                        :id, :title, :link, :description, :num_views, :post_id, :post_name, :post_time, :status
                    )");

                    $stmt->bindParam(':id', $item['id'], PDO::PARAM_INT);
                    $stmt->bindParam(':title', $item['title'], PDO::PARAM_STR);
                    $stmt->bindParam(':link', $item['link'], PDO::PARAM_STR);
                    $stmt->bindParam(':description', $item['description'], PDO::PARAM_STR, strlen($item['description']));
                    $stmt->bindParam(':num_views', $item['num_views'], PDO::PARAM_INT);
                    $stmt->bindParam(':post_id', $item['post_id'], PDO::PARAM_INT);
                    $stmt->bindParam(':post_name', $item['post_name'], PDO::PARAM_STR);
                    $stmt->bindParam(':post_time', $item['post_time'], PDO::PARAM_INT);
                    $stmt->bindParam(':status', $item['status'], PDO::PARAM_INT);

                    $stmt->execute();
                }
            } catch (PDOException $e) {
                trigger_error(print_r($e, true));
                ajax_respon('Lỗi copy ảnh', 'text-danger');
            }

            if ($num_rows < $limit_row) {
                $nexturl .= '&runstep=3';
                ajax_respon('Thành công', 'text-success', $nexturl, 'Copy ảnh trong album');
            } else {
                $runoffset += $limit_row;
                $nexturl .= '&runstep=2&runoffset=' . $runoffset;
                ajax_respon('Thành công', 'text-success', $nexturl, 'Tiếp tục copy ' . $limit_row . ' ảnh tiếp theo');
            }
        } elseif ($runstep == 3) {
            // Copy ảnh trong album
            try {
                $db->query("INSERT " . NV_PREFIXLANG . "_" . $mod_data . "_pin_album (
                    id, pid, aid, weight
                ) SELECT
                    id, pid, aid, weight
                FROM " . NV_PREFIXLANG3 . "_" . $mod_data3 . "_pin_album");
            } catch (PDOException $e) {
                trigger_error(print_r($e, true));
                ajax_respon('Lỗi copy ảnh trong album: ' . $e->getMessage(), 'text-danger');
            }

            $nexturl .= '&runstep=4';
            ajax_respon('Thành công', 'text-success', $nexturl, 'Kết thúc cập nhật');
        } elseif ($runstep == 4) {
            $nv_Cache->delMod($mod_name);

            $nexturl .= '&runstep=4';
            ajax_respon('<a href="' . nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $mod_name, true) . '">Hoàn thành cập nhật, nhấp vào đây để xem kết quả</a>', 'text-success', '', '', 1);
        }
    } else {
        ajax_respon('Không tồn tại module cần nâng cấp', 'text-danger');
    }
} else {
    $noNv3DB = false;
    try {
        $result = $db->query('SELECT title, module_data, custom_title FROM ' . NV3_PREFIX . '_' . NV_LANG_DATA . '_modules WHERE module_file="nvalbums"');
        $array_nv3_nvalbums = $result->fetchAll();
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
        if ($mod_data['module_file'] == 'nvalbums') {
            $mod_data['value'] = $mod_name;
            $xtpl->assign('MOD_DATA', $mod_data);
            $xtpl->parse('main.mod_data');
        }
    }

    if (!empty($array_nv3_nvalbums)) {
        foreach ($array_nv3_nvalbums as $nv3_nvalbums) {
            $xtpl->assign('NV3_NVALBUMS', $nv3_nvalbums);
            $xtpl->parse('main.nv3_nvalbums');
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
