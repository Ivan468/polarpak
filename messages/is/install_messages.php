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
	"INSTALL_TITLE" => "Uppsetning ViArt Verslunar",

	"INSTALL_STEP_1_TITLE" => "Uppsetning: Skref 1",
	"INSTALL_STEP_1_DESC" => "Takk fyrir að velja ViArt Verslun. Til að halda uppsetningu áfram, vinsamlegast fylltu út upplýsingarnar hér að neðan. Athugaðu að gagnabankinn sem þú velur ætti nú þegar að vera til. Ef þú ert að setja upp á gagnagrunn sem notar ODBC, t.d MS Access ættirðu fyrst að útbúa DSN áður en þú heldur áfram.",
	"INSTALL_STEP_2_TITLE" => "Uppsetning: Skref 2",
	"INSTALL_STEP_2_DESC" => "",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "Uppsetning: Skref 3",
	"INSTALL_STEP_3_DESC" => "Vinsamlegast veldu útlit síðu. Þú munt geta breytt útliti seinna.",
	"INSTALL_FINAL_TITLE" => "Uppsetning: Lokaskref",
	"SELECT_DATE_TITLE" => "Veldu dagsetningarsnið",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "Stillingar gagnagrunns",
	"DB_PROGRESS_MSG" => "Gagnagrunnsupplýsingar sóttar...",
	"SELECT_PHP_LIB_MSG" => "Veja PHP safn?",
	"SELECT_DB_TYPE_MSG" => "Velja gagnagrunnstegund",
	"ADMIN_SETTINGS_MSG" => "Stjórnunarstillingar",
	"DATE_SETTINGS_MSG" => "Dagsetningarsnið",
	"NO_DATE_FORMATS_MSG" => "Engin dagsetningarsnið tiltæk",
	"INSTALL_FINISHED_MSG" => "Nú er grunnuppsetningu lokið. Vinsamlegast athugaðu hvort að allar stillingar í stjórnunarhlutanum séu réttar og breyttu ef þörf krefur.",
	"ACCESS_ADMIN_MSG" => "Smelltu hér til að fá aðgang að stjórnunarhlutanum",
	"ADMIN_URL_MSG" => "Stjórnunarslóð",
	"MANUAL_URL_MSG" => "Manual URL",
	"THANKS_MSG" => "Takk fyrir að velja <b>ViArt SHOP</b>.",

	"DB_TYPE_FIELD" => "Gerð gagnagrunns",
	"DB_TYPE_DESC" => "Vinsamlegast veldu þá <b>gerð gagnagrunns</b> sem þú notar. Ef þú ert að nota SQL þjón eða Microsoft Access, veldu ODBC.",
	"DB_PHP_LIB_FIELD" => "PHP safn",
	"DB_HOST_FIELD" => "Nafn hýsistölvu",
	"DB_HOST_DESC" => "Vinsamlegast sláðu inn <b>nafn</b> eða <b>IP fang þjónsins</b> sem mun hýsa ViArt gagnagrunninn. Ef þú notar heimilistölvu þína til að hýsa gagnagrunninn ætti að duga að slá inn \"<b>localhost</b>\" og skilja port/tengi eftir tómt. Ef þú ert að nota gagnagrunn hjá hýsingaraðila skaltu athuga upplýsingar frá hýsingaraðila varðandi stillingar þjónsins.",
	"DB_PORT_FIELD" => "Tengi",
	"DB_NAME_FIELD" => "Nafn gagnagrunns / DSN",
	"DB_NAME_DESC" => "Ef þú ert að nota MySQL eða PostgreSQL eða aðra sambærilega gagnagrunna, vinsamlegast sláðu inn <b>nafn gagnagrunnsins</b> þar sem þú vilt að ViArt útbúi töflur gagnagrunnsins. Þessi gagnagrunnur verður að vera til í kerfinu nú þegar. Ef þú ert aðeins að setja upp ViArt í tilraunaskyni á þína persónulegu tölvu, þá eiga flest kerfi að bjóða upp á \"<b>prufu</b>\" gagnagrunn sem þú getur notað. Ef ekki, útbúðu þá gagnagrunn eins og t.d. \"viart\" og notaðu hann. Ef þú ert að nota Microsoft Access eða SQL þjón skal nafn gagnagrunnsins vera <b>DSN nafnið</b> sem þú hefur uppsett í Gagnaveitu (ODBC) hluta stjórnborðsins (Control Panel).",
	"DB_USER_FIELD" => "Notendanafn",
	"DB_PASS_FIELD" => "Lykilorð",
	"DB_USER_PASS_DESC" => "<b>Notendanafn</b> og <b>lykilorð</b> - Vinsamlegast sláðu inn það notendanafn og lykilorð sem þú vilt nota til að fá aðgang að gagnagrunninum. Ef þú ert að nota reynsluútgáfu er notendanafnið líklega \"<b>root</b>\" og lykilorðareiturinn hafður tómur. Slíkt fyrirkomulag er ágætt fyrir tilraunaútgáfu, en það ber að athuga að það er ekki öruggt á framleiðsluþjónum.",
	"DB_PERSISTENT_FIELD" => "Stöðug tenging",
	"DB_PERSISTENT_DESC" => "Til að nota MySQL eða Postgre stöðuga tengingu, merktu í þennan hakreit. Ef þú veist ekki hvað þetta þýðir, er líklega best að haka ekki í reitinn.",
	"DB_CREATE_DB_FIELD" => "Útbúa gagnagrunn",
	"DB_CREATE_DB_DESC" => "hakaðu hér viljir þú láta stofna gagnagrunn sjálfvirkt. Virkar aðeins á MySQL og Postgres",
	"DB_POPULATE_FIELD" => "Fylla inn í gagnagrunn",
	"DB_POPULATE_DESC" => "Til að útbúa töfluskipan gagnagrunns og fylla með gögnum, merktu í hakreitinn.",
	"DB_TEST_DATA_FIELD" => "Tilraunagögn",
	"DB_TEST_DATA_DESC" => "til að bæta tilraunagögnum við gagnagrunninn, merktu í hakreitinn",
	"ADMIN_EMAIL_FIELD" => "Netfang stjórnanda",
	"ADMIN_LOGIN_FIELD" => "Notendanafn stjórnanda",
	"ADMIN_PASS_FIELD" => "Lykilorð stjórnanda",
	"ADMIN_CONF_FIELD" => "Staðfesta lykilorð",
	"DATETIME_SHOWN_FIELD" => "Snið dags. og tíma (sést á vef)",
	"DATE_SHOWN_FIELD" => "Dags. snið (sést á vef)",
	"DATETIME_EDIT_FIELD" => "Snið dags. og tíma (þegar ritstýrt)",
	"DATE_EDIT_FIELD" => "Dags. snið (þegar ritstýrt)",
	"DATE_FORMAT_COLUMN" => "Snið dagsetningar",

	"DB_LIBRARY_ERROR" => "PHP skipanir fyrir {db_library} hafa ekki verið skilgreindar. Vinsamlegast athugaðu gagnagrunnsstillingarnar í PHP stillingum þínum - php.ini.",
	"DB_CONNECT_ERROR" => "Ekki tókst að tengjast gagnagrunni. Vinsamlegast athugaðu gagnagrunnsbreyturnar.",
	"INSTALL_FINISHED_ERROR" => "Uppsetningarferli var nú þegar lokið.",
	"WRITE_FILE_ERROR" => "Hef ekki heimild til að skrifa í skrá <b>'includes/var_definition.php'</b>. Vinsamlegast athugaðu skráarréttindin áður en þú heldur áfram",
	"WRITE_DIR_ERROR" => "Hef ekki heimild til að skrifa í möppu  <b>'includes/'</b>. Vinsamlegast athugaðu möppuréttindi áður en þú heldur áfram",
	"DUMP_FILE_ERROR" => "Dump skráin '{file_name}' fannst ekki.",
	"DB_TABLE_ERROR" => "Tafla '{file_name}' fannst ekki. Vinsamlegast settu viðeigandi gögn inn í gagnagrunninn.",
	"TEST_DATA_ERROR" => "Athuga skal <b>{POPULATE_DB_FIELD}</b> áður en töflur eru fylltar með tilraunagögnum.",
	"DB_HOST_ERROR" => "Hýsisnafnið sem þú tilgreindir fannst ekki.",
	"DB_PORT_ERROR" => "Ekki tókst að tengjast gagnagrunni með tilgreindu tengi",
	"DB_USER_PASS_ERROR" => "Notendanafnið eða lykilorðið sem þú slóst inn er ekki rétt",
	"DB_NAME_ERROR" => "Skráningarstillingarnar voru réttar, en gagnagrunnurinn '{db_name}' fannst ekki.",

	// upgrade messages
	"UPGRADE_TITLE" => "ViArt Verslun uppfærsla",
	"UPGRADE_NOTE" => "Gættu þess að útbúa afrit af gagnagrunni áður en lengra er haldið.",
	"UPGRADE_AVAILABLE_MSG" => "Gagnagrunnsuppfærsla fáanleg",
	"UPGRADE_BUTTON" => "Uppfæra gagnagrunn í {version_number} núna",
	"CURRENT_VERSION_MSG" => "Sú útgáfa sem uppsett er núna",
	"LATEST_VERSION_MSG" => "Útgáfa fáanleg til uppsetningar",
	"UPGRADE_RESULTS_MSG" => "Niðurstöður uppfærslu",
	"SQL_SUCCESS_MSG" => "SQL leitir/fyrirspurnir tókust",
	"SQL_FAILED_MSG" => "SQL leitir/fyrirspurnir mistókust",
	"SQL_TOTAL_MSG" => "Samtals SQL leitir/fyrirspurnir framkvæmdar",
	"VERSION_UPGRADED_MSG" => "Gagnagrunnur þinn hefur verið uppfærður í",
	"ALREADY_LATEST_MSG" => "Þú hefur nú þegar nýjustu útgáfu",
	"DOWNLOAD_NEW_MSG" => "Nýja útgáfan fannst",
	"DOWNLOAD_NOW_MSG" => "Niðurhala útgáfa {version_number} nú",
	"DOWNLOAD_FOUND_MSG" => "Útgáfa {version_number} er nú fáanleg til niðurhals. Smelltu á slóðina hér að neðan til að hefja niðurhal. Eftir að niðurhali er lokið og nýjum skrám komið fyrir, ekki gleyma að keyra uppfærlsuferlið aftur.",
	"NO_XML_CONNECTION" => "Viðvörun! Engin tenging við 'http://www.viart.com/' til staðar!",

	"END_USER_LICENSE_AGREEMENT_MSG" => "End User License Agreement",
	"AGREE_LICENSE_AGREEMENT_MSG" => "I have read and agree to the License Agreement",
	"READ_LICENSE_AGREEMENT_MSG" => "Click here to read license agreement",
	"LICENSE_AGREEMENT_ERROR" => "Please read and agree to the License Agreement before proceeding.",

);
$va_messages = array_merge($va_messages, $messages);
