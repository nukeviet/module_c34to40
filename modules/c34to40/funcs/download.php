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

$typeflag = array();
$typeflag[1] = 'gif';
$typeflag[2] = 'jpg';
$typeflag[3] = 'png';
$typeflag[4] = 'swf';
$typeflag[5] = 'psd';
$typeflag[6] = 'bmp';
$typeflag[7] = 'tiff';
$typeflag[8] = 'tiff';
$typeflag[9] = 'jpc';
$typeflag[10] = 'jp2';
$typeflag[11] = 'jpf';
$typeflag[12] = 'jb2';
$typeflag[13] = 'swc';
$typeflag[14] = 'aiff';
$typeflag[15] = 'wbmp';
$typeflag[16] = 'xbm';

if ($nv_Request->isset_request('mod_name', 'post')) {
    $mod_name = $nv_Request->get_string('mod_name', 'post');
    $mod_data3 = $nv_Request->get_string('nv3_news', 'post');
    if (isset($site_mods[$mod_name])) {
        $mod_data = $site_mods[$mod_name]['module_data'];
        define('NV_PREFIXLANG3', NV3_PREFIX . '_' . NV_LANG_DATA);

        // Chuyển chủ đề
        $db->query("TRUNCATE " . NV_PREFIXLANG . "_" . $mod_data . "_categories");

        try {
            $_sql = 'SELECT * FROM ' . NV_PREFIXLANG3 . '_' . $mod_data3 . '_categories';
            $_query = $db->query($_sql);
            while ($row = $_query->fetch()) {
                if ($row['who_view'] == 1) {
                    $groups_view = '4';
                } elseif ($row['who_view'] == 2) {
                    $groups_view = '3';
                } elseif ($row['who_view'] == 3) {
                    $groups_view = $row['groups_view'];
                } else {
                    $groups_view = '6';
                }

                if ($row['who_download'] == 1) {
                    $groups_download = '4';
                } elseif ($row['who_download'] == 2) {
                    $groups_download = '3';
                } elseif ($row['who_download'] == 3) {
                    $groups_download = $row['groups_download'];
                } else {
                    $groups_download = '6';
                }
                $groups_onlineview = $groups_download;

                $db->query("INSERT " . NV_PREFIXLANG . "_" . $mod_data . "_categories (
                    id, parentid, title, alias, description, groups_view, groups_onlineview, groups_download, weight, status
                ) SELECT id, parentid, title, alias, description, '" . $groups_view . "', '" . $groups_onlineview . "', '" . $groups_download . "', weight, status
                    FROM " . NV_PREFIXLANG3 . "_" . $mod_data3 . "_categories WHERE id = " . $row['id']);

                if ($row['parentid'] != 0) {
                    $_sub_sql = 'SELECT * FROM ' . NV_PREFIXLANG . '_' . $mod_data . '_categories WHERE id = ' . $row['parentid'];
                    $_sub_query = $db->query($_sub_sql);
                    while ($_row = $_sub_query->fetch()) {
                        $_row['numsubcat'] = $_row['numsubcat'] + 1;
                        $_row['subcatid'] = ($_row['subcatid'] == '') ? $row['id'] : $_row['subcatid'] . ',' . $row['id'];
                        $db->query("UPDATE " . NV_PREFIXLANG . "_" . $mod_data . "_categories
				            SET numsubcat= " . $_row['numsubcat'] . ", subcatid= " . $db->quote($_row['subcatid']) . "
						WHERE id = " . $_row['id']);
                    }
                }
            }
        } catch (PDOException $e) {
            die($e->getMessage());
        }

        // Chuyển bài đăng
        $db->query("TRUNCATE " . NV_PREFIXLANG . "_" . $mod_data);
        $db->query("TRUNCATE " . NV_PREFIXLANG . "_" . $mod_data . "_files");
        $db->query("TRUNCATE " . NV_PREFIXLANG . "_" . $mod_data . "_detail");

        $groups_view = '6';
        $groups_download = '6';

        try {
            $_sql = 'SELECT * FROM ' . NV_PREFIXLANG3 . '_' . $mod_data3;
            $_query = $db->query($_sql);

            while ($row = $_query->fetch()) {
                if ($row['who_view'] == 1) {
                    $groups_view = '4';
                } elseif ($row['who_view'] == 2) {
                    $groups_view = '3';
                } elseif ($row['who_view'] == 3) {
                    $groups_view = $row['groups_view'];
                } else {
                    $groups_view = '6';
                }

                if ($row['who_download'] == 1) {
                    $groups_download = '4';
                } elseif ($row['who_download'] == 2) {
                    $groups_download = '3';
                } elseif ($row['who_download'] == 3) {
                    $groups_download = $row['groups_download'];
                } else {
                    $groups_download = '6';
                }

                if ($row['who_comment'] == 1) {
                    $groups_comment = '4';
                } elseif ($row['who_comment'] == 2) {
                    $groups_comment = '3';
                } elseif ($row['who_comment'] == 3) {
                    $groups_comment = $row['groups_comment'];
                } else {
                    $groups_comment = '6';
                }
                $groups_onlineview = $groups_download;
                $num_fileupload = 0;
                $num_linkdirect = 0;

                // Copy fileupload
                $fileupload = explode('[NV]', $row['fileupload']);
                $weight = 1;

                foreach ($fileupload as $file) {
                    if (! empty($file)) {
                        $file2 = NV_UPLOADS_DIR . $file;

                        if (file_exists(NV_ROOTDIR . '/' . $file2) and ($filesize = filesize(NV_ROOTDIR . '/' . $file2)) != 0) {
                            $num_fileupload ++;

                            $sql = 'INSERT INTO ' . NV_PREFIXLANG . "_" . $mod_data . '_files (
                                download_id, server_id, file_path, scorm_path, filesize, weight, status
                            ) VALUES (
                                ' . $row['id'] . ', 0, :file_path, :scorm_path, :filesize, ' . ($weight++) . ', 1
                            )';
                            $data_insert = array();
                            $data_insert['file_path'] = $file;
                            $data_insert['scorm_path'] = '';
                            $data_insert['filesize'] = $filesize;
                            $file_id = $db->insert_id($sql, 'file_id', $data_insert);
                        }
                    }
                }

                // Kiểm tra linkredirect
                $linkdirect = explode('[NV]', $row['linkdirect']);
                foreach ($linkdirect as $links) {
                    $links = array_filter(explode('<br />', $links));
                    $num_linkdirect += sizeof($links);
                }

                // Copy detail
                try {
                    $stmt = $db->prepare("INSERT INTO " . NV_PREFIXLANG . "_" . $mod_data . "_detail (
                        id, description, linkdirect, groups_comment, groups_view, groups_onlineview, groups_download, rating_detail
                    ) VALUES(
                        " . $row['id'] . ", :description, :linkdirect, :groups_comment, :groups_view, :groups_onlineview, :groups_download, :rating_detail
                    )");

                    $stmt->bindParam(':description', $row['description'], PDO::PARAM_STR, strlen($row['description']));
                    $stmt->bindParam(':linkdirect', $row['linkdirect'], PDO::PARAM_STR, strlen($row['linkdirect']));
                    $stmt->bindParam(':groups_comment', $groups_comment, PDO::PARAM_STR);
                    $stmt->bindParam(':groups_view', $groups_view, PDO::PARAM_STR);
                    $stmt->bindParam(':groups_onlineview', $groups_onlineview, PDO::PARAM_STR);
                    $stmt->bindParam(':groups_download', $groups_download, PDO::PARAM_STR);
                    $stmt->bindParam(':rating_detail', $row['rating_detail'], PDO::PARAM_STR);
                    $stmt->execute();
                } catch (PDOException $e) {
                    die($e->getMessage());
                }

                $db->query("INSERT INTO " . NV_PREFIXLANG . "_" . $mod_data . "(
                    id, catid, title, alias, introtext, uploadtime, updatetime, user_id, user_name,
                    author_name, author_email, author_url, version, filesize, fileimage, status,
                    copyright, num_fileupload, num_linkdirect, view_hits, download_hits, comment_hits
                ) SELECT id, catid, title, alias, introtext, uploadtime, updatetime, user_id, user_name, author_name, author_email, author_url,
                 version, filesize, fileimage, status, copyright, " . $num_fileupload . ", " . $num_linkdirect . ", view_hits, download_hits,
                 comment_hits FROM " . NV_PREFIXLANG3 . "_" . $mod_data3 . " WHERE id = " . $row['id']);
            }
        } catch (PDOException $e) {
            die($e->getMessage());
        }

        // Chuyển bình luận
        $db->query("DELETE FROM " . NV_PREFIXLANG . "_comment WHERE module=" . $db->quote($mod_name));

        $sql = 'SELECT * FROM ' . NV_PREFIXLANG3 . '_' . $mod_data3 . '_comments';
        $result = $db->query($sql);

        while ($row = $result->fetch()) {
            $sql = "INSERT INTO " . NV_PREFIXLANG . "_comment
            (cid, module, area, id, pid, content, post_time, userid, post_name, post_email, post_ip, status, likes, dislikes)
            VALUES (:cid,:module,:area,:id,:pid,:content,:post_time,:userid,:post_name,:post_email,:post_ip,:status,:likes,:dislikes)";

            $data_insert = array();
            $data_insert['cid'] = $row['id'];
            $data_insert['module'] = $mod_name;
            $data_insert['area'] = $site_mods[$mod_name]['funcs']['viewfile']['func_id'];
            $data_insert['id'] = $row['fid'];
            $data_insert['pid'] = 0;
            $data_insert['content'] = $row['comment'];
            $data_insert['post_time'] = $row['post_time'];
            $data_insert['userid'] = $row['post_id'];
            $data_insert['post_name'] = $row['post_name'];
            $data_insert['post_email'] = $row['post_email'];
            $data_insert['post_ip'] = $row['post_ip'];
            $data_insert['status'] = $row['status'];
            $data_insert['likes'] = 0;
            $data_insert['dislikes'] = 0;

            $cid = intval($db->insert_id($sql, 'cid', $data_insert));
        }

        $db->query("INSERT " . NV_PREFIXLANG . "_" . $mod_data . "_tmp SELECT * FROM " . NV_PREFIXLANG3 . "_" . $mod_data3 . "_tmp");
        $db->query("INSERT " . NV_PREFIXLANG . "_" . $mod_data . "_report SELECT * FROM " . NV_PREFIXLANG3 . "_" . $mod_data3 . "_report");

        $nv_Cache->delMod($mod_name);
        Header('Location: ' . nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $mod_name, true));
        die();
    }
} else {
    $array_nv3_download = array();
    $noNv3DB = false;
    try {
        $result = $db->query('SELECT title, module_data, custom_title FROM ' . NV3_PREFIX . '_' . NV_LANG_DATA . '_modules WHERE module_file="download"');
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
        if ($mod_data['module_file'] == 'download') {
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