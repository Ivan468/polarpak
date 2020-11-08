<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  install_messages.php                                     ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

$messages = array(
	// installation messages
	"INSTALL_TITLE" => "ViArt店安装",

	"INSTALL_STEP_1_TITLE" => "安装：第1步",
	"INSTALL_STEP_1_DESC" => "感谢您选择ViArt店。为了继续安装，请填写以下详细资料。请注意，您所选择的数据库已经存在。如果您正在安装一个数据库，使用ODBC，例如MS访问，你应该首先创建一个DSN，然后再继续。",
	"INSTALL_STEP_2_TITLE" => "安装：步骤2",
	"INSTALL_STEP_2_DESC" => "",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "安装：第3步",
	"INSTALL_STEP_3_DESC" => "请选择一个站点布局。你会之后能够改变布局。",
	"INSTALL_FINAL_TITLE" => "安装：最终",
	"SELECT_DATE_TITLE" => "选择日期格式",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "数据库设置",
	"DB_PROGRESS_MSG" => "填充数据库结构的进展",
	"SELECT_PHP_LIB_MSG" => "选择PHP库",
	"SELECT_DB_TYPE_MSG" => "选择数据库类型",
	"ADMIN_SETTINGS_MSG" => "管理设置",
	"DATE_SETTINGS_MSG" => "日期格式",
	"NO_DATE_FORMATS_MSG" => "没有可用的日期格式",
	"INSTALL_FINISHED_MSG" => "在这一点上，您的基本安装完成。请务必检查设置中的管理部分，并进行所需的任何更改。",
	"ACCESS_ADMIN_MSG" => "访问管理部分，请点击这里",
	"ADMIN_URL_MSG" => "管理URL",
	"MANUAL_URL_MSG" => "手动URL",
	"THANKS_MSG" => "感谢您选择<b> ViArt店</ B>。",

	"DB_TYPE_FIELD" => "数据库类型",
	"DB_TYPE_DESC" => "</ B>您正在使用，请选择的<b>的类型的数据库。如果你使用的是SQL Server或Microsoft Access，请选择\"ODBC\"。",
	"DB_PHP_LIB_FIELD" => "PHP库",
	"DB_HOST_FIELD" => "主机名",
	"DB_HOST_DESC" => "请输入名称 <b> </b> 或 ViArt 数据库将在其运行的服务器 </b> <b> IP 地址。如果您在您的本地 PC 上运行您的数据库然后可以也许只是保留这作为\" <b>localhost </b>\"和港口空白。如果您使用的数据库提供您的托管公司，请参阅您的托管公司文档服务器设置。",
	"DB_PORT_FIELD" => "端口",
	"DB_NAME_FIELD" => "数据库名称/ DSN",
	"DB_NAME_DESC" => "如果你正在使用的数据库，如MySQL或PostgreSQL，那么请输入的<b>的的数据库名称</ B>，你想ViArt创建表。该数据库必须已经存在。如果你只是用于测试目的在本地PC上的安装ViArt，那么大多数系统有一个\"测试</ B>\"数据库，您可以使用。如果没有，请创建一个数据库如\"viart\"，并使用它。如果您使用的是Microsoft Access或SQL Server数据库名称应该是<b>名称</ B>您已经在您的控制面板数据源（ODBC）\"一节的DSN。",
	"DB_USER_FIELD" => "用户名",
	"DB_PASS_FIELD" => "密码",
	"DB_USER_PASS_DESC" => "用户名</ B>和<B>密码</ B> - 请输入您要使用访问数据库的用户名和密码。如果您使用的是本地测试安装的用户名可能是\"<B>根</ B>\"，密码可能为空。这是适合于测试，但请注意，这不是安全生产服务器上。",
	"DB_PERSISTENT_FIELD" => "永久连接",
	"DB_PERSISTENT_DESC" => "使用MySQL或Postgre持续性的连接，勾选此框。如果你不知道这意味着什么，然后离开它取消选中可能是最好的。",
	"DB_CREATE_DB_FIELD" => "创建DB",
	"DB_CREATE_DB_DESC" => "创建数据库如果可能的话，请勾选此框。只适用于MySQL和Postgre",
	"DB_POPULATE_FIELD" => "填充DB",
	"DB_POPULATE_DESC" => "创建数据库表结构及填充数据的复选框中打勾",
	"DB_TEST_DATA_FIELD" => "测试数据",
	"DB_TEST_DATA_DESC" => "添加一些测试数据到你的数据库的复选框中打勾",
	"ADMIN_EMAIL_FIELD" => "管理员电子邮件",
	"ADMIN_LOGIN_FIELD" => "管理员登录",
	"ADMIN_PASS_FIELD" => "管理员密码",
	"ADMIN_CONF_FIELD" => "确认密码",
	"DATETIME_SHOWN_FIELD" => "日期时间格式（现场）",
	"DATE_SHOWN_FIELD" => "日期格式（现场）",
	"DATETIME_EDIT_FIELD" => "日期时间格式（用于编辑）",
	"DATE_EDIT_FIELD" => "日期格式（用于编辑）",
	"DATE_FORMAT_COLUMN" => "日期格式",

	"DB_LIBRARY_ERROR" => "PHP功能{以及DB_library}是没有定义的。请检查你的数据库在您的配置文件 - php.ini中设置。",
	"DB_CONNECT_ERROR" => "无法连接到数据库。请检查您的数据库参数。",
	"INSTALL_FINISHED_ERROR" => "安装过程已经完成。",
	"WRITE_FILE_ERROR" => "没有写入权限的文件<b>\"包括/ var_definition.php\"</ B>。在继续之前，请更改文件的权限。",
	"WRITE_DIR_ERROR" => "还没有写入权限的文件夹<B>\"includes/\"</ B>。在继续之前，请更改文件夹的权限。",
	"DUMP_FILE_ERROR" => "转储文件\"{file_name}\"没有被发现。",
	"DB_TABLE_ERROR" => "表'{table_name}\"没有被发现。请提供必要的数据填充数据库。",
	"TEST_DATA_ERROR" => "检查<B> {POPULATE_DB_FIELD} </ b>在测试数据填充表",
	"DB_HOST_ERROR" => "找不到您所指定的主机名。",
	"DB_PORT_ERROR" => "无法连接到数据库服务器使用特定的端口。",
	"DB_USER_PASS_ERROR" => "您指定的用户名或密码不正确。",
	"DB_NAME_ERROR" => "登录设置是正确的，但数据库\"{db_name}\"无法被发现。",

	// upgrade messages
	"UPGRADE_TITLE" => "ViArt商店升级",
	"UPGRADE_NOTE" => "注意：请考虑使数据库备份，然后再继续。",
	"UPGRADE_AVAILABLE_MSG" => "数据库升级",
	"UPGRADE_BUTTON" => "现在升级的数据库为 {version_number}",
	"CURRENT_VERSION_MSG" => "当前安装的版本",
	"LATEST_VERSION_MSG" => "版本安装",
	"UPGRADE_RESULTS_MSG" => "升级结果",
	"SQL_SUCCESS_MSG" => "SQL查询成功",
	"SQL_FAILED_MSG" => "SQL查询失败",
	"SQL_TOTAL_MSG" => "执行SQL查询总数",
	"VERSION_UPGRADED_MSG" => "你的数据库已经升级到",
	"ALREADY_LATEST_MSG" => "你已经有最新版本",
	"DOWNLOAD_NEW_MSG" => "检测到新版本",
	"DOWNLOAD_NOW_MSG" => "立即下载版本 {version_number}",
	"DOWNLOAD_FOUND_MSG" => "我们检测到的新的 {version_number} 版本可供下载。请单击下面的链接以开始下载。完成下载并替换这些文件后不要忘记再次运行升级例程。",
	"NO_XML_CONNECTION" => "警告！无连接到\"http://www.viart.com/\"！",

	"END_USER_LICENSE_AGREEMENT_MSG" => "最终用户许可协议",
	"AGREE_LICENSE_AGREEMENT_MSG" => "我已阅读并同意\"许可协议\"",
	"READ_LICENSE_AGREEMENT_MSG" => "点击这里阅读许可协议",
	"LICENSE_AGREEMENT_ERROR" => "请仔细阅读并同意许可协议，然后再继续。",

);
$va_messages = array_merge($va_messages, $messages);
