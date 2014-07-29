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
Nếu có điều kiện viết lại các đoạn code
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