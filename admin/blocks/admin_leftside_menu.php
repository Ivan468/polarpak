<?php

	$admin_leftside_menu_tree = array(
		DASHBOARD_MSG => array(
			"href" => $admin_site_url . "admin.php",
			"class"=> "dboard",
			"subs" => array(
				PRODUCTS_MSG => array(
					"href" => $admin_site_url . "admin_items_list.php",
					"code" => 1,
					"perm" => "products_categories",
					"subs" => array(
						PRODUCTS_CATEGORIES_MSG => array(
							"href" => $admin_site_url . "admin_items_list.php",
							"perm" => "products_categories",
							"subs" => array(
								CATEGORY_EDIT_MSG           => "admin_category_edit.php",
								CATEGORY_PRODUCTS_MSG       => "admin_category_items.php",
								CHANGE_CATEGORIES_ORDER_MSG => "admin_categories_order",
								EDIT_PRODUCT_MSG            => "admin_product.php",
								ADD_PRODUCT_MSG             => "admin_product_add.php",
								PRICES_MSG                  => "admin_item_prices.php",
								IMAGES_MSG                  => "admin_item_images.php",
								EDIT_IMAGE_MSG              => "admin_item_image.php",
								PROD_SPECIFICATION_MSG      => "admin_item_features.php",
								RELATED_PRODUCTS_TITLE      => "admin_item_related.php",
								CATEGORIES_TITLE            => "admin_item_categories.php",
								PROD_ACCESSORIES_MSG        => "admin_item_accessories.php",
								RELATED_FORUMS_MSG          => "admin_item_forums_related.php",
								RELATED_ARTICLES_MSG        => "admin_item_articles_related.php",
								EDIT_SELECTED_MSG           => "admin_products_edit.php",
								CHANGE_PRODUCTS_ORDER_MSG   => "admin_products_order.php",
								PRODUCT_SELECTION_MSG       => "admin_products_copy_properties.php",
								RELEASES_TITLE              => "admin_releases.php",
								EDIT_RELEASE_MSG            => "admin_release.php",
								EDIT_RELEASE_CHANGES_MSG    => "admin_release_changes.php",
								OPTIONS_AND_COMPONENTS_MSG  => array(
									"href"    => "admin_properties.php",
									"not_set" => "item_type_id"
								),
								EDIT_PRODUCT_MSG . ' ' . OPTION_MSG => array(
									"href"    => "admin_property.php",
									"not_set" => "item_type_id"
								),
								EDIT_SUBCOMP_MSG             => array(
									"href"    => "admin_component_single.php",
									"not_set" => "item_type_id"
								),
								EDIT_SUBCOMP_SELECTION_MSG  => array(
									"href"    => "admin_component_selection.php",
									"not_set" => "item_type_id"
								),
								EXPORT_MSG => "admin_export.php?table=items",
								EDIT_CUSTOM_FIELD_MSG => "admin_export_custom.php",
								IMPORT_MSG => "admin_import.php?table=items",
								IMPORT_PRODUCTS_MSG . ' ' . ADMIN_DOWNLOADABLE_MSG => "admin_import.php?table=items_files",
							),
							"additional_title" => array(
								"table" => $table_prefix . "items",
								"id"    => "item_id",
								"title" => "item_name",
								"href"  => "admin_product.php"
							),
							"additional_tree" => array(
								"table" => $table_prefix . "categories",
								"id"    => "category_id",
								"title" => "category_name"
							)							
						),
						PRODUCTS_REVIEWS_MSG    => array(
							"href" => $admin_site_url . "admin_reviews.php",
							"perm" => "products_reviews",
							"subs" => array(
								EDIT_REVIEW_MSG => "admin_review.php",
							)
						),
						COUPONS_MSG             => array(
							"href" => $admin_site_url . "admin_coupons.php",
							"perm" => "coupons",
							"subs" => array(
								EDIT_COUPON_MSG => "admin_coupon.php"
							)
						),
						WISHLIST_TYPES_MSG      => array(
							"href" => $admin_site_url . "admin_saved_types.php",
							"perm" => "saved_types",
							"subs" => array(
								EDIT_TYPE_MSG => "admin_saved_type.php"
							)
						),
						PRODUCTS_REPORT         => array(
							"href" => $admin_site_url . "admin_products_report.php",
							"perm" => "products_report"
						),
						KEYWORDS_MSG => array(
							"href" =>  $admin_site_url . "admin_keywords.php",
							"perm" => "update_products"
						),
					),
				),
				ORDERS_MSG => array(
					"href" => $orders_list_site_url . "admin_orders.php",
					"code" => 1,
					"perm" => "sales_orders",
					"subs" => array(
						SALES_ORDERS_MSG       => array(
							"href" => $orders_list_site_url  . "admin_orders.php",
							"perm" => "sales_orders",
							"subs" => array(
								ADMIN_ORDER_MSG => "admin_order.php",
								EDIT_MSG . ' ' . ADMIN_ORDER_MSG. ' ' .ADMIN_PRODUCT_MSG => "admin_order_item.php",
								ORDER_NOTES_MSG => "admin_order_notes.php",
								ORDER_NOTE_MSG => "admin_order_note.php",
								SEND_EMAIL_MESSAGE_MSG => "admin_order_email.php",
								DOWNLOAD_LINKS_MSG => "admin_order_links.php",
								ADMIN_EDIT_LINK_MSG => "admin_order_link.php",
								ADMIN_SERIAL_NUMBERS_MSG => "admin_order_serials.php",
								EDIT_SERIAL_NUMBER_MSG => "admin_order_serial.php",
								EXPORT_MSG => "admin_export.php?table=orders",
								IMPORT_MSG => "admin_import.php?table=orders",
								EDIT_MSG . ' ' . PERSONAL_DETAILS_MSG => "admin_order_edit.php"
							),
							"additional_title" => array(
								"table" => $table_prefix . "orders",
								"id"    => "order_id",
								"title" => "order_id",
								"href"   => "admin_order.php",
								"title_prefix" => "Order #"
							)
						),

						ORDERS_REPORTS_MSG     =>  array(
							"href" => $orders_pages_site_url . "admin_orders_report.php",
							"perm" => "orders_stats"
						),
						CARTS_REPORT_MSG     =>  array(
							"href" => $orders_pages_site_url . "admin_carts_report.php",
							"perm" => "orders_stats"
						),
						ORDERS_RECOVER_MSG => array(
							"href" => $orders_pages_site_url . "admin_orders_recover.php",
							"perm" => "orders_recover"
						)		
					)				
				),
				ARTICLES_TITLE => array(
					"href" => $admin_site_url . "admin_articles_all.php",					
					"code" => 2,
					"perm" => "articles",
					"subs" => array(
						ARTICLES_REVIEWS_MSG => array(
							"href" => $admin_site_url . "admin_articles_reviews.php",
							"perm" => "articles_reviews",
							"subs" => array(
								EDIT_REVIEW_MSG => "admin_articles_review.php",
								EDIT_REVIEW_MSG => "admin_article_review.php"
							)
						),
						ARTICLES_LOST_MSG => array(
							"href" => $admin_site_url . "admin_articles_lost.php",
							"perm" => "articles_lost"
						),
						ARTICLES_TITLE              		=> "admin_articles.php",
						EDIT_ARTICLE_MSG            		=> "admin_article.php",
						ASSIGN_CATEGORIES_MSG       		=> "admin_articles_assign.php",
						CHANGE_CATEGORIES_ORDER_MSG 		=> "admin_articles_categories.php",
						EDIT_CATEGORY_MSG           		=> "admin_articles_category.php",
						CHANGE_ARTICLES_ORDER_MSG   		=> "admin_articles_order.php",
						ARTICLE_RELATED_PRODUCTS_TITLE 	=> "admin_article_items_related.php",
						CATEGORY_RELATED_PRODUCTS_TITLE => "admin_article_category_items_related.php",
						RELATED_FORUMS_MSG          		=> "admin_article_forums_related.php",
						RELATED_ARTICLES_MSG 						=> "admin_article_related.php",
						IMAGES_MSG 						=> "admin_article_images.php",
						IMAGE_MSG 						=> "admin_article_image.php",
					),
					"additional_tree" => array(
						"table" => $table_prefix . "articles_categories",
						"id"    => "category_id",
						"title" => "category_name",
						"href" => $admin_site_url . "admin_articles.php",					
					)
				),
				HELPDESK_MSG => array(
					"href" => $tickets_site_url . "admin_support.php",
					"code" => 4,
					"perm" => "support",
					"subs" => array(
						SUPPORT_TICKETS_MSG => array(
							"href" => $tickets_site_url . "admin_support.php",
							"perm" => "support",
							"subs" => array(
								VIEW_REPLY_MSG => "admin_support_reply.php",
								EDIT_REQUEST_MSG => "admin_support_request.php"
							)
						),
						PREDEFINED_REPLIES_MSG => array(
							"href" => $helpdesk_site_url . "admin_support_prereplies.php",
							"perm" => "support",
							"subs" => array(
								EDIT_PREDEFINED_REPLY_MSG => "admin_support_prereply.php"
							)
						),
						PREDEFINED_TYPES_MSG => array(
							"href" => $helpdesk_site_url . "admin_support_pretypes.php",
							"perm" => "support_predefined_reply",
							"subs" => array(
								EDIT_TYPE_MSG => "admin_support_pretype.php"
							)
						),
						OPERATORS_REPORT_MSG => array(
							"href" => $helpdesk_site_url . "admin_support_users_report.php",
							"perm" => "support_users_stats"
						),
						CHATS_MSG => array(
							"href" => $tickets_site_url . "admin_support_chats.php",
							"perm" => "support",
						),

					)
				),
				ADMIN_FORUM_TITLE => array(
					"href" => $admin_site_url . "admin_forum.php",
					"code" => 8,
					"perm" => "forum",
					"subs" => array(
						EDIT_CATEGORY_MSG => "admin_forum_category.php",
						EDIT_FORUM_MSG    => "admin_forum_edit.php",
						EDIT_TOPIC_MSG    => "admin_forum_topic.php",
						FORUM_TOPICS_THREAD_MSG => "admin_forum_thread.php",
						MESSAGE_MSG       => "admin_forum_message.php",
						ADMIN_RELATED_PRODUCTS_TITLE => "admin_forum_items_related.php",
						RELATED_ARTICLES_MSG => "admin_forum_articles_related.php",				
					),
					"additional_title" => array(
						"table" => $table_prefix . "forum",
						"id"    => "thread_id",
						"title" => "topic",
						"href"  => "admin_forum_thread.php"
					)					
				),
				MANUAL_MSG => array(
					"href" => $admin_site_url . "admin_manual.php",
					"code" => 32,
					"perm" => "manual",
					"subs" => array(
						EDIT_MANUAL_MSG . ' ' . CATEGORY_MSG => "admin_manual_category.php",
						EDIT_MANUAL_MSG => "admin_manual_edit.php",
						EDIT_MSG . ' ' . MANUAL_ARTICLE_MSG => "admin_manual_article.php"
					),
					"additional_title" => array(
						"table" => $table_prefix . "manuals_list",
						"id"    => "manual_id",
						"title" => "manual_title",
						"href"  => "admin_manual.php"
					)
				),
				ADS_TITLE => array(
					"href" => $admin_site_url . "admin_ads.php",
					"code" => 16,
					"perm" => "ads",
					"subs" => array(
						ADS_TITLE              => "admin_ads.php",
						ASSIGN_CATEGORIES_MSG  => "admin_ads_assign.php",
						EDIT_MSG               => "admin_ads_edit.php",
						EDIT_CATEGORY_MSG      => "admin_ads_category.php",
						CUSTOM_PROPERTIES_MSG  => "admin_ads_properties.php",
						AD_SPECIFICATION_MSG   => "admin_ads_features.php",
						ASSIGN_CATEGORIES_MSG  => "admin_ads_assign.php",
						IMAGES_MSG             => "admin_ads_images.php",
						IMAGE_MSG              => "admin_ads_image.php"					
					),
					"additional_title" => array(
						"table" => $table_prefix . "ads_items",
						"id"    => "item_id",
						"title" => "item_title",
						"href"  => "admin_ads_edit.php"
					),
					"additional_tree" => array(
						"table" => $table_prefix . "ads_categories",
						"id"    => "category_id",
						"title" => "category_name"
					)
				),
				PRODUCT_REGISTRATION_MSG => array(
					"href" => $admin_site_url . "admin_registrations.php",
					"code" => 1,
					"perm" => "admin_registration",
					"subs" => array(
						PRODUCT_REGISTRATION_MSG => array(
							"href" => $admin_site_url . "admin_registrations.php",
							"perm" => "admin_registration",
							"subs" => array(
								EDIT_PRODUCT_REGISTRATION_MSG => "admin_registration_edit.php",
								PRODUCT_REGISTRATION_MSG => "admin_registration_view.php",
								EXPORT_MSG => "admin_export.php?table=registrations"
							)
						),
						REGISTRATION_PRODUCTS_MSG  => array(
							"href" => $admin_site_url . "admin_registration_products.php",
							"perm" => "admin_registration",
							"subs" => array(
								EDIT_CATEGORY_MSG           => "admin_registration_category.php",
								EDIT_PRODUCT_MSG            => "admin_registration_product.php",
								CHANGE_CATEGORIES_ORDER_MSG => "admin_registration_categories_order.php",
								CATEGORY_PRODUCTS_MSG       => "admin_registration_products_categories.php",
								CHANGE_PRODUCTS_ORDER_MSG   => "admin_registration_products_order.php",
							),
							"additional_tree" => array(
								"table" => $table_prefix . "registration_categories",
								"id"    => "category_id",
								"title" => "category_name"
							)
						)
					)
				),
				CUSTOMERS_MSG => array(
					"href" => $admin_site_url . "admin_users.php",
					"subs" => array(
						ACCOUNTS_MSG => array(
							"href" => $admin_site_url . "admin_users.php",
							"subs" => array(
								ADMIN_USER_MSG => "admin_user.php",
								LOGIN_DETAILS_MSG => "admin_user_login.php",
								UPGRADE_DOWNGRADE_MSG => "admin_user_change_type.php",
								EXPORT_MSG => "admin_export.php?table=users",
								IMPORT_MSG => "admin_import.php?table=users",
								ADD_POINTS_MSG . ' ' => "admin_user_points.php",
								ADD_POINTS_MSG => "admin_user_point.php",
								"Add credits " => "admin_user_credits.php",
								"Add credits" => "admin_user_credit.php"
							),
							"additional_title" => array(
								"table" => $table_prefix . "user_types",
								"id"    => "type_id",
								"title" => "type_name",
								"href"  => "admin_user_type.php"
							)
						),						
					)
				),
				EMAIL_CAMPAIGNS_MSG => array(
					"href" => $admin_site_url . "admin_newsletter_campaigns.php",
					"subs" => array(
						EMAIL_CAMPAIGNS_MSG => array(
							"href" => $admin_site_url . "admin_newsletter_campaigns.php",
							"subs" => array(
								EMAIL_CAMPAIGN_MSG => "admin_newsletter_campaign.php",
								CAMPAIGN_NEWSLETTERS_MSG => "admin_newsletters.php",
								NEWSLETTER_MSG => "admin_newsletter.php",
								STATS_MSG => "admin_newsletter_stats.php",
								EMAILS_MSG => "admin_newsletter_emails.php",
								EMAIL_MSG => "admin_newsletter_email.php",
							),
							"additional_title" => array(
								"table" => $table_prefix . "newsletters",
								"id"    => "newsletter_id",
								"title" => "newsletter_subject",
								"href"  => "admin_newsletter.php"
							)
						),						
						NEWSLETTER_USERS_MSG => array(
							"href" => $admin_site_url . "admin_newsletter_users.php",
							"subs" => array(
								EDIT_MSG . ' ' . NEWSLETTER_USERS_MSG => "admin_newsletter_users_edit.php",
 								IMPORT_MSG => "admin_import.php?table=newsletters_users",
 								EXPORT_MSG => "admin_export.php?table=newsletters_users"
							)
						)
					)
				),
				SEARCH_TITLE    => array(
					"href" => "admin_global_search.php",
					"subs" => array(
						SEARCH_TITLE => array(
							"href" => "admin_global_search.php"
						)
					)				
				)
			)
		),
		CMS_MSG => array(
			"href" => $admin_site_url . "admin_cms.php",
			"class"=> "sbuilder",
			"subs" => array(
				CMS_MSG => array(
					"href" => $admin_site_url . "admin_cms.php",
					"subs" => array(
						PAGES_LAYOUTS_MSG => array(
							"href" => $admin_site_url . "admin_cms.php",
							"subs" => array(
								PAGE_LAYOUT_MSG => "admin_layout_page.php",
								LAYOUT_MSG => "admin_cms_page_layout.php"
							)
						),
						MULTI_EDIT_MSG => array(
							"href" => $admin_site_url . "admin_cms_multi_edit.php",
						),
						CMS_LAYOUTS_MSG => array(
							"href" => $admin_site_url . "admin_cms_layouts.php",
							"subs" => array(
								CMS_LAYOUT_MSG => "admin_cms_layout.php"
							)
						),
						CMS_MODULES_MSG => array(
							"href" => $admin_site_url . "admin_cms_modules.php",
							"subs" => array(
								MODULE_MSG => "admin_cms_module.php"
							)
						),
						CMS_PAGES_MSG => array(
							"href" => $admin_site_url . "admin_cms_pages.php",
							"subs" => array(
								PAGE_MSG => "admin_cms_page.php",
							)
						),
						CMS_BLOCKS_MSG => array(
							"href" => $admin_site_url . "admin_cms_blocks.php",
							"subs" => array(
								CMS_BLOCK_MSG => "admin_cms_block.php",
								OPTIONS_MSG => "admin_cms_block_properties.php",
								OPTION_MSG => "admin_cms_block_property.php"
							)
						),
						DESIGNS_MSG => array(
							"href" => $admin_site_url . "admin_designs.php",
							"subs" => array(
								EDIT_MSG => "admin_design.php",
								DESIGN_SCHEME_MSG => "admin_design_scheme.php"
							)
						),
						SITE_NAVIGATION_MSG => array(
							"href" => $admin_site_url . "admin_header_menus.php",
							"subs" => array(
								EDIT_MENU_MSG     => "admin_menu_item.php",
								CHANGE_ORDER_MSG  => "admin_header_menus_order.php",
								SUBMENU_ITEMS_MSG => "admin_menu_submenus.php",
								EDIT_SUBMENU_ITEM_MSG => "admin_menu_submenu.php",
								SUBMENU_ITEMS_MSG . ' ' . CHANGE_ORDER_MSG => "admin_header_submenus_order.php"
							),
							"additional_title" => array(
								"table" => $table_prefix . "header_links",
								"id"    => "menu_id",
								"title" => "menu_title",
								"href"  => "admin_menu_item.php"
							)
						),
						CUSTOM_MENUS_MSG => array(
							"href" => $admin_site_url . "admin_custom_menus.php",
							"subs" => array(
								EDIT_MENU_MSG => "admin_custom_menu.php",
								EDIT_MENU_ITEMS_MSG . ' ' . TREE_VIEW_MSG => "admin_menu_items.php",
								EDIT_MENU_ITEMS_MSG . ' ' . DATASHEET_VIEW_MSG => "admin_menu_datasheet.php"
							),
							"additional_title" => array(
								"table" => $table_prefix . "menus",
								"id"    => "menu_id",
								"title" => "menu_title",
								"href"  => "admin_custom_menu.php"
							)
						),
						CUSTOM_BLOCKS_MSG => array(
							"href" => $admin_site_url . "admin_custom_blocks.php",
							"subs" => array(
								EDIT_BLOCK_MSG => "admin_custom_block.php"
							)
						),
						/*
						CUSTOM_FORMS_MSG => array(
							"href" => $admin_site_url . "admin_custom_forms.php",
							"subs" => array(
								"Edit Form" => "admin_custom_form.php", 
								"Sent Forms" => "admin_custom_forms_sent.php", 
								"Sent Form" => "admin_custom_form_sent.php",
								"Edit Form Field" => "admin_custom_form_field.php"
							)
						),//*/
						CUSTOM_PAGES_MSG => array(
							"href" => $admin_site_url . "admin_pages.php",
							"subs" => array(
								EDIT_MSG => "admin_page.php"
							)
						),
						CUSTOM_FRIENDLY_URLS_MSG => array(
							"href" => $admin_site_url . "admin_friendly_urls.php",
							"subs" => array(
								EDIT_MSG . ' ' . FRIENDLY_URLS_MSG => "admin_friendly_url.php"
							)
						),
						BANNERS_GROUPS_MSG => array(
							"href" => $admin_site_url . "admin_banners_groups.php",
							"subs" => array(
								EDIT_GROUP_MSG => "admin_banners_group.php"
							)
						),
						BANNERS_MSG => array(
							"href" => $admin_site_url . "admin_banners.php",
							"subs" => array(
								EDIT_BANNER_MSG => "admin_banner.php"
							)
						),
						OPINION_POLLS_MSG => array(
							"href" => $admin_site_url . "admin_polls.php",
							"subs" => array(
								EDIT_POLL_MSG => "admin_poll.php"
							)
						),
						FILTERS_MSG => array(
							"href" => $admin_site_url . "admin_filters.php",
							"subs" => array(
								EDIT_MSG . ' ' . ADMIN_FILTER_MSG => "admin_filter.php",
								FILTER_OPTIONS_MSG => "admin_filter_properties.php",
								EDIT_MSG . ' ' . FILTER_OPTIONS_MSG => "admin_filter_property.php"
							),
							"additional_title" => array(
								"table" => $table_prefix . "filters",
								"id"    => "filter_id",
								"title" => "filter_name",
								"href"  => "admin_filter.php"
							)
						),
						SLIDERS_MSG => array(
							'href' => $admin_site_url . "admin_sliders.php",
							'subs' => array(
								EDIT_MSG . ' ' . ADMIN_ITEMS_MSG => array(
									"href" => "admin_slider_items.php"
								),
								EDIT_MSG => "admin_slider_item_edit.php",
								EDIT_MSG . ' ' . SLIDER_MSG => "admin_slider.php"	
							)							
						),						
					)
				)
			)
		),
		TOOLS_MSG => array(
			"href" => $admin_site_url . "admin_dump.php",
			"class"=> "tools",
			"subs" => array(
				DATABASE_MANAGEMENT_MSG => array(
					"href" => $admin_site_url . "admin_dump.php",
					"subs" => array(
						DATABASE_MANAGEMENT_MSG => array(
							"href" => "admin_dump.php"
						),
						CREATE_NEW_DUMP_MSG => array(
							"href" => "admin_dump_create.php"
						),
						APPLY_DUMP_MSG => "admin_dump_apply.php",
						RUN_SQL_QUERY_MSG => array(
							"href" => "admin_db_query.php"
						),
						EXPORT_TEMPLATES_MSG => array(
							"href" => $admin_site_url ."admin_export_templates.php",
							"subs" => array(
								EXPORT_TEMPLATE_MSG => "admin_export_template.php",
								EXPORT_FIELDS_MSG => "admin_export_fields.php",
								EXPORT_FIELD_MSG => "admin_export_field.php",
							),
						),
					)
				), //DB management

				TOOLS_MSG => array(
					"href" => $admin_site_url . "admin_fm.php",
					"subs" => array(
						FILE_MANAGER_MSG => array(
							"href" => $admin_site_url . "admin_fm.php",
							"subs" => array(
								VIEW_FILE_MSG => "admin_fm_view_file.php",
								UPLOAD_FILE_MSG => "admin_fm_upload_files.php",
								EDIT_FILE_MSG => "admin_fm_edit_file.php"
							),
						),
						FILE_TRANSFERS_MSG => array(
							"href" => $admin_site_url ."admin_file_transfers.php",
							"subs" => array(
								FILE_TRANSFER_MSG => "admin_file_transfer.php",
							),
						),
						RESIZE_IMAGES_MSG => array(
							"href" => $admin_site_url . "admin_images_resize.php",
							),
						BLACK_IPS_MSG => array(
							"href" => $admin_site_url . "admin_black_ips.php",
							"subs" => array(
								EDIT_IP_MSG => "admin_black_ip.php"
							)
						),
						COUNTRIES_IPS_MSG => array(
							"href" => $admin_site_url . "admin_ips_countries.php",
							),

						BANNED_CONTENT => array(
							"href" => $admin_site_url . "admin_banned_contents.php",
							"subs" => array(
								EDIT_TEXT_MSG => "admin_banned_content.php"
							)
						),
						TRACKING_VISITS_REPORT_MSG => array(
							"href" => $admin_site_url . "admin_visits_report.php"
						),
						SYSTEM_UPGRADE_MSG => array(
							"href" => "admin_upgrade.php"
						),
						BOOKMARKS_MSG => array(
							"href" => $admin_site_url ."admin_bookmarks.php",
							"subs" => array(
								EDIT_BOOKMARK_MSG => "admin_bookmark.php"
							)
						)
					) // subs (tools)
				)// tools
			)
		),
		SETTINGS_MSG => array(
			"href" => "admin_global_settings.php",
			"class"=> "settings",
			"subs" => array(
				SYSTEM_MSG => array(
					"href" => "admin_global_settings.php",
					"subs" => array(
						GLOBAL_SETTINGS_MSG => array(
							"href" => "admin_global_settings.php"
						),
						ADMIN_SITES_MSG => array(
							"href" => $admin_site_url . "admin_sites.php",
							"subs" => array(
								EDIT_SITE_MSG => "admin_site.php",
								SETTINGS_MSG => "admin_site_settings.php",
								PRODUCTS_MSG  => "admin_site_items.php"
							),
							"additional_title" => array(
								"table"  => $table_prefix . "sites",
								"id"     => "param_site_id",
								"where"  => "site_id",
								"title"  => "site_name",
								"href"   => "admin_site.php"
							)
						),
						ADMINISTRATORS_MSG => array(
							"href" => $admin_site_url . "admin_admins.php",
							"subs" => array(
								EDIT_ADMINISTRATOR_MSG => "admin_admin.php",
								CHANGE_PASSWORD_MSG    => "admin_admin_password.php"
							)
						),
						PRIVILEGE_GROUPS_MSG => array(
							"href" => $admin_site_url . "admin_privileges.php",
							"subs" => array(
								EDIT_PERMISSIONS_MSG => "admin_privileges_edit.php"
							)
						),
						TWO_FACTOR_AUTH_MSG => array(
							"href" => $admin_site_url . "settings_two_factor.php",
						),
						STATIC_TABLES_MSG => array(
							"href" => $admin_site_url . "admin_static_tables.php",
							"subs" => array(
								COUNTRIES_MSG => "admin_countries.php",
								EDIT_COUNTRY_MSG => "admin_country.php",
								STATES_MSG => "admin_states.php",
								EDIT_STATE_MSG => "admin_state.php",
								CREDIT_CARDS_MSG => "admin_credit_cards.php",
								EDIT_CREDIT_CARD_MSG => "admin_credit_card.php",
								CC_EXPIRY_YEARS_MSG => "admin_cc_expiry_years.php",
								CREDIT_CARD_EXPIRY_YEAR_MSG => "admin_cc_expiry_year.php",
								CC_START_YEARS_MSG => "admin_cc_start_years.php",
								CREDIT_CARD_START_YEAR_MSG => "admin_cc_start_year.php",
								ISSUE_NUMBERS_MSG => "admin_issue_numbers.php",
								EDIT_ISSUE_NUMBER_MSG => "admin_issue_number.php",								
								RELEASE_TYPES_MSG => "admin_release_types.php",
								EDIT_RELEASE_TYPE_MSG => "admin_release_type.php",
								CHANGE_TYPES_MSG => "admin_change_types.php",
								EDIT_CHANGE_TYPE_MSG => "admin_change_type.php",
								COMPANIES_MSG => "admin_companies.php",
								EDIT_COMPANY_MSG => "admin_company.php",
								LANGUAGE_TITLE => "admin_languages.php",
								EDIT_LANGUAGE_MSG => "admin_language.php",
								SEARCH_ENGINES_MSG => "admin_search_engines.php",
								EDIT_ENGINE_MSG => "admin_search_engine.php",
								CELL_PHONES_ALLOWED_NUMBERS_MSG => "admin_allowed_cell_phones.php",
								EDIT_MSG . ' ' . CELL_PHONES_ALLOWED_NUMBERS_MSG => "admin_allowed_cell_phone.php",
								GOOGLE_BASE_ITEM_TYPES_MSG => "admin_google_base_types.php",
								EDIT_MSG . ' ' . GOOGLE_BASE_ITEM_TYPES_MSG => "admin_google_base_type.php",								
								GOOGLE_BASE_ITEM_TYPES_MSG . ' ' . EDIT_MSG . ' ' . GOOGLE_BASE_ATTRIBUTE_MSG => "admin_google_base_type_attr.php",
								GOOGLE_BASE_ATTRIBUTE_MSG => "admin_google_base_attributes.php",
								EDIT_MSG . ' ' . GOOGLE_BASE_ATTRIBUTE_MSG => "admin_google_base_attribute.php",
								PRICE_CODES_MSG => "admin_price_codes.php",
								EDIT_MSG . ' ' . PRICE_CODES_MSG => "admin_price_code.php",
								EXPORT_TEMPLATES_MSG => "admin_export_templates.php",
								EXPORT_TEMPLATE_MSG => "admin_export_template.php",
								EXPORT_FIELDS_MSG => "admin_export_fields.php",
								EXPORT_FIELD_MSG => "admin_export_field.php",
							)
						),
						SYSTEM_STATIC_MESSAGES_MSG => array(
							"href" => $admin_site_url . "admin_messages.php",
							"subs" => array(
								EDIT_MESSAGE_MSG => "admin_message.php"
							)
						),
						CONTACT_US_MSG => array(
							"href" => "admin_contact_us.php"
						),
						INTERNAL_MESSAGES_MSG => array(
							"href" => "admin_messages_settings.php"
						),
					)
				),
				PRODUCTS_MSG => array(
					"href" => $admin_site_url . "admin_products_settings.php",
					"code" => 1,
					"subs" => array(
						PRODUCTS_SETTINGS_MSG => array(
							"href" => $admin_site_url . "admin_products_settings.php"
						),
						SUPPLIERS_MSG => array(
							"href" => $admin_site_url . "admin_suppliers.php",
							"subs" => array(
								EDIT_SUPPLIER_MSG => "admin_supplier.php"
							)
						),
						SPECIFICATION_GROUPS_MSG => array(
							"href" => $admin_site_url . "admin_features_groups.php",
							"subs" => array(
								EDIT_GROUP_MSG => "admin_features_group.php"
							)
						),
						/*PRODUCTS_NOTIFICATIONS_MSG => array(
							"href" => $admin_site_url . "admin_products_notify.php"
						),*/
						REVIEWS_SETTINGS_MSG => array(
							"href" => $admin_site_url . "admin_products_reviews_sets.php"
						),
						DOWNLOADABLE_PRODUCTS_MSG => array(
							"href" => $admin_site_url . "admin_download_info.php"
						),
						ADVANCED_SEARCH_TITLE => array(
							"href" => $admin_site_url . "admin_search.php"
						),
						TELL_FRIEND => array(
							"href" =>  $admin_site_url . "admin_tell_friend.php?type=products"
						),
						SAVED_CART_NOTIFICATION_MSG => array(
							"href" => $admin_site_url . "admin_saved_cart_notify.php"
						),
						PRODUCTS_TYPES_MSG      => array(
							"href" => $admin_site_url . "admin_item_types.php",
							"perm" => "product_types",
							"subs" => array(
								EDIT_PRODUCT_TYPE_MSG        => "admin_item_type.php",
								OPTIONS_AND_COMPONENTS_MSG   => "admin_properties.php",
								PREDEFINED_SPECIFICATION_MSG => "admin_default_features.php",
								EDIT_PRODUCT_MSG . ' ' . OPTION_MSG => "admin_property.php",
								EDIT_SUBCOMP_MSG            => "admin_component_single.php",
								EDIT_SUBCOMP_SELECTION_MSG  => "admin_component_selection.php"
							),
							"additional_title" => array(
								"table" => $table_prefix . "item_types",
								"id"    => "item_type_id",
								"title" => "item_type_name"
							)
						),
						MANUFACTURERS_TITLE     => array(
							"href" => $admin_site_url . "admin_manufacturers.php",
							"perm" => "manufacturers",
							"subs" => array(
								EDIT_MANUFACTURER_MSG => "admin_manufacturer.php"
							)
						),
						SUPPLIERS_MSG     => array(
							"href" => $admin_site_url . "admin_suppliers.php",
							"perm" => "suppliers",
							"subs" => array(
								EDIT_SUPPLIER_MSG => "admin_supplier.php"
							)
						),

					)	
				),
				ORDERS_MSG => array(
					"href" => $orders_pages_site_url . "admin_order_info.php",
					"code" => 1,
					"subs" => array(
						ORDER_PROFILE_PAGE_MSG => array(
							"href" => $orders_pages_site_url . "admin_order_info.php",
							"subs" => array(
								EDIT_CUSTOM_FIELD_MSG => "admin_order_property.php"
							)
						),
						SHIPPING_METHODS_MSG => array(
							"href" => $admin_site_url . "admin_shipping_modules.php",
							"subs" => array(
								EDIT_SHIPPING_MSG => "admin_shipping_module.php",
								SHIPPING_TYPES_MSG => "admin_shipping_types.php",
								EDIT_SHIPPING_TYPE_MSG => "admin_shipping_type.php"
							),
							"additional_title" => array(
								"table" => $table_prefix . "shipping_modules",
								"id"    => "shipping_module_id",
								"title" => "shipping_module_name",
								"href"  => "admin_shipping_types.php"
							)
						),
						SHIPPING_TIMES_MSG => array(
							"href" => $admin_site_url . "admin_shipping_times.php",
							"subs" => array(
								EDIT_SHIPPING_TIME_MSG => "admin_shipping_time.php"
							)
						),
						SHIPPING_RULES_MSG => array(
							"href" => $admin_site_url . "admin_shipping_rules.php",
							"subs" => array(
								EDIT_MSG => "admin_shipping_rule.php"
							)
						),
						PAYMENT_SYSTEMS_MSG => array(
							"href" =>  $orders_pages_site_url . "admin_payment_systems.php",
							"subs" => array(
								EDIT_PAYMENT_SYSTEM_MSG     => "admin_payment_system.php",
								PAYMENT_DETAILS_PAGE_MSG    => "admin_credit_card_info.php",
								EDIT_CUSTOM_FIELD_MSG       => "admin_order_property.php",
								FINAL_CHECKOUT_SETTINGS_MSG => "admin_order_final.php",
								RECURRING_SETTINGS_MSG      => "admin_recurring_settings.php",
								ADD_PREDEFINED_PS_MSG       => "admin_payment_predefined.php",
								IMPORT_PAYMENT_SYSTEM_MSG   => "admin_import_payment_system.php"
							),
							"additional_title" => array(
								"table" => $table_prefix . "payment_systems",
								"id"    => "payment_id",
								"title" => "payment_name",
								"href"  => "admin_payment_system.php"
							)
						),
						ORDER_CONFIRMATION_PAGE_MSG => array(
							"href" => $orders_pages_site_url . "admin_order_confirmation.php"
						),
						ORDERS_STATUSES_MSG => array(
							"href" => $orders_pages_site_url . "admin_order_statuses.php",
							"subs" => array(
								EDIT_MSG . ' ' . ORDERS_STATUSES_MSG => "admin_order_status.php"
							)
						),
						TAX_RATES_MSG => array(
							"href" => $orders_pages_site_url . "admin_tax_rates.php",
							"subs" => array(
								EDIT_TAX_MSG => "admin_tax_rate.php"
							)
						),
						CURRENCIES_MSG => array(
							"href" => $orders_pages_site_url . "admin_currencies.php",
							"subs" => array(
								EDIT_CURRENCY_MSG => "admin_currency.php",
							)
						),
						PRINTABLE_PAGE_SETTINGS_MSG => array(
							"href" => $orders_pages_site_url . "admin_order_printable.php"
						),
						BOM_SETTINGS_MSG => array(
							"href" =>  $orders_pages_site_url . "admin_orders_bom_settings.php",
							"subs" => array(
								EDIT_CUSTOM_FIELD_MSG => "admin_orders_bom_column.php"
							)
						),
						ORDERS_RECOVER_MSG => array(
							"href" => $orders_pages_site_url . "admin_orders_recover_settings.php"
						)
					)
				),
				ARTICLES_TITLE => array(
					"href" => $admin_site_url . "admin_articles_top.php",
					"code" => 2,
					"subs" => array(
						ARTICLES_SETTINGS_MSG => array(
							"href" => $admin_site_url . "admin_articles_top.php",
						),
						ARTICLES_STATUSES_MSG => array(
							"href" => $admin_site_url . "admin_articles_statuses.php"
						),
						REVIEWS_SETTINGS_MSG => array(
							"href" => $admin_site_url . "admin_articles_reviews_sets.php"
						)
					)			
				),
				HELPDESK_MSG => array(
					"href" => $helpdesk_site_url . "admin_support_settings.php",
					"code" => 4,
					"subs" => array(
						HELPDESK_SETTINGS_MSG => array(
							"href" => $helpdesk_site_url . "admin_support_settings.php",
							"subs" => array(
								EDIT_CUSTOM_FIELD_MSG => "admin_support_property.php"
							)
						),
						SUPPORT_USERS_MSG => array(
							"href" => $helpdesk_site_url . "admin_support_admins.php",
							"subs" => array(
								EDIT_USER_MSG => "admin_support_admin_edit.php"
							)
						),
						CUSTOMERS_RANKS_MSG => array(
							"href" => $helpdesk_site_url . "admin_support_ranks.php",
							"subs" => array(
								EDIT_CUSTOMER_RANK_MSG => "admin_support_rank.php"
							)
						),
						DEPARTMENTS_MSG => array(
							"href" => $helpdesk_site_url . "admin_support_departments.php",
							"subs" => array(
								EDIT_MSG => "admin_support_dep_edit.php"
							)
						),
						SUPPORT_TYPES_MSG => array(
							"href" => $helpdesk_site_url . "admin_support_types.php",
							"subs" => array(
								EDIT_TYPE_MSG => "admin_support_type.php"
							)
						),
						SUPPORT_PRODUCTS_MSG => array(
							"href" => $helpdesk_site_url . "admin_support_products.php",
							"subs" => array(
								EDIT_PRODUCT_MSG => "admin_support_product.php"
							)
						),
						SUPPORT_PRIORITIES_MSG => array(
							"href" => $helpdesk_site_url . "admin_support_priorities.php",
							"subs" => array(
								EDIT_PRIORITY_MSG => "admin_support_priority.php"
							)
						),
						SUPPORT_STATUSES_MSG => array(
							"href" => $helpdesk_site_url . "admin_support_statuses.php",
							"subs" => array(
								EDIT_STATUS_MSG => "admin_support_status.php"							
							)
						)
					)				
				),
				ADMIN_FORUM_TITLE => array(
					"href" => $admin_site_url . "admin_forum_settings.php",
					"code" => 8,
					"subs" => array(
						FORUM_SETTINGS_MSG => array(
							"href" => $admin_site_url . "admin_forum_settings.php"
						),
						FORUM_PRIORITIES_MSG => array(
							"href" => $admin_site_url . "admin_forum_priorities.php",
							"subs" => array(
								EDIT_PRIORITY_MSG => "admin_forum_priority.php"
							)
						),
						EMOTION_ICONS_MSG => array(
							"href" => $admin_site_url . "admin_icons.php",
							"subs" => array(
								EDIT_ICON_MSG => "admin_icon.php"
							)
						)
					)				
				),
				ADS_TITLE => array(
					"href" => $admin_site_url . "admin_ads_settings.php",
					"code" => 16,
					"subs" => array(
						ADS_SETTINGS_MSG => array(
							"href" => $admin_site_url . "admin_ads_settings.php"
						),
						ADS_TYPES_MSG => array(
							"href" => $admin_site_url . "admin_ads_types.php",
							"subs" => array(
								EDIT_AD_TYPE_MSG          => "admin_ads_type.php",
								DEFAULT_SPECIFICATION_MSG => "admin_ads_features_default.php",
								DEFAULT_PROPERTIES_MSG    => "admin_ads_properties_default.php"
							),
							"additional_title" => array(
								"table" => $table_prefix . "ads_types",
								"id"    => "type_id",
								"title" => "type_name",
								"href"  => "admin_ads_type.php"
							)
						),
						ADVERT_NOTIFICATION_MSG => array(
							"href" => $admin_site_url . "admin_ads_notify.php"
						),
						ADVERT_REQUEST_MSG => array(
							"href" => $admin_site_url . "admin_ads_request.php"
						),
						ADVANCED_SEARCH_TITLE => array(
							"href" => $admin_site_url . "admin_ads_search.php"
						),
						TELL_FRIEND => array(
							"href" => $admin_site_url . "admin_tell_friend.php?type=ads"
						),
						SPECIFICATION_GROUPS_MSG => array(
							"href" => $admin_site_url . "admin_ads_features_groups.php",
							"subs" => array(
								EDIT_GROUP_MSG => "admin_ads_features_group.php"
							)
						),
						ADS_DAYS_MSG => array(
							"href" => $admin_site_url . "admin_ads_days.php",
							"subs" => array(
								EDIT_DAY_MSG => "admin_ads_day.php"
							)
						),
						ADS_HOT_DAYS_MSG => array(
							"href" => $admin_site_url . "admin_ads_hot_days.php",
							"subs" => array(
								EDIT_DAY_MSG => "admin_ads_hot_day.php"
							)
						),
						ADS_SPECIAL_DAYS_MSG => array(
							"href" => $admin_site_url . "admin_ads_special_days.php",
							"subs" => array(
								EDIT_DAY_MSG => "admin_ads_special_day.php"
							)
						)
					)				
				),
				PRODUCT_REGISTRATION_MSG => array(
					"code" => 1,
					"href" => $admin_site_url . "admin_registration_settings.php",
					"subs" => array(
						EDIT_CUSTOM_FIELD_MSG => "admin_registration_property.php"
					)
				),
				CUSTOMERS_MSG => array(
					"href" => $admin_site_url . "admin_user_types.php",
					"subs" => array(
						USERS_TYPES_MSG => array(
							"href" => $admin_site_url . "admin_user_types.php",
							"subs" => array(
								EDIT_TYPE_MSG     => "admin_user_type.php",								
								PROFILE_SETTINGS_MSG => "admin_user_profile.php",
								USER_PRODUCT_MSG => "admin_user_product.php",
								EDIT_CUSTOM_FIELD_MSG => "admin_user_property.php",
								CONTACT_FORM_SETTINGS_MSG => "admin_user_contact.php"
							),
							"additional_title" => array(
								"table" => $table_prefix . "user_types",
								"id"    => "type_id",
								"title" => "type_name",
								"href"  => "admin_user_type.php"
							)
						),
						FORM_SECTIONS_MSG => array(
							"href" => $admin_site_url . "admin_user_sections.php",
							"subs" => array(
								EDIT_MSG => "admin_user_section.php"
							)
						),
						SUBSCRIPTIONS_MSG => array(
							"href" => $admin_site_url . "admin_subscriptions.php",
							"subs" => array(
								SUBSCRIPTION_MSG  => "admin_subscription.php"
							)
						),
						SUBSCRIPTIONS_GROUPS_MSG => array(
							"href" => $admin_site_url ."admin_subscriptions_groups.php",
							"subs" => array(
								SUBSCRIPTIONS_GROUP_MSG => "admin_subscriptions_group.php"
							)
						),
						FORGOTTEN_PASSWORD_MSG => array(
							"href" => $admin_site_url . "admin_forgotten_password.php"
						),
						CUSTOMERS_COMISSIONS_MSG => array(
							"href" => $admin_site_url . "admin_user_commissions.php"
						),
						COMMISSION_PAYMENTS => array(
							"href" => $admin_site_url . "admin_user_payments.php",
							"subs" => array(
								EDIT_PAYMENT_MSG => "admin_user_payment.php"
							)
						)
					)
				),
				PROFILES_TITLE => array(
					"href" => $admin_site_url . "admin_profiles_settings.php",
					"code" => 64,
					"subs" => array(
						SETTINGS_MSG => array(
							"href" => $admin_site_url . "admin_profiles_settings.php"
						),
					)
				),

			)
		)
	);

	admin_leftside_db_generated();

function admin_leftside_db_generated() {
	global $db, $table_prefix, $admin_leftside_menu_tree;
	$permissions    = get_permissions();
	if (get_setting_value($permissions, "articles", 0)) {
		$sql  = " SELECT category_id, category_name ";
		$sql .= " FROM " . $table_prefix . "articles_categories ";
		$sql .= " WHERE parent_category_id=0 ";
		$sql .= " ORDER BY category_order ";
		$db->query($sql);
		$articles = array();
		while($db->next_record()) {
			$id    = $db->f("category_id");
			$href  = "admin_articles.php?category_id=" . $id;
			$title = get_translation($db->f("category_name"));
			$articles[$title] = array(
				"href" => $href
			);
		}
		foreach ($admin_leftside_menu_tree[DASHBOARD_MSG]["subs"][ARTICLES_TITLE]["subs"] AS $title => $vars) {
			$articles[$title] = $vars;
		}
		$admin_leftside_menu_tree[DASHBOARD_MSG]["subs"][ARTICLES_TITLE]["subs"] = $articles;
	}
	if (get_setting_value($permissions, "forum", 0)) {
		$sql  = " SELECT category_id, category_name ";
		$sql .= " FROM " . $table_prefix . "forum_categories ";
		$sql .= " ORDER BY category_order ";
		$db->query($sql);
		$forums = array();
		while($db->next_record()) {
			$id    = $db->f("category_id");
			$href  = "admin_forum.php?category_id=" . $id;
			$title = get_translation($db->f("category_name"));
			$forums[$title] = array(
				"href" => $href
			);
		}
		if (isset($admin_leftside_menu_tree[DASHBOARD_MSG]["subs"][ADMIN_FORUM_TITLE]["subs"])) {
			foreach ($admin_leftside_menu_tree[DASHBOARD_MSG]["subs"][ADMIN_FORUM_TITLE]["subs"] AS $title => $vars) {
				$forums[$title] = $vars;
			}
		}
		$admin_leftside_menu_tree[DASHBOARD_MSG]["subs"][ADMIN_FORUM_TITLE]["subs"] = $forums;
	}
	if (get_setting_value($permissions, "manual", 0)) {
		$sql  = " SELECT category_id, category_name ";
		$sql .= " FROM " . $table_prefix . "manuals_categories ";
		$sql .= " ORDER BY category_order ";
		$db->query($sql);
		$manuals = array();
		while($db->next_record()) {
			$id    = $db->f("category_id");
			$href  = "admin_manual.php?category_id=" . $id;
			$title = get_translation($db->f("category_name"));
			$manuals[$title] = array(
				"href" => $href
			);
		}
		if (isset($admin_leftside_menu_tree[DASHBOARD_MSG]["subs"][MANUAL_MSG]["subs"])) {
			foreach ($admin_leftside_menu_tree[DASHBOARD_MSG]["subs"][MANUAL_MSG]["subs"] AS $title => $vars) {
				$manuals[$title] = $vars;
			}
		}
		$admin_leftside_menu_tree[DASHBOARD_MSG]["subs"][MANUAL_MSG]["subs"] = $manuals;
	}
	if (get_setting_value($permissions, "ads", 0)) {
		$sql  = " SELECT category_id, category_name ";
		$sql .= " FROM " . $table_prefix . "ads_categories ";
		$sql .= " WHERE parent_category_id=0 ";
		$sql .= " ORDER BY category_order ";
		$db->query($sql);
		$ads = array();
		while($db->next_record()) {
			$id    = $db->f("category_id");
			$href  = "admin_ads.php?category_id=" . $id;
			$title = get_translation($db->f("category_name"));
			$ads[$title] = array(
				"href" => $href
			);
		}
		if (isset($admin_leftside_menu_tree[DASHBOARD_MSG]["subs"][ADS_TITLE]["subs"])) {
			foreach ($admin_leftside_menu_tree[DASHBOARD_MSG]["subs"][ADS_TITLE]["subs"] AS $title => $vars) {
				$ads[$title] = $vars;
			}
		}
		$admin_leftside_menu_tree[DASHBOARD_MSG]["subs"][ADS_TITLE]["subs"] = $ads;
	}
}

// start copy here

function admin_leftside_menu_breadcrumbs() {
	global $admin_leftside_breadrcumbs, $admin_leftside_menu_tree, $db, $table_prefix;
	if (isset($admin_leftside_breadrcumbs)) return $admin_leftside_breadrcumbs;
		
	$current_breads = admin_leftside_menu_find_current_breadcrumbs();
	
	$current_values = false;
	if (isset($current_breads["TYPE"]) && isset($current_breads["GROUP"])) {
		if (isset($current_breads["ITEM"])) {
			$current_values  = $admin_leftside_menu_tree[$current_breads["TYPE"][0]]["subs"][$current_breads["GROUP"][0]]["subs"][$current_breads["ITEM"][0]];
		}
		if (!is_array($current_values)) {
			$current_values  = $admin_leftside_menu_tree[$current_breads["TYPE"][0]]["subs"][$current_breads["GROUP"][0]];
		}
	}	
		
	if ($current_values) {
		$current_breads_keys = array_keys($current_breads);
		$last_item_id = array_pop($current_breads_keys);
		$last_item = array_pop($current_breads);
		if (isset($current_values["additional_tree"])){	
			$additional_vars = $current_values["additional_tree"];			
			$current_id      = (int) get_param($additional_vars["id"]);
			if ($current_id) {
				$sql  = " SELECT category_path FROM " . $additional_vars["table"];
				$sql .= " WHERE " . $additional_vars["id"] . "=" . $db->tosql($current_id, INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					$category_path  = $db->f("category_path");
					$category_ids   = explode(',', $category_path);					
					$category_ids[] = $current_id;
					$categories     = array();
					$sql  = " SELECT " . $additional_vars["id"] . ", " . $additional_vars["title"];
					$sql .= " FROM " . $additional_vars["table"];
					$sql .= " WHERE " . $additional_vars["id"] . " IN (" . $db->tosql($category_ids, INTEGERS_LIST) . ')';
					$db->query($sql);
					while ($db->next_record()) {
						$categories[$db->f(0)] = get_translation($db->f(1));
					}
					
					$first_category_id = (isset($category_ids[1]) && $category_ids[1]) ? $category_ids[1] : $current_id;
					//first category is selected in the left
					$last_item_id = "";
					$current_breads["ITEM"] = array(
						$categories[$first_category_id],
						(isset($additional_vars["href"]) ? $additional_vars["href"] : $current_values["href"]) 
						. "?" . $additional_vars["id"] . "=" . $first_category_id
					);
					
					foreach ($category_ids AS $category_id) {
						if (!isset($categories[$category_id]) || ($category_id == $first_category_id)) continue;						
						$current_breads[] = array(
							$categories[$category_id],
							(isset($additional_vars["href"]) ? $additional_vars["href"] : $current_values["href"]) 
							. "?" . $additional_vars["id"] . "=" . $category_id
						);
					}
				}
			}	
		}
		if (isset($current_values["additional_title"])) {
			$additional_vars = $current_values["additional_title"];
			$current_id      = (int) get_param($additional_vars["id"]);
			if ($current_id) {
				$sql  = " SELECT " . $additional_vars["title"] . " FROM " . $additional_vars["table"];
				$sql .= " WHERE " . (isset($additional_vars["where"]) ? $additional_vars["where"] : $additional_vars["id"]) . "=" . $db->tosql($current_id, INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					$current_breads[] = array(
						(isset($additional_vars["title_prefix"]) ? $additional_vars["title_prefix"] : '') . $db->f(0), 
						(isset($additional_vars["href"]) ? $additional_vars["href"] : $current_values["href"]) 
						. "?" . $additional_vars["id"] . "=" . $current_id
					);
				}
			}
		}
		if ($last_item_id) {
			$current_breads[$last_item_id] = $last_item;
		} else {
			$current_breads[] = $last_item;
		}
	}
	$admin_leftside_breadrcumbs = $current_breads;
	return $admin_leftside_breadrcumbs;
}

function admin_leftside_breadcrumbs_block($block_name) {
	global $t, $admin_leftside_menu_tree, $custom_breadcrumb;
	$t->set_file("block_body", "admin_block_leftside_breadcrumbs.html");
	
	$t->set_var("breadcrumb_item", "");
	if (isset($custom_breadcrumb) && is_array($custom_breadcrumb)) {
		$bc_total = count($custom_breadcrumb);
		$bc_index = 0;
		foreach ($custom_breadcrumb as $url => $title) {
			$bc_index++;
			if ($bc_index == $bc_total) {
				$t->set_var("breadcrumb_last", $title);
			} else {
				$t->set_var("breadcrumb_title", htmlspecialchars($title));
				$t->set_var("breadcrumb_href", htmlspecialchars($url));
				$t->parse("breadcrumb_item");
			}
		}
	} else {
		$current_breads = admin_leftside_menu_breadcrumbs();
		$last_item = array_pop($current_breads);
		
		$admin_meta_title = '';
		foreach ($current_breads AS $type => $vars) {
			if (!$vars[1]) continue;
			$admin_meta_title = get_translation($vars[0]) . ' :: ';
			$t->set_var("breadcrumb_title", get_translation($vars[0]));
			$t->set_var("breadcrumb_href", $vars[1]);
			$t->parse("breadcrumb_item");
		}	
		$admin_meta_title =  ADMINISTRATION_MSG . ' :: ' . $admin_meta_title . $last_item[0];
		$t->set_var("breadcrumb_last", $last_item[0]);
		$t->set_var("admin_meta_title", $admin_meta_title);	
	}
	
	$t->parse("block_body", false);
	$t->parse_to("block_body", $block_name, true);
}

function admin_leftside_topmenu_block($block_name) {
	global $t, $admin_leftside_menu_tree;
	$t->set_file("block_body", "admin_block_leftside_topmenu.html");
	$current_breads = admin_leftside_menu_breadcrumbs();
	$t->set_var("leftside_top_type", "");
	foreach ($admin_leftside_menu_tree AS $leftside_type_title => $leftside_type_vars) {
		if ($leftside_type_title == $current_breads["TYPE"][0]) {
			$t->set_var("leftside_type_class",  $leftside_type_vars["class"] . " " . $leftside_type_vars["class"]. "Active");
		} else {
			$t->set_var("leftside_type_class",  $leftside_type_vars["class"]);
		}
		$t->set_var("leftside_type_title", $leftside_type_title);
		$t->set_var("leftside_type_href",  $leftside_type_vars["href"]);
		$t->parse("leftside_top_type");	
	}
	
	$t->parse("block_body", false);
	$t->parse_to("block_body", $block_name, true);
}


function admin_leftside_menu_block($block_name) {
	// building left menu panel
	global $t, $admin_leftside_menu_tree;
	$t->set_file("block_body", "admin_block_leftside_menu.html");
	$va_version_code = va_version_code();

	$permissions    = get_permissions();
	$current_breads = admin_leftside_menu_breadcrumbs();
	$i = 0;
	foreach ($admin_leftside_menu_tree[$current_breads["TYPE"][0]]["subs"] AS $leftside_group_title => $leftside_group_vars ) {
		$i++;
		$t->set_var("leftside_items", "");
		$t->set_var("leftside_item", "");
		$t->set_var("leftside_group_id", "leftside_group_" . $i);
		
		$t->set_var("leftside_group_class", "leftNavNonActive");
		if (isset($current_breads["GROUP"])) {
			if ($current_breads["GROUP"][0] == $leftside_group_title) {
				$t->set_var("leftside_group_class", "leftNavActive");
			}
		} elseif ($i==1) {
			$t->set_var("leftside_group_class", "leftNavActive");
		}
		$t->set_var("leftside_group_title", $leftside_group_title);
		if (isset($leftside_group_vars["href"])) {
			$t->set_var("leftside_group_href",  $leftside_group_vars["href"]);
		} else {
			$t->set_var("leftside_group_href", "#\" onclick=\"overhid('leftside_group_$i'); return false;\"");
		}
		if (isset($leftside_group_vars["perm"])) {
			if (!get_setting_value($permissions, $leftside_group_vars["perm"], 0)) {
				continue;
			}
		}
		if (isset($leftside_group_vars["code"])) {
			if (!($leftside_group_vars["code"] & $va_version_code)) {
				continue;
			}
		}
		if (isset($leftside_group_vars["subs"])) {
			$items_index = 0;
			foreach ($leftside_group_vars["subs"] AS $leftside_item_title => $leftside_item_vars) {
				if ((is_array($leftside_item_vars) && isset($leftside_item_vars["href"]))) {
					$leftside_item_href = $leftside_item_vars["href"];
				} else {
					continue;
				}
				if (isset($leftside_item_vars["perm"])) {
					if (!get_setting_value($permissions, $leftside_item_vars["perm"], 0)) {
						continue;
					}
				}
				$items_index++;
				if (isset($current_breads["ITEM"][0]) && ($current_breads["ITEM"][0] == $leftside_item_title)) {
					$t->set_var("leftside_class", "leftNavSubActive");
				} else {
					$t->set_var("leftside_class", "leftNavSub");
				}
				$t->set_var("leftside_title", $leftside_item_title);
				$t->set_var("leftside_href",  $leftside_item_href);
				
				$t->parse("leftside_item");
			}
			if ($items_index) {
				$t->parse("leftside_items");			
			}
		}
		$t->parse("leftside_group");
	}
	
	
	$t->parse("block_body", false);
	$t->parse_to("block_body", $block_name, true);
}

function admin_leftside_menu_find_current_breadcrumbs($current_href = false, $go_deep = true) {
	global $admin_leftside_menu_tree, $admin_site_url;
		
	if (!$current_href) {
		$current_href = get_script_name();
		if (get_param("type")) {
			$current_href = $current_href . "?type=" . get_param("type");
		}
		if (get_param("table")) {
			$current_href = $current_href . "?table=" . get_param("table");
		}
	}
	$current_breads = array();
	foreach ($admin_leftside_menu_tree AS $leftside_type_title => $leftside_type_vars) {		
		foreach ($leftside_type_vars["subs"] AS $leftside_group_title => $leftside_group_vars) {
			if (isset($leftside_group_vars["subs"])) {
				foreach ($leftside_group_vars["subs"] AS $leftside_item_title => $leftside_item_vars) {
					if (is_array($leftside_item_vars) && isset($leftside_item_vars["href"]) && $current_href == basename($leftside_item_vars["href"])) {
						$current_breads["TYPE"]  = array($leftside_type_title,  $leftside_type_vars["href"]);
						if (isset($leftside_group_vars["href"])) {
							$current_breads["GROUP"] = array($leftside_group_title, $leftside_group_vars["href"]);
						} else {
							$current_breads["GROUP"] = array($leftside_group_title, '');
						}
						$current_breads["ITEM"]  = array($leftside_item_title, $leftside_item_vars["href"]);
						return $current_breads;

					} elseif (!is_array($leftside_item_vars)) {
						if ($current_href == basename($leftside_item_vars)) {
							$current_breads["TYPE"]   = array($leftside_type_title,  $leftside_type_vars["href"]);
							$current_breads["GROUP"]  = array($leftside_group_title, $leftside_group_vars["href"]);
							$current_breads["ITEM"]   = array($leftside_item_title,  $leftside_item_vars);
							return $current_breads;
						}
					} elseif (isset($leftside_item_vars["subs"])) {
						foreach ($leftside_item_vars["subs"] AS $leftside_subitem_title => $leftside_subitem_vars) {
							$leftside_subitem_href = is_array($leftside_subitem_vars) ? $leftside_subitem_vars["href"] : $leftside_subitem_vars;
							if ($current_href == basename($leftside_subitem_href)) {								
								if (isset($leftside_subitem_vars["not_set"]) && get_param($leftside_subitem_vars["not_set"])) continue;
								
								$current_breads["TYPE"]    = array($leftside_type_title,    $leftside_type_vars["href"]);
								$current_breads["GROUP"]   = array($leftside_group_title,   $leftside_group_vars["href"]);
								$current_breads["ITEM"]    = array($leftside_item_title,    $leftside_item_vars["href"]);
								$current_breads["SUBITEM"] = array($leftside_subitem_title, $leftside_subitem_href);
								return $current_breads;
							}
						}
					}
				}
			}
			// level 2 link
			if ($current_href == basename($leftside_group_vars["href"])) {
				$current_breads["TYPE"]  = array($leftside_type_title,  $leftside_type_vars["href"]);
				$current_breads["GROUP"] = array($leftside_group_title, $leftside_group_vars);
				return $current_breads;
			}
		}
		// level 1 link
		if ($current_href == basename($leftside_type_vars["href"])) {
			$current_breads["TYPE"]  = array($leftside_type_title,  $leftside_type_vars["href"]);
			return $current_breads;
		}
	}
	if ($go_deep) {
		$current_href   = get_script_name();
		return admin_leftside_menu_find_current_breadcrumbs($current_href, false);
	} else {
		$current_breads["TYPE"]  = array(DASHBOARD_MSG,  $admin_site_url . "admin_items_list.php");
		$current_breads["GROUP"] = array(PRODUCTS_MSG, $admin_site_url . "admin_items_list.php");
		$current_breads["ITEM"]  = array(PRODUCTS_CATEGORIES_MSG, $admin_site_url . "admin_items_list.php");
	}
	return $current_breads;
}
?>