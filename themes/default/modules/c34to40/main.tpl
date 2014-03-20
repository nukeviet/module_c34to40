<!-- BEGIN: main -->

1) Cần kiểm tra lại tất cả các chỗ dùng SQL có câu lệnh: INSERT, UPDATE, DELETE: nếu câu lệnh nào cần xác định số kết quả trả về thì cần thay bằng thương thức PDO::exec
<br/>
<br/>
2) Kiểm tra lại tất cả các chỗ có //$xxx->closeCursor();, thay thế đúng bằng $result->closeCursor(); tùy theo các đoạn code viết bên trên
<br/>
<br/>
3) Kiểm tra lại tất cả các chỗ có $db->sql_affectedrows() Nếu trên đó có câu lệnh $db->query( $sql ); thì hãy xóa dòng $db->query( $sql ); và sửa  $db->sql_affectedrows() thành $db->exec( $sql );

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

8) Để module chạy được trên các loại CSDL khác cần bỏ dầu nháy ` trong các đoạn truy vấn CSDL 
<br/>
<br/>

{DATA}

<!-- END: main -->