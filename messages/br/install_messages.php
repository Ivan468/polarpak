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
	"INSTALL_TITLE" => "ViArt SHOP Installation",

	"INSTALL_STEP_1_TITLE" => "Instalação: Passo 1",
	"INSTALL_STEP_1_DESC" => "Obrigado por escolher ViArt SHOP. Para completar esta instalação, por favor preencha os detalhes abaixo. Por favor, verifique se o Bando de Dados que você selecionou já exista. Se você estiver instalando um banco de dados que use ODBC, e.g MS Access você deve criar um DSN para prosseguir",
	"INSTALL_STEP_2_TITLE" => "Instalação: Passo 2",
	"INSTALL_STEP_2_DESC" => " ",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "Instalação: Passo 3",
	"INSTALL_STEP_3_DESC" => "Por favor escolher um layout para seu site. Você poderá alterar o layout posterioramente.",
	"INSTALL_FINAL_TITLE" => "Instalação : Finalização",
	"SELECT_DATE_TITLE" => "Selecione o formato de data",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "Parâmetros de Banco de Dados",
	"DB_PROGRESS_MSG" => "Carregando o Banco de Dados",
	"SELECT_PHP_LIB_MSG" => "Selecione biblioteca PHP",
	"SELECT_DB_TYPE_MSG" => "Selecione o tipo de Banco de Dados",
	"ADMIN_SETTINGS_MSG" => "Parâmetros Administrativo",
	"DATE_SETTINGS_MSG" => "Formato de datas",
	"NO_DATE_FORMATS_MSG" => "Formato de datas não disponivel",
	"INSTALL_FINISHED_MSG" => "A instalação básica está concluída. Por favor verifique as configurações na seção administrativa.",
	"ACCESS_ADMIN_MSG" => "Para acessar à seção de administração, clicar aqui",
	"ADMIN_URL_MSG" => "URL da administração",
	"MANUAL_URL_MSG" => "URL do manual",
	"THANKS_MSG" => "Obrigado por escolher <b>ViArt SHOP</b>.",

	"DB_TYPE_FIELD" => "Tipo Banco de Dados",
	"DB_TYPE_DESC" => "Por favor selecione o <b>tipo de banco de dados</b> que você esta utilizando. Para SQL Server ou Microsoft Access, por favor selecione ODBC.",
	"DB_PHP_LIB_FIELD" => "Biblioteca PHP",
	"DB_HOST_FIELD" => "Nome do Host",
	"DB_HOST_DESC" => "Por favor informar o <b>nome</b> ou <b>endereço IP do servidor</b> onde seu banco de dados ViArt será executado. Se você esta executando seu banco de dados no seu PC local, você provavelmente pode deixar esse campo como \"<b>localhost</b>\" e o campo Porta em branco.",
	"DB_PORT_FIELD" => "Porta",
	"DB_NAME_FIELD" => "Nome do Banco de Dados / DSN",
	"DB_NAME_DESC" => "Se você esta utilizando um banco de dados como MySQL ou PostgreSQL, por favor informar o <b>nome do banco de dados</b> onde você quer que ViArt crie as tabelas. Esse banco de dados deve existir. Se você esta utilizando Microsoft Access ou SQL Server, o nome do banco de dados deve ser o <b>nome do DSN</b> que você configurou na seção Data Sources (ODBC) do seu painel de controle.",
	"DB_USER_FIELD" => "Usuário",
	"DB_PASS_FIELD" => "Senha",
	"DB_USER_PASS_DESC" => "<b>Usuario</b> e <b>Senha</b> -  por favor informar o usuário e senha que você quer utilizar para esse banco de dados.",
	"DB_PERSISTENT_FIELD" => "Conexões persistentes",
	"DB_PERSISTENT_DESC" => "para utilizar conexões persistentes do MySQL e Postgre, marque essa caixa. Se você estiver na dúvida, deixar esse caixa desmarcada é provavelmente a melhor opção.",
	"DB_CREATE_DB_FIELD" => "Criar banco de dados",
	"DB_CREATE_DB_DESC" => "para criar o banco de dados, marque esse caixa. Funciona somente para MySQL e Postgre",
	"DB_POPULATE_FIELD" => "Carregar banco de dados",
	"DB_POPULATE_DESC" => "Para criar a estrutura de tabela do banco de dados e carregar os dados, marque essa caixa",
	"DB_TEST_DATA_FIELD" => "Testar banco de dados",
	"DB_TEST_DATA_DESC" => "para adicionar dados de teste para seu banco de dados, marque essa caixa",
	"ADMIN_EMAIL_FIELD" => "Email do administrator",
	"ADMIN_LOGIN_FIELD" => "Login do administrador",
	"ADMIN_PASS_FIELD" => "Senha do administrator",
	"ADMIN_CONF_FIELD" => "Confirmar senha",
	"DATETIME_SHOWN_FIELD" => "Formato de data e hora (exibido no site)",
	"DATE_SHOWN_FIELD" => "Formato de data (exibido no site)",
	"DATETIME_EDIT_FIELD" => "Formato de data e hora (para edição)",
	"DATE_EDIT_FIELD" => "Formato de data (para edição)",
	"DATE_FORMAT_COLUMN" => "Formato de data",

	"DB_LIBRARY_ERROR" => "Funções PHP para {db_library} não estão definidas. Por favor verifique seu arquivo de configuração - php.ini.",
	"DB_CONNECT_ERROR" => "Não foi possivel conectar-se ao banco de dados. Por favor verifique os parametros do seu banco de dados.",
	"INSTALL_FINISHED_ERROR" => "Processo de instalação finalizado",
	"WRITE_FILE_ERROR" => "Não tem permissão para gravar neste arquivo <b>'incluir/var_definition.php'</b>. Por favor altere as permissões antes de continuar.",
	"WRITE_DIR_ERROR" => "Não tem permissão para gravar nesta pasta <b>'incluir/'</b>. Por favor altere as permissões antes de continuar.",
	"DUMP_FILE_ERROR" => "Arquivo de Dump '{file_name}' não foi encontrado.",
	"DB_TABLE_ERROR" => "Tabela '{table_name}' não foi encontrada. Por favor enviar os dados necessários ao banco de dados.",
	"TEST_DATA_ERROR" => "Marcar <b>{POPULATE_DB_FIELD}</b> antes de enviar dados de teste para as tabelas",
	"DB_HOST_ERROR" => "O nome do host especificado não pôde ser encontrado",
	"DB_PORT_ERROR" => "Não foi possível conectar-se ao banco de dados utilizando a porta especificada.",
	"DB_USER_PASS_ERROR" => "O usuário ou senha especificada não é correto",
	"DB_NAME_ERROR" => "As informações de login estão corretas, mas o '{db_name}' do banco de dados não pôde ser encontrado",

	// upgrade messages
	"UPGRADE_TITLE" => "Atualização do ViArt SHOP ",
	"UPGRADE_NOTE" => "Nota: Recomendamos fazer um backup antes de continuar",
	"UPGRADE_AVAILABLE_MSG" => "Atualização de Banco de Dados disponivel",
	"UPGRADE_BUTTON" => "Atualizar  Banco de Dados para {version_number} agora",
	"CURRENT_VERSION_MSG" => "Versão atual instalada",
	"LATEST_VERSION_MSG" => "Versão disponivel para instalação",
	"UPGRADE_RESULTS_MSG" => "Resultado da atualização",
	"SQL_SUCCESS_MSG" => "Consultas SQL realizada com sucesso",
	"SQL_FAILED_MSG" => "Consultas SQL falharam",
	"SQL_TOTAL_MSG" => "Total de consultas SQL realizadas",
	"VERSION_UPGRADED_MSG" => "Seu banco de dados foi atualizado para ",
	"ALREADY_LATEST_MSG" => "Voce já está com a ultima versão",
	"DOWNLOAD_NEW_MSG" => "Uma nova versão foi encontrada",
	"DOWNLOAD_NOW_MSG" => "Baixar a versão {version_number} agora",
	"DOWNLOAD_FOUND_MSG" => "Nos detectamos que a nova versão {version_number} esta disponivel para download. Favor clique no link abaixo para iniciar o downloada. Depois de completar o download e substituir os arquivos, não esqueça de fazer a rotina de atualização novamente.",
	"NO_XML_CONNECTION" => "Aviso! A conexão para 'http://www.viart.com/' não pode ser estabelecida",

	"END_USER_LICENSE_AGREEMENT_MSG" => "End User License Agreement",
	"AGREE_LICENSE_AGREEMENT_MSG" => "I have read and agree to the License Agreement",
	"READ_LICENSE_AGREEMENT_MSG" => "Click here to read license agreement",
	"LICENSE_AGREEMENT_ERROR" => "Please read and agree to the License Agreement before proceeding.",

);
$va_messages = array_merge($va_messages, $messages);
