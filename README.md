# Hướng dẫn nâng cấp hệ thống từ NukeViet 3 lên NukeViet 4 RC3

(Module này chưa tương thích với NukeViet 4 Final, bạn có thể kiểm tra lại vào 17h ngày 20/05/2016 )

Ở phiên bản NukeViet 4, nhân hệ thống thay đổi hoàn toàn so với NukeViet 3, do đó người dùng cần thay mới hoàn toàn code, không hỗ trợ nâng cấp module mà chỉ hỗ trợ chuyển đỗi dữ liệu các module hệ thống.

Các bước thực hiện:
- Cài đặt NukeViet 4 (phiên bản mới nhất) và đăng nhập quản trị tối cao.
- Import các bảng dữ liệu module NukeViet 3 vào CSDL chứa các bảng của NukeViet 4
- Cài đặt module c34to40 bằng gói cài đặt (Hoặc download trên https://github.com/nukeviet/module_c34to40, giải nén và copy tương ứng với cấu trúc của NukeViet 4)
- Mở modules/c34to40/function.php. Tìm đến dòng define( 'NV3_PREFIX', 'nv3' ); và thay ‘nv3’ bằng tiếp đầu tố bảng dữ liệu NukeViet 3 của bạn.

## 1. Nâng cấp dữ liệu module Users (Tài khoản)
- Xóa hết tất cả tài khoản thành viên (nếu có), chỉ dữ lại tài khoản quản trị tối cao.
- Truy cập đường dẫn http:/domain/c34to40/users/
- Chọn module muốn nâng cấp (users), sau đó nhấn “Thực hiện nâng cấp”
- Đợi đến khi nhận được thông báo thành công.
- Cập nhật lại biến $global_config['sitekey'] trong file config.php từ site cũ sang, nếu không làm việc này các tài khoản sẽ báo sai mật khẩu.

## 2. Nâng cấp dữ liệu module News (Tin tức)
- Xóa hết tất cả dữ liệu module news hoặc module ảo của news (nếu có) (Có thể thực hiện thao tác “Cài lại”)
- Copy thư mục uploads/news (hoặc uploads/module-ao-news) ở NukeViet 3 vào thư mục tương ứng của NukeViet 4
- Truy cập đường dẫn http:/domain/c34to40/news/
- Chọn module lấy dữ liệu (Module của NukeViet 3), sau đó chọn module muốn nâng cấp (ở NukeViet 4), sau đó nhấn “Thực hiện nâng cấp”
- Đợi đến khi nhận được thông báo thành công.

## 3. Nâng cấp dữ liệu module Download
- Xóa hết tất cả dữ liệu module download hoặc module ảo của download (nếu có) (Có thể thực hiện thao tác “Cài lại”)
- Copy thư mục uploads/download (hoặc uploads/module-ao-download) ở NukeViet 3 vào thư mục tương ứng của NukeViet 4
- Truy cập đường dẫn http:/domain/c34to40/download/
- Chọn module lấy dữ liệu (Module của NukeViet 3), sau đó chọn module muốn nâng cấp (ở NukeViet 4), sau đó nhấn “Thực hiện nâng cấp”
- Đợi đến khi nhận được thông báo thành công.

## 4. Nâng cấp dữ liệu module Shops
- Xóa hết tất cả dữ liệu module shops hoặc module ảo của shops (nếu có) (Có thể thực hiện thao tác “Cài lại”)
- Copy thư mục uploads/shops (hoặc uploads/module-ao-shops) ở NukeViet 3 vào thư mục tương ứng của NukeViet 4
- Truy cập đường dẫn http:/domain/c34to40/shop/
- Chọn module lấy dữ liệu (Module của NukeViet 3), sau đó chọn module muốn nâng cấp (ở NukeViet 4), sau đó nhấn “Thực hiện nâng cấp”
- Đợi đến khi nhận được thông báo thành công.

## 5. Nâng cấp các loại dữ liệu khác
- Chuyển dữ liệu bảng counter (lượt truy cập)
- Chuyển dữ liệu bảng searchkeys (từ khóa tìm kiếm)
- Chuyển dữ liệu bảng referer_stats (số liệu thống kê)
- Truy cập đường dẫn http:/domain/c34to40/other_db/
- Đợi đến khi nhận được thông báo thành công.

## Các thông tin khác:
- Hướng dẫn nâng cấp module: https://github.com/nukeviet/module_c34to40/wiki/H%C6%B0%E1%BB%9Bng-d%E1%BA%ABn-n%C3%A2ng-c%E1%BA%A5p-module-NukeViet-3-l%C3%AAn-NukeViet-4
- Thảo luận tại diễn đàn: http://forum.nukeviet.vn/viewtopic.php?f=173&t=35858
- Thông báo lỗi: https://github.com/nukeviet/module_c34to40/issues
