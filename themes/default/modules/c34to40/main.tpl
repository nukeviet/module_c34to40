<!-- BEGIN: main -->

1) Cần kiểm tra lại tất cả các chỗ dùng SQL có câu lệnh: INSERT, UPDATE, DELETE: nếu câu lệnh nào cần xác định số kết quả trả về thì nên thay bằng thương thức PDO::exec
<br/>
<br/>
2) Kiểm tra lại tất cả các chỗ có //$xxx-&gt;closeCursor();, thay thế đúng bằng $result-&gt;closeCursor(); tùy theo các đoạn code viết bên trên
<br/>
<br/>
3) Kiểm tra lại tất cả các chỗ có $db-&gt;sql_affectedrows() Nếu trên đó có câu lệnh $db-&gt;query( $sql ); thì hãy xóa dòng $db-&gt;query( $sql ); và sửa  $db-&gt;sql_affectedrows() thành $db-&gt;exec( $sql );
<br/>
<br/>

4) Tìm và thay thế các đoạn javascript sử dụng nv_ajax thay bằng ajax của jquery.
<br/>
Ví dụ:
<br/>
<pre>
	<code class="language-javascript">

function nv_del_content(id)
{
    if(confirm(nv_is_del_confirm[0]))
    {
        nv_ajax('post', script_name, nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=list&action=delete&listid=' + id + '&checkss=' + checkss, '', 'nv_del_content_result');
    }
    return false;
}

function nv_del_content_result(res)
{
    var r_split = res.split("_");
    if(r_split[0] == 'OK')
    {
        window.location.href = script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=list';
    }
    else if(r_split[0] == 'ERR')
    {
        alert(r_split[1]);
    }
    else
    {
        alert(nv_is_del_confirm[2]);
    }
    return false;
}

	</code>
</pre>
Cần được thay thành
<pre>
	<code class="language-javascript">

function nv_del_content(id)
{
    if(confirm(nv_is_del_confirm[0]))
    {
		$.post(script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=list&nocache=' + new Date().getTime(), 'action=delete&listid=' + id + '&checkss=' + checkss, function(res) {
		    var r_split = res.split("_");
		    if(r_split[0] == 'OK')
		    {
		        window.location.href = script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=list';
		    }
		    else if(r_split[0] == 'ERR')
		    {
		        alert(r_split[1]);
		    }
		    else
		    {
		        alert(nv_is_del_confirm[2]);
		    }
		    return false;
		});
    }
    return false;
}
	</code>

</pre>

<br/>
<br/>

<b>Nếu có điều kiện viết lại các đoạn code</b>
<br/>
<br/>
4) Không sử dụng SQL REPLACE, bởi Oracle không hỗ trợ REPLACE
<br/>
<br/>
5) sử dụng PDOStatement::rowCount
<br/>
<br/>

6) Nên thay thế  UNIX_TIMESTAMP() bằng biến NV_CURRENTTIME trong PHP
<br/>
<br/>

7) Để module chạy được trên các loại CSDL khác cần bỏ dầu nháy ` trong các đoạn truy vấn CSDL
<br/>
<br/>

8) Chức năng phân quyền đã thay đổi, cụ thể không còn hàm nv_set_allow mà thay vào đó là hàm nv_user_in_groups, cần xóa các trường who_view nếu có.
<br/>
<br/>

9) Chú ý đường dẫn trong admin. Ở phiên bản mới đã được thêm vào biến lang. Ví dụ
<pre>
	<code class="html">"{NV_BASE_ADMINURL}index.php?{NV_NAME_VARIABLE}=upload&amp;popup=1&amp;area=logo&amp;path={IMG_DIR}&amp;type=image"</code>
</pre>
Cần được thay thành
<pre>
	<code class="html">"{NV_BASE_ADMINURL}index.php?{NV_LANG_VARIABLE}={NV_LANG_DATA}&amp;{NV_NAME_VARIABLE}=upload&amp;popup=1&amp;area=logo&amp;path={IMG_DIR}&amp;type=image"</code>
</pre>

<p><strong>Chi tiết các file được thay đổi:</strong></p>
<pre>
	<code class="xml">{DATA}</code>
</pre>

<!-- END: main -->