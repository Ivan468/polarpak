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
	"DOWNLOAD_WRONG_PARAM" => "ערכי טעינה שגויים",
	"DOWNLOAD_MISS_PARAM" => "ערכי טעינה חסרים",
	"DOWNLOAD_INACTIVE" => "טעינה לא פעילה",
	"DOWNLOAD_EXPIRED" => "פג תוקף תקופת הטעינה שלך",
	"DOWNLOAD_LIMITED" => "עברת אתמיספר הטעינות המירבי",
	"DOWNLOAD_PATH_ERROR" => "לא נימצא מסלול המוצר",
	"DOWNLOAD_RELEASE_ERROR" => "לא נמצאה ההוצאה",
	"DOWNLOAD_USER_ERROR" => "טעינהת קובץ זה רק למשתמשים רשומים",
	"ACTIVATION_OPTIONS_MSG" => "אפשרויות הפעלה",
	"ACTIVATION_MAX_NUMBER_MSG" => "מיספר הפעלות מירבי",
	"DOWNLOAD_OPTIONS_MSG" => "אפשרויות למוצרי טעינה / תוכנות",
	"DOWNLOADABLE_MSG" => "מוצרי טעינה (תוכנות)",
	"DOWNLOADABLE_DESC" => "למוצרי טעינה ניתן לציין 'תקופת טעינה', 'מסלול לקובץ טעינה', ו-'אפשרויות הפעלה'",
	"DOWNLOAD_PERIOD_MSG" => "תקופת טעינה",
	"DOWNLOAD_PATH_MSG" => "מסלול לקובץ טעינה",
	"DOWNLOAD_PATH_DESC" => "אתה יכול להוסיףמיספר מסלולים מופרדים ע\"י נקודה-פסיק",
	"UPLOAD_SELECT_MSG" => "בחר קובץ להעלאה ולחץ על כפתור {button_name} ",
	"SPECIFY_PATH_FILE_MSG" => "Or please specify the path to your file and press 'Continue' button.",
	"UPLOADED_FILE_MSG" => "קובץ <b>{filename}</b> הועלה.",
	"UPLOAD_SELECT_ERROR" => "נא לבחור קובץ תחילה",
	"UPLOAD_IMAGE_ERROR" => "מותרים רק קבצי תמונה",
	"UPLOAD_FORMAT_ERROR" => "סוג קובץ זה אסור",
	"UPLOAD_SIZE_ERROR" => "קבצים גדולים מ- {filesize} אסורים",
	"UPLOAD_DIMENSION_ERROR" => "תמונות גדולות מ- {dimension} אסורים",
	"UPLOAD_CREATE_ERROR" => "המערכת אינה יכולה ליצור את הקובץ",
	"UPLOAD_ACCESS_ERROR" => "אין לך אישור להעלאת קבצים",
	"DELETE_FILE_CONFIRM_MSG" => "האם אתה בטוח שברצונך למחוק קובץ זה?",
	"NO_FILES_MSG" => "לא נמצאו קבצים",
	"SERIAL_GENERATE_MSG" => "צורמיספר סידורי",
	"SERIAL_DONT_GENERATE_MSG" => "אל תיצור",
	"SERIAL_RANDOM_GENERATE_MSG" => "צורמיספר אקראי עבור מוצר תוכנה",
	"SERIAL_FROM_PREDEFINED_MSG" => "קבלמיספר סידורי מרשימה מוגדרת מראש",
	"SERIAL_PREDEFINED_MSG" => "מיספר סידורי מוגדר מראש",
	"SERIAL_NUMBER_COLUMN" => "מיספר סידורי",
	"SERIAL_USED_COLUMN" => "בשימוש",
	"SERIAL_DELETE_COLUMN" => "מחק",
	"SERIAL_MORE_MSG" => "להוסיף עודמיספרים סדוריים?",
	"SERIAL_PERIOD_MSG" => "תקופתמיספר סידורי",
	"DOWNLOAD_SHOW_TERMS_MSG" => "הראה תנאים ",
	"DOWNLOAD_SHOW_TERMS_DESC" => "כדי להוריד קובץ זה על המשתמש לקרוא ולהסכים לתנאים שלנו.",
	"DOWNLOAD_TERMS_USER_ERROR" => "כדי להוריד קובץ זה עלך לקרוא ולהסכים לתנאים שלנו.",

	"DOWNLOAD_TITLE_MSG" => "כותרת טעינה",
	"DOWNLOADABLE_FILES_MSG" => "קבצים ניתנים להורדה",
	"DOWNLOAD_INTERVAL_MSG" => "מירווח הורדה",
	"DOWNLOAD_LIMIT_MSG" => "גבולות הורדה",
	"DOWNLOAD_LIMIT_DESC" => "מיספר פעמים שקובץ מורשה להורדה",
	"MAXIMUM_DOWNLOADS_MSG" => "מיספר הורדות מירבי",
	"PREVIEW_TYPE_MSG" => "סוג צפיה מראש",
	"PREVIEW_TITLE_MSG" => "כותרת צפיה מראש",
	"PREVIEW_PATH_MSG" => "מסלול לצפיה מראש בקובץ",
	"PREVIEW_IMAGE_MSG" => "תמונה לצפיה מראש",
	"MORE_FILES_MSG" => "עוד קבצים",
	"UPLOAD_MSG" => "העלאה",
	"USE_WITH_OPTIONS_MSG" => "לשימוש עם אפשרויות בלבד",
	"PREVIEW_AS_DOWNLOAD_MSG" => "צפיה מראש תוך כדי הורדה",
	"PREVIEW_USE_PLAYER_MSG" => "השתמש בנגן לצפיה מראש",
	"PROD_PREVIEWS_MSG" => "צפיות מראש",

);
$va_messages = array_merge($va_messages, $messages);
