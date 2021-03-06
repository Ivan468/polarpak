<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  messages.php                                             ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

$messages = array(
	"CHARSET" => "utf-8",
	// date messages
	"YEAR_MSG" => "年",
	"YEARS_QTY_MSG" => "{quantity} 年",
	"MONTH_MSG" => "月",
	"MONTHS_QTY_MSG" => "{quantity} 月",
	"DAY_MSG" => "日",
	"DAYS_MSG" => "日",
	"DAYS_QTY_MSG" => "{quantity} 日",
	"HOUR_MSG" => "时",
	"HOURS_QTY_MSG" => "{quantity} 小时",
	"MINUTE_MSG" => "分",
	"MINUTES_QTY_MSG" => "{quantity} 分",
	"SECOND_MSG" => "秒",
	"SECONDS_QTY_MSG" => "{quantity} 秒",
	"WEEK_MSG" => "星期",
	"WEEKS_QTY_MSG" => "{quantity} 星期",
	"TODAY_MSG" => "今日",
	"YESTERDAY_MSG" => "昨天",
	"LAST_7DAYS_MSG" => "过去7天",
	"THIS_MONTH_MSG" => "本月",
	"LAST_MONTH_MSG" => "上月",
	"THIS_QUARTER_MSG" => "本季度",
	"THIS_YEAR_MSG" => "本年度",
	"CURRENT_DATE_MSG" => "当前日期",
	"CURRENT_TIME_MSG" => "当前时间",
	"DATE_MSG" => "日期",
	"TIME_MSG" => "Time",

	// months
	"JANUARY" => "一月",
	"FEBRUARY" => "二月",
	"MARCH" => "三月",
	"APRIL" => "四月",
	"MAY" => "五月",
	"JUNE" => "六月",
	"JULY" => "七月",
	"AUGUST" => "八月",
	"SEPTEMBER" => "九月",
	"OCTOBER" => "十月",
	"NOVEMBER" => "十一月",
	"DECEMBER" => "十二月",

	"JANUARY_SHORT" => "一月",
	"FEBRUARY_SHORT" => "二月",
	"MARCH_SHORT" => "三月",
	"APRIL_SHORT" => "四月",
	"MAY_SHORT" => "五月",
	"JUNE_SHORT" => "六月",
	"JULY_SHORT" => "七月",
	"AUGUST_SHORT" => "八月",
	"SEPTEMBER_SHORT" => "九月",
	"OCTOBER_SHORT" => "十月",
	"NOVEMBER_SHORT" => "十一月",
	"DECEMBER_SHORT" => "十二月",

	// weekdays
	"SUNDAY" => "星期日",
	"MONDAY" => "星期一",
	"TUESDAY" => "星期二",
	"WEDNESDAY" => "星期三",
	"THURSDAY" => "星期四",
	"FRIDAY" => "星期五",
	"SATURDAY" => "星期六",

	"SUNDAY_SHORT" => "日",
	"MONDAY_SHORT" => "一",
	"TUESDAY_SHORT" => "二",
	"WEDNESDAY_SHORT" => "三",
	"THURSDAY_SHORT" => "四",
	"FRIDAY_SHORT" => "五",
	"SATURDAY_SHORT" => "六",

	// validation messages
	"REQUIRED_MESSAGE" => "<b>{field_name}</ b>的要求",
	"INVALID_CHARS_MESSAGE" => "<b>{field_name}</b> contains invalid characters: <b>&lt;</b> <b>&gt;</b>. Please remove them to continue.",
	"UNIQUE_MESSAGE" => "值的领域<b>{field_name} </ b>是已经在数据库中",
	"VALIDATION_MESSAGE" => "验证失败领域<b>{字段名称}</ b>",
	"MATCHED_MESSAGE" => "<b>的{field_one}</ b>和<b>的{field_two}</ b>不匹配",
	"INSERT_ALLOWED_ERROR" => "很抱歉，但插入操作允许你",
	"UPDATE_ALLOWED_ERROR" => "很抱歉，但是更新操作允许你",
	"DELETE_ALLOWED_ERROR" => "很抱歉，但删除操作允许你",
	"ALPHANUMERIC_ALLOWED_ERROR" => "只有字母数字字符，连字符和下划线允许为1场<b>{FIELD_NAME} </ b>",
	"NAME_REGEXP_ERROR" => "Only letters, numeric characters, hyphen and underscore are allowed for field <b>{field_name}</b>",
	"CORRECT_ERRORS_BELOW_MSG" => "请更正以下错误，然后再继续。",
	"CORRECT_HIGHLIGHTED_FIELDS_MSG" => "Please fill in all highlighted fields with correct values.",
	"SIGN_IN_FIRST_ERROR" => "您需要先登录，然后再继续。",

	"INCORRECT_DATE_MESSAGE" => "<b> {field_name} </ b>有不正确的日期值。使用日历日期",
	"INCORRECT_MASK_MESSAGE" => "<b> {field_name}</ b>与掩码不匹配。使用下面的'<b> {field_mask} </ b>",
	"INCORRECT_EMAIL_MESSAGE" => "无效的电子邮件格式的字段{field_name}",
	"INCORRECT_VALUE_MESSAGE" => "不正确的值，在现场<b>{field_name} </ b>",

	"MIN_VALUE_MESSAGE" => "域{field_name}中的值不能小于{min_value}",
	"MAX_VALUE_MESSAGE" => "领域<B>中的值{field_name}</ b>可以不大于{max_value}",
	"MIN_LENGTH_MESSAGE" => "<b>{field_name} </ b>可以不低于{min_length}符号在现场长度",
	"MAX_LENGTH_MESSAGE" => "字段的长度，以<B>{field_name}</ b>可以不大于{max_length}符号",
	"MIN_MAX_VALUE_MSG" => "Please enter a value between {min_value} and {max_value}.",


	"FILE_PERMISSION_MESSAGE" => "不要有可写权限的文件<b>\"{文件名}\"</ b>。在继续之前，请更改文件权限",
	"FOLDER_PERMISSION_MESSAGE" => "不要有写入权限的文件夹<b>\"{文件夹}</ b>。在继续之前，请更改文件夹的权限",
	"INVALID_EMAIL_MSG" => "你的电子邮件是无效的。",
	"DATABASE_ERROR_MSG" => "数据库发生错误",
	"BLACK_IP_MSG" => "这个动作是不允许从您的主机。",
	"BANNED_CONTENT_MSG" => "很抱歉，所提供的内容中包含有非法的声明。",
	"ERRORS_MSG" => "错误",
	"REGISTERED_ACCESS_MSG" => "只有注册用户才可以访问此选项。",
	"SELECT_FROM_LIST_MSG" => "从列表中选择",

	// titles 
	"TOP_RATED_TITLE" => "最高评分",
	"TOP_VIEWED_TITLE" => "观看最多",
	"RECENTLY_VIEWED_TITLE" => "最近浏览",
	"HOT_TITLE" => "热",
	"LATEST_TITLE" => "最新",
	"CONTENT_TITLE" => "内容",
	"RELATED_TITLE" => "连带",
	"SEARCH_TITLE" => "搜索",
	"ADVANCED_SEARCH_TITLE" => "高级搜索",
	"LOGIN_TITLE" => "用户登录",
	"CATEGORIES_TITLE" => "分类",
	"MANUFACTURERS_TITLE" => "制造商",
	"SPECIAL_OFFER_TITLE" => "特价",
	"NEWS_TITLE" => "消息",
	"EVENTS_TITLE" => "活动",
	"PROFILE_TITLE" => "外貌",
	"USER_HOME_TITLE" => "首页",
	"DOWNLOAD_TITLE" => "下载",
	"FAQ_TITLE" => "常问问题",
	"POLL_TITLE" => "投票",
	"HOME_PAGE_TITLE" => "首页",
	"CURRENCY_TITLE" => "货币",
	"SUBSCRIBE_TITLE" => "订阅",
	"UNSUBSCRIBE_TITLE" => "退订",
	"UPLOAD_TITLE" => "上载",
	"ADS_TITLE" => "广告",
	"ADS_COMPARE_TITLE" => "广告比较",
	"ADS_SELLERS_TITLE" => "卖家",
	"AD_REQUEST_TITLE" => "报价/向卖家提问",
	"LANGUAGE_TITLE" => "语言",
	"MERCHANTS_TITLE" => "招商",
	"PREVIEW_TITLE" => "预览",
	"ARTICLES_TITLE" => "用品",
	"SITE_MAP_TITLE" => "网站地图",
	"LAYOUTS_TITLE" => "布局",
	"LINKS_TITLE" => "链接",
	"PROFILES_TITLE" => "Profiles",
	"DATING_TITLE" => "约会",
	"RANDOM_TITLE" => "Random",
	"AUTHORS_MSG" => "作者",
	"AUTHOR_MSG" => "Author",
	"ALBUMS_MSG" => "Albums",
	"ALBUM_MSG" => "Album",

	// menu items
	"MENU_MSG" => "菜单",
	"MENU_ABOUT" => "关于我们",
	"MENU_ACCOUNT" => "您的帐户",
	"MENU_BASKET" => "您的购物车",
	"MENU_CONTACT" => "联系我们",
	"MENU_DOCUMENTATION" => "文档",
	"MENU_DOWNLOADS" => "下载",
	"MENU_EVENTS" => "事件",
	"MENU_FAQ" => "常问问题",
	"MENU_FORUM" => "座谈会",
	"MENU_HELP" => "帮助",
	"MENU_HOME" => "首页",
	"MENU_HOW" => "如何选购",
	"MENU_MEMBERS" => "成员",
	"MENU_MYPROFILE" => "我的个人资料",
	"MENU_NEWS" => "消息",
	"MENU_PRIVACY" => "隐私",
	"MENU_PRODUCTS" => "产品",
	"MENU_REGISTRATION" => "注册",
	"MENU_SHIPPING" => "航运",
	"MENU_SIGNIN" => "登录",
	"MENU_SIGNOUT" => "退出",
	"MENU_SUPPORT" => "帮助",
	"MENU_USERHOME" => "用户家",
	"MENU_ADS" => "分类广告",
	"MENU_ADMIN" => "管理",
	"MENU_KNOWLEDGE" => "知识库",
	"NAVIGATION_BAR_MSG" => "Navigation Bar",
	"MAIN_PAGES_MSG" => "主页",

	// main terms
	"NO_MSG" => "没有",
	"YES_MSG" => "是",
	"NOT_AVAILABLE_MSG" => "N/A",
	"MORE_MSG" => "更多...",
	"LESS_MSG" => "less",
	"SHOW_DETAILS_MSG" => "Show details",
	"HIDE_DETAILS_MSG" => "Hide details",
	"READ_MORE_MSG" => "读更多...",
	"ADD_MORE_MSG" => "Add more",
	"CLICK_HERE_MSG" => "请点击这里",
	"ENTER_YOUR_MSG" => "请输入您的",
	"CHOOSE_A_MSG" => "选择",
	"PLEASE_CHOOSE_MSG" => "请选择",
	"SELECT_MSG" => "选择",
	"PLEASE_SELECT_MSG" => "Please select",
	"DATE_FORMAT_MSG" => "使用的格式为<b>{date_format}</ b>",
	"NEXT_PAGE_MSG" => "下面",
	"PREV_PAGE_MSG" => "过急的",
	"FIRST_PAGE_MSG" => "最初",
	"LAST_PAGE_MSG" => "最后",
	"OF_PAGE_MSG" => "的",
	"TOP_CATEGORY_MSG" => "顶部",
	"SEARCH_IN_CURRENT_MSG" => "当前类别",
	"SEARCH_IN_ALL_MSG" => "所有分类",
	"FOUND_IN_MSG" => "发现",
	"TOTAL_VIEWS_MSG" => "数",
	"VOTES_MSG" => "投票",
	"TOTAL_VOTES_MSG" => "总票数",
	"TOTAL_POINTS_MSG" => "总积分",
	"VIEW_RESULTS_MSG" => "查看结果",
	"PREVIOUS_POLLS_MSG" => "以前的投票",
	"AMOUNT_MSG" => "量",
	"BALANCE_MSG" => "Balance",
	"TOTAL_MSG" => "总",
	"CLOSED_MSG" => "关闭",
	"CLOSE_WINDOW_MSG" => "关闭窗口",
	"ASTERISK_MSG" => "星号（<span class=\"required\">*</ span>） - 必填字段",
	"PROVIDE_INFO_MSG" => "请用红色的章节中提供的信息，然后单击\"{button_name}\"",
	"FOUND_ARTICLES_MSG" => "我们发现<b> {found_records}</ b>的文章相匹配的项（s）\"<b> {SEARCH_STRING} </ b>",
	"NO_ARTICLE_MSG" => "这个ID是不是文章",
	"NO_ARTICLES_MSG" => "没有发现任何文章",
	"NOTES_MSG" => "笔记",
	"KEYWORDS_MSG" => "关键词",
	"TAGS_MSG" => "Tags",
	"TAG_MSG" => "Tag",
	"LINK_URL_MSG" => "链接",
	"DOWNLOAD_URL_MSG" => "下载",
	"SUBSCRIBE_FORM_MSG" => "为了让我们的通讯，请输入您的电子邮件地址，在下面的框中，然后按\"{button_name}\"按钮。",
	"UNSUBSCRIBE_FORM_MSG" => "请输入您的电子邮件地址在下面的框中，然后按\"{button_name}\"按钮。",
	"SUBSCRIBE_LINK_MSG" => "订阅",
	"UNSUBSCRIBE_LINK_MSG" => "退订",
	"SUBSCRIBED_MSG" => "恭喜！您现在的通讯完全认购的成员。",
	"ALREADY_SUBSCRIBED_MSG" => "您已经订阅我们的邮件列表。谢谢。",
	"UNSUBSCRIBED_MSG" => "您已成功取消订阅电子报。谢谢。",
	"UNSUBSCRIBED_ERROR_MSG" => "我们很抱歉，但我们可以在我们的数据库中没有找到匹配您的邮件地址，你可能已经取消订阅电子报。",
	"FORGOT_PASSWORD_MSG" => "忘记密码？",
	"FORGOT_PASSWORD_DESC" => "请输入您注册时使用您的电子邮件地址：",
	"FORGOT_EMAIL_ERROR_MSG" => "我们很抱歉，但我们不能在我们的数据库中找到匹配您的邮件地址。",
	"FORGOT_EMAIL_SENT_MSG" => "登录指令的详细信息已发送到您的电子邮件地址。",
	"RESET_PASSWORD_REQUIRE_MSG" => "错过一些必要的参数。",
	"RESET_PASSWORD_PARAMS_MSG" => "你提供的参数不匹配任何在数据库中。",
	"RESET_PASSWORD_EXPIRY_MSG" => "您所提供的复位代码已经过期。请申请一个新的代码来重设密码。",
	"RESET_PASSWORD_SAVED_MSG" => "您的新密码已成功保存。",
	"PRINTER_FRIENDLY_MSG" => "打印机友好",
	"PRINT_PAGE_MSG" => "打印此页",
	"ATTACHMENTS_MSG" => "附件",
	"ATTACHMENT_MSG" => "附件",
	"ATTACH_FILES_MSG" => "Attach Files",
	"VIEW_DETAILS_MSG" => "查看详细信息",
	"HTML_MSG" => "HTML",
	"PLAIN_TEXT_MSG" => "纯文本",
	"META_DATA_MSG" => "元数据",
	"META_TITLE_MSG" => "页面标题",
	"META_KEYWORDS_MSG" => "Meta关键字",
	"META_DESCRIPTION_MSG" => "Meta描述",
	"FRIENDLY_URL_MSG" => "友好的URL",
	"IMAGES_MSG" => "图片",
	"IMAGE_MSG" => "图像",
	"TINY_MSG" => "Tiny",
	"IMAGE_TINY_MSG" => "微小的图片",
	"IMAGE_TINY_ALT_MSG" => "微小的图像Alt",
	"SMALL_MSG" => "Small",
	"IMAGE_SMALL_MSG" => "小图片",
	"IMAGE_SMALL_DESC" => "显示在列表页",
	"IMAGE_SMALL_ALT_MSG" => "小图片的alt",
	"LARGE_MSG" => "Large",
	"IMAGE_LARGE_MSG" => "大图",
	"IMAGE_LARGE_DESC" => "详细信息页面上显示",
	"IMAGE_LARGE_ALT_MSG" => "大图片的alt",
	"SUPERSIZED_MSG" => "Supersized",
	"IMAGE_SUPER_MSG" => "超尺寸的图像",
	"IMAGE_SUPER_DESC" => "在新窗口中弹出图像",
	"IMAGE_POSITION_MSG" => "图片位置",
	"UPLOAD_IMAGE_MSG" => "图片位置...",
	"UPLOAD_FILE_MSG" => "上传文件",
	"SELECT_IMAGE_MSG" => "选择图片",
	"SELECT_FILE_MSG" => "选择\"文件\"",
	"UPLOAD_PHOTO_MSG" => "Upload Photo",
	"MANAGE_PHOTOS_MSG" => "Manage Photos",
	"SHOW_BELOW_PRODUCT_IMAGE_MSG" => "显示图像下面大的产品形象",
	"SHOW_IN_SEPARATE_SECTION_MSG" => "在不同的图像部分显示图像",
	"DRAG_FILES_MSG" => "Drag files here or select them manually",
	"DRAFT_MSG" => "Draft",
	"IS_APPROVED_MSG" => "已批准",
	"APPROVED_MSG" => "已批准",
	"NOT_APPROVED_MSG" => "不批准",
	"IS_ACTIVE_MSG" => "处于活动状态",
	"CATEGORY_MSG" => "类别",
	"SELECT_CATEGORY_MSG" => "选择产品类别",
	"REDIRECT_TO_CATEGORY_MSG" => "Redirect to Category",
	"DESCRIPTION_MSG" => "描述",
	"SHORT_DESCRIPTION_MSG" => "简单介绍",
	"FULL_DESCRIPTION_MSG" => "完整说明",
	"HIGHLIGHTS_MSG" => "亮点",
	"SPECIAL_OFFER_MSG" => "特别提供",
	"ARTICLE_MSG" => "文章",
	"OTHER_MSG" => "其他",
	"MEASUREMENT_UNITS_MSG" => "Measurement Units",
	"WIDTH_MSG" => "宽度",
	"HEIGHT_MSG" => "高度",
	"LENGTH_MSG" => "长度",
	"WEIGHT_MSG" => "重量",
	"WEIGHT_TOTAL_MSG" => "重量总",
	"CHARGEABLE_WEIGHT_MSG" => "chargeable weight",
	"ACTUAL_WEIGHT_MSG" => "Actual Weight",
	"QUANTITY_MSG" => "数量",
	"CALENDAR_MSG" => "日历",
	"FROM_DATE_MSG" => "从日期",
	"TO_DATE_MSG" => "至今",
	"TIME_PERIOD_MSG" => "时间段",
	"TIME_INTERVAL_MSG" => "Time Interval",
	"GROUP_BY_MSG" => "集团通过",
	"BIRTHDAY_MSG" => "生日",
	"BIRTH_DATE_MSG" => "出生日期",
	"BIRTH_YEAR_MSG" => "出生年份",
	"BIRTH_MONTH_MSG" => "出生月份",
	"BIRTH_DAY_MSG" => "出生日期",
	"STEP_NUMBER_MSG" => "的步骤{current_step}{total_steps}",
	"WHERE_STATUS_IS_MSG" => "其中，状态",
	"ID_MSG" => "身分证",
	"IDS_MSG" => "IDs",
	"IDS_DESC" => "appropriate ID numbers separated by comma",
	"ORDINAL_NUMBER_MSG" => "Ordinal Number",
	"ORDINAL_NUMBER_COLUMN" => "No.",
	"ORDER_MSG" => "顺序",
	"QTY_MSG" => "数量",
	"TYPE_MSG" => "类型",
	"NAME_MSG" => "名称",
	"TITLE_MSG" => "标题",
	"DEFAULT_MSG" => "缺省",
	"OPTIONS_MSG" => "选项",
	"EDIT_MSG" => "编辑",
	"MULTI_EDIT_MSG" => "Multi-edit",
	"CONFIRM_DELETE_MSG" => "你想删除这个记录名（record_name）}？",
	"DESC_MSG" => "Desc",
	"ASC_MSG" => "Asc",
	"ACTIVE_MSG" => "活性",
	"INACTIVE_MSG" => "迟顿",
	"DISABLED_MSG" => "Disabled",
	"EXPIRED_MSG" => "过期",
	"SAVED_MSG" => "Saved",
	"EMOTICONS_MSG" => "表情",
	"EMOTION_ICONS_MSG" => "表情图标（笑）",
	"VIEW_MORE_EMOTICONS_MSG" => "查看更多表情",
	"SITE_NAME_MSG" => "站点名称",
	"SITE_URL_MSG" => "网站的URL",
	"SHORT_NAME_MSG" => "Short Name",
	"SHORT_NAME_DESC" => "usually shown in table column and other places where there is no enough space to show the full name",
	"SORT_ORDER_MSG" => "排序顺序",
	"NEW_MSG" => "新",
	"USED_MSG" => "使用",
	"DEFECT_MSG" => "Defect",
	"REFURBISHED_MSG" => "翻新",
	"ADD_NEW_MSG" => "添加新",
	"SETTINGS_MSG" => "设置",
	"VIEW_MSG" => "看",
	"STATUS_MSG" => "状态",
	"NONE_MSG" => "无",
	"PRICE_MSG" => "价格",
	"SALE_PRICE_MSG" => "拍卖",
	"TEXT_MSG" => "文本",
	"WARNING_MSG" => "警告",
	"HIDDEN_MSG" => "隐",
	"CODE_MSG" => "法规",
	"LANGUAGE_MSG" => "语",
	"DEFAULT_VIEW_TYPE_MSG" => "默认视图类型",
	"CLICK_TO_OPEN_SECTION_MSG" => "点击打开部分",
	"CURRENCY_WRONG_VALUE_MSG" => "货币代码有错误的值。",
	"TRANSACTION_AMOUNT_DOESNT_MATCH_MSG" => "的<b>交易金额</ b>和<b>订单金额</ b>不匹配。",
	"STATUS_CANT_BE_UPDATED_MSG" => "订单＃{order_id}不能被更新的状态。",
	"CANT_FIND_STATUS_MSG" => "无法找到状态ID：{status_id}",
	"NOTIFICATION_SENT_MSG" => "通知发送",
	"AUTO_SUBMITTED_PAYMENT_MSG" => "自动提交支付",
	"FONT_METRIC_FILE_ERROR" => "无法包含字体度量文件",
	"PER_LINE_MSG" => "每行",
	"PER_LETTER_MSG" => "每信",
	"PER_NON_SPACE_LETTER_MSG" => "每个非空间信",
	"LETTERS_ALLOWED_MSG" => "字母允许",
	"LETTERS_ALLOWED_PER_LINEMSG" => "每行允许的信件",
	"RENAME_MSG" => "重命名",
	"IMAGE_FORMAT_ERROR_MSG" => "图片格式不支持GD库",
	"GD_LIBRARY_ERROR_MSG" => "GD库加载",
	"INVALID_CODE_MSG" => "无效的代码：",
	"INVALID_CODE_TYPE_MSG" => "无效的代码类型",
	"INVALID_FILE_EXTENSION_MSG" => "无效的文件扩展名：",
	"FOLDER_WRITE_PERMISSION_MSG" => "该文件夹不存在，或者您没有权限",
	"UNDEFINED_RECORD_PARAMETER_MSG" => "未定义的记录参数：<b> {parameter_name}</b>",
	"MAX_RECORDS_LIMITATION_MSG" => "你不能添加更多的不是<b>的{max_records}</ b>您的版本的{records_name}",
	"ACCESS_DENIED_MSG" => "你不能访问此部分。",
	"DELETE_RECORDS_BEFORE_PROCEED_MSG" => "请删除一些records_name}之前进行。",
	"FOLDER_DOESNT_EXIST_MSG" => "该文件夹不存在：",
	"FILE_DOESNT_EXIST_MSG" => "该文件不存在：",
	"PARSE_ERROR_IN_BLOCK_MSG" => "块解析错误：",
	"BLOCK_DOESNT_EXIST_MSG" => "块不存在：",
	"NUMBER_OF_ELEMENTS_MSG" => "的元素数",
	"MISSING_COMPONENT_MSG" => "缺少组件/参数。",
	"RELEASES_TITLE" => "发布",
	"DETAILED_MSG" => "详细",
	"LIST_MSG" => "表",
	"READONLY_MSG" => "只读",
	"CREDIT_MSG" => "信用",
	"ONLINE_MSG" => "在线",
	"OFFLINE_MSG" => "当前离线",
	"SMALL_CART_MSG" => "小型车",
	"NEVER_MSG" => "从来没有",
	"SEARCH_EXACT_WORD_OR_PHRASE" => "确切的字眼或短语",
	"SEARCH_ONE_OR_MORE" => "这些词中的一个或多个",
	"SEARCH_ALL" => "这一切的话",
	"RELATED_ARTICLES_MSG" => "相关文章",
	"RELATED_FORUMS_MSG" => "相关论坛",
	"APPEARANCE_MSG" => "出现",
	"FILTER_BY_MSG" => "Filter By",
	"FILTERED_BY_MSG" => "Filtered By",
	"FILTERS_MSG" => "过滤器",
	"SELECTED_FILTERS_MSG" => "Selected Filters:",
	"REMOVE_FILTER_MSG" => "删除过滤器",
	"BACK_MSG" => "Back",

	"RECORD_UPDATED_MSG" => "已成功更新",
	"RECORD_ADDED_MSG" => "已成功添加新的记录。",
	"RECORD_DELETED_MSG" => "记录已被成功删除。",
	"FAST_PRODUCT_ADDING_MSG" => "产品快速添加",
	"CHANGES_SAVED_MSG" => "更改已保存",
	"CURRENT_SUBSCRIPTION_MSG" => "目前认购",
	"SUBSCRIPTION_EXPIRATION_MSG" => "订阅过期日期",
	"UPGRADE_DOWNGRADE_MSG" => "升级/降级",
	"SUBSCRIPTION_MONEY_BACK_MSG" => "认购退款",
	"MONEY_TO_CREDITS_BALANCE_MSG" => "这笔钱将被添加到您的积分余额",
	"USED_VOUCHERS_MSG" => "使用优惠券",
	"VOUCHERS_TOTAL_MSG" => "优惠券总计",
	"SUBSCRIPTIONS_GROUPS_MSG" => "订阅组",
	"SUBSCRIPTIONS_GROUP_MSG" => "订阅集团",
	"SUBSCRIPTIONS_MSG" => "订阅",
	"SUBSCRIPTION_START_DATE_MSG" => "认购开始日期",
	"SUBSCRIPTION_EXPIRY_DATE_MSG" => "订阅过期日期",
	"RECALCULATE_COMMISSIONS_AND_POINTS_MSG" => "自动重新计算佣金，并指出此项目使用价值",
	"SUBSCRIPTION_PAGE_MSG" => "订阅页面",
	"SUBSCRIPTION_WITHOUT_REGISTRATION_MSG" => "用户可以添加订阅他的车没有登记",
	"SUBSCRIPTION_REQUIRE_REGISTRATION_MSG" => "用户必须拥有一个帐户，然后进入订阅页面",

	"MATCH_EXISTED_PRODUCTS_MSG" => "匹配现有的产品",
	"MATCH_BY_ITEM_CODE_MSG" => "产品代码",
	"MATCH_BY_MANUFACTURER_CODE_MSG" => "制造商代码",

	"ACCOUNT_SUBSCRIPTION_MSG" => "帐户订阅",
	"ACCOUNT_SUBSCRIPTION_DESC" => "要激活他的帐户的用户需要支付订阅费",
	"SUBSCRIPTION_CANCELLATION_MSG" => "订阅取消",
	"CONFIRM_CANCEL_SUBSCRIPTION_MSG" => "您确定要取消此订阅吗？",
	"CONFIRM_RETURN_SUBSCRIPTION_MSG" => "您确定要取消本次认购，并返回{credits_amount，以平衡吗？",
	"CANCEL_SUBSCRIPTION_MSG" => "取消订阅",
	"DONT_RETURN_MONEY_MSG" => "不要将钱",
	"RETURN_MONEY_TO_CREDITS_BALANCE_MSG" => "返回钱未使用的期间，信贷平衡",
	"UPGRADE_DOWNGRADE_TYPE_MSG" => "用户可以升级/降级，他的帐户类型",

	"PREDEFINED_TYPES_MSG" => "预定义类型",
	"SHIPPING_TAX_PERCENT_MSG" => "船舶吨税的百分比",
	"PACKAGES_NUMBER_MSG" => "包装件数",
	"PER_PACKAGE_MSG" => "每包",
	"CURRENCY_SHOW_DESC" => "用户可以选择将显示该货币价格",
	"CURRENCY_DEFAULT_SHOW_DESC" => "默认情况下，该货币价格",
	"UPDATE_STATUS_MSG" => "更新状态",

	"FIRST_CONTROLS_ARE_FREE_MSG" => "第一的{free_price_amount}控制是免费的",
	"FIRST_LETTERS_ARE_FREE_MSG" => "第一的{free_price_amount}个字母是免费的",
	"FIRST_NONSPACE_LETTERS_ARE_FREE_MSG" => "第一{free_price_amount}非空间字母是免费的",

	// email & SMS messages
	"EMAIL_NOTIFICATION_MSG" => "電郵通知",
	"EMAIL_NOTIFICATION_ADMIN_MSG" => "管理员电子邮件通知",
	"EMAIL_NOTIFICATION_USER_MSG" => "用户的电子邮件通知",
	"EMAIL_SEND_ADMIN_MSF" => "向管理员发送通知",
	"EMAIL_SEND_USER_MSG" => "为用户发送通知",
	"EMAIL_USER_IF_STATUS_MSG" => "发送邮件通知给用户时的状态",
	"EMAIL_TO_MSG" => "给",
	"EMAIL_TO_USER_DESC" => "客户的电子邮件的使用，如果是空的",
	"EMAIL_FROM_MSG" => "由",
	"EMAIL_CC_MSG" => "Cc",
	"EMAIL_BCC_MSG" => "Bcc",
	"EMAIL_REPLY_TO_MSG" => "回复",
	"EMAIL_RETURN_PATH_MSG" => "返回路径",
	"EMAIL_SUBJECT_MSG" => "主题",
	"EMAIL_MESSAGE_TYPE_MSG" => "消息类型",
	"EMAIL_MESSAGE_MSG" => "信息",
	"SMS_NOTIFICATION_MSG" => "SMS通知",
	"SMS_NOTIFICATION_ADMIN_MSG" => "管理员SMS通知",
	"SMS_NOTIFICATION_USER_MSG" => "用户短信通知",
	"SMS_SEND_ADMIN_MSG" => "发送短信通知管理员",
	"SMS_SEND_USER_MSG" => "发送短信通知用户",
	"SMS_USER_IF_STATUS_MSG" => "发送短信通知用户应用时的状态",
	"SMS_RECIPIENT_MSG" => "短信收件人",
	"SMS_RECIPIENT_ADMIN_DESC" => "管理员的手机号码",
	"SMS_RECIPIENT_USER_DESC" => "如果使用空\"的手机值",
	"SMS_ORIGINATOR_MSG" => "SMS发起人",
	"SMS_MESSAGE_MSG" => "SMS消息",

	// account messages
	"LOGIN_AS_MSG" => "您登录为{user_name}",
	"LOGIN_INFO_MSG" => "登录信息",
	"ACCESS_HOME_MSG" => "要访问您的帐户",
	"REMEMBER_ME_MSG" => "remember me",
	"REMEMBER_LOGIN_MSG" => "记住我的登录名和密码",
	"ENTER_LOGIN_MSG" => "输入您的登录名和密码才能继续",
	"LOGIN_PASSWORD_ERROR" => "是不正确的密码或登录",
	"ACCOUNT_APPROVE_ERROR" => "很抱歉，您的帐户尚未获得批准。",
	"ACCOUNT_EXPIRED_MSG" => "您的帐户已过期。",
	"SESSION_EXPIRED_MSG" => "您的会话已过期。",
	"NEW_PROFILE_ERROR" => "您没有权限开立一个帐户。",
	"EDIT_PROFILE_ERROR" => "您没有权限编辑该配置文件。",
	"CHANGE_DETAILS_MSG" => "更改您的详细资料",
	"CHANGE_DETAILS_DESC" => "更改的联系人或创建您的帐户时输入的登录信息。",
	"CHANGE_PASSWORD_MSG" => "更改密码",
	"CHANGE_PASSWORD_DESC" => "下面的链接将带你到该页面中，您可以更改您的密码",
	"LOG_IN_MSG" => "Log In",
	"SIGN_UP_MSG" => "现在就注册",
	"MY_ACCOUNT_MSG" => "我的帐户",
	"NEW_USER_MSG" => "新用户",
	"EXISTS_USER_MSG" => "现有用户",
	"EDIT_PROFILE_MSG" => "编辑个人资料",
	"PERSONAL_DETAILS_MSG" => "个人资料",
	"DELIVERY_DETAILS_MSG" => "交货详情",
	"SAME_DETAILS_MSG" => "如果寄送的地址与前面相同请在这里打勾<br>如果不同，请填以下信息",
	"DELIVERY_MSG" => "输送",
	"SUBSCRIBE_CHECKBOX_MSG" => "如果你想收到我们的电子报，请勾选此复选框。",
	"ADDITIONAL_DETAILS_MSG" => "其他详细资料",
	"GUEST_MSG" => "客人",
	"USER_MSG" => "用户",
	"SELECT_USER_MSG" => "选择用户",

	// ads messages
	"MY_ADS_MSG" => "我的广告",
	"MY_ADS_DESC" => "如果你有项目，你想卖，在这里放置广告。它的快速和容易放置广告。",
	"AD_GENERAL_MSG" => "一般广告信息",
	"ALL_ADS_MSG" => "所有广告",
	"AD_SELLER_MSG" => "卖家",
	"AD_START_MSG" => "日期开始",
	"AD_RUNS_MSG" => "天运行",
	"AD_QTY_MSG" => "数量",
	"AD_AVAILABILITY_MSG" => "可用性",
	"AD_COMPARED_MSG" => "允许广告比较",
	"AD_UPLOAD_MSG" => "图片位置...",
	"AD_LOCATION_MSG" => "地点",
	"AD_LOCATION_INFO_MSG" => "其他信息",
	"AD_PROPERTIES_MSG" => "广告属性",
	"AD_SPECIFICATION_MSG" => "广告规范",
	"AD_MORE_IMAGES_MSG" => "更多图片",
	"AD_IMAGE_DESC_MSG" => "图片说明",
	"AD_DELETE_CONFIRM_MSG" => "你想删除这个广告吗？",
	"AD_RUNNING_MSG" => "累",
	"AD_CLOSED_MSG" => "关闭",
	"AD_NOT_STARTED_MSG" => "未开始",
	"AD_NEW_ERROR" => "您没有权限创建一个新的广告。",
	"AD_EDIT_ERROR" => "您没有权限编辑这个广告。",
	"AD_DELETE_ERROR" => "您没有权限删除这个广告。",
	"NO_ADS_MSG" => "没有发现任何广告",
	"NO_AD_MSG" => "这个ID是此类别没有广告",
	"FOUND_ADS_MSG" => "我们发现<b> {found_records} </ b>广告相匹配的项（S）\"<B> {search_string} </ B>",
	"AD_OFFER_MESSAGE_MSG" => "呈",
	"AD_REQUEST_BUTTON" => "发送查询",
	"AD_SENT_MSG" => "您的出价已成功发送。",
	"ADS_SETTINGS_MSG" => "广告设置",
	"ADS_DAYS_MSG" => "天运行广告",
	"ADS_HOT_DAYS_MSG" => "运行天热广告",
	"AD_HOT_OFFER_MSG" => "热卖",
	"AD_HOT_ACTIVATE_MSG" => "将此广告热卖节",
	"AD_HOT_START_MSG" => "热收购建议开始日期",
	"AD_HOT_DESCRIPTION_MSG" => "热说明",
	"ADS_SPECIAL_DAYS_MSG" => "天运行特殊的广告",
	"AD_SPECIAL_OFFER_MSG" => "特价",
	"AD_SPECIAL_ACTIVATE_MSG" => "将此广告特别优惠部分",
	"AD_SPECIAL_START_MSG" => "特别优惠开始日期",
	"AD_SPECIAL_DESCRIPTION_MSG" => "特别优惠说明",
	"AD_CREDITS_BALANCE_ERROR" => "没有足够的学分，您需要在您的余额{more_credits}发布此广告。",
	"AD_CREDITS_MSG" => "广告积分",
	"ADS_SPECIAL_OFFERS_SETTINGS_MSG" => "广告特别优惠设置",
	//ad types
	"AD_PRODUCT_TYPE_MSG" => "产品",
	"AD_ACCESSORY_TYPE_MSG" => "附件",

	"EDIT_DAY_MSG" => "编辑日",
	"DAYS_PRICE_MSG" => "天价格",
	"ADS_PUBLISH_PRICE_MSG" => "价格发布广告",
	"DAYS_NUMBER_MSG" => "天数",
	"DAYS_TITLE_MSG" => "天标题",
	"USER_ADS_LIMIT_MSG" => "用户可以添加的广告数",
	"USER_ADS_LIMIT_DESC" => "离开这一领域的空白，如果你不希望限制​​数量的用户可以发布广告",
	"USER_ADS_LIMIT_ERROR" => "很抱歉，但你可以添加更多比{ads_limit}广告。",

	"ADS_SHOW_TERMS_MSG" => "显示条款及条件",
	"ADS_SHOW_TERMS_DESC" => "要提交的广告用户，阅读并同意我们的条款和条件",
	"ADS_TERMS_MSG" => "条款及条件",
	"ADS_TERMS_USER_DESC" => "我已阅读并同意条款和条件",
	"ADS_TERMS_USER_ERROR" => "要提交的广告，一定要仔细阅读并同意我们的条款和条件",
	"ADS_ACTIVATION_MSG" => "广告激活",
	"ACTIVATE_ADS_MSG" => "激活广告",
	"ACTIVATE_ADS_NOTE" => "自动启动，所有用户的广告，如果他的状态更改为\"已批准\"",
	"DEACTIVATE_ADS_MSG" => "停用广告",
	"DEACTIVATE_ADS_NOTE" => "自动关闭所有用户的广告，如果他的状态更改为\"不批准\"",

	"MIN_ALLOWED_ADS_PRICE_MSG" => "容许的最低价格",
	"MIN_ALLOWED_ADS_PRICE_NOTE" => "离开这一领域的空白，如果你不想限制较低的价格广告",
	"MAX_ALLOWED_ADS_PRICE_MSG" => "最大允许价格",
	"MAX_ALLOWED_ADS_PRICE_NOTE" => "离开这一领域的空白，如果你不希望以较高的价格限制广告",

	// search message
	"SEARCH_FOR_MSG" => "搜索",
	"SEARCH_IN_MSG" => "相关搜索",
	"SEARCH_TITLE_MSG" => "标题",
	"SEARCH_CODE_MSG" => "码",
	"SEARCH_SHORT_DESC_MSG" => "简短描述",
	"SEARCH_FULL_DESC_MSG" => "详细描述",
	"SEARCH_CATEGORY_MSG" => "搜索类别",
	"SEARCH_MANUFACTURER_MSG" => "生产厂家",
	"SEARCH_SELLER_MSG" => "卖家",
	"SEARCH_PRICE_MSG" => "价格范围",
	"SEARCH_WEIGHT_MSG" => "重量限制",
	"SEARCH_RESULTS_MSG" => "搜索结果",
	"FULL_SITE_SEARCH_MSG" => "全站搜索",
	"KEYWORDS_CRITERION_MSG" => "Keywords: <b>{keywords}</b>",
	"AUTHOR_CRITERION_MSG" => "Author: <b>{author}</b>",
	"CATEGORY_CRITERION_MSG" => "Category: <b>{category}</b>",
	"DATE_CRITERION_MSG" => "Date: <b>{date}</b>",
	"TYPE_CRITERION_MSG" => "Type: <b>{type}</b>",
	"COUNTRY_CRITERION_MSG" => "Country: <b>{country}</b>",
	"STATE_CRITERION_MSG" => "State: <b>{state}</b>",
	"POSTAL_CODE_CRITERION_MSG" => "Postal code: <b>{postal_code}</b>",
	"AGE_CRITERION_MSG" => "Age: <b>{age}</b>",

	// compare messages
	"COMPARE_MSG" => "比较",
	"COMPARE_REMOVE_MSG" => "清除",
	"COMPARE_REMOVE_HELP_MSG" => "点击这里删除这个产品比较表",
	"COMPARE_MIN_ALLOWED_MSG" => "您必须至少选择2个产品",
	"COMPARE_MAX_ALLOWED_MSG" => "您必须选择不超过5个产品",
	"COMPARE_PARAM_ERROR_MSG" => "比较参数有一个错误的值",

	// Tell a friend messages
	"TELL_FRIEND_TITLE" => "推荐给朋友",
	"TELL_FRIEND_SUBJECT_MSG" => "你的朋友给你这个链接。",
	"TELL_FRIEND_DEFAULT_MSG" => "嗨{friend_name} - 我想你可能会对兴趣在看到item_title的}在这个网站上item_url}",
	"TELL_YOUR_NAME_FIELD" => "你的名字",
	"TELL_YOUR_EMAIL_FIELD" => "您的电子邮件",
	"TELL_FRIENDS_NAME_FIELD" => "朋友的名字",
	"TELL_FRIENDS_EMAIL_FIELD" => "朋友的电子邮件",
	"TELL_COMMENT_FIELD" => "评论",
	"TELL_FRIEND_PRIVACY_NOTE_MSG" => "隐私声明：我们不会保存或重用你的，或者你的朋友的电子邮件地址（ES）用于任何其他目的。",
	"TELL_SENT_MSG" => "您的消息已发送成功！<br>感谢您！",
	"TELL_FRIEND_MESSAGE_MSG" => "我想你可能会对看到的的{item_title}在{item_url}\\ n\\ n{user_name}给你留了一张纸条：\\ n{user_comment}",
	"TELL_FRIEND_PARAM_MSG" => "介绍一个朋友的URL",
	"TELL_FRIEND_PARAM_DESC" => "\"推荐给朋友\"链接，如果它存在的用户增加了一个朋友的URL参数",
	"FRIEND_COOKIE_EXPIRES_MSG" => "朋友cookie过期",

	"CONTACT_US_TITLE" => "联系我们",
	"CONTACT_USER_NAME_FIELD" => "你的名字",
	"CONTACT_USER_EMAIL_FIELD" => "您的电邮地址",
	"CONTACT_SUMMARY_FIELD" => "行概述",
	"CONTACT_DESCRIPTION_FIELD" => "描述",
	"CONTACT_REQUEST_SENT_MSG" => "您的请求已成功发送。",

	// internal messages system
	"FOLDER_INBOX_MSG" => "Inbox",
	"FOLDER_SENT_MSG" => "Sent",
	"FOLDER_DRAFT_MSG" => "Draft",
	"FOLDER_TRASH_MSG" => "Trash",
	"READ_MSG" => "Read",
	"USER_NOT_FOUND_MSG" => "User {username} wasn't found.",
	"MESSAGE_SAVED_MSG" => "Your message has been saved.",
	"NO_MESSAGES_MSG" => "No messages were found",
	"SEND_ME_MESSAGE_MSG" => "Send me a Message",
	"WRITE_NEW_MESSAGE_MSG" => "Write a new Message",
	"REACH_BOX_LIMIT_MSG" => "You reach box limit - {box_limit} messages. Delete some messages before proceed.",
	"MESSAGES_DAY_LIMIT_ERROR" => "You can't send more than {day_limit} messages per day.",

	// buttons
	"GO_BUTTON" => "去",
	"CONTINUE_BUTTON" => "繼續",
	"BACK_BUTTON" => "以前",
	"NEXT_BUTTON" => "下一个",
	"PREV_BUTTON" => "前",
	"SIGN_IN_BUTTON" => "登录",
	"LOGIN_BUTTON" => "注册",
	"LOGOUT_BUTTON" => "登出",
	"SEARCH_BUTTON" => "搜索",
	"RATE_IT_BUTTON" => "速度吧！",
	"ADD_BUTTON" => "加",
	"UPDATE_BUTTON" => "更新",
	"APPLY_BUTTON" => "申请",
	"REGISTER_BUTTON" => "注册",
	"VOTE_BUTTON" => "投票",
	"CANCEL_BUTTON" => "取消",
	"CLEAR_BUTTON" => "肃清",
	"CLEAR_ALL_BUTTON" => "Clear All",
	"RESET_BUTTON" => "复位",
	"DELETE_BUTTON" => "删除",
	"DELETE_ALL_BUTTON" => "全部删除",
	"SUBSCRIBE_BUTTON" => "订阅",
	"UNSUBSCRIBE_BUTTON" => "退订",
	"SUBMIT_BUTTON" => "呈递",
	"UPLOAD_BUTTON" => "上载",
	"SEND_BUTTON" => "发送",
	"PREVIEW_BUTTON" => "预览",
	"FILTER_BUTTON" => "滤",
	"DOWNLOAD_BUTTON" => "下载",
	"REMOVE_BUTTON" => "清除",
	"EDIT_BUTTON" => "编辑",
	"CHANGE_BUTTON" => "改变",
	"SAVE_BUTTON" => "保存",
	"REPLY_BUTTON" => "回覆",
	"COMMENT_BUTTON" => "Comment",
	"ANSWER_BUTTON" => "Answer",
	"FORWARD_BUTTON" => "Forward",
	"PLACE_ORDER_BUTTON" => "Place Order",
	"REQUEST_QUOTE_BUTTON" => "Request a Quote",
	"CLOSE_BUTTON" => "Close",
	"CONFIRM_BUTTON" => "Confirm",
	"LIKE_BUTTON" => "Like",
	"DISLIKE_BUTTON" => "Dislike",
	"ACCEPT_BUTTON" => "Accept",

	// controls
	"CHECKBOXLIST_MSG" => "复选框列表",
	"LABEL_MSG" => "标签",
	"LISTBOX_MSG" => "ListBox",
	"RADIOBUTTON_MSG" => "单选按钮",
	"TEXTAREA_MSG" => "多行文本控件",
	"TEXTBOX_MSG" => "文本框",
	"TEXTBOXLIST_MSG" => "文本框列表",
	"WIDTH_AND_HEIGHT_MSG" => "Width & Height",
	"IMAGEUPLOAD_MSG" => "图片上传",
	"IMAGE_SELECT_MSG" => "Image Select",
	"CREDIT_CARD_MSG" => "信用卡",
	"GROUP_MSG" => "小组",

	// fields
	"LOGIN_FIELD" => "用户名",
	"USERNAME_FIELD" => "用户名",
	"PASSWORD_FIELD" => "密码",
	"CONFIRM_PASS_FIELD" => "确认密码",
	"NEW_PASS_FIELD" => "新密码",
	"CURRENT_PASS_FIELD" => "目前的密码",
	"FULL_NAME_FIELD" => "名称",
	"FIRST_NAME_FIELD" => "名字",
	"MIDDLE_NAME_FIELD" => "Middle Name",
	"LAST_NAME_FIELD" => "姓",
	"NICKNAME_FIELD" => "昵称",
	"PERSONAL_IMAGE_FIELD" => "个人形象",
	"COMPANY_SELECT_FIELD" => "公司",
	"SELECT_COMPANY_MSG" => "选择公司",
	"COMPANY_NAME_FIELD" => "公司名称",
	"EMAIL_FIELD" => "电子邮件",
	"STREET_FIRST_FIELD" => "地址 1",
	"STREET_SECOND_FIELD" => "地址 2",
	"STREET_THIRD_FIELD" => "Address (continue)",
	"CITY_FIELD" => "城市",
	"PROVINCE_FIELD" => "省",
	"SELECT_STATE_MSG" => "选择国家",
	"STATE_FIELD" => "州",
	"ZIP_FIELD" => "邮编/邮编",
	"SELECT_COUNTRY_MSG" => "选择国家",
	"COUNTRY_FIELD" => "国家",
	"PHONE_FIELD" => "电话号码",
	"DAYTIME_PHONE_FIELD" => "日间电话号码",
	"EVENING_PHONE_FIELD" => "晚上电话号码",
	"CELL_PHONE_FIELD" => "手机号码",
	"FAX_FIELD" => "传真号",
	"VALIDATION_CODE_FIELD" => "验证码",
	"AFFILIATE_CODE_FIELD" => "联盟代码",
	"AFFILIATE_CODE_HELP_MSG" => "创建一个属于我们的网站的链接，请使用下列网址{affiliate_url}",
	"PAYPAL_ACCOUNT_FIELD" => "PayPal帐户",
	"TAX_ID_FIELD" => "\"税号\"",
	"MSN_ACCOUNT_FIELD" => "MSN帐号",
	"ICQ_NUMBER_FIELD" => "ICQ号码",
	"USER_SITE_URL_FIELD" => "用户的网站URL",
	"HIDDEN_STATUS_FIELD" => "隐藏状态",
	"HIDE_MY_ONLINE_STATUS_MSG" => "不要显示我的在线状态",
	"SHOW_ON_SITE_FIELD" => "显示在网站",
	"SUMMARY_MSG" => "简介",
	"MY_ADDRESS_BOOK_MSG" => "我的地址簿",
	"MY_ADDRESS_BOOK_DESC" => "管理您的通讯录中的送货地址，以便下一次你为了什么，你可以把它捡起来，从送货地址列表中，而不必重新输入一遍又一遍的地址。",
	"ADDRESS_MSG" => "地址",
	"ADDRESS_TYPE_MSG" => "地址类型",
	"MY_ADDRESSES_MSG" => "我的地址",
	"NO_ADDRESSES_MSG" => "没有发现任何地址",
	"SELECT_ADDRESS_MSG" => "选择地址",
	"SELECT_COUNTRY_FIRST_MSG" => "首先选择国家",
	"NO_STATES_FOR_COUNTRY_MSG" => "没有一个国家可以为选定的国家",
	"ACCESS_TOKEN_MSG" => "访问令牌",
	"MY_MESSAGES_MSG" => "My Messages",
	"MY_MESSAGES_DESC" => "Here you can communicate with other users without having to share your e-mail addresses.",
	"MY_VOUCHERS_MSG" => "My Vouchers",
	"MY_VOUCHERS_DESC" => "Here you can find the list of all your vouchers",
	"VOUCHER_MSG" => "Voucher",
	"VOUCHERS_MSG" => "Vouchers",
	"SEND_VOUCHER_MSG" => "Send Voucher",
	"CASH_OUT_MSG" => "Cash Out",
	"NO_VOUCHERS_MSG" => "No vouchers were found",
	"VOUCHER_SIGN_IN_MSG" => "To purchase this voucher please sign in first",
	"MY_PLAYLISTS_MSG" => "My Playlists",
	"MY_PLAYLISTS_DESC" => "Save your playlists here and add your favorite songs to them.",
	"MY_PLAYLIST_MSG" => "My Playlist",
	"PLAYLIST_MSG" => "Playlist",
	"PLAYLISTS_MSG" => "Playlists",
	"NO_PLAYLISTS_MSG" => "You don't have any playlists yet.",
	"ADD_NEW_PLAYLIST_MSG" => "add new playlist",
	"EDIT_PLAYLIST_SETTINGS_MSG" => "edit playlist settings",
	"EDIT_PLAYLIST_SONGS_MSG" => "edit playlist songs",
	"PLAY_PLAYLIST_MSG" => "play the playlist",
	"CHANGE_PLAYLIST_ORDER_MSG" => "change playlist order",
	"SONGS_MSG" => "Songs",
	"SONG_NAME_MSG" => "Song Name",
	"LAST_SONG_ACTION_MSG" => "After playing last song in playlist",
	"STOP_PLAYING_MSG" => "stop playing playlist",
	"RESTART_PLAYLIST_MSG" => "restart playlist again",
	"GO_TO_NEXT_PLAYLIST_MSG" => "go to next playlist",
	"SEARCH_ADD_PLAYLIST_MSG" => "Search to add a new song to your playlist",
	"NO_SONGS_FOUND_MSG" => "We can't find any songs for your criterion.",
	"CHECK_WORDS_SPELLING_MSG" => "Make sure that all words are spelled correctly.",
	"TRY_DIFFERENT_KEYWORDS_MSG" => "Try different keywords.",
	"TRY_GENERAL_KEYWORDS_MSG" => "Try more general keywords.",
	"CLICK_ON_SONG_TO_ADD_MSG" => "Click on song to add",
	"CLICK_MORE_SETS_MSG" => "Click on any song to get more settings for it",
	"DELETE_PLAYLIST_SONG_MSG" => "Delete song from the playlist",
	"CHANGE_SONG_ORDER_MSG" => "Change song order",
	"EDIT_SONG_MSG" => "Edit song",
	"PLAY_SONG_NSG" => "Play the song",
	"PLAY_ALL_MSG" => "Play All",
	"TERMS_MSG" => "条款及条件",
	"TERMS_USER_DESC" => "我已阅读并同意条款和条件",

	// general form tabs
	"GENERAL_TAB" => "General",
	"DESCRIPTION_TAB" => "Description",
	"LOCATION_TAB" => "Location",
	"IMAGES_TAB" => "Images",
	"PHOTOS_TAB" => "Photos",

	// no records messages
	"NO_RECORDS_MSG" => "没有找到记录",
	"NO_EVENTS_MSG" => "没有发现任何事件",
	"NO_NEWS_MSG" => "新闻文章被发现",
	"NO_POLLS_MSG" => "没有发现任何调查",
	"NO_REMINDERS_MSG" => "没有发现任何提醒",
	"NO_DATA_WERE_FOUND_MSG" => "没有发现任何数据",


	// SMS messages
	"SMS_TITLE" => "SMS",
	"SMS_TEST_TITLE" => "SMS 測試",
	"SMS_TEST_DESC" => "请输入您的手机号码，按下按钮\"SEND_BUTTON接收测试消息",
	"INVALID_CELL_PHONE" => "不正确的手机号码",

	"ARTICLE_RELATED_PRODUCTS_TITLE" => "文章相关产品",
	"CATEGORY_RELATED_PRODUCTS_TITLE" => "分类产品",
	"SELECT_TYPE_MSG" => "选择类型",
	"OFFER_PRICE_MSG" => "发售价",
	"OFFER_MESSAGE_MSG" => "提供消息",

	"MY_WISHLIST_MSG" => "我的收藏",
	"MY_WISHLIST_DESC" => "在这里，您可以查看保存在过去的愿望清单项目。",
	"SELECT_WISHLIST_TYPE_MSG" => "请选择一种类型保存在您的愿望清单的产品",
	"MY_REMINDERS_MSG" => "我的提醒",
	"EDIT_REMINDER_MSG" => "编辑提醒",


	"SELECT_SUBFOLDER_MSG" => "选择子文件夹",
	"CURRENT_DIR_MSG" => "当前目录",
	"NO_AVAILIABLE_CATEGORIES_MSG" => "无类别",
	"SHOW_FOR_NON_REGISTERED_USERS_MSG" => "显示非注册用户",
	"SHOW_FOR_REGISTERED_USERS_MSG" => "显示所有注册用户",
	"VIEW_ITEM_IN_THE_LIST_MSG" => "在产品列表中查看项目",
	"ACCESS_DETAILS_MSG" => "访问详细信息",
	"ACCESS_ITEMS_DETAILS_MSG" => "访问项目的详细信息/购买项目",
	"OTHER_SUBSCRIPTIONS_MSG" => "其他订阅",
	"USE_CATEGORY_ALL_SITES_MSG" => "使用此类别中的所有站点（勾去掉复选框以选择网站）",
	"USE_ITEM_ALL_SITES_MSG" => "使用此项目的所有网站（勾去掉复选框以选择网站）",
	"SAVE_SUBSCRIPTIONS_SETTINGS_BY_CATEGORY_MSG" => "订阅设置保存类别",
	"SAVE_SITES_SETTINGS_BY_CATEGORY_MSG" => "网站设置保存类别",
	"ACCESS_LEVELS_MSG" => "访问级别",
	"FULL_ACCESS_MSG" => "Full Access",
	"RESTRICTED_ACCESS_MSG" => "Restricted Access",
	"NO_ACCESS_MSG" => "No Access",
	"SITE_MSG" => "Site",
	"SITES_MSG" => "网站",
	"NON_REGISTERED_USERS_MSG" => "非注册用户",
	"REGISTERED_CUSTOMERS_MSG" => "注册用户",
	"PREVIEW_IN_SEPARATE_SECTION_MSG" => "在单独的章节中显示",
	"PREVIEW_BELOW_DETAILS_IMAGE_MSG" => "下面的图片中显示详细信息页面",
	"PREVIEW_BELOW_LIST_IMAGE_MSG" => "下面的图片中显示的列表页",
	"PREVIEW_POSITION_MSG" => "位置",
	"ADMIN_NOTES_MSG" => "管理员注意事项",
	"USER_NOTES_MSG" => "用户须知",
	"CMS_PERMISSIONS_MSG" => "CMS权限",
	"ARTICLES_PERMISSIONS_MSG" => "文章的权限",
	"WISHLIST_MSG" => "收藏",
	"TYPE_IS_NOT_AVAILIABLE_MSG" => "类型是未取得等",
	"TYPE_IS_NOT_SELECTED_MSG" => "未选择类型",
	"SHOW_ALL_MSG" => "显示所有",

	"MEMBER_SINCE_MSG" => "注册为会员年份",
	"SITEMAP_TITLE_INDEX" => "称号",
	"SITEMAP_URL_INDEX" => "url",
	"SITEMAP_SUBS_INDEX" => "subs",

	"HOT_RELEASES_MSG" => "热发布",
	"PAY_FOR_AD_MSG" => "支付广告",
	"CHECK_SITE_MSG" => "检查网站",

	"CONTACT_US_MSG" => "联系我们",
	"REGISTERED_USERS_ALLOWED_MESSAGES_MSG" => "只有注册用户可以发送他们的消息。",
	"NOT_ALLOWED_SEND_MESSAGES_MSG" => "很抱歉，但你不允许发送邮件。",
	"MESSAGE_SENT_MSG" => "您的消息已发送成功",
	"MESSAGE_INTERVAL_ERROR" => "请等候{interval_time}发送邮件。",
	"ONE_LINE_SUMMARY_MSG" => "一个行总结",
	"QUESTION_COMMENT_MSG" => "Question / Comment",
	"YOUR_REVIEW_MSG" => "Your Review",
	"YOUR_COMMENT_MSG" => "详细评论",
	"YOUR_QUESTION_MSG" => "Your Question",
	"YOUR_QUESTION_COMMENT_MSG" => "Your question or comment",
	"YOUR_ANSWER_MSG" => "Your Answer",
	"YOUR_MESSAGE_MSG" => "Your Message",
	"REVIEW_MSG" => "查看",
	"COMMENT_MSG" => "Comment",
	"QUESTION_MSG" => "问题",
	"ANSWER_MSG" => "Answer",
	"REMINDER_MSG" => "提醒",
	"CHAINED_MENU_TITLE" => "连锁菜单",
	//article statuses
	"ARTICLE_NEW_MSG" => "新",
	"ARTICLE_PUBLISHED_MSG" => "发布时间",
	"ARTICLE_PENDING_MSG" => "有待",
	"ARTICLE_HIDDEN_MSG" => "隐",
	//priorities
	"PRIORITY_HIGH_MSG" => "高等的",
	"PRIORITY_NORMAL_MSG" => "正常",
	"PRIORITY_LOW_MSG" => "低",
	//customer types
	"CUSTOMER_TYPE_MSG" => "顾客",
	"CALL_CENTER_MODE_MSG" => "您现在的位置<b>呼叫中心模式</ b>。这意味着所有结帐settigns你看到的是可见的只有你以管理员身份。 <b>登出</ b>管理面板，切换到常用的模式。",
	//cookies constants
	"COOKIE_CONTROL_MSG" => "曲奇控制",
	"COOKIE_INFO_MSG" => "<b>的Cookie控制</ b><br/><br/>本网站使用cookie来存储信息在您的计算机上。如果您关闭了cookie，一些网站的功能将不可用。<br/><a href=\"page.php?page=use_of_cookies\">阅读更多</ a><br/><br/>保持饼干吗？",
	"COOKIE_BAR_MSG" => "Cookie Bar",
	"COOKIE_BAR_DESC" => "Our website uses cookies in order to function correctly and to provide you with the best possible online experience. For more information click the show details link or refer to our use of cookies policy statement. By clicking 'Accept' button with appropriate options you accept our <a href=\"page.php?page=use_of_cookies\">use of cookies</a>. You can change your cookie settings at any time.",
	"COOKIE_BAR_SHOW_MSG" => "Show Cookie Bar",
	"COOKIE_BAR_SHOW_DESC" => "show cookie bar for users who haven't given consent to use cookies yet",
	"COOKIE_CONSENT_TIME_MSG" => "Cookie Consent Time",
	"COOKIE_CONSENT_TIME_DESC" => "a number of days for which the user consent is valid",
	"NECESSARY_COOKIES_MSG" => "Necessary Cookies",
	"NECESSARY_COOKIES_DESC" => "These cookies are necessary for the website to function and cannot be switched off. These are used to let you login, to ensure site security and to provide shopping cart functionality. Without this type of cookies, our website won't work properly or won't be able to provide certain features and functionalities.",
	"ANALYTICS_COOKIES_MSG" => "Analytics cookies",
	"ANALYTICS_COOKIES_DESC" => "These cookies are used to analyse how visitors use a website, for instance which pages visitors visit most often, in order to provide a better user experience. Analytics cookies are recommended but optional and could be disabled. ",
	"PERSONAL_COOKIES" => "Personalisation Cookies",
	"PERSONAL_COOKIES_DESC" => "These cookies are used to remember choices you have made such as language, currency or region, and to provide personalized content recommendations. These cookies may also remember items you have previously placed in your shopping cart while visiting our website.  Personalisation cookies are optional and could be disabled.",
	"TARGET_COOKIES_MSG" => "Targeting Cookies",
	"TARGET_COOKIES_DESC" => "These cookies are usually third-party cookies from marketing partners used to deliver adverts relevant to you and your interests or from social media services to enable you to share our content with your friends and networks. These cookies can track your browsing history across websites. If you wish to prevent this type of cookie, you may do so through your device’s browser security settings.",
	"OTHER_COOKIES_MSG" => "Other Cookies",
	"OTHER_COOKIES_DESC" => "Other cookies are cookies that are not in any other category. ",
	"CUSTOM_NAME_MSG" => "Custom Name",
	"CUSTOM_MESSAGE_MSG" => "Custom Message",
	"COOKIE_SHOW_MSG" => "Show Cookie",
	"COOKIE_SHOW_DESC" => "show information and settings for this type of cookie",
	"ALLOWED_DISABLE_COOKIE_MSG" => "Allowed to disable",
	"ALLOWED_DISABLE_COOKIE_DESC" => "user can disable this type of cookie ",
	"YOU_CANT_DISABLE_COOKIE_MSG" => "You can't disable this type of cookie",

);
$va_messages = array_merge($va_messages, $messages);
