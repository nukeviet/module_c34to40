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
    $mod_data3 = $nv_Request->get_string('nv3_videoclips', 'post,get', '');
    $runstep = $nv_Request->get_int('runstep', 'post,get', 0);
    $runoffset = $nv_Request->get_int('runoffset', 'post,get', 0);

    $nexturl = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op;
    $nexturl .= '&mod_name=' . $mod_name;
    $nexturl .= '&nv3_videoclips=' . $mod_data3;

    if (isset($site_mods[$mod_name])) {
        $mod_data = $site_mods[$mod_name]['module_data'];
        define('NV_PREFIXLANG3', NV3_PREFIX . '_' . NV_LANG_DATA);

        // Bước 1: Xóa dữ liệu module
        if ($runstep == 0) {
            // Xóa dữ liệu module hiện tại
            try {
                $db->query("TRUNCATE TABLE " . NV_PREFIXLANG . "_" . $mod_data . "_clip");
                $db->query("TRUNCATE TABLE " . NV_PREFIXLANG . "_" . $mod_data . "_hit");
                $db->query("TRUNCATE TABLE " . NV_PREFIXLANG . "_" . $mod_data . "_topic");
            } catch (PDOException $e) {
                trigger_error(print_r($e, true));
                ajax_respon('Lỗi xóa dữ liệu module', 'text-danger');
            }

            $nexturl .= '&runstep=1';
            ajax_respon('Xóa dữ liệu module thành công', 'text-success', $nexturl, 'Copy các thể loại');
        } elseif ($runstep == 1) {
            $limit_row = 500;
            // Copy các thể loại
            try {
                $db->sqlreset()->select('*')->from(NV_PREFIXLANG3 . '_' . $mod_data3)->order('weight ASC')->limit($limit_row)->offset($runoffset);
                $sql = $db->sql();
                $result = $db->query($sql);
                $num_rows = $result->rowCount();

                $weightRow = $runoffset;

                while ($item = $result->fetch()) {
                    $weightRow++;

                    $stmt = $db->prepare("INSERT INTO " . NV_PREFIXLANG . "_" . $mod_data . "_topic (
                        id, parentid, title, alias, description, weight, img, status, keywords
                    ) VALUES (
                        :id, 0, :title, :alias, :description, :weight, '', :status, ''
                    )");

                    $stmt->bindParam(':id', $item['albumid'], PDO::PARAM_INT);
                    $stmt->bindParam(':title', $item['name'], PDO::PARAM_STR);
                    $stmt->bindParam(':alias', $item['alias'], PDO::PARAM_STR);
                    $stmt->bindParam(':description', $item['description'], PDO::PARAM_STR, strlen($item['description']));
                    $stmt->bindParam(':weight', $weightRow, PDO::PARAM_INT);
                    $stmt->bindParam(':status', $item['active'], PDO::PARAM_INT);

                    $stmt->execute();
                }
            } catch (PDOException $e) {
                trigger_error(print_r($e, true));
                ajax_respon('Lỗi copy thể loại', 'text-danger');
            }

            if ($num_rows < $limit_row) {
                $nexturl .= '&runstep=2';
                ajax_respon('Thành công', 'text-success', $nexturl, 'Copy các video');
            } else {
                $runoffset += $limit_row;
                $nexturl .= '&runstep=1&runoffset=' . $runoffset;
                ajax_respon('Thành công', 'text-success', $nexturl, 'Tiếp tục copy ' . $limit_row . ' thể loại tiếp theo');
            }
        } elseif ($runstep == 2) {
            $limit_row = 500;
            // Copy các video
            try {
                $db->sqlreset()->select('*')->from(NV_PREFIXLANG3 . '_' . $mod_data3 . '_picture')->order('pictureid ASC')->limit($limit_row)->offset($runoffset);
                $sql = $db->sql();
                $result = $db->query($sql);
                $num_rows = $result->rowCount();

                while ($item = $result->fetch()) {
                    $stmt = $db->prepare("INSERT INTO " . NV_PREFIXLANG . "_" . $mod_data . "_clip (
                        id, tid, userid, title, alias, hometext, bodytext, keywords, img, internalpath, externalpath, comm, status, addtime
                    ) VALUES (
                        :id, :tid, 1, :title, :alias, '', :bodytext, '', '', '', :externalpath, 1, 1, " . NV_CURRENTTIME . "
                    )");

                    $stmt->bindParam(':id', $item['pictureid'], PDO::PARAM_INT);
                    $stmt->bindParam(':tid', $item['albumid'], PDO::PARAM_INT);
                    $stmt->bindParam(':title', $item['name'], PDO::PARAM_STR);
                    $stmt->bindParam(':alias', $item['alias'], PDO::PARAM_STR);
                    $stmt->bindParam(':bodytext', $item['description'], PDO::PARAM_STR, strlen($item['description']));
                    $stmt->bindParam(':externalpath', $item['vpath'], PDO::PARAM_STR, strlen($item['vpath']));

                    $stmt->execute();

                    $sql = "INSERT INTO " . NV_PREFIXLANG . "_" . $mod_data . "_hit (cid, view, liked, unlike, broken) VALUES (
                        " . $item['pictureid'] . ", " . $item['num_view'] . ", 0, 0, 0
                    )";
                    $db->query($sql);
                }
            } catch (PDOException $e) {
                trigger_error(print_r($e, true));
                ajax_respon('Lỗi copy video', 'text-danger');
            }

            if ($num_rows < $limit_row) {
                $nexturl .= '&runstep=3';
                ajax_respon('Thành công', 'text-success', $nexturl, 'Kết thúc nâng cấp');
            } else {
                $runoffset += $limit_row;
                $nexturl .= '&runstep=2&runoffset=' . $runoffset;
                ajax_respon('Thành công', 'text-success', $nexturl, 'Tiếp tục copy ' . $limit_row . ' video tiếp theo');
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
        $result = $db->query('SELECT title, module_data, custom_title FROM ' . NV3_PREFIX . '_' . NV_LANG_DATA . '_modules WHERE module_file="video"');
        $array_nv3_videoclips = $result->fetchAll();
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
        if ($mod_data['module_file'] == 'videoclips') {
            $mod_data['value'] = $mod_name;
            $xtpl->assign('MOD_DATA', $mod_data);
            $xtpl->parse('main.mod_data');
        }
    }

    if (!empty($array_nv3_videoclips)) {
        foreach ($array_nv3_videoclips as $nv3_videoclips) {
            $xtpl->assign('NV3_VIDEOCLIPS', $nv3_videoclips);
            $xtpl->parse('main.nv3_videoclips');
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
