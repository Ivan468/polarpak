<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  download_messages.php                                    ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

$messages = array(
	// download messages
	"DOWNLOAD_WRONG_PARAM" => "Sai tham số tải xuống",
	"DOWNLOAD_MISS_PARAM" => "Sai tham số tải xuống",
	"DOWNLOAD_INACTIVE" => "Chức năng tải xuống chưa kích hoạt.",
	"DOWNLOAD_EXPIRED" => "Khoảng thời gian tải xuống bị hết hạn.",
	"DOWNLOAD_LIMITED" => "Bạn đã tải quá số lần quy định.",
	"DOWNLOAD_PATH_ERROR" => "Đường dẫn không thể tìm thấy.",
	"DOWNLOAD_RELEASE_ERROR" => "Phiên bản phát hành không thể tìm thấy.",
	"DOWNLOAD_USER_ERROR" => "Chỉ có thành viên đăng ký mới có thể tải xuống file này.",
	"ACTIVATION_OPTIONS_MSG" => "Kích hoạt những tùy chọn",
	"ACTIVATION_MAX_NUMBER_MSG" => "Số tối đa của những lần kích hoạt",
	"DOWNLOAD_OPTIONS_MSG" => "Tải xuống/ Tùy chọn phần mềm",
	"DOWNLOADABLE_MSG" => "Tải xuống (phần mềm)",
	"DOWNLOADABLE_DESC" => "để tải xuống sản phẩm bạn có thể mô tả rõ 'Khoảng thời gian tải xuống', 'Đường dẫn để tải xuống' và 'Kích hoạt những tùy chọn'",
	"DOWNLOAD_PERIOD_MSG" => "Khoảng thời gian tải xuống",
	"DOWNLOAD_PATH_MSG" => "Đường dẫn để tải tệp",
	"DOWNLOAD_PATH_DESC" => "bạn có thể thêm nhiều đường dẫn để phân chia",
	"UPLOAD_SELECT_MSG" => "Chọn tệp để tải lên và nhấn nút {button_name}.",
	"SPECIFY_PATH_FILE_MSG" => "Or please specify the path to your file and press 'Continue' button.",
	"UPLOADED_FILE_MSG" => "Tệp <b>{filename}</b> vừa được tải lên.",
	"UPLOAD_SELECT_ERROR" => "Xin vui lòng chọn tệp đầu tiên.",
	"UPLOAD_IMAGE_ERROR" => "Chỉ tệp dạng hình ảnh mới được cho phép.",
	"UPLOAD_FORMAT_ERROR" => "Loại tệp này không được phép.",
	"UPLOAD_SIZE_ERROR" => "Không cho phép tệp có dung lượng lớn hơn {fileszie}",
	"UPLOAD_DIMENSION_ERROR" => "Không cho phép tệp hình ảnh lớn hơn {dimension}.",
	"UPLOAD_CREATE_ERROR" => "Hệ thống không thể tạo tệp.",
	"UPLOAD_ACCESS_ERROR" => "Bạn không có quyền tải lên tệp dữ liệu.",
	"DELETE_FILE_CONFIRM_MSG" => "Bạn có muốn chắc xóa tệp dữ liệu này?",
	"NO_FILES_MSG" => "Không tìm thấy tệp dữ liệu",
	"SERIAL_GENERATE_MSG" => "Tạo số S/N",
	"SERIAL_DONT_GENERATE_MSG" => "Không tạo",
	"SERIAL_RANDOM_GENERATE_MSG" => "Không tạo số S/N ngẫu nhiên cho sản phẩm này",
	"SERIAL_FROM_PREDEFINED_MSG" => "Lấy số S/N từ danh sách tạo sẵn.",
	"SERIAL_PREDEFINED_MSG" => "Danh sách S/N tạo sẵn",
	"SERIAL_NUMBER_COLUMN" => "S/N",
	"SERIAL_USED_COLUMN" => "Sử dụng",
	"SERIAL_DELETE_COLUMN" => "Xóa",
	"SERIAL_MORE_MSG" => "Thêm số S/N",
	"SERIAL_PERIOD_MSG" => "Khoảng thời gian hiệu lực S/N",
	"DOWNLOAD_SHOW_TERMS_MSG" => "Hiển thị điều khoản và điều kiện",
	"DOWNLOAD_SHOW_TERMS_DESC" => "Để tải xuống sản phẩm này khách hàng xin đọc và đồng ý với các điều khoản và điều kiện của chúng tôi",
	"DOWNLOAD_TERMS_USER_ERROR" => "Để tải xuống sản phẩm này khách hàng xin đọc và đồng ý với các điều khoản và điều kiện của chúng tôi",

	"DOWNLOAD_TITLE_MSG" => "Download Title",
	"DOWNLOADABLE_FILES_MSG" => "Downloadable Files",
	"DOWNLOAD_INTERVAL_MSG" => "Download Interval",
	"DOWNLOAD_LIMIT_MSG" => "Downloads Limit",
	"DOWNLOAD_LIMIT_DESC" => "number of times file can be downloaded",
	"MAXIMUM_DOWNLOADS_MSG" => "Maximum Downloads",
	"PREVIEW_TYPE_MSG" => "Preview Type",
	"PREVIEW_TITLE_MSG" => "Preview Title",
	"PREVIEW_PATH_MSG" => "Path to Preview File",
	"PREVIEW_IMAGE_MSG" => "Preview Image",
	"MORE_FILES_MSG" => "More Files",
	"UPLOAD_MSG" => "Upload",
	"USE_WITH_OPTIONS_MSG" => "Use with options only",
	"PREVIEW_AS_DOWNLOAD_MSG" => "Preview as download",
	"PREVIEW_USE_PLAYER_MSG" => "Use player to preview",
	"PROD_PREVIEWS_MSG" => "Previews",

);
$va_messages = array_merge($va_messages, $messages);
