<!-- BEGIN: main -->

<b>Hướng dẫn thực hiện:</b>
<br>
<br>
- Cài lại module shops, để xoá các dữ liệu trước đó.
<br>
<br>
- dùng phpmyadmin, hoặc các công cụ khác để import CSDL site NukeViet 3.4 vào cùng một CSDL với site chạy NukeViet 4
<br>
<br>
- Copy các ảnh của module shops cũ, Sang thư mục của module shops đang sử dụng của NUkeViet 4
<br>
<br>
- Nếu site có sử dụng chức năng đa ngôn ngữ thì cần cài đa ngôn ngữ trước khi thực hiện chuyển đổi.
<br>
<br>
- Sử dụng chức năng để chuyển đổi.
<br>
<br>
- Xóa cache sau khi thực hiện xong.

<form class="form-horizontal" action="{NV_BASE_SITEURL}index.php?{NV_LANG_VARIABLE}={NV_LANG_DATA}&amp;{NV_NAME_VARIABLE}={MODULE_NAME}&amp;{NV_OP_VARIABLE}={OP}" method="post">
	<input type="hidden" name="save"  value="1" />
	<input type="hidden" name="id" value="{DATA.id}" />
	<div class="form-group">
		<label class="col-sm-4 control-label">Bảng dữ liệu NukeViet 3</label>
		<div class="col-sm-20">
			<select name="nv3_shop" class="form-control">
				<!-- BEGIN: nv3_shop -->
				<option value="{NV3_SHOP.module_data}">{NV3_SHOP.module_data} ({NV3_SHOP.custom_title})</option>
				<!-- END: nv3_shop -->
			</select>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-4 control-label">Module news trên NukeViet 4</label>
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
			<input name="import" type="submit" value="Thực hiện nâng cấp" class="btn btn-primary">
		</div>
	</div>
</form>

<p style="color: #D30000">{ERR}</p>
<!-- END: main -->