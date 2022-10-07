<!-- BEGIN: main -->

<b>Hướng dẫn thực hiện:</b>
<br>
<br>
- Cài lại module videoclips (hoặc module ảo), để xoá các bài viết và chuyên mục đã có trước đó.
<br>
<br>
- dùng phpmyadmin, hoặc các công cụ khác để import CSDL site NukeViet 3.4 vào cùng một CSDL với site chạy NukeViet 4
(nếu đã import rồi thì bỏ qua bước này). Chú ý trước khi import cần chạy qua tool này:
<pre>
    <code class="language-php">&#x3C;?php

function unfixdb($value)
{
    $value = preg_replace(array(
        &#x22;/(se)\-(lect)/i&#x22;,
        &#x22;/(uni)\-(on)/i&#x22;,
        &#x22;/(con)\-(cat)/i&#x22;,
        &#x22;/(c)\-(har)/i&#x22;,
        &#x22;/(out)\-(file)/i&#x22;,
        &#x22;/(al)\-(ter)/i&#x22;,
        &#x22;/(in)\-(sert)/i&#x22;,
        &#x22;/(d)\-(rop)/i&#x22;,
        &#x22;/(f)\-(rom)/i&#x22;,
        &#x22;/(whe)\-(re)/i&#x22;,
        &#x22;/(up)\-(date)/i&#x22;,
        &#x22;/(de)\-(lete)/i&#x22;,
        &#x22;/(cre)\-(ate)/i&#x22;), &#x22;$1$2&#x22;, $value);
    return $value;
}

$contents = file_get_contents(&#x27;mysql.sql&#x27;);
$contents = unfixdb($contents);

file_put_contents(&#x27;mysql_fixed.sql&#x27;, $contents, LOCK_EX);

echo(&#x27;OK&#x27;);</code>
</pre>
- Copy các file của module videoclips cũ sang thư mục của module videoclips đang sử dụng của NukeViet 4.
<br>
<br>
- Sử dụng chức năng để chuyển đổi.
<br>
<br>

<!-- BEGIN: error_modulenv3 -->
<div class="alert alert-danger">Lỗi: Chưa có CSDL NukeViet v3 được import.</div>
<!-- END: error_modulenv3 -->

<form id="form-update" class="form-horizontal" action="{NV_BASE_SITEURL}index.php?{NV_LANG_VARIABLE}={NV_LANG_DATA}&amp;{NV_NAME_VARIABLE}={MODULE_NAME}&amp;{NV_OP_VARIABLE}={OP}&amp;" method="post">
    <div class="form-group">
        <label class="col-sm-4 control-label">Bảng dữ liệu NukeViet 3</label>
        <div class="col-sm-20">
            <select name="nv3_videoclips" class="form-control">
                <!-- BEGIN: nv3_videoclips -->
                <option value="{NV3_VIDEOCLIPS.module_data}">{NV3_VIDEOCLIPS.module_data} ({NV3_VIDEOCLIPS.custom_title})</option>
                <!-- END: nv3_videoclips -->
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-4 control-label">Module videoclips trên NukeViet 4</label>
        <div class="col-sm-20">
            <select name="mod_name" class="form-control">
                <!-- BEGIN: mod_data -->
                <option value="{MOD_DATA.value}">{MOD_DATA.custom_title}</option>
                <!-- END: mod_data -->
            </select>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <button type="button" value="1" class="btn btn-primary" onclick="$(this).html('<i class=\'fa fa-spin fa-spinner\'></i> Đang thực hiện vui lòng đợi').prop('disabled', true);$('#form-update').submit()">Thực hiện nâng cấp</button>
        </div>
    </div>
</form>

<div id="update-result">
    <div id="update-result-top"></div>
</div>

<script type="text/javascript">
function controlRequest(ajurl) {
    $.ajax({
        url: ajurl,
        dataType: 'json'
    }).done(function(e) {
        showMessage(e.message, e.class);
        if (e.url != '') {
            showMessage(e.urlmessage, '');
            setTimeout(function(){
                controlRequest(e.url)
            }, 1000)
        }
        if (e.complete) {
            showMessage('Tiến trình hoàn tất', 'text-success');
            $('#form-update').find('select').prop('disabled', false);
            $('#form-update').find('[type="button"]').prop('disabled', false).html('Thực hiện nâng cấp');
        }
    }).fail(function(e) {
        showMessage('Lỗi AJAX', 'text-danger');
    });
}
function showMessage(m, c) {
    $('<p class="' + c + '" style="margin-bottom:0">&raquo; ' + m + '</p>').insertAfter('#update-result-top');
}
$(function(){
    $('#form-update').submit(function(e){
        e.preventDefault();
        var data = $('#form-update').serialize();
        var action = $('#form-update').attr('action');
        $('#form-update').find('select').prop('disabled', true);
        $('<p>Tiến trình bắt đầu chạy</p>').insertAfter('#update-result-top');
        if (action.indexOf("&amp;") == -1 && action.indexOf("&") == -1) {
            controlRequest(action + '?' + data);
        } else {
            controlRequest(action + data);
        }

    });
})
</script>
<!-- END: main -->
