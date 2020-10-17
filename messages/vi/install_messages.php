<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  install_messages.php                                     ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

$messages = array(
	// installation messages
	"INSTALL_TITLE" => "Cài đặt ViArt SHOP",

	"INSTALL_STEP_1_TITLE" => "Cài đặt: Bước 1",
	"INSTALL_STEP_1_DESC" => "Cám ơn bạn đã chọn ViArt SHOP. Để tiếp tục cài đặt, xin hãy điền chi  tiết yêu cầu dưới đây. Xin lưu ý rằng database của bạn phải tồn tại trong máy chủ. Nếu bạn chọn cài đặt database bằng MS Access với kết nối ODBC xin hãy tạo DSN đầu tiên trước khi tiếp tục quá trình này.",
	"INSTALL_STEP_2_TITLE" => "Cài đặt: Bước 2",
	"INSTALL_STEP_2_DESC" => "",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "Cài đặt: Bước 3",
	"INSTALL_STEP_3_DESC" => "Xin hãy chọn kiểu hiển thị trang web. Bạn sẽ thay đổi nó sau này.",
	"INSTALL_FINAL_TITLE" => "Cài đặt: Hoàn tất",
	"SELECT_DATE_TITLE" => "Chọn định dạng ngày tháng",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "Các thiết lập database",
	"DB_PROGRESS_MSG" => "Tạo cấu trúc database",
	"SELECT_PHP_LIB_MSG" => "Chọn thư viện PHP",
	"SELECT_DB_TYPE_MSG" => "Chọn kiểu database",
	"ADMIN_SETTINGS_MSG" => "Các thiết lập về quản trị",
	"DATE_SETTINGS_MSG" => "Các định dạng ngày tháng",
	"NO_DATE_FORMATS_MSG" => "Không có sẵn các định dạng ngày tháng",
	"INSTALL_FINISHED_MSG" => "Tại thời điểm này quá trình cài đặt cơ bản đã hoàn thành. Xin vui lòng kiểm các thiết lập ở khu vực quản trị và những thay đổi bắt buộc ",
	"ACCESS_ADMIN_MSG" => "Để truy cập vào khu vực quản trị chọn ở đây",
	"ADMIN_URL_MSG" => "Đường dẫn khu vực quản trị",
	"MANUAL_URL_MSG" => "Tự vào đường dẫn",
	"THANKS_MSG" => "Cám ơn bạn đã chọn <b>ViArt SHOP</b>.",

	"DB_TYPE_FIELD" => "Kiểu database",
	"DB_TYPE_DESC" => "Xin vui lòng chọn <b>kiểu database</b> mà bạn sử dụng. Nếu bạn sử dụng SQL Server hoặc MS Access, xin vui lòng chọn ODBC",
	"DB_PHP_LIB_FIELD" => "Thư viện PHP",
	"DB_HOST_FIELD" => "Hostname",
	"DB_HOST_DESC" => "Xin vui lòng điền vào <b>tên</b> hoặc <b>địa chỉ IP của máy chủ</b> mà database của ViArt sẽ chạy. Nếu bạn sử dụng database trên máy chủ nội bộ xin vui lòng để trống hoặc gõ <b>localhost</b>. Nếu bạn cài đặt trên máy chủ hosting xin vui lòng xem lại tài liệu hoặc liên hệ với bộ phận hỗ trợ dịch vụ hosting.",
	"DB_PORT_FIELD" => "Cổng",
	"DB_NAME_FIELD" => "Tên database/DSN",
	"DB_NAME_DESC" => "Nếu bạn sử dụng database MySQL hoặc PostgreSQL xin vui lòng gõ vào <b>tên database</b>. Database này phải tồn tại sẵn. Nếu bạn cài đặt ViArt SHOP với mục đích test trên máy nội bộ bạn có thể điền vào <b>test</b>. Nếu không bạn có thể gõ vào <b>Viart</b> và sử dụng nó. Nếu bạn sử dụng MS Access hoặc SQL Server xin hãy điền vào <b>tên DSN</b> mà bạn thiết lập trong kết nối ODBC ở khu vực Control Panel.",
	"DB_USER_FIELD" => "Tên truy cập",
	"DB_PASS_FIELD" => "Mật mã",
	"DB_USER_PASS_DESC" => "<b>Tên truy cập<b> và <b>Mật mã</b> - Xin vui lòng điền vào tên truy cập và mật mã mà bạn sử dụng để truy cập được database. Nếu bạn dùng với mục đích để test xin vui lòng gõ vào \"<b>root</b>\" và mật mã để trống. Xin lưu ý đừng dùng nó cho mục đích đưa lên Internet vì nó không an toàn về bảo mật.",
	"DB_PERSISTENT_FIELD" => "Persistent Connection",
	"DB_PERSISTENT_DESC" => "để sử dụng kiểu kết nối persistent với MySQL và PostgresSQL, chọn ở ô này. Nếu bạn không chắc chắn, xin hãy bỏ qua.",
	"DB_CREATE_DB_FIELD" => "Tạo DB",
	"DB_CREATE_DB_DESC" => "để tạo database, nếu được xin chọn ô này. Chức năng chỉ hoạt động với kiểu dữ liệu MySQL và Postgre",
	"DB_POPULATE_FIELD" => "Đưa vào dữ liệu mẫu",
	"DB_POPULATE_DESC" => "Để tạo cấu trúc database xin chọn vào ô này",
	"DB_TEST_DATA_FIELD" => "Kiểm tra dữ liệu",
	"DB_TEST_DATA_DESC" => "để thêm vào một vài dữ liệu để kiểm tra xin chọn ô này",
	"ADMIN_EMAIL_FIELD" => "Email người quản trị",
	"ADMIN_LOGIN_FIELD" => "Tên truy cập admin",
	"ADMIN_PASS_FIELD" => "Mật mã của admin",
	"ADMIN_CONF_FIELD" => "Xác nhận lại mật mã",
	"DATETIME_SHOWN_FIELD" => "Định dạng giờ (trên trang web)",
	"DATE_SHOWN_FIELD" => "Định dạng ngày tháng (trên trang web)",
	"DATETIME_EDIT_FIELD" => "Định dạng giờ (trên trang quản trị)",
	"DATE_EDIT_FIELD" => "Định dạng ngày tháng (trên trang quản trị)",
	"DATE_FORMAT_COLUMN" => "Định dạng ngày",

	"DB_LIBRARY_ERROR" => "Các hàm chức năng PHP cho {db_library} không được định nghĩa. Xin vui lòng kiểm tra lại thiết lập database trong tệp cấu hình php.ini.",
	"DB_CONNECT_ERROR" => "Không thể kết nối database. Xin vui lòng kiểm tra tham số database.",
	"INSTALL_FINISHED_ERROR" => "Quá trình cài đặt đã hoàn tất.",
	"WRITE_FILE_ERROR" => "Không có quyền ghi tệp dữ liệu <b>'includes/var_definition.php'</b>. Xin vui lòng thay đổi quyền truy cập trước khi thực hiện bước này.",
	"WRITE_DIR_ERROR" => "Không có quyền ghi thư mục <b>'includes/'</b>. Xin vui lòng thay đổi quyền cho thư mục trước khi tiếp tục.",
	"DUMP_FILE_ERROR" => "Tệp '{file_name}' không tìm thấy.",
	"DB_TABLE_ERROR" => "Table '{table_name}' không tìm thấy. Xin vui lòng tạo database với dữ liệu cần thiết.",
	"TEST_DATA_ERROR" => "Kiểm tra <b>{POPULATE_DB_FIELD}</b> trước khi tạo bảng table khi kiểm tra dữ liệu",
	"DB_HOST_ERROR" => "Hostname mà bạn mô tả không thể tìm thấy.",
	"DB_PORT_ERROR" => "Không thể kết nối máy chủ database với cổng hiện tại.",
	"DB_USER_PASS_ERROR" => "Tên truy cập và mật mã bạn mô tả không đúng.",
	"DB_NAME_ERROR" => "Các thiết lập đăng nhập là đúng, nhưng database '{db_name}' không thể tìm thấy.",

	// upgrade messages
	"UPGRADE_TITLE" => "Nâng cấp ViArt SHOP",
	"UPGRADE_NOTE" => "Lưu ý: Xin hãy lưu ý rằng chắc chắn backup database trước khi tiếp tục.",
	"UPGRADE_AVAILABLE_MSG" => "Đã sẵn sàng cập nhật database",
	"UPGRADE_BUTTON" => "Cập nhật database đến {version_number} ngay",
	"CURRENT_VERSION_MSG" => "Phiên bản hiện tại đã cài đặt",
	"LATEST_VERSION_MSG" => "Phiên bản mới để cài đặt",
	"UPGRADE_RESULTS_MSG" => "Kết quả nâng cấp",
	"SQL_SUCCESS_MSG" => "Truy vấn SQL thành công",
	"SQL_FAILED_MSG" => "Truy vấn SQL bị lỗi",
	"SQL_TOTAL_MSG" => "Tất cả truy vấn SQL thi hành",
	"VERSION_UPGRADED_MSG" => "Database của bạn được nâng cấp đến",
	"ALREADY_LATEST_MSG" => "Bạn đã cập nhật phiên bản mới nhất",
	"DOWNLOAD_NEW_MSG" => "Phiên bản mới được kiểm tra",
	"DOWNLOAD_NOW_MSG" => "Tải xuống phiên bản {version_number} ngay.",
	"DOWNLOAD_FOUND_MSG" => "Chúng tôi đã kiểm tra phiên bản {version_number} đã sẵn sàng cho việc tải xuống. Nhấn chọn đường link dưới đây để tải xuống. Sau khi hoàn tất tải xuống và thay thế tệp dữ liệu, nhớ đừng quên chạy bản cập nhật trở lại.",
	"NO_XML_CONNECTION" => "Cảnh báo! Không có kết nốisẵn đến địa chỉ 'http://www.viart.com/'!",

	"END_USER_LICENSE_AGREEMENT_MSG" => "End User License Agreement",
	"AGREE_LICENSE_AGREEMENT_MSG" => "I have read and agree to the License Agreement",
	"READ_LICENSE_AGREEMENT_MSG" => "Click here to read license agreement",
	"LICENSE_AGREEMENT_ERROR" => "Please read and agree to the License Agreement before proceeding.",

);
$va_messages = array_merge($va_messages, $messages);
