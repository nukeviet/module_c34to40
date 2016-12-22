<!-- BEGIN: main -->

<b>Hướng dẫn thực hiện:</b>
<br>
<br>
- dùng phpmyadmin, hoặc các công cụ khác để import CSDL site NukeViet 3.4 vào cùng một CSDL với site chạy NukeViet 4
(nếu đã import rồi thì bỏ qua bước này)
<br>
<br>
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