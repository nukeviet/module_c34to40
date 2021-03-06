<!-- BEGIN: main -->

<b>Hướng dẫn thực hiện:</b>
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
- Nếu đã tùy biến trường dữ liệu của thành viên cần xóa toàn bộ phần này, sau khi chuyển đổi rồi mới thêm lại nếu không sẽ không ghi được các thông tin của thành viên cũ sang
<br>
<br>
- Sử dụng chức năng để chuyển đổi.
<br>
<br>
- Sau khi nâng cấp xong cần cập nhật lại sitekey cho site để có thể đăng nhập được,
- Tìm đến file config.php của site NukeViet 3.4 copy sitekey và paste vào file config của site mới.
<br>
<br>
<form action="{NV_BASE_SITEURL}index.php?{NV_LANG_VARIABLE}={NV_LANG_DATA}&amp;{NV_NAME_VARIABLE}={MODULE_NAME}&amp;{NV_OP_VARIABLE}={OP}" method="post">
	<input type="hidden" name="save"  value="1" />
	<input type="hidden" name="id" value="{DATA.id}" />
	<p>Chọn module muốn nâng cấp</p>
	<div class="form-inline">
        <select name="mod_name" class="form-control w500">
    		<!-- BEGIN: mod_data -->
    		<option value="{MOD_DATA.value}">{MOD_DATA.value}</option>
    		<!-- END: mod_data -->
    	</select>
    	<input name="import" type="submit" value="Thực hiện nâng cấp" class="btn btn-success">
    </div>
</form>

<p style="color: #D30000" class="margin-top">{ERR}</p>
<!-- END: main -->