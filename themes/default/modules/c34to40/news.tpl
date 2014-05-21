<!-- BEGIN: main -->

<b>Hướng dẫn thực hiện:</b>
<br>
<br>
- Cài lại module news, để xoá các bài viết và chuyên mục đã có trước đó.
<br>
<br>
- dùng phpmyadmin, hoặc các công cụ khác để import CSDL site NukeViet 3.4 vào cùng một CSDL với site chạy NukeViet 4
<br>
<br>
- Copy các ảnh của module news cũ, Sang thư mục của module news đang sử dụng của NUkeViet 4
<br>
<br>
- Sử dụng chức năng để chuyển đổi.
<br>
<br>

<form action="{NV_BASE_SITEURL}index.php?{NV_LANG_VARIABLE}={NV_LANG_DATA}&amp;{NV_NAME_VARIABLE}={MODULE_NAME}&amp;{NV_OP_VARIABLE}={OP}" method="post">
	<input type="hidden" name="save"  value="1" />
	<input type="hidden" name="id" value="{DATA.id}" />
	<select name="mod_name">
		<!-- BEGIN: mod_data -->
		<option value="{MOD_DATA.value}">{MOD_DATA.custom_title}</option>
		<!-- END: mod_data -->
	</select>
	<input name="import" type="submit" value="Thực hiện nâng cấp">
</form>

<!-- END: main -->