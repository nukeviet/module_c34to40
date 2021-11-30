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
    $mod_data3 = $nv_Request->get_string('nv3_laws', 'post,get', '');
    $runstep = $nv_Request->get_int('runstep', 'post,get', 0);
    $runoffset = $nv_Request->get_int('runoffset', 'post,get', 0);

    $nexturl = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op;
    $nexturl .= '&mod_name=' . $mod_name;
    $nexturl .= '&nv3_laws=' . $mod_data3;

    if (isset($site_mods[$mod_name])) {
        $mod_data = $site_mods[$mod_name]['module_data'];
        define('NV_PREFIXLANG3', NV3_PREFIX . '_' . NV_LANG_DATA);

        // Bước 1: Xóa dữ liệu module
        if ($runstep == 0) {
            // Xóa dữ liệu module hiện tại
            try {
                $db->query("TRUNCATE TABLE " . NV_PREFIXLANG . "_" . $mod_data . "_area");
                $db->query("TRUNCATE TABLE " . NV_PREFIXLANG . "_" . $mod_data . "_cat");
                $db->query("TRUNCATE TABLE " . NV_PREFIXLANG . "_" . $mod_data . "_row");
                $db->query("TRUNCATE TABLE " . NV_PREFIXLANG . "_" . $mod_data . "_row_area");
                $db->query("TRUNCATE TABLE " . NV_PREFIXLANG . "_" . $mod_data . "_set_replace");
                $db->query("TRUNCATE TABLE " . NV_PREFIXLANG . "_" . $mod_data . "_signer");
                $db->query("TRUNCATE TABLE " . NV_PREFIXLANG . "_" . $mod_data . "_subject");
            } catch (PDOException $e) {
                trigger_error(print_r($e, true));
                ajax_respon('Lỗi xóa dữ liệu module', 'text-danger');
            }

            $nexturl .= '&runstep=1';
            ajax_respon('Xóa dữ liệu module thành công', 'text-success', $nexturl, 'Copy dữ liệu lĩnh vực, người ký, cơ quan ban hành');
        } elseif ($runstep == 1) {
            // Copy lĩnh vực
            try {
                $db->query("INSERT " . NV_PREFIXLANG . "_" . $mod_data . "_area (
                    id, parentid, alias, title, introduction, keywords, addtime, weight
                ) SELECT
                    id, parentid, alias, title, introduction, keywords, addtime, weight
                FROM " . NV_PREFIXLANG3 . "_" . $mod_data3 . "_area");
            } catch (PDOException $e) {
                trigger_error(print_r($e, true));
                ajax_respon('Lỗi copy lĩnh vực: ' . $e->getMessage(), 'text-danger');
            }

            // Copy chủ đề
            try {
                $db->query("INSERT " . NV_PREFIXLANG . "_" . $mod_data . "_cat (
                    id, parentid, alias, title, introduction, keywords, newday, addtime, weight
                ) SELECT
                    id, parentid, alias, title, introduction, keywords, 5, addtime, weight
                FROM " . NV_PREFIXLANG3 . "_" . $mod_data3 . "_cat");
            } catch (PDOException $e) {
                trigger_error(print_r($e, true));
                ajax_respon('Lỗi copy chủ đề: ' . $e->getMessage(), 'text-danger');
            }

            // Copy người ký văn bản
            try {
                $db->query("INSERT " . NV_PREFIXLANG . "_" . $mod_data . "_signer (
                    id, title, offices, positions, addtime
                ) SELECT
                    id, title, offices, positions, addtime
                FROM " . NV_PREFIXLANG3 . "_" . $mod_data3 . "_signer");
            } catch (PDOException $e) {
                trigger_error(print_r($e, true));
                ajax_respon('Lỗi copy người ký văn bản: ' . $e->getMessage(), 'text-danger');
            }

            // Copy cơ quan ban hành
            try {
                $db->query("INSERT " . NV_PREFIXLANG . "_" . $mod_data . "_subject (
                    id, alias, title, introduction, keywords, numcount, numlink, addtime, weight
                ) SELECT
                    id, alias, title, introduction, keywords, 0, 5, addtime, weight
                FROM " . NV_PREFIXLANG3 . "_" . $mod_data3 . "_subject");
            } catch (PDOException $e) {
                trigger_error(print_r($e, true));
                ajax_respon('Lỗi copy cơ quan ban hành: ' . $e->getMessage(), 'text-danger');
            }

            // Copy thiết lập thay thế
            try {
                $db->query("INSERT " . NV_PREFIXLANG . "_" . $mod_data . "_set_replace (
                    id, oid, nid
                ) SELECT
                    id, oid, nid
                FROM " . NV_PREFIXLANG3 . "_" . $mod_data3 . "_set_replace");
            } catch (PDOException $e) {
                trigger_error(print_r($e, true));
                ajax_respon('Lỗi copy thiết lập thay thế: ' . $e->getMessage(), 'text-danger');
            }

            $nexturl .= '&runstep=2';
            ajax_respon('Thành công', 'text-success', $nexturl, 'Copy các văn bản');
        } elseif ($runstep == 2) {
            $limit_row = 500;
            // Copy các văn bản
            try {
                $db->sqlreset()->select('*')->from(NV_PREFIXLANG3 . '_' . $mod_data3 . '_row')->order('id ASC')->limit($limit_row)->offset($runoffset);
                $sql = $db->sql();
                $result = $db->query($sql);
                $num_rows = $result->rowCount();

                while ($item = $result->fetch()) {
                    // Xử lý lại quyền xem
                    if ($item['who_view'] == 1) {
                        $groups_view = '4';
                    } elseif ($item['who_view'] == 2) {
                        $groups_view = '3';
                    } elseif ($item['who_view'] == 3) {
                        $groups_view = $item['groups_view'];
                    } else {
                        $groups_view = '6';
                    }

                    // Xử lý lại quyền tải
                    if ($item['who_download'] == 1) {
                        $groups_download = '4';
                    } elseif ($item['who_download'] == 2) {
                        $groups_download = '3';
                    } elseif ($item['who_download'] == 3) {
                        $groups_download = $item['groups_download'];
                    } else {
                        $groups_download = '6';
                    }

                    // Xử lý lại đường dẫn đến file
                    $mod_upload3 = str_replace('_', '-', $mod_data3);
                    $item['files'] = str_replace('/' . $mod_upload3 . '/', '', $item['files']);

                    // Cắt ngắn bớt các cột
                    $keys = [
                        'code' => 50
                    ];
                    foreach ($keys as $field => $length) {
                        $item[$field] = nv_substr($item[$field], 0, $length);
                        $item[$field] = trim($item[$field], '-');
                        $item[$field] = trim($item[$field]);
                    }

                    $stmt = $db->prepare("INSERT INTO " . NV_PREFIXLANG . "_" . $mod_data . "_row (
                        id, replacement, relatement, title, alias, code, cid, sid,
                        note, introtext, bodytext, keywords, groups_view, groups_download, files,
                        status, approval, addtime, edittime, publtime, start_comm_time,
                        end_comm_time, startvalid, exptime, view_hits, download_hits,
                        admin_add, admin_edit
                    ) VALUES (
                        :id, :replacement, :relatement, :title, :alias, :code, :cid, :sid,
                        :note, :introtext, :bodytext, :keywords, :groups_view, :groups_download, :files,
                        :status, 0, :addtime, :edittime, :publtime, 0,
                        0, :startvalid, :exptime, :view_hits, :download_hits,
                        :admin_add, :admin_edit
                    )");

                    $stmt->bindParam(':id', $item['id'], PDO::PARAM_INT);
                    $stmt->bindParam(':replacement', $item['replacement'], PDO::PARAM_STR);
                    $stmt->bindParam(':relatement', $item['relatement'], PDO::PARAM_STR);
                    $stmt->bindParam(':title', $item['title'], PDO::PARAM_STR);
                    $stmt->bindParam(':alias', $item['alias'], PDO::PARAM_STR);
                    $stmt->bindParam(':code', $item['code'], PDO::PARAM_STR);
                    $stmt->bindParam(':cid', $item['cid'], PDO::PARAM_INT);
                    $stmt->bindParam(':sid', $item['sid'], PDO::PARAM_INT);
                    $stmt->bindParam(':note', $item['note'], PDO::PARAM_STR, strlen($item['note']));
                    $stmt->bindParam(':introtext', $item['introtext'], PDO::PARAM_STR, strlen($item['introtext']));
                    $stmt->bindParam(':bodytext', $item['bodytext'], PDO::PARAM_STR, strlen($item['bodytext']));
                    $stmt->bindParam(':keywords', $item['keywords'], PDO::PARAM_STR);
                    $stmt->bindParam(':groups_view', $groups_view, PDO::PARAM_STR);
                    $stmt->bindParam(':groups_download', $groups_download, PDO::PARAM_STR);
                    $stmt->bindParam(':files', $item['files'], PDO::PARAM_STR, strlen($item['files']));
                    $stmt->bindParam(':status', $item['status'], PDO::PARAM_INT);
                    $stmt->bindParam(':addtime', $item['addtime'], PDO::PARAM_INT);
                    $stmt->bindParam(':edittime', $item['edittime'], PDO::PARAM_INT);
                    $stmt->bindParam(':publtime', $item['publtime'], PDO::PARAM_INT);
                    $stmt->bindParam(':startvalid', $item['startvalid'], PDO::PARAM_INT);
                    $stmt->bindParam(':exptime', $item['exptime'], PDO::PARAM_INT);
                    $stmt->bindParam(':view_hits', $item['view_hits'], PDO::PARAM_INT);
                    $stmt->bindParam(':download_hits', $item['download_hits'], PDO::PARAM_INT);
                    $stmt->bindParam(':admin_add', $item['admin_add'], PDO::PARAM_INT);
                    $stmt->bindParam(':admin_edit', $item['admin_edit'], PDO::PARAM_INT);

                    $stmt->execute();

                    if (!empty($item['aid'])) {
                        $sql = "INSERT INTO " . NV_PREFIXLANG . "_" . $mod_data . "_row_area (row_id, area_id) VALUES (
                            " . $item['id'] . ", " . $item['aid'] . "
                        )";
                        $db->query($sql);
                    }
                }
            } catch (PDOException $e) {
                trigger_error(print_r($e, true));
                ajax_respon('Lỗi copy văn bản', 'text-danger');
            }

            if ($num_rows < $limit_row) {
                $nexturl .= '&runstep=3';
                ajax_respon('Thành công', 'text-success', $nexturl, 'Kết thúc cập nhật');
            } else {
                $runoffset += $limit_row;
                $nexturl .= '&runstep=2&runoffset=' . $runoffset;
                ajax_respon('Thành công', 'text-success', $nexturl, 'Tiếp tục copy ' . $limit_row . ' văn bản tiếp theo');
            }
        } elseif ($runstep == 3) {
            $nv_Cache->delMod($mod_name);

            $nexturl .= '&runstep=3';
            ajax_respon('<a href="' . nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $mod_name, true) . '">Hoàn thành cập nhật, nhấp vào đây để xem kết quả</a>', 'text-success', '', '', 1);
        }
    } else {
        ajax_respon('Không tồn tại module cần nâng cấp', 'text-danger');
    }
} else {
    $noNv3DB = false;
    try {
        $result = $db->query('SELECT title, module_data, custom_title FROM ' . NV3_PREFIX . '_' . NV_LANG_DATA . '_modules WHERE module_file="laws"');
        $array_nv3_laws = $result->fetchAll();
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
        if ($mod_data['module_file'] == 'laws') {
            $mod_data['value'] = $mod_name;
            $xtpl->assign('MOD_DATA', $mod_data);
            $xtpl->parse('main.mod_data');
        }
    }

    if (!empty($array_nv3_laws)) {
        foreach ($array_nv3_laws as $nv3_laws) {
            $xtpl->assign('NV3_LAWS', $nv3_laws);
            $xtpl->parse('main.nv3_laws');
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
