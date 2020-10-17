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
	// diegimo žinutės
	"INSTALL_TITLE" => "ViArt parduotuvės diegimas",

	"INSTALL_STEP_1_TITLE" => "Diegimas: Žingsnis 1",
	"INSTALL_STEP_1_DESC" => "Dėkojame už ViArt SHOP pasirinkimą. Tam kad baigti šį diegimą, prašome užpildyti visas reikiamas detales žemiau. Prašome atkreipti dėmesį kad duombazė kurią renkatės turi jau būti sukurta. Jei jūs diegiate į duombazę kuri naudoja ODBC, pvz. MS Access jūs pirmiau turite sukurti DSN jai prieš tęsiant.",
	"INSTALL_STEP_2_TITLE" => "Diegimas: Žingsnis 2",
	"INSTALL_STEP_2_DESC" => "",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "Diegimas: Žingsnis 3",
	"INSTALL_STEP_3_DESC" => "Prašome rinktis svetainės išdėstymą. Jūs galėsite keisti išdėstymą vėliau.",
	"INSTALL_FINAL_TITLE" => "Diegimas: Galas",
	"SELECT_DATE_TITLE" => "Rinkitės datos formatą",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "Duombazės nustatymai",
	"DB_PROGRESS_MSG" => "Duombazės struktūros užpildymas tęsiamas",
	"SELECT_PHP_LIB_MSG" => "Rinkitės PHP biblioteką",
	"SELECT_DB_TYPE_MSG" => "Rinkitės duombazės tipą",
	"ADMIN_SETTINGS_MSG" => "Valdymo nustatymai",
	"DATE_SETTINGS_MSG" => "Datos formatai",
	"NO_DATE_FORMATS_MSG" => "Nėra jokių datos formatų",
	"INSTALL_FINISHED_MSG" => "Iki šio taško pagrindinis diegimas baigtas. Prašome tikrai patikrinti nustatymus valdymo skyriuje ir atlikti reikiamus keitimus.",
	"ACCESS_ADMIN_MSG" => "Kad patekti į valdymo skyrių spauskite čia",
	"ADMIN_URL_MSG" => "Valdymo nuoroda",
	"MANUAL_URL_MSG" => "Rankinis URL nuoroda",
	"THANKS_MSG" => "Dėkojame jums už pasirinktą <b>ViArt parduotuvę</b>.",

	"DB_TYPE_FIELD" => "Duombazės tipas",
	"DB_TYPE_DESC" => "Prašome rinktis <b>type of database</b> kurią naudojate. Jei jūs naudojate SQL Serverį arba Microsoft Access, prašome rinktis ODBC.",
	"DB_PHP_LIB_FIELD" => "PHP biblioteka",
	"DB_HOST_FIELD" => "Šeimininko vardas",
	"DB_HOST_DESC" => "Prašome įvesti <b>name</b> ar <b>IP address of the server</b> ant kurio jūsų ViArt duomenų bazė suksis. Jei jūs sukate jūsų duombazę ant savo vietinio PC, tai jūs tikriausiai galite palikti tai kaip yra \"<b>localhost</b>\" ir prievadą tuščią. Jei jūs naudojate duombazę suteiktą jūsų talpinimo firmos, prašome skaityti jūsų talpinimo firmos dokumentaciją serverio nustatymams.",
	"DB_PORT_FIELD" => "Prievadas",
	"DB_NAME_FIELD" => "Duombazės vardas / DSN",
	"DB_NAME_DESC" => "Jei jūs naudojate duombazę tokią kaip MySQL ar PostgreSQL tai prašome įvesti <b>name of the database</b> kur jūs norite kad ViArt sukurtų savo lenteles. Ši duombazė jau turi būti sukurta. Jei jūs tik diegiate ViArt testavimo tikslams ant savo vietinio PC tai daugiausia sistemų turi \"<b>test</b>\" duombazę kurią galite naudoti. Jei ne, prašome sukurti duombazę tokią kaip \"viart\" ir ją naudoti. Jei jūs naudojate Microsoft Access ar SQL Serverį, tai Duombazės Vardas turi būti <b>name of the DSN</b> kurį jūs sukūrėte Data Sources (ODBC) skyriuke jūsų Valdymo Skydelyje.",
	"DB_USER_FIELD" => "Vartotojo vardas",
	"DB_PASS_FIELD" => "Slaptažodis",
	"DB_USER_PASS_DESC" => "<b>Username</b> ir <b>Password</b> - prašome įvesti vartotojo vardą ir slaptažodį kurį norite naudoti duombazės priėjimui. Jei jūs naudojate vietinį testavimo diegimą vartotojo vardas tikriausiai yra \"<b>root</b>\" ir slaptažodis tikriausiai yra tuščias. Tai gerai testavimui, bet užsižymėkite, kad tai nėra saugu ant veikiančių gamybinių serverių.",
	"DB_PERSISTENT_FIELD" => "Išliekantis pasijungimas",
	"DB_PERSISTENT_DESC" => "Tam kad naudoti MySQL išliekančius pasijungimus, uždėktie varnelę šioje dėžutėje. Jei jūs nežinote kas tai yra , tai palikti ją neatžymėtą bus geriausia.",
	"DB_CREATE_DB_FIELD" => "Create DB",
	"DB_CREATE_DB_DESC" => "to create database if possible, tick this box. Works only for MySQL and Postgre",
	"DB_POPULATE_FIELD" => "Užpildyk DB",
	"DB_POPULATE_DESC" => "Kad sukurti duombazės lentelių struktūrą ir užpildyti jas duomenimis spustelkite žymių dėžutę",
	"DB_TEST_DATA_FIELD" => "Bandymų duomenys",
	"DB_TEST_DATA_DESC" => "kad pridėti šiek tiek bandymų duomenų į jūsų duombazę pažymėkite varnele",
	"ADMIN_EMAIL_FIELD" => "Valdytojo e-paštas",
	"ADMIN_LOGIN_FIELD" => "Valdytojo įsijungimas",
	"ADMIN_PASS_FIELD" => "Valdytojo slaptažodis",
	"ADMIN_CONF_FIELD" => "Patvirtinkite slaptažodį",
	"DATETIME_SHOWN_FIELD" => "Datos-laiko formatas (rodomas svetainėje)",
	"DATE_SHOWN_FIELD" => "Datos formatas (rodomas svetainėje)",
	"DATETIME_EDIT_FIELD" => "Datos-laiko formatas (redagavimui)",
	"DATE_EDIT_FIELD" => "Datos formatas (redagavimui)",
	"DATE_FORMAT_COLUMN" => "Datos formatas",

	"DB_LIBRARY_ERROR" => "PHP funkcijos {db_library} nėra apibrėžtos. Prašome patikrinti duombazės nustatymus jūsų konfigūracijos byloje - php.ini.",
	"DB_CONNECT_ERROR" => "Negaliu pasijungti prie duombazės. Prašome tikrinti jūsų duombazės parametrus.",
	"INSTALL_FINISHED_ERROR" => "Diegimo eiga jau baigėsi.",
	"WRITE_FILE_ERROR" => "Neturiu rašymo teisių bylai <b>'includes/var_definition.php'</b>. Prašome pakeisti bylos teises prieš tęsiant.",
	"WRITE_DIR_ERROR" => "Neturiu rašymo teisių aplankui  <b>'includes/'</b>. Prašome pakeisti aplanko teises prieš tęsiant.",
	"DUMP_FILE_ERROR" => "Iškrovimo byla '{file_name}' nerasta.",
	"DB_TABLE_ERROR" => "Lentelė '{table_name}' nerasta. Prašome užpildyti duombazę reikalingais duomenimis.",
	"TEST_DATA_ERROR" => "Patikrink <b>{POPULATE_DB_FIELD}</b> prieš užpildant lenteles su bandymų duomenimis",
	"DB_HOST_ERROR" => "Mazgo vardas kurį nurodėte nerastas.",
	"DB_PORT_ERROR" => "Negaliu pasijungti prie MySQL serverio naudojant nurodytą prievadą.",
	"DB_USER_PASS_ERROR" => "Vartotojo vardas ir slaptažodis kurį nurodėte neteisingi.",
	"DB_NAME_ERROR" => "Įsijungimo nustatymai buvo teisingi, bet duombazė '{db_name}' nerasta.",

	// atnaujinimo pranešimai
	"UPGRADE_TITLE" => "ViArt Parduotuvės Naujinimas",
	"UPGRADE_NOTE" => "Pastaba: Prašome pagalvoti apie duombazės kopiją prieš tęsiant.",
	"UPGRADE_AVAILABLE_MSG" => "Duombazės naujinimas teikiamas",
	"UPGRADE_BUTTON" => "Naujinti duombazę iki {version_number} dabar",
	"CURRENT_VERSION_MSG" => "Dabar įdiegta versija",
	"LATEST_VERSION_MSG" => "Versija tiekiama diegimui",
	"UPGRADE_RESULTS_MSG" => "Naujinimo pasekmės",
	"SQL_SUCCESS_MSG" => "SQL užklausa pavyko",
	"SQL_FAILED_MSG" => "SQL užklausa nepavyko",
	"SQL_TOTAL_MSG" => "Viso SQL užklausų įvykdyta",
	"VERSION_UPGRADED_MSG" => "Jūsų duombazė buvo atnaujinta iki",
	"ALREADY_LATEST_MSG" => "Jūs jau turite naujausią versiją",
	"DOWNLOAD_NEW_MSG" => "Nauja versija buvo aptikta",
	"DOWNLOAD_NOW_MSG" => "Nusisiųskite versiją {version_number} dabar",
	"DOWNLOAD_FOUND_MSG" => "Mes aptikome kad nauja {version_number} versija tiekiama nusisiuntimui. Prašome spausti nuorodą žemiau kad pradėti nusisiuntimą. Po siuntimosi baigimo ir bylų pakeitimo nepamirškite leisti Naujinimo paprogramę vėl.",
	"NO_XML_CONNECTION" => "Įspėjimas! Nėra ryšio iki 'http://www.viart.com/' prieinama!",

	"END_USER_LICENSE_AGREEMENT_MSG" => "End User License Agreement",
	"AGREE_LICENSE_AGREEMENT_MSG" => "I have read and agree to the License Agreement",
	"READ_LICENSE_AGREEMENT_MSG" => "Click here to read license agreement",
	"LICENSE_AGREEMENT_ERROR" => "Please read and agree to the License Agreement before proceeding.",

);
$va_messages = array_merge($va_messages, $messages);
