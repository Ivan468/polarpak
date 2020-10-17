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
	"INSTALL_TITLE" => "Εγκατάσταση ViArt SHOP",

	"INSTALL_STEP_1_TITLE" => "Εγκατάσταση Βήμα 1",
	"INSTALL_STEP_1_DESC" => "Σας ευχαριστούμε για την επιλογή του καταστήματος ViArt SHOP. <br>Παρακαλούμε συμπληρώστε όλες τις λεπτομέρειες που θα σας ζητηθούν.<br>Προσέξτε σαν πρώτο Βήμα να υπάρχει ήδη η βάση δεδομένων. Εάν χρησιμοποιείτε σαν βάση δεδομένων την ODBC, π.χ. MS Access  πρέπει πρώτα να δημιουργήσετε τα  dsn αλλιώς η Εγκατάσταση δεν θα προχωρήσει.",
	"INSTALL_STEP_2_TITLE" => "Εγκατάσταση Βήμα 2",
	"INSTALL_STEP_2_DESC" => "",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "Εγκατάσταση Βήμα 3",
	"INSTALL_STEP_3_DESC" => "Επιλέξτε την εμφάνιση της ιστοσελίδας σας , αργότερα μπορείτε να αλλάξετε την επιλογή σας.",
	"INSTALL_FINAL_TITLE" => "Τέλος εγκατάστασης",
	"SELECT_DATE_TITLE" => "επιλέξτε πως θα εμφανίζετε η Ημερομηνία",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "Ρυθμίσεις βάσης δεδομένων",
	"DB_PROGRESS_MSG" => "Populating database structure progress",
	"SELECT_PHP_LIB_MSG" => "επιλέξτε Βιβλιοθήκη PHP",
	"SELECT_DB_TYPE_MSG" => "επιλέξτε τύπο βάσης δεδομένων",
	"ADMIN_SETTINGS_MSG" => "Ρυθμίσεις διαχείρισης",
	"DATE_SETTINGS_MSG" => "Ρυθμίσεις ημερομηνίας",
	"NO_DATE_FORMATS_MSG" => "Δεν υπάρχουν διαθέσιμες επιλογές ημερομηνίας",
	"INSTALL_FINISHED_MSG" => "Σε αυτό το σημείο η Εγκατάσταση ολοκληρώθηκε , Παρακαλώ βεβαιωθείτε ότι Έχετε βάλει τις σωστές παραμέτρους",
	"ACCESS_ADMIN_MSG" => "Για πρόσβαση στο κοντρόλ πάνελ κάντε κλικ ΕΔΏ",
	"ADMIN_URL_MSG" => "URL Διαχειριστή",
	"MANUAL_URL_MSG" => "Manual URL",
	"THANKS_MSG" => "ευχαριστούμε που επιλέξατε το <b>ViArt SHOP</b>.",

	"DB_TYPE_FIELD" => "Τύπος βάσης δεδομένων",
	"DB_TYPE_DESC" => "Please select the <b>type of database</b> that you are using. If you using SQL Server or Microsoft Access, please select ODBC.",
	"DB_PHP_LIB_FIELD" => "Βιβλιοθήκη PHP",
	"DB_HOST_FIELD" => "Hostname",
	"DB_HOST_DESC" => "Please enter the <b>name</b> or <b>IP address of the server</b> on which your ViArt database will run. If you are running your database on your local PC then you can probably just leave this as \"<b>localhost</b>\" and the port blank. If you using a database provided by your hosting company, please see your hosting company's documentation for the server settings.",
	"DB_PORT_FIELD" => "Port",
	"DB_NAME_FIELD" => "Όνομα βάσης δεδομένων / DSN",
	"DB_NAME_DESC" => "If you are using a database such as MySQL or PostgreSQL then please enter the <b>name of the database</b> where you would like ViArt to create its tables. This database must exist already. If you are just installing ViArt for testing purposes on your local PC then most systems have a \"<b>test</b>\" database you can use. If not, please create a database such as \"viart\" and use that. If you are using Microsoft Access or SQL Server then the Database Name should be the <b>name of the DSN</b> that you have set up in the Data Sources (ODBC) section of your Control Panel.",
	"DB_USER_FIELD" => "Όνομα χρήστη",
	"DB_PASS_FIELD" => "Κωδικός",
	"DB_USER_PASS_DESC" => "<b>Username</b> and <b>Password</b> - please enter the username and password you want to use to access the database. If you are using a local test installation the username is probably \"<b>root</b>\" and the password is probably blank. This is fine for testing, but please note that this is not secure on production servers.",
	"DB_PERSISTENT_FIELD" => "Persistent Connection",
	"DB_PERSISTENT_DESC" => "to use MySQL or Postgre persistent connections, tick this box. If you do not know what it means, then leaving it unticked is probably best.",
	"DB_CREATE_DB_FIELD" => "Create DB",
	"DB_CREATE_DB_DESC" => "to create database if possible, tick this box. Works only for MySQL and Postgre",
	"DB_POPULATE_FIELD" => "Γεμίστε την βάση δεδομένων",
	"DB_POPULATE_DESC" => "Τσεκάρετε εδώ για δημιουργήσετε την δομή της βάσης και να συμπληρώσετε τα δεδομένα της",
	"DB_TEST_DATA_FIELD" => "Test Data",
	"DB_TEST_DATA_DESC" => "to add some test data to your database tick the checkbox",
	"ADMIN_EMAIL_FIELD" => "E-mail Διαχειριστή",
	"ADMIN_LOGIN_FIELD" => "Είσοδος Διαχειριστή",
	"ADMIN_PASS_FIELD" => "Κωδικός Διαχειριστή",
	"ADMIN_CONF_FIELD" => "Ξαναγράψτε τον κωδικό",
	"DATETIME_SHOWN_FIELD" => "Εμφάνιση ημερομηνίας και ώρας(Στην ιστοσελίδα)",
	"DATE_SHOWN_FIELD" => "Εμφάνιση ημερομηνίας (στην ιστοσελίδα)",
	"DATETIME_EDIT_FIELD" => "Ημερομηνία και ώρα (για επεξεργασία)",
	"DATE_EDIT_FIELD" => "Ημερομηνία (για επεξεργασία)",
	"DATE_FORMAT_COLUMN" => "Εμφάνιση ημερομηνίας",

	"DB_LIBRARY_ERROR" => "PHP functions for {db_library} are not defined. Please check your database settings in your configuration file - php.ini.",
	"DB_CONNECT_ERROR" => "Δεν μπορώ να επικοινωνήσω με την βάση δεδομένων , Παρακαλώ ελξτε τις παραμέτρους",
	"INSTALL_FINISHED_ERROR" => "Η διαδικασία εγκατάστασης Έχει ήδη τελειώσει",
	"WRITE_FILE_ERROR" => "Δεν Έχετε άδεια να αλλάξετε αυτό το αρχείο <b>'includes/var_definition.php'</b>.Παρακαλώ αλλάξτε τις ιδιότητες του αρχείου πριν να συνεχίσετε",
	"WRITE_DIR_ERROR" => "Δεν Έχετε άδεια να αλλάξετε αυτόν τον φάκελο <b>'includes/'</b>.Παρακαλώ αλλάξτε τις ιδιότητες του φακέλου πριν να συνεχίσετε",
	"DUMP_FILE_ERROR" => "Το αρχείο '{file_name}' δεν βρέθηκε !",
	"DB_TABLE_ERROR" => "Ο πίνακας '{table_name} δεν βρέθηκε ! Παρακαλώ προσθέστε στην βάση δεδομένων τα απαραίτητα στοιχεία.",
	"TEST_DATA_ERROR" => "Check <b>{POPULATE_DB_FIELD}</b> before populating tables with test data",
	"DB_HOST_ERROR" => "The hostname that you specified could not be found.",
	"DB_PORT_ERROR" => "Can't connect to database server using specified port.",
	"DB_USER_PASS_ERROR" => "The username or password you specified is not correct.",
	"DB_NAME_ERROR" => "Login settings were correct, but the database '{db_name}' could not be found.",

	// upgrade messages
	"UPGRADE_TITLE" => "ViArt SHOP αναβάθμιση",
	"UPGRADE_NOTE" => "Σημείωση : Αποθηκεύστε την βάση δεδομένων πριν συνεχίσετε.",
	"UPGRADE_AVAILABLE_MSG" => "Είναι διαθέσιμη αναβάθμιση του προγράμματος",
	"UPGRADE_BUTTON" => "Αναβαθμίστε σε {version_number} Τώρα ",
	"CURRENT_VERSION_MSG" => "Έχεις ήδη εγκαταστήσει αυτήν την έκδοση",
	"LATEST_VERSION_MSG" => "έκδοση διαθέσιμη για εγκαταση",
	"UPGRADE_RESULTS_MSG" => "Αποτελέσματα αναβάθμισης",
	"SQL_SUCCESS_MSG" => "SQL ερωτήματα επιτυχή",
	"SQL_FAILED_MSG" => "SQL ερωτήματα λανθασμένα",
	"SQL_TOTAL_MSG" => "Όλα τα SQL ερωτήματα έχουν πραγματοποιηθεί",
	"VERSION_UPGRADED_MSG" => "Η έκδοση σας Έχει αναβαθμιστεί σε",
	"ALREADY_LATEST_MSG" => "Έχεις ήδη την πιο πρόσφατη έκδοση",
	"DOWNLOAD_NEW_MSG" => "The new version was detected",
	"DOWNLOAD_NOW_MSG" => "Download version {version_number} now",
	"DOWNLOAD_FOUND_MSG" => "We have detected that the new {version_number} version is available to download. Please click the link below to start downloading. After completing the download and replacing the files don't forget to run Upgrade routine again.",
	"NO_XML_CONNECTION" => "Warning! No connection to 'http://www.viart.com/' available!",

	"END_USER_LICENSE_AGREEMENT_MSG" => "End User License Agreement",
	"AGREE_LICENSE_AGREEMENT_MSG" => "I have read and agree to the License Agreement",
	"READ_LICENSE_AGREEMENT_MSG" => "Click here to read license agreement",
	"LICENSE_AGREEMENT_ERROR" => "Please read and agree to the License Agreement before proceeding.",

);
$va_messages = array_merge($va_messages, $messages);
