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
	"INSTALL_TITLE" => "Instalação ViArt SHOP",

	"INSTALL_STEP_1_TITLE" => "Instalação: 1º Passo",
	"INSTALL_STEP_1_DESC" => "Obrigado por escolher a ViArt SHOP. Para continuar a instalação, por favor, preencha os dados pedidos abaixo. Por favor, certifique-se de que a base de dados que seleccionar já existe. Se instalar numa base de dados que utilize ODBC (ex.: MS Access), primeiro, deverá criar um DSN para prosseguir.",
	"INSTALL_STEP_2_TITLE" => "Instalação: 2º Passo",
	"INSTALL_STEP_2_DESC" => " ",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "Instalação: 3º Passo",
	"INSTALL_STEP_3_DESC" => "Por favor, seleccione um layout do site. Terá a possibilidade de alterar o layout posteriormente.",
	"INSTALL_FINAL_TITLE" => "Fim da Instalação",
	"SELECT_DATE_TITLE" => "Seleccionar o Formato da Data",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "Definições da Base de Dados",
	"DB_PROGRESS_MSG" => "A carregar o progresso da estrutura da base de dados",
	"SELECT_PHP_LIB_MSG" => "Seleccionar a Biblioteca PHP",
	"SELECT_DB_TYPE_MSG" => "Seleccionar o Tipo de Base de Dados",
	"ADMIN_SETTINGS_MSG" => "Definições Administrativas",
	"DATE_SETTINGS_MSG" => "Formatos da Data",
	"NO_DATE_FORMATS_MSG" => "Não há formatos da data disponíveis",
	"INSTALL_FINISHED_MSG" => "Até este ponto, a instalação básica está concluída. Por favor, verifique as definições na secção de administração e efectue as necessárias modificações.",
	"ACCESS_ADMIN_MSG" => "Para ter acesso à secção da administração, clique aqui",
	"ADMIN_URL_MSG" => "URL da Administração",
	"MANUAL_URL_MSG" => "URL do Manual",
	"THANKS_MSG" => "Obrigado por ter escolhido <b>ViArt SHOP</b>.",

	"DB_TYPE_FIELD" => "Tipo de Base de Dados",
	"DB_TYPE_DESC" => "Por favor, seleccione <b>type of database</b> que está a utilizar. Se estiver a utilizar o SQL Server ou o Microsoft Access, por favor, seleccione ODBC.",
	"DB_PHP_LIB_FIELD" => "Biblioteca PHP",
	"DB_HOST_FIELD" => "Nome do Host",
	"DB_HOST_DESC" => "Por favor, digite o <b>name</b> ou <b>IP address of the server</b> no qual a sua base de dados ViArt irá correr. Se estiver a correr a sua base de dados no seu PC local, então poderá deixar apenas como \"<b>localhost</b>\" e a porta em branco. Se estiver a utilizar uma base de dados proveniente do servidor da empresa, por favor, veja a documentação do servidor da sua empresa para as definições do servidor.",
	"DB_PORT_FIELD" => "Número da Porta",
	"DB_NAME_FIELD" => "Nome da Base de Dados / DSN",
	"DB_NAME_DESC" => "Se estiver a utilizar uma base de dados como o MySQL ou o PostgreSQL, por favor, digite <b>name of the database</b> onde gostasse que o ViArt criasse as suas tabelas. Esta base de dados já deverá existir. Se está a instalar o ViArt apenas com o propósito de o testar no seu PC local, então a maioria dos sistemas possui \"<b>test</b>\" base de dados que pode utilizar. Senão, por favor, crie uma base de dados similar à \"viart\" e use-a. Se estiver a utilizar o Microsoft Access ou o SQL Server, o nome da base de dados deverá ser <b>name of the DSN</b> que tinha ajustado na secção Data Sources (ODBC) do seu Painel de Controlo.",
	"DB_USER_FIELD" => "Nome do Utilizador",
	"DB_PASS_FIELD" => "Senha",
	"DB_USER_PASS_DESC" => "<b>Username</b> e <b>Password</b> - por favor, digite o Nome de Utilizador e a Senha que pretende utilizar para ter acesso à base de dados. Se estiver a utilizar um teste de instalação local, o Nome de Utilizador é, provavelmente, \"<b>root</b>\" e a Senha fica, provavelmente, em branco. Mas repare que, apesar de ser bom testar, não é seguro em servidores de produção.",
	"DB_PERSISTENT_FIELD" => "Conexão persistente",
	"DB_PERSISTENT_DESC" => "to use MySQL or Postgre persistent connections, tick this box. If you do not know what it means, then leaving it unticked is probably best.",
	"DB_CREATE_DB_FIELD" => "Criar Base de Dados",
	"DB_CREATE_DB_DESC" => "para criar base de dados clique nesta caixa. Funciona apenas para o MySQL e o Postgre.",
	"DB_POPULATE_FIELD" => "Carregar a Base de Dados",
	"DB_POPULATE_DESC" => "para criar a estrutura tabelar da base de dados e carregá-la com dados, assinale na caixa",
	"DB_TEST_DATA_FIELD" => "Testar Dados",
	"DB_TEST_DATA_DESC" => "para adicionar alguns dados de teste à sua base de dados, assinale na caixa",
	"ADMIN_EMAIL_FIELD" => "E-mail do Administrador",
	"ADMIN_LOGIN_FIELD" => "Login do Administrador",
	"ADMIN_PASS_FIELD" => "Senha do Administrator",
	"ADMIN_CONF_FIELD" => "Confirmar a Senha",
	"DATETIME_SHOWN_FIELD" => "Formato da Data e da Hora (mostrado no site)",
	"DATE_SHOWN_FIELD" => "Formato da Data (mostrado no site)",
	"DATETIME_EDIT_FIELD" => "Formato da Data e da Hora (para editar)",
	"DATE_EDIT_FIELD" => "Formato da Data (para editar)",
	"DATE_FORMAT_COLUMN" => "Formato da Data",

	"DB_LIBRARY_ERROR" => "As funções de PHP para {db_library} não estão definidas. Por favor, verifique as definições da sua base de dados no seu ficheiro de configuração - php.ini.",
	"DB_CONNECT_ERROR" => "Não conecta à base de dados. Por favor, verifique os parâmetros da sua base de dados.",
	"INSTALL_FINISHED_ERROR" => "Processo de instalação finalizado.",
	"WRITE_FILE_ERROR" => "Não tem permissão para gravar no arquivo <b>'includes/var_definition.php'</b>. Por favor, altere as permissões de arquivo antes de continuar.",
	"WRITE_DIR_ERROR" => "Não tem permissão para gravar na pasta <b>'includes/'</b>. Por favor, altere as permissões de pasta antes de continuar.",
	"DUMP_FILE_ERROR" => "O ficheiro Dump '{file_name}' não foi encontrado.",
	"DB_TABLE_ERROR" => "A tabela '{table_name}' não foi encontrada. Por favor, construa a base de dados com os dados necessários.",
	"TEST_DATA_ERROR" => "Verificar <b>{POPULATE_DB_FIELD}</b> antes de preencher as tabelas com dados de teste",
	"DB_HOST_ERROR" => "O nome do host que especificou não foi encontrado.",
	"DB_PORT_ERROR" => "Não conecta ao servidor da base de dados, utilizando a porta especificada.",
	"DB_USER_PASS_ERROR" => "O Nome do Utilizador e/ou a Senha que especificou não estão os correctos.",
	"DB_NAME_ERROR" => "As definições de login estavam correctas, mas a base de dados '{db_name}' não foi encontrada.",

	// upgrade messages
	"UPGRADE_TITLE" => "Actualização ViArt SHOP",
	"UPGRADE_NOTE" => "Nota: por favor, faça um backup da base de dados antes de continuar",
	"UPGRADE_AVAILABLE_MSG" => "Upgrade da base de dados disponível",
	"UPGRADE_BUTTON" => "Actualizar a base de dados para {version_number}, agora",
	"CURRENT_VERSION_MSG" => "Versão actualmente instalada",
	"LATEST_VERSION_MSG" => "Versão disponível para instalação",
	"UPGRADE_RESULTS_MSG" => "Resultados da actualização",
	"SQL_SUCCESS_MSG" => "Consulta SQL bem sucedida",
	"SQL_FAILED_MSG" => "Consulta SQL mal sucedida",
	"SQL_TOTAL_MSG" => "Total de consultas SQL executadas",
	"VERSION_UPGRADED_MSG" => "A sua base de dados foi actualizada para",
	"ALREADY_LATEST_MSG" => "Já possui a última versão",
	"DOWNLOAD_NEW_MSG" => "Uma nova versão foi encontrada",
	"DOWNLOAD_NOW_MSG" => "Download da versão {version_number}, agora",
	"DOWNLOAD_FOUND_MSG" => "Detectámos que a nova versão {version_number} está disponível para download. Por favor, clique no link abaixo para iniciar o download. Depois de concluir o download e substitutir os ficheiros, não se esqueça de correr o Upgrade novamente.",
	"NO_XML_CONNECTION" => "Aviso! A conexão para 'http://www.viart.com/' não pode ser estabelecida",

	"END_USER_LICENSE_AGREEMENT_MSG" => "End User License Agreement",
	"AGREE_LICENSE_AGREEMENT_MSG" => "I have read and agree to the License Agreement",
	"READ_LICENSE_AGREEMENT_MSG" => "Click here to read license agreement",
	"LICENSE_AGREEMENT_ERROR" => "Please read and agree to the License Agreement before proceeding.",

);
$va_messages = array_merge($va_messages, $messages);
