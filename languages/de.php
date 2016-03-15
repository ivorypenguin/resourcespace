<?php
# German
# Language File for ResourceSpace
# UTF-8 encoded
# -------
# Note: when translating to a new language, preserve the original case if possible.
#
# Updated by [Name] [Date] for version [svn version], [comments]
# Updated by Henrik Frizén 20110124 for version 2296, added missing $lang from the en.php (search for #$lang to find the untranslated strings).
# Updated by Stefan Wild 20110201 for version 2296, translated the missing $lang keys from the en.php that Henrik added.
# Updated by Henrik Frizén 20110222 for version 2390+, added missing $lang from the en.php (search for #$lang to find the untranslated strings).
# Updated by Stefan Wild 20110309 for version 2390+, translated the missing $lang keys from the en.php that Henrik added.
# Updated by Stefan Wild 20110427 for version 2652, translated the missing $lang keys from the en.php.
# Updated by Stefan Wild 20110730 for version 2852, translated the missing $lang keys from the en.php.
# Updated by Stefan Wild 20130124 for version 4220, translated the missing $lang keys from the en.php.
# Updated by Stefan Wild 20140220 for version 5313, translated the missing $lang keys from the en.php.
# Updated by Stefan Wild 20140619 for version 5550, translated the missing $lang keys from the en.php.
# Updated by Stefan Wild 20141215 for version 6127, translated the missing $lang keys from the en.php.

# User group names (for the default user groups)
$lang["usergroup-administrators"]="Administratoren";
$lang["usergroup-general_users"]="Allgemeine Benutzer";
$lang["usergroup-super_admin"]="Super Admin";
$lang["usergroup-archivists"]="Archiv";
$lang["usergroup-restricted_user_-_requests_emailed"]="Eingeschränkte Benutzer - Anfragen per E-Mail";
$lang["usergroup-restricted_user_-_requests_managed"]="Eingeschränkte Benutzer - Anfragen verwaltet";
$lang["usergroup-restricted_user_-_payment_immediate"]="Eingeschränkte Benutzer - Bezahlung sofort";
$lang["usergroup-restricted_user_-_payment_invoice"]="Eingeschränkte Benutzer - Bezahlung auf Rechnung";

# Resource type names (for the default resource types)
$lang["resourcetype-photo"]="Foto";
$lang["resourcetype-document"]="Dokument";
$lang["resourcetype-video"]="Video";
$lang["resourcetype-audio"]="Audio";
$lang["resourcetype-global_fields"]="Globale Felder";
$lang["resourcetype-archive_only"]="Nur Archiv";
$lang["resourcetype-photo-2"]="Fotos";
$lang["resourcetype-document-2"]="Dokumente";
$lang["resourcetype-video-2"]="Videos";
$lang["resourcetype-audio-2"]="Audio";

# Image size names (for the default image sizes)
$lang["imagesize-thumbnail"]="Thumbnail";
$lang["imagesize-preview"]="Vorschau";
$lang["imagesize-screen"]="Bildschirm";
$lang["imagesize-low_resolution_print"]="Druck (niedrige Auflösung)";
$lang["imagesize-high_resolution_print"]="Druck (hohe Auflösung)";
$lang["imagesize-collection"]="Kollektion";

# Field titles (for the default fields)
$lang["fieldtitle-keywords"]="Stichworte";
$lang["fieldtitle-country"]="Land";
$lang["fieldtitle-title"]="Titel";
$lang["fieldtitle-story_extract"]=$lang["storyextract"]="Zusammenfassung";
$lang["fieldtitle-credit"]="Urheber";
$lang["fieldtitle-date"]=$lang["date"]="Datum";
$lang["fieldtitle-expiry_date"]="Ablaufdatum";
$lang["fieldtitle-caption"]="Beschriftung";
$lang["fieldtitle-notes"]="Anmerkungen";
$lang["fieldtitle-named_persons"]="Person(en)";
$lang["fieldtitle-camera_make_and_model"]="Kamera";
$lang["fieldtitle-original_filename"]="Original Dateiname";
$lang["fieldtitle-video_contents_list"]="Video Inhaltsliste";
$lang["fieldtitle-source"]="Quelle";
$lang["fieldtitle-website"]="Website";
$lang["fieldtitle-artist"]="Künstler";
$lang["fieldtitle-album"]="Album";
$lang["fieldtitle-track"]="Lied";
$lang["fieldtitle-year"]="Jahr";
$lang["fieldtitle-genre"]="Genre";
$lang["fieldtitle-duration"]="Dauer";
$lang["fieldtitle-channel_mode"]="Kanalmodus";
$lang["fieldtitle-sample_rate"]="Sample Rate";
$lang["fieldtitle-audio_bitrate"]="Audio Bitrate";
$lang["fieldtitle-frame_rate"]="Bildrate";
$lang["fieldtitle-video_bitrate"]="Video Bitrate";
$lang["fieldtitle-aspect_ratio"]="Seitenverhältnis";
$lang["fieldtitle-video_size"]="Videogröße";
$lang["fieldtitle-image_size"]="Bildgröße";
$lang["fieldtitle-extracted_text"]="Entnommener Text";
$lang["fieldtitle-file_size"]=$lang["filesize"]="Dateigröße";
$lang["fieldtitle-category"]="Kategorie";
$lang["fieldtitle-subject"]="Betreff";
$lang["fieldtitle-author"]="Autor";
$lang["fieldtitle-owner"]="Eigentümer";

# Field types
$lang["fieldtype-text_box_single_line"]="Textfeld (einzeilig)";
$lang["fieldtype-text_box_multi-line"]="Textfeld (mehrzeilig)";
$lang["fieldtype-text_box_large_multi-line"]="Textfeld (mehrzeilig, groß)";
$lang["fieldtype-text_box_formatted_and_ckeditor"]="Textfeld (formatiert / CKeditor)";
$lang["fieldtype-check_box_list"]="Check box Liste";
$lang["fieldtype-drop_down_list"]="Dropdown Menü";
$lang["fieldtype-date"]="Datum";
$lang["fieldtype-date_and_optional_time"]="Datum und optionale Zeit";
$lang["fieldtype-date_and_time"]="Datum / Uhrzeit";
$lang["fieldtype-expiry_date"]="Ablaufdatum";
$lang["fieldtype-category_tree"]="Kategoriebaum";
$lang["fieldtype-dynamic_keywords_list"]="Dynamische Stichwortliste";
$lang["fieldtype-dynamic_tree_in_development"]="Dynamischer Baum (in Entwicklung)";

# Property labels (for the default properties)
$lang["documentation-permissions"]="Weitere Informationen über die Berechtigungen finden Sie in der <a href=../../documentation/permissions.txt target=_blank>Berechtigungen Hilfe-Datei</a>.";
$lang["property-reference"]="Referenz";
$lang["property-name"]="Name";
$lang["property-permissions"]="Berechtigungen";
$lang["information-permissions"]="HINWEIS: Globale Berechtigungen aus der config.php könnten außerdem in Kraft sein";
$lang["property-fixed_theme"]="Festes Theme";
$lang["property-parent"]="Übergeordneter Eintrag";
$lang["property-search_filter"]="Suchfilter";
$lang["property-edit_filter"]="Bearbeitungsfilter";
$lang["property-resource_defaults"]="Ressourcen Vorgaben";
$lang["property-override_config_options"]="Konfigurationsoptionen überschreiben";
$lang["property-email_welcome_message"]="Willkommens-E-Mail";
$lang["information-ip_address_restriction"]="Wildcards werden für IP-Adress-Einschränkungen unterstützt, z.B. 128.124.*";
$lang["property-ip_address_restriction"]="IP-Adress-Einschränkungen";
$lang["property-request_mode"]="Anfragemodus";
$lang["property-allow_registration_selection"]="In der Registrierungsauswahl anzeigen";

$lang["property-resource_type_id"]="Ressourcen-Typ ID";
$lang["information-allowed_extensions"]="Wenn gesetzt, können nur die angegebenen Dateierweiterungen hochgeladen werden, z.B. jpg,gif";
$lang["property-allowed_extensions"]="Erlaubte Dateierweiterungen";
$lang["information-resource_type_config_override"]="Erlaubt individuelle Konfigurationsoptionen für jeden Ressourcen-Typ. Beeinflusst Suchresultate, Ressourcenansicht und -bearbeitung. Bitte vergessen Sie nicht, Ihre Änderungen hier ggf. bei den anderen Ressourcen-Typen wieder zu überschreiben.";

$lang["property-field_id"]="Feld ID";
$lang["property-title"]="Titel";
$lang["property-resource_type"]="Ressourcen-Typ";
$lang["property-field_type"]="Feldtyp";

$lang["property-options"]="Optionen";
$lang["property-required"]="Pflichtfeld";
$lang["property-order_by"]="Sortieren nach";
$lang["property-indexing"]="<b>Indizieren</b>";
$lang["information-if_you_enable_indexing_below_and_the_field_already_contains_data-you_will_need_to_reindex_this_field"]="Wenn Sie die Indizierung aktivieren und das Feld bereits Daten enthält, müssen Sie <a target=_blank href=../tools/reindex_field.php?field=%ref>dieses Feld neu indizieren</a>"; # %ref will be replaced with the field id
$lang["property-index_this_field"]="Feld indizieren";
$lang["information-enable_partial_indexing"]="Partielle Indizierung der Stichworte (Präfix+Infix Indizierung) sollte sparsam eingesetzt werden, da es die Größe des Index deutlich erhöht. Weitere Details im Wiki.";
$lang["property-enable_partial_indexing"]="Partielle Indizierung aktivieren";
$lang["information-shorthand_name"]="Wichtig: Kurzname muss gesetzt sein, damit das Feld in der erweiterten Suche erscheint. Der Kurzname darf nur aus Kleinbuchstaben bestehen - keine Leerzeichen, Ziffern oder Sonderzeichen.";
$lang["property-shorthand_name"]="Kurzname";
$lang["property-display_field"]="Feld anzeigen";
$lang["property-enable_advanced_search"]="In erweiterter Suche aktivieren";
$lang["property-enable_simple_search"]="In einfacher Suche aktivieren";
$lang["property-use_for_find_similar_searching"]="Für ähnliche Suche benutzen";
$lang["property-iptc_equiv"]="IPTC Äquivalent";
$lang["property-display_template"]="Anzeigetemplate";
$lang["property-value_filter"]="Eingabefilter";
$lang["property-regexp_filter"]="Regexp Filter";
$lang["information-regexp_filter"]="Filter durch reguläre Ausdrücke - z.B. wird '[A-Z]+' nur Großbuchstaben zulassen.";
$lang["information-regexp_fail"]="Der eingegebene Wert war nicht im erforderlichen Format.";
$lang["property-tab_name"]="Tab Name";
$lang["property-smart_theme_name"]="Smart-Theme Name";
$lang["property-exiftool_field"]="Exiftool Feld";
$lang["property-exiftool_filter"]="Exiftool Filter";
$lang["property-help_text"]="Hilfetext";
$lang["property-tooltip_text"]="Tooltip Text";
$lang["information-tooltip_text"]="Tooltip Text: Der Text, der in der einfachen/erweiterten Suche erscheint, wenn Sie mit dem Mauszeiger über dem Feld bleiben";
$lang["information-display_as_dropdown"]="Checkbox Listen und Dropdown Menüs: in der erweiterten Suche als Dropdown Menü anzeigen? (wird standardmäßig als Checkbox Liste dargestellt, um ODER Abfrage zu ermöglichen)";
$lang["property-display_as_dropdown"]="Als Dropdown darstellen";
$lang["property-external_user_access"]="Zugriff für externe Benutzer";
$lang["property-autocomplete_macro"]="Makro für Autovervollständigen";
$lang["property-hide_when_uploading"]="Beim Upload verstecken";
$lang["property-hide_when_restricted"]="Verstecken wenn eingeschränkt";
$lang["property-omit_when_copying"]="Beim Kopieren ignorieren";
$lang["property-sync_with_field"]="Abgleichen mit Feld";
$lang["information-copy_field"]="<a href=field_copy.php?ref=%ref>Feld kopieren</a>";
$lang["property-display_condition"]="Voraussetzung zur Anzeige";
$lang["information-display_condition"]="Voraussetzung zur Anzeige: Dieses Feld wird nur angezeigt, wenn die folgenden Voraussetzungen erfüllt sind. Verwenden Sie das gleiche Format wie bei Suchfiltern, z.B. kurzname=wert1|wert2, kurznamea=optiona;kurznameb=optionb1|optionb2";
$lang["property-onchange_macro"]="Makro bei Veränderung";
$lang["information-onchange_macro"]="Makro bei Veränderung: wird ausgeführt, wenn der Wert des Feldes sich ändert. VORSICHT";
$lang["information-derestrict_filter"]="Einschränkung aufheben. Kann zusammen mit der g Berechtigung genutzt werden, so dass alle Ressourcen eingeschränkt sind, es sei denn die hier angegebenen Kriterien sind erfüllt";

$lang["property-query"]="Abfrage";

$lang["information-id"]="Hinweis: 'ID' unten MUSS auf einen eindeutigen, dreistelligen Buchstabencode gesetzt sein";
$lang["property-id"]="ID";
$lang["property-width"]="Breite";
$lang["property-height"]="Höhe";
$lang["property-pad_to_size"]="Auf Größe auffüllen";
$lang["property-internal"]="Intern";
$lang["property-allow_preview"]="Vorschau erlauben";
$lang["property-allow_restricted_download"]="Download bei eingeschränktem Zugriff erlauben";

$lang["property-total_resources"]="Ressourcen gesamt";
$lang["property-total_keywords"]="Stichworte gesamt";
$lang["property-resource_keyword_relationships"]="Ressourcen / Stichworte Verknüpfungen";
$lang["property-total_collections"]="Kollektionen gesamt";
$lang["property-collection_resource_relationships"]="Kollektionen / Ressourcen Verknüpfungen";
$lang["property-total_users"]="Benutzer gesamt";


# Top navigation bar (also reused for page titles)
$lang["logout"]="Abmelden";
$lang["contactus"]="Kontakt";
# next line
$lang["home"]="Startseite";
$lang["searchresults"]="Suchergebnisse";
$lang["themes"]="Themen";
$lang["mycollections"]="Meine Kollektionen";
$lang["myrequests"]="Meine Anfragen";
$lang["collections"]="Kollektionen";
$lang["mycontributions"]="Meine Beiträge";
$lang["researchrequest"]="Suchanfrage";
$lang["helpandadvice"]="Hilfe &amp; Unterstützung";
$lang["teamcentre"]="Administration";
# footer link
$lang["aboutus"]="Über uns";
$lang["interface"]="Darstellung";
$lang["changethemeto"] = "Darstellung wechseln zu";

# Search bar
$lang["simplesearch"]="Einfache Suche";
$lang["searchbutton"]="Suchen";
$lang["clearbutton"]="zurücksetzen";
$lang["bycountry"]="Nach Land";
$lang["bydate"]="Nach Datum";
$lang["anyyear"]="beliebiges Jahr";
$lang["anymonth"]="beliebiger Monat";
$lang["anyday"]="beliebiger Tag";
$lang["anycountry"]="beliebiges Land";
$lang["resultsdisplay"]="Ergebnisse anzeigen";
$lang["xlthumbs"]="sehr groß";
$lang["xlthumbstitle"]="Sehr große Vorschaubilder";
$lang["largethumbs"]="groß";
$lang["largethumbstitle"]="Große Vorschaubilder";
$lang["smallthumbs"]="klein";
$lang["smallthumbstitle"]="Kleine Vorschaubilder";
$lang["list"]="Liste";
$lang["listtitle"]="Listenansicht";
$lang["perpage"]="pro Seite";

$lang["gotoadvancedsearch"]="zur erweiterten Suche";
$lang["viewnewmaterial"]="neue Einträge anzeigen";
$lang["researchrequestservice"]="Suchanfrage";

# Team Centre
$lang["manageresources"]="Ressourcen verwalten";
$lang["overquota"]="Speicherplatz erschöpft; es können keine weiteren Ressourcen hinzugefügt werden";
$lang["managearchiveresources"]="Archivierte Ressourcen verwalten";
$lang["managethemes"]="Themen verwalten";
$lang["manageresearchrequests"]="Suchanfragen verwalten";
$lang["manageusers"]="Benutzer verwalten";
$lang["managecontent"]="Inhalte verwalten";
$lang["viewstatistics"]="Statistiken ansehen";
$lang["viewreports"]="Berichte ansehen";
$lang["viewreport"]="Bericht ansehen";
$lang["treeobjecttype-report"]=$lang["report"]="Bericht";
$lang["sendbulkmail"]="Massenmail senden";
$lang["systemsetup"]="Systemeinstellungen";
$lang["usersonline"]="Benutzer, die zur Zeit online sind (Leerlaufzeit in Minuten)";
$lang["diskusage"]="Speicherplatzverbrauch";
$lang["available"]="gesamt";
$lang["used"]="verwendet";
$lang["free"]="verfügbar";
$lang["editresearch"]="Suchanfragen verwalten";
$lang["editproperties"]="Eigenschaften verwalten";
$lang["selectfiles"]="Dateien auswählen";
$lang["searchcontent"]="Inhalt durchsuchen";
$lang["ticktodeletehelp"]="Anwählen, um diesen Abschnitt zu löschen";
$lang["createnewhelp"]="Neuen Abschnitt erstellen";
$lang["searchcontenteg"]="(Seite, Name oder Text)";
$lang["copyresource"]="Ressource kopieren";
$lang["resourceidnotfound"]="Die Ressourcen-ID konnte nicht gefunden werden";
$lang["inclusive"]="(inklusive)";
$lang["pluginssetup"]="Plugins verwalten";
$lang["pluginmanager"]="Plugin Manager";
$lang["users"]="Benutzer";


# Team Centre - Bulk E-mails
$lang["emailrecipients"]="E-Mail Empfänger";
$lang["emailsubject"]="E-Mail Betreff";
$lang["emailtext"]="E-Mail Text";
$lang["emailhtml"]="HTML aktiviert - Text der E-Mail muss HTML-Formatierung nutzen";
$lang["send"]="Senden";
$lang["emailsent"]="E-Mail wurde gesendet.";
$lang["mustspecifyoneuser"]="Sie müssen mindestens einen Benutzer auswählen";
$lang["couldnotmatchusers"]="Keine passende Benutzer gefunden (oder Benutzer mehrfach angegeben)";

# Team Centre - User management
$lang["comments"]="Kommentare";

# Team Centre - Resource management
$lang["viewuserpending"]="Durch Benutzer zur Freischaltung eingereichte Ressourcen anzeigen";
$lang["userpending"]="Durch Benutzer zur Freischaltung eingereichte Ressourcen";
$lang["viewuserpendingsubmission"]="Durch Benutzer hochgeladene Ressourcen anzeigen";
$lang["userpendingsubmission"]="Durch Benutzer hochgeladene Ressourcen";
$lang["searcharchivedresources"]="Archivierte Ressourcen durchsuchen";
$lang["viewresourcespendingarchive"]="Zu archivierende Ressourcen anzeigen";
$lang["resourcespendingarchive"]="Zu archivierende Ressourcen";
$lang["uploadresourcebatch"]="Ressourcen hochladen";
$lang["uploadinprogress"]="Hochladen und Größenanpassung in Bearbeitung";
$lang["donotmoveaway"]="WICHTIG: Bitte verlassen Sie diese Seite nicht bis das Hochladen abgeschlossen ist!";
$lang["pleaseselectfiles"]="Bitte wählen Sie eine oder mehrere Dateien aus.";
$lang["previewstatus"]="Vorschau erstellt für %file% von %filestotal% Ressourcen."; # %file%, %filestotal% will be replaced, e.g. Created previews for resource 2 of 2.
$lang["uploadedstatus"]="Ressource %file% von %filestotal% hochgeladen - %path%"; # %file%, %filestotal% and %path% will be replaced, e.g. Resource 2 of 2 uploaded - pub/pictures/astro-images/JUPITER9.JPG
$lang["upload_failed_for_path"]="Hochladen fehlgeschlagen für %path%"; # %path% will be replaced, e.g. Upload failed for abc123.jpg
$lang["uploadcomplete"]="Hochladen abgeschlossen";
$lang["upload_summary"]="Hochladen – Zusammenfassung";
$lang["resources_uploaded-0"]="0 Ressourcen erfolgreich hochgeladen.";
$lang["resources_uploaded-1"]="1 Ressource erfolgreich hochgeladen.";
$lang["resources_uploaded-n"]="%done% Ressourcen erfolgreich hochgeladen."; # %done% will be replaced, e.g. 17 resources uploaded OK.
$lang["resources_failed-0"]="0 Ressourcen fehlgeschlagen.";
$lang["resources_failed-1"]="1 Ressource fehlgeschlagen.";
$lang["resources_failed-n"]="%done% Ressourcen fehlgeschlagen."; # %failed% will be replaced, e.g. 2 resources failed.
$lang["specifyftpserver"]="Einrichtung des FTP-Servers";
$lang["ftpserver"]="FTP-Server";
$lang["ftpusername"]="FTP-Benutzername";
$lang["ftppassword"]="FTP-Password";
$lang["ftpfolder"]="FTP-Verzeichnis";
$lang["connect"]="Verbinden";
$lang["uselocalupload"]="ODER: Verwenden Sie das lokale 'upload'-Verzeichnis anstelle des FTP-Servers.";

# User contributions
$lang["contributenewresource"]="Neue Ressource einreichen";
$lang["viewcontributedps"]="Meine Beiträge anzeigen - Freischaltung noch nicht erledigt";
$lang["viewcontributedpr"]="Meine Beiträge anzeigen - Prüfung und Freischaltung durch Ressourcen-Team noch nicht erledigt";
$lang["viewcontributedsubittedl"]="Meine Beiträge anzeigen - freigeschalten bzw. online";
$lang["contributedps"]="Meine Beiträge - Freischaltung noch nicht erledigt";
$lang["contributedpr"]="Meine Beiträge - Prüfung und Freischaltung durch Ressourcen-Team noch nicht erledigt";
$lang["contributedsubittedl"]="Meine Beiträge - Live";

# Collections
$lang["editcollection"]="Kollektion bearbeiten";
$lang["editcollectionresources"]="Kollektionsvorschau bearbeiten";
$lang["access"]="Zugriff";
$lang["private"]="privat";
$lang["public"]="öffentlich";
$lang["attachedusers"]="zugeordnete Benutzer";
$lang["themecategory"]="Themenkategorie";
$lang["theme"]="Thema";
$lang["newcategoryname"]="ODER: Tragen sie eine neue Themenkategorie ein...";
$lang["allowothersaddremove"]="Anderen Benutzern das hinzufügen/entfernen von Ressourcen erlauben";
$lang["resetarchivestatus"]="Archivierungsstatus für alle Ressourcen einer Kollektion zurücksetzen";
$lang["editallresources"]="Alle Ressourcen in der Kollektion bearbeiten";
$lang["editresources"]="Ressourcen bearbeiten";
$lang["multieditnotallowed"]="Mehrfache Bearbeitung nicht erlaubt - die Ressourcen sind nicht vom selben Typ bzw. Status.";
$lang["emailcollectiontitle"]="Kollektion als E-Mail senden";
$lang["collectionname"]="Name der Kollektion";
$lang["collection-name"]="Kollektion: %collectionname%"; # %collectionname will be replaced, e.g. Collection: Cars
$lang["collectionid"]="Kollektion (ID)";
$lang["collectionidprefix"]="Kol_ID";
$lang["_dupe"]="_dupe";
$lang["emailtousers"]="E-Mail an Benutzer...";
$lang["removecollectionareyousure"]="Möchten Sie diese Kollektion aus Ihrer Liste löschen?";
$lang["managemycollections"]="'Meine Kollektionen' verwalten";
$lang["createnewcollection"]="Neue Kollektion erstellen";
$lang["findpubliccollection"]="Öffentliche Kollektionen finden";
$lang["searchpubliccollections"]="Öffentliche Kollektionen suchen";
$lang["addtomycollections"]="zu 'Meine Kollektionen' hinzufügen";
$lang["action-addtocollection"]="Zur Kollektion hinzufügen";
$lang["action-removefromcollection"]="Aus Kollektion entfernen";
$lang["addtocollection"]="Zur Kollektion hinzufügen";
$lang["cantmodifycollection"]="Sie können diese Kollektion nicht bearbeiten.";
$lang["currentcollection"]="Aktuelle Kollektion";
$lang["viewcollection"]="Kollektion anzeigen";
$lang["viewall"]="Alle anzeigen";
$lang["action-editall"]="Alle bearbeiten";
$lang["hidethumbnails"]="Vorschaubilder ausblenden";
$lang["showthumbnails"]="Vorschaubilder einblenden";
$lang["toggle"]="Umschalten";
$lang["resize"]="Größe verändern";
$lang["contactsheet"]="Kontaktabzug";
$lang["mycollection"]="Meine Kollektion";
$lang["editresearchrequests"]="Suchanfragen bearbeiten";
$lang["research"]="Recherche";
$lang["savedsearch"]="Gespeicherte Suche";
$lang["mustspecifyoneusername"]="Bitte geben Sie mindestens einen Benutzernamen an";
$lang["couldnotmatchallusernames"]="Es konnten nicht alle passenden Benutzer gefunden werden";
$lang["emailcollectionmessage"]="hat Ihnen eine Kollektion an Ressourcen von $applicationname gesendet, welche auf der Seite 'Meine Kollektionen' zu finden ist."; # suffixed to user name e.g. "Fred has e-mailed you a collection.."
$lang["nomessage"]="Keine Nachricht";
$lang["emailcollectionmessageexternal"]="hat Ihnen über $applicationname eine Kollektion von Ressourcen gesendet."; # suffixed to user name e.g. "Fred has e-mailed you a collection.."
$lang["clicklinkviewcollection"]="Klicken Sie auf den untenstehenden Link um die Kollektion anzuzeigen.";
$lang["zippedcollectiontextfile"]="Textdatei mit Kollektions-/Ressourcendaten einfügen.";
$lang["archivesettings"]="Kompressionseinstellungen";
$lang["archive-zip"]="ZIP";
$lang["archive-7z"]="7Z";
$lang["download-of-collections-not-enabled"]="Herunterladen von Kollektionen ist nicht aktiviert.";
$lang["archiver-utility-not-found"]="Konnte das Programm zur Kompression nicht finden.";
$lang["collection_download_settings-not-defined"]="\$collection_download_settings ist nicht definiert.";
$lang["collection_download_settings-not-an-array"]="\$collection_download_settings ist kein Array.";
$lang["listfile-argument-not-defined"]="\$archiver_listfile_argument ist nicht definiert.";
$lang["nothing_to_download"]="Nichts herunterzuladen.";
$lang["copycollectionremoveall"]="Alle Ressourcen vor dem Kopieren entfernen";
$lang["purgeanddelete"]="Bereinigen und löschen";
$lang["purgecollectionareyousure"]="Sind Sie sicher, dass Sie diese Kollektion entfernen und alle enthaltenen Ressourcen löschen wollen?";
$lang["collectionsdeleteempty"]="Leere Kollektionen löschen";
$lang["collectionsdeleteemptyareyousure"]="Sind Sie sicher dass Sie alle Ihre leeren Kollektionen löschen wollen?";
$lang["collectionsnothemeselected"]="Bitte einen Themennamen auswählen oder eingeben.";
$lang["downloaded"]="Heruntergeladen";
$lang["contents"]="Inhalte";
$lang["forthispackage"]="für dieses Paket";
$lang["didnotinclude"]="Enthielt nicht";
$lang["selectcollection"]="Kollektion auswählen";
$lang["total"]="Gesamt";
$lang["ownedbyyou"]="von Ihnen erstellt";
$lang["edit_theme_category"]="Themenkategorie bearbeiten";
$lang["emailthemecollectionmessageexternal"]="hat Ihnen mehrere Ressourcen-Kollektionen aus $applicationname geschickt."; 
$lang["emailthememessage"]="hat Ihnen eine Auswahl an Themen aus $applicationname geschickt, die zu der Seite 'Meine Kollektionen' hinzugefügt wurden.";
$lang["clicklinkviewthemes"]="Klicken Sie den untenstehenden Link an, um die Themen anzusehen.";
$lang["clicklinkviewcollections"]="Klicken Sie den untenstehenden Link an, um die Kollektionen anzusehen.";

# Lightbox
$lang["lightbox-image"] = "Bild";
$lang["lightbox-of"] = "von";

# Resource create / edit / view
$lang["createnewresource"]="Neue Ressource erstellen";
$lang["treeobjecttype-resource_type"]=$lang["resourcetype"]="Ressourcen-Typ";
$lang["resourcetypes"]="Ressourcen-Typen";
$lang["deleteresource"]="Ressource löschen";
$lang["downloadresource"]="Ressource herunterladen";
$lang["rightclicktodownload"]="Klicken Sie die rechte Maustaste und wählen Sie 'Speichern unter...' um den Datei-Download zu starten...";
$lang["downloadinprogress"]="Download in Bearbeitung";
$lang["editmultipleresources"]="Mehrere Ressourcen bearbeiten";
$lang["editresource"]="Ressource bearbeiten";
$lang["resources_selected-1"]="1 Ressource ausgewählt"; # 1 resource selected
$lang["resources_selected-2"]="%number Ressourcen ausgewählt"; # e.g. 17 resources selected
$lang["image"]="Bild";
$lang["previewimage"]="Vorschaubild";
$lang["file"]="Datei";
$lang["upload"]="Upload";
$lang["action-upload"]="Upload";
$lang["action-upload-to-collection"]="Hochladen in diese Kollektion";
$lang["uploadafile"]="Datei hochladen";
$lang["replacefile"]="Datei ersetzen";
$lang["showwatermark"]="Wasserzeichen zeigen";
$lang["hidewatermark"]="Wasserzeichen nicht zeigen";
$lang["imagecorrection"]="Bild-Korrekturen";
$lang["previewthumbonly"]="(nur Vorschaubild anzeigen)";
$lang["rotateclockwise"]="im Uhrzeigersinn drehen"; # Verkehrte Zuordnung in der Funktion, daher hier vertauscht
$lang["rotateanticlockwise"]="gegen den Uhrzeigersinn drehen"; # Verkehrte Zuordnung in der Funktion, daher hier vertauscht
$lang["increasegamma"]="Gamma-Wert erhöhen (heller)";
$lang["decreasegamma"]="Gamma-Wert verringern (dunkler)";
$lang["restoreoriginal"]="Original wiederhestellen";
$lang["recreatepreviews"]="Vorschaugrößen neu erstellen";
$lang["retrypreviews"]="Vorschaugrößen erneut neu erstellen";
$lang["specifydefaultcontent"]="Standard-Inhalt für neue Ressourcen festlegen";
$lang["properties"]="Eigenschaften";
$lang["relatedresources"]="Verwandte Ressourcen";
$lang["relatedresources-filename_extension"]="Verwandte Ressourcen &ndash; %EXTENSION"; # Use %EXTENSION, %extension or %Extension as a placeholder. The placeholder will be replaced with the filename extension, using the same case. E.g. "Related Resources - %EXTENSION" -> "Related Resources - JPG"
$lang["relatedresources-id"]="Verwandte Ressourcen - ID%id%"; # %id% will be replaced, e.g. Related Resources - ID57
$lang["relatedresources-restype"]="Verwandte Ressourcen - %Restype%"; # Use %RESTYPE%, %restype% or %Restype% as a placeholder. The placeholder will be replaced with the resource type in plural, using the same case. E.g. "Related resources - %restype%" -> "Related resources - photos"
$lang["relatedresources_onupload"]="Ressourcen beim Upload verknüpfen";
$lang["indexedsearchable"]="Indexierte, durchsuchbare Felder";
$lang["clearform"]="Formular zurücksetzen";
$lang["similarresources"]="ähnliche Ressourcen"; # e.g. 17 similar resources
$lang["similarresource"]="ähnliche Ressource"; # e.g. 1 similar resource
$lang["nosimilarresources"]="keine ähnlichen Ressourcen";
$lang["emailresourcetitle"]="Ressource senden (E-Mail)";
$lang["resourcetitle"]="Ressourcen-Titel";
$lang["requestresource"]="Ressource anfordern";
$lang["action-viewmatchingresources"]="Passende Ressourcen anzeigen";
$lang["nomatchingresources"]="keine passenden Ressourcen";
$lang["matchingresources"]="passende Ressourcen"; # e.g. 17 matching resources
$lang["advancedsearch"]="Erweiterte Suche";
$lang["archiveonlysearch"]="Nur im Archiv suchen";
$lang["allfields"]="alle Felder";
$lang["typespecific"]="Spezifisch";
$lang["youfound"]="Sie haben"; # e.g. you found 17 resources
$lang["youfoundresources"]="Ressourcen gefunden"; # e.g. you found 17 resources
$lang["youfoundresource"]="Ressource gefunden"; # e.g. you found 1 resource
$lang["youfoundresults"]="Ergebnisse"; # e.g. you found 17 resources
$lang["youfoundresult"]="Ergebnis"; # e.g. you found 1 resource
$lang["display"]="Anzeige"; # e.g. Display: thumbnails / list
$lang["sortorder"]="Sortierung";
$lang["relevance"]="Relevanz";
$lang["asadded"]="nach Eingang";
$lang["popularity"]="Popularität";
$lang["rating"]="Bewertung";
$lang["colour"]="Farbe";
$lang["jumptopage"]="springe zur Seite";
$lang["jump"]="springe";
$lang["titleandcountry"]="Titel / Land";
$lang["torefineyourresults"]="Um Ihre Ergebnisse zu verfeinern versuchen Sie";
$lang["verybestresources"]="Die besten Ressourcen";
$lang["addtocurrentcollection"]="Zur aktuellen Kollektion hinzufügen";
$lang["addresource"]="Einzelne Ressource hinzufügen";
$lang["addresourcebatch"]="Ressourcen hinzufügen";
$lang["fileupload"]="Datei-Upload";
$lang["clickbrowsetolocate"]="für eine Dateiauswahl bitte klicken";
$lang["resourcetools"]="Ressourcen-Werkzeuge";
$lang["fileinformation"]="Datei-Information";
$lang["options"]="Optionen";
$lang["previousresult"]="voriges Ergebnis";
$lang["viewallresults"]="alle Ergebnisse anzeigen";
$lang["nextresult"]="nächstes Ergebnis";
$lang["pixels"]="Pixel";
$lang["download"]="Download";
$lang["preview"]="Vorschau";
$lang["fullscreenpreview"]="Vollbild-Vorschau";
$lang["originalfileoftype"]="Original %EXTENSION Datei"; # Use %EXTENSION, %extension or %Extension as a placeholder. The placeholder will be replaced with the filename extension, using the same case. E.g. "Original %EXTENSION File" -> "Original PDF File"
$lang["fileoftype"]="? Datei"; # ? will be replaced, e.g. "MP4 File"
$lang["cell-fileoftype"]="%EXTENSION Datei"; # Use %EXTENSION, %extension or %Extension as a placeholder. The placeholder will be replaced with the filename extension, using the same case. E.g. "%EXTENSION File" -> "JPG File"
$lang["field-fileextension"]="%EXTENSION"; # Use %EXTENSION, %extension or %Extension as a placeholder. The placeholder will be replaced with the filename extension, using the same case. E.g. "%EXTENSION" -> "JPG"
$lang["fileextension-inside-brackets"]="[%EXTENSION]"; # Use %EXTENSION, %extension or %Extension as a placeholder. The placeholder will be replaced with the filename extension, using the same case. E.g. "[%EXTENSION]" -> "[JPG]"
$lang["fileextension"]="%EXTENSION"; # Use %EXTENSION, %extension or %Extension as a placeholder. The placeholder will be replaced with the filename extension, using the same case. E.g. "%EXTENSION" -> "JPG"
$lang["log"]="Protokoll";
$lang["resourcedetails"]="Ressourcen-Details";
$lang["offlineresource"]="Offline-Ressource";
$lang["action-request"]="Anfragen";
$lang["request"]="Anfrage";
$lang["requestlog"]="Anfrageprotokoll";
$lang["searchforsimilarresources"]="Nach ähnlichen Ressourcen suchen";
$lang["clicktoviewasresultset"]="diese Ressourcen zusammenfassend anzeigen";
$lang["searchnomatches"]="Keine passenden Suchergebnisse verfügbar.";
$lang["try"]="Versuchen Sie";
$lang["tryselectingallcountries"]="Versuchen Sie <strong>alle Länder</strong> auszuwählen, oder";
$lang["tryselectinganyyear"]="versuchen Sie <strong>beliebiges Jahr</strong> auszuwählen, oder";
$lang["tryselectinganymonth"]="versuchen Sie <strong>beliebigen Monat</strong> auszuwählen, oder";
$lang["trybeinglessspecific"]="versuchen Sie Ihre weniger spezifisch zu suchen";
$lang["enteringfewerkeywords"]="(weniger Suchbegriffe eingeben)."; # Suffixed to any of the above 4 items e.g. "Try being less specific by entering fewer search keywords"
$lang["match"]="passend";
$lang["matches"]="passende";
$lang["inthearchive"]="im Archiv";
$lang["nomatchesinthearchive"]="Keine passenden Archiv-Einträge";
$lang["savethissearchtocollection"]="Suchanfrage in der aktuellen Kollektion speichern";
$lang["mustspecifyonekeyword"]="Sie müssen mindestens einen Suchbegriff angeben.";
$lang["hasemailedyouaresource"]="hat Ihnen eine Ressource gesendet."; # Suffixed to user name, e.g. Fred has e-mailed you a resource
$lang["clicktoviewresource"]="Klicken Sie untenstehenden Link um die Ressource anzuzeigen.";
$lang["statuscode"]="Statuscode";
$lang["unoconv_pdf"]="erzeugt durch Open Office";
$lang['calibre_pdf']="erzeugt durch Calibre";
$lang["resourcenotfound"]="Ressource nicht gefunden.";

# Resource log - actions
$lang["resourcelog"]="Ressourcenprotokoll";
$lang["log-u"]="Hochgeladene Datei(en)";
$lang["log-c"]="Erstellte Ressourcen";
$lang["log-d"]="heruntergeladene Datei(en)";
$lang["log-e"]="Bearbeitetes Ressourcen-Feld";
$lang["log-m"]="Bearbeitetes Ressourcen-Feld (Mehrfach-Bearbeitung)";
$lang["log-E"]="Ressource via E-Mail weitergegeben an ";//  + notes field
$lang["log-v"]="Ressource angesehen";
$lang["log-x"]="Ressource gelöscht";
$lang["log-l"]="Eingeloggt"; # For user entries only.
$lang["log-t"]="Datei transformiert";
$lang["log-s"]="Status geändert";
$lang["log-a"]="Zugriff geändert";
$lang["log-r"]="Metadaten zurückgesetzt";
$lang["log-b"]="Alternative Datei erstellt";
$lang["log-missinglang"]="[type] (Sprache fehlt)"; # [type] will be replaced.

$lang["backtoresourceview"]="Zurück zur Ressourcen-Ansicht";
$lang["continuetoresourceview"]="Weiter zur Ressourcen-Ansicht";

# Resource status
$lang["status"]="Status";
$lang["status-2"]="Benutzer-Beiträge: Freischaltung noch nicht erledigt";
$lang["status-1"]="Benutzer-Beiträge: Überprüfung noch nicht erledigt";
$lang["status0"]="Aktiv";
$lang["status1"]="Archivierung noch nicht erledigt";
$lang["status2"]="Archiviert";
$lang["status3"]="Gelöscht";

# Charts
$lang["activity"]="Aktivität";
$lang["summary"]="Zusammenfassung";
$lang["mostinaday"]="am meisten pro Tag";
$lang["totalfortheyear"]="Gesamt für das Jahr";
$lang["totalforthemonth"]="Gesamt für den Monat";
$lang["dailyaverage"]="Tagesdurchschnitt für aktive Tage";
$lang["nodata"]="Keine Daten für diesen Zeitabschnitt verfügbar.";
$lang["max"]="max."; # i.e. maximum
$lang["statisticsfor"]="Statistik für"; # e.g. Statistics for 2007
$lang["printallforyear"]="Alle Statistiken dieses Jahres ausdrucken";

# Log in / user account
$lang["nopassword"]="Klicken Sie hier, wenn Sie über keinen Zugang verfügen";
$lang["forgottenpassword"]="Klicken Sie hier, wenn Sie Ihr Passwort vergessen haben";
$lang["keepmeloggedin"]="Auf diesem Computer angemeldet bleiben";
$lang["columnheader-username"]=$lang["username"]="Benutzername";
$lang["password"]="Passwort";
$lang["login"]="Anmelden";
$lang["loginincorrect"]="Fehler beim Benutzernamen bzw. Passwort. Bitte versuchen Sie es erneut.";
$lang["accountexpired"]="Ihr Benutzer-Account ist abgelaufen. Bitte kontaktieren Sie das Ressourcen-Team.";
$lang["useralreadyexists"]="Es existiert bereits ein Benutzer-Account mit diesem Benutzernamen bzw. dieser E-Mail Adresse. Änderungen wurden nicht gespeichert.";
$lang["useremailalreadyexists"]="Es existiert bereits ein Benutzer-Account mit dieser E-Mail Adresse.";
$lang["ticktoemail"]="Anklicken, um dem Benutzer den Benutzernamen und das Passwort zu senden (E-Mail)";
$lang["ticktodelete"]="Anklicken, um diesen Benutzer zu löschen";
$lang["edituser"]="Benutzer bearbeiten";
$lang["columnheader-full_name"]=$lang["fullname"]="Vollständiger Name";
$lang["email"]="E-Mail";
$lang["columnheader-e-mail_address"]=$lang["emailaddress"]="E-Mail Adresse";
$lang["suggest"]="vorschlagen";
$lang["accountexpiresoptional"]="Account gültig bis (optional)";
$lang["lastactive"]="Letzte Aktivität";
$lang["lastbrowser"]="Letzter Browser";
$lang["searchusers"]="Benutzer suchen";
$lang["createuserwithusername"]="Benutzer mit Benutzernamen erstellen...";
$lang["emailnotfound"]="die gesuchte E-Mail Adresse konnte nicht gefunden werden";
$lang["yourname"]="Ihr Name";
$lang["youremailaddress"]="Ihre E-Mail Adresse";
$lang["sendreminder"]="Erinnerung senden";
$lang["sendnewpassword"]="Neues Passwort senden";
$lang["requestuserlogin"]="Benutzer-Login anfordern";
$lang["accountlockedstatus"]="Account ist gesperrt";
$lang["accountunlock"]="Unlock";

# Research request
$lang["nameofproject"]="Name des Projektes";
$lang["descriptionofproject"]="Beschreibung des Projektes";
$lang["descriptionofprojecteg"]="(z.B.: Publikum / Mode / Fachgebiet / geografisches Gebiet)";
$lang["deadline"]="Abgabefrist";
$lang["nodeadline"]="keine Abgabefrist";
$lang["noprojectname"]="Sie müssen einen Projekt-Namen angeben";
$lang["noprojectdescription"]="Sie müssen einen Projekt-Beschreibung angeben";
$lang["contacttelephone"]="Kontakt: Telefon";
$lang["finaluse"]="Endgültiger Verwendungszweck";
$lang["finaluseeg"]="(z.B. Powerpoint / Broschüre / Poster)";
$lang["noresourcesrequired"]="Anzahl der benötigten Ressourcen für das endgültige Produkt?";
$lang["shaperequired"]="Gestaltung/Art der Bilder erforderlich";
$lang["portrait"]="Hochformat";
$lang["landscape"]="Querformat";
$lang["square"]="Quadrat";
$lang["either"]="egal";
$lang["sendrequest"]="Anfrage senden";
$lang["editresearchrequest"]="Such-Anfrage editieren";
$lang["requeststatus0"]=$lang["unassigned"]="nicht zugeordnet";
$lang["requeststatus1"]="in Bearbeitung";
$lang["requeststatus2"]="fertiggestellt";
$lang["copyexistingresources"]="Ressource dieser Suchanfrage in eine existierende Kollektion kopieren";
$lang["deletethisrequest"]="Diese Anfrage löschen?";
$lang["requestedby"]="Angefragt von";
$lang["requesteditems"]="Angefragte Objekte";
$lang["assignedtoteammember"]="Zuordnung an Team-Mitglied";
$lang["typecollectionid"]="(ID der Kolletion eintragen)";
$lang["researchid"]="ID der Suchanfrage";
$lang["assignedto"]="Zugeordnet an";
$lang["createresearchforuser"]="Suchanfrage für Benutzer erstellen";
$lang["searchresearchrequests"]="Suchanfragen durchsuchen";
$lang["requestasuser"]="Anfrage als Benutzer";
$lang["haspostedresearchrequest"]="hat eine Suchanfrage angefordert"; # username is suffixed to this
$lang["newresearchrequestwaiting"]="Neue Suchanfragen warten auf die Bearbeitung";
$lang["researchrequestassignedmessage"]="Ihre Suchanfrage wurde unserem Team weitergeleitet. Sobald Ihre Suchanfrage abgeschlossen ist, erhalten Sie ein E-Mail mit allen von uns empfohlenen Ressourcen.";
$lang["researchrequestassigned"]="Suchanfrage zugeordnet";
$lang["researchrequestcompletemessage"]="Ihre Suchanfrage ist fertiggestellt und wurde auf Ihrer Seite 'Meine Kollektion' hinzugefügt.";
$lang["researchrequestcomplete"]="Suchanfrage fertiggestellt";


# Misc / global
$lang["selectgroupuser"]="Gruppe/Benutzer auswählen...";
$lang["select"]="auswählen...";
$lang["selectloading"]="auswählen....";
$lang["add"]="hinzufügen";
$lang["create"]="Erstellen";
$lang["treeobjecttype-group"]=$lang["group"]="Gruppe";
$lang["confirmaddgroup"]="Alle Benutzer dieser Gruppe zuordnen?";
$lang["backtoteamhome"]="zurück zur Administration";
$lang["columnheader-resource_id"]=$lang["resourceid"]="Ressource (ID)";
$lang["id"]="ID";
$lang["todate"]="bis";
$lang["fromdate"]="von";
$lang["day"]="Tag";
$lang["month"]="Monat";
$lang["year"]="Jahr";
$lang["hour-abbreviated"]="HH";
$lang["minute-abbreviated"]="MM";
$lang["itemstitle"]="Objekte";
$lang["tools"]="Werkzeuge";
$lang["created"]="erstellt";
$lang["user"]="Benutzer";
$lang["owner"]="Besitzer";
$lang["message"]="Nachricht";
$lang["name"]="Name";
$lang["action"]="Aktion";
$lang["treeobjecttype-field"]=$lang["field"]="Feld";
$lang["save"]="Speichern";
$lang["revert"]="Wiederherstellen";
$lang["cancel"]="abbrechen";
$lang["view"]="zeige";
$lang["type"]="Typ";
$lang["text"]="Text";
$lang["yes"]="ja";
$lang["no"]="nein";
$lang["key"]="Bedeutung:"; # e.g. explanation of icons on search page
$lang["languageselection"]="Sprache";
$lang["language"]="Sprache";
$lang["changeyourpassword"]="Ändern Sie Ihr Passwort";
$lang["yourpassword"]="Ihr Passwort";
$lang["currentpassword"]="Aktuelles Passwort";
$lang["newpassword"]="Neues Passwort";
$lang["newpasswordretype"]="Neues Passwort (Eingabe wiederholen)";
$lang["passwordnotvalid"]="Dies ist kein gültiges Passwort";
$lang["passwordnotmatch"]="Die eingebenen Passwörter stimmen nicht überein";
$lang["wrongpassword"]="Passwort nicht korrekt, bitte versuchen Sie es erneut";
$lang["action-view"]="Anzeigen";
$lang["action-preview"]="Vorschau";
$lang["action-expand"]="Ausklappen";
$lang["action-select"]="Auswählen";
$lang["action-download"]="Download";
$lang["action-email"]="E-Mail";
$lang["action-edit"]="Bearbeiten";
$lang["action-delete"]="Löschen";
$lang["action-deletecollection"]="Kollektion löschen";
$lang["action-revertmetadata"]="Metadaten wiederherstellen";
$lang["confirm-revertmetadata"]="Sind Sie sicher, dass Sie die ursprünglichen Metadaten aus dieser Datei neu einlesen wollen? Bei dieser Aktion gehen alle Änderungen an den Metadaten verloren.";
$lang["action-remove"]="Entfernen";
$lang["complete"]="Fertig";
$lang["backtohome"]="zurück zur Startseite";
$lang["continuetohome"]="weiter zur Startseite";
$lang["backtohelphome"]="zurück zur Hilfeseite";
$lang["backtosearch"]="zurück zu meinen Suchergebnissen";
$lang["backtoview"]="Ressource-Ansicht";
$lang["backtoeditresource"]="zurück zur Ressourcen-Bearbeitung";
$lang["backtouser"]="zurück zum Benutzer-Login";
$lang["continuetouser"]="weiter zum Benutzer-Login";
$lang["termsandconditions"]="Allg. Geschäfts- und Nutzungsbedingungen";
$lang["iaccept"]="Ich akzeptiere";
$lang["contributedby"]="Beigetragen von";
$lang["format"]="Format";
$lang["notavailableshort"]="N/A";
$lang["allmonths"]="Alle Monate";
$lang["allgroups"]="Alle Gruppen";
$lang["status-ok"]="OK";
$lang["status-fail"]="FEHLER";
$lang["status-warning"]="WARNUNG";
$lang["status-notinstalled"]="Nicht installiert";
$lang["status-never"]="Niemals";
$lang["softwareversion"]="? Version"; # E.g. "PHP version"
$lang["softwarebuild"]="? Build"; # E.g. "ResourceSpace Build"
$lang["softwarenotfound"]="'?' nicht gefunden"; # ? will be replaced.
$lang["client-encoding"]="(Client-encoding: %encoding)"; # %encoding will be replaced, e.g. client-encoding: utf8
$lang["browseruseragent"]="Browser User-Agent";
$lang['serverplatform']="Serverplattform";
$lang["are_available-0"]="sind verfügbar";
$lang["are_available-1"]="ist verfügbar";
$lang["are_available-2"]="sind verfügbar";
$lang["were_available-0"]="waren verfügbar";
$lang["were_available-1"]="war verfügbar";
$lang["were_available-2"]="waren verfügbar";
$lang["resource-0"]="Ressourcen";
$lang["resource-1"]="Ressource";
$lang["resource-2"]="Ressourcen";
$lang["status-note"]="HINWEIS";
$lang["action-changelanguage"]="Sprache ändern";
$lang["loading"]="Laden...";

# Pager
$lang["next"]="vor";
$lang["previous"]="zurück";
$lang["page"]="Seite";
$lang["of"]="von"; # e.g. page 1 of 2
$lang["items"]="Objekte"; # e.g. 17 items
$lang["item"]="Objekt"; # e.g. 1 item

# Statistics
$lang["stat-addpubliccollection"]="Öffentliche Kollektion hinzufügen";
$lang["stat-addresourcetocollection"]="Ressourcen zur Kollektion hinzufügen";
$lang["stat-addsavedsearchtocollection"]="Gespeicherte Suchen zur Kollektion";
$lang["stat-addsavedsearchitemstocollection"]="Gespeicherte Suchen (Objkete) zur Kollektion";
$lang["stat-advancedsearch"]="Erweiterte Suche";
$lang["stat-archivesearch"]="Archivsuche";
$lang["stat-assignedresearchrequest"]="Zugeordnete Suchanfrage";
$lang["stat-createresource"]="Ressource erstellen";
$lang["stat-e-mailedcollection"]="gesendete Kollektion (E-Mail)";
$lang["stat-e-mailedresource"]="gesendete Ressource (E-Mail)";
$lang["stat-keywordaddedtoresource"]="hinzugefügte Suchbegriffe (Ressource)";
$lang["stat-keywordusage"]="Suchbegriffe";
$lang["stat-newcollection"]="Neue Kollektion";
$lang["stat-newresearchrequest"]="Neue Suchanfrage";
$lang["stat-printstory"]="Inhalt drucken";
$lang["stat-processedresearchrequest"]="durchgeführte Suchanfragen";
$lang["stat-resourcedownload"]="Ressource (Download)";
$lang["stat-resourceedit"]="Ressource (bearbeiten)";
$lang["stat-resourceupload"]="Ressource (Upload)";
$lang["stat-resourceview"]="Ressource (Ansicht)";
$lang["stat-search"]="Suchen";
$lang["stat-usersession"]="Benutzersession";
$lang["stat-addedsmartcollection"]="Smarte Kollektion hinzugefügt";

# Access
$lang["access0"]="Offen";
$lang["access1"]="eingeschränkt";
$lang["access2"]="vertraulich";
$lang["access3"]="benutzerdefiniert";
$lang["statusandrelationships"]="Status und Beziehungen";

# Lists
$lang["months"]=array("Januar","Februar","März","April","Mai","Juni","Juli","August","September","Oktober","November","Dezember");
$lang["false-true"]=array("Falsch","Wahr");

# Formatting
$lang["plugin_field_fmt"]="%A (%B)"; // %A and %B are replaced by content defined by individual plugins. See, e.e., config_db_single_select in /include/plugin_functions.php


#Sharing
$lang["share"]="Weitergeben";
$lang["sharecollection"]="Kollektion weitergeben";
$lang["sharecollection-name"]="Kollektion weitergeben - %collectionname"; # %collectionname will be replaced, e.g. Share Collection - Cars
$lang["share_theme_category"]="Themenkategorie weitergeben";
$lang["share_theme_category_subcategories"]="Themen in Unterkategorien für externe Benutzer einschließen?";
$lang["email_theme_category"]="Themenkategorie per E-Mail versenden";
$lang["generateurl"]="URL generieren";
$lang["generateurls"]="URLs generieren";
$lang["generateexternalurl"]="Externe URL generieren";
$lang["generateexternalurls"]="Externe URLs generieren";
$lang["generateurlinternal"]="Die folgende URL funktioniert nur für eingeloggte Benutzer.";
$lang["generateurlexternal"]="Die folgende URL funktioniert ohne Login. <strong>Bitte beachten Sie: Wenn neue Ressourcen zur Kollektion hinzugefügt werden, funktioniert diese URL aus Sicherheitsgründen nicht mehr und muss neu generiert werden.</strong>";
$lang["generatethemeurlsexternal"]="Die untenstehenden URLs können ohne Login benutzt werden.";
$lang["showexistingthemeshares"]="Bestehende Weitergaben für Themen in dieser Kategorie anzeigen";
$lang["internalusersharing"]="Weitergeben an interne Benutzer";
$lang["externalusersharing"]="Weitergeben an externe Benutzer";
$lang["externalusersharing-name"]="Weitergegeben an externe Benutzer - %collectionname%"; # %collectionname will be replaced, e.g. External User Sharing - Cars
$lang["accesskey"]="Zugangscode";
$lang["sharedby"]="Weitergegeben von";
$lang["sharedwith"]="Weitergegeben an";
$lang["lastupdated"]="Letzte Aktualisierung";
$lang["lastused"]="Zuletzt benutzt";
$lang["noattachedusers"]="keine zugeordneten Benutzer";
$lang["confirmdeleteaccess"]="Sind Sie sicher, dass Sie diesen Zugangscode löschen wollen? Benutzer, denen Sie den Zugangscode geschickt haben, können dann nicht mehr auf die Kollektion zugreifen.";
$lang["confirmdeleteaccessresource"]="Sind Sie sicher, dass Sie diesen Zugangscode löschen wollen? Benutzer, denen Sie den Zugangscode geschickt haben, können dann nicht mehr auf die Ressource zugreifen.";
$lang["noexternalsharing"]="Nicht an externe Benutzer weitergegeben.";
$lang["sharedcollectionaddwarning"]="Achtung: Diese Kollektion wurde an externe Benutzer weitergegeben. Die Ressource, die Sie zur Kollektion hinzugefügt haben, ist nun auch für diese Benutzer verfügbar. Klicken Sie auf 'Weitergeben', um die Einstellungen zu verwalten.";
$lang["sharedcollectionaddwarningupload"]="Achtung: Diese Kollektion wurde an externe Benutzer weitergegeben. Die Ressourcen, die Sie hochladen, werden auch für diese Benutzer verfügbar. Klicken Sie auf 'Weitergeben', um die Einstellungen zu verwalten.";

$lang["restrictedsharecollection"]="Sie haben eingeschränkten Zugriff auf eine oder mehrere Ressourcen in dieser Kollektion, daher ist die Weitergabe deaktiviert.";
$lang["selectgenerateurlexternal"]="Um eine URL für Nutzer ohne Login zu generieren, wählen Sie bitte die Zugriffsrechte aus, die Sie für diese Ressourcen gewähren wollen.";
$lang["selectgenerateurlexternalthemecat"]="Um URLs für Nutzer ohne Login zu generieren, wählen Sie bitte die Zugriffsrechte aus, die Sie für diese Ressourcen gewähren wollen.";
$lang["externalselectresourceaccess"]="Wenn Sie die Ressourcen per E-Mail an Nutzer ohne Login weitergeben wollen, wählen Sie bitte die Zugriffsrechte aus, die Sie für diese Ressourcen gewähren wollen.";
$lang["externalselectresourceexpires"]="Wenn Sie die Ressourcen per E-Mail an Nutzer ohne Login weitergeben wollen, geben Sie bitte ein Ablaufdatum für den Link ein.";
$lang["externalshareexpired"]="Dieser Link ist leider abgelaufen und damit nicht mehr verfügbar.";
$lang["notapprovedsharecollection"]="Eine oder mehrere Ressourcen in dieser Kollektion sind nicht aktiv. Die Weitergabe ist deshalb nicht erlaubt.";
$lang["notapprovedsharetheme"]="Die Weitergabe mindestens einer Kollektion ist nicht erlaubt, da eine oder mehrere Ressourcen nicht aktiv sind.";
$lang["notapprovedresources"]="Die folgenden Ressourcen sind nicht aktiv und können deshalb nicht zu einer weitergegebenen Kollektion hinzugefügt werden: ";


# New for 1.3
$lang["savesearchitemstocollection"]="Gefundene Objekte in der aktuellen Kollektion speichern";
$lang["removeallresourcesfromcollection"]="Alle Ressourcen aus dieser Kollektion entfernen";
$lang["deleteallresourcesfromcollection"]="Alle Ressourcen dieser Kollektion löschen";
$lang["deleteallsure"]="Sind Sie sicher, dass Sie diese Ressourcen LÖSCHEN möchten? Die Ressourcen werden gelöscht und nicht (nur) aus dieser Kollektion entfernt.";
$lang["batchdonotaddcollection"]="(keiner Kollektion hinzufügen)";
$lang["collectionsthemes"]="Verwandte Themen und öffentliche Kollektionen";
$lang["recent"]="Neueste";
$lang["n_recent"]="%qty neueste";
$lang["batchcopyfrom"]="Daten von der Ressource (ID) kopieren";
$lang["copy"]="kopieren";
$lang["zipall"]="alle komprimieren (zip)";
$lang["downloadzip"]="Kollektion als ZIP-Datei herunterladen";
$lang["downloadsize"]="Download-Größe";
$lang["tagging"]="Tagging";
$lang["speedtagging"]="Speed Tagging";
$lang["existingkeywords"]="Existierende Suchbegriffe:";
$lang["extrakeywords"]="zusätzliche Suchbegriffe";
$lang["leaderboard"]="Rangliste";
$lang["confirmeditall"]="Sind Sie sicher, dass Sie speichern möchten? Dies wird alle existierenden Werte der ausgewählten Felder für alle Ressourcen in Ihrer gegenwärtigen Kollektion überschreiben.";
$lang["confirmsubmitall"]="Sind Sie sicher, dass Sie alles zur Überprüfung abschicken wollen? Dies wird alle existierenden Einträge für die ausgewählten Felder für alle Ressourcen in Ihrer aktuellen Kollektion überschreiben und sie zur Überprüfung abschicken.";
$lang["confirmunsubmitall"]="Sind Sie sicher, dass Sie alles von der Überprüfung zurücknehmen wollen? Dies wird alle Einträge für die ausgewählten Felder für alle Ressourcen in Ihrer aktuellen Kollektion überschreiben und sie von der Überprüfung zurücknehmen.";
$lang["confirmpublishall"]="Sind Sie sicher, dass Sie alles veröffentlichen wollen? Dies wird alle Einträge für die ausgewählten Felder für alle Ressourcen in Ihrer aktuellen Kollektion überschreiben und sie veröffentlichen.";
$lang["confirmunpublishall"]="Sind Sie sicher, dass Sie alles von der Veröffentlichung zurücknehmen wollen? Dies wird alle Einträge für die ausgewählten Felder für alle Ressourcen in Ihrer aktuellen Kollektion überschreiben und sie von der Veröffentlichung zurücknehmen.";
$lang["collectiondeleteconfirm"]="Sind Sie sicher, dass Sie diese Kollektion löschen möchten?";
$lang["hidden"]="(versteckt)";
$lang["requestnewpassword"]="Neues Passwort anfordern";

# New for 1.4
$lang["reorderresources"]="Ressourcen innerhalb der Kollektion neu anordnen (halten und ziehen)";
$lang["addorviewcomments"]="Kommentare hinzufügen oder ansehen";
$lang["collectioncomments"]="Kommentare zu den Kollektionen";
$lang["collectioncommentsinfo"]="Add a comment to this collection for this resource. This will only apply to this collection.";
$lang["comment"]="Kommentar";
$lang["warningexpired"]="Ressource abgelaufen";
$lang["warningexpiredtext"]="Warnung! Diese Ressource hat das Ablaufdatum überschritten. Klicken Sie den untenstehenden Link um die Download-Funktion wieder zu aktivieren..";
$lang["warningexpiredok"]="&gt; Ressourcen-Download aktivieren";
$lang["userrequestcomment"]="Kommentar";
$lang["addresourcebatchbrowser"]="Ressourcen hinzufügen - Im Browser";
$lang["addresourcebatchbrowserjava"]="Ressourcen hinzufügen - Im Browser (Java, alte Version)";

$lang["addresourcebatchftp"]="Ressourcen hinzufügen - FTP";
$lang["replaceresourcebatch"]="Ressourcen ersetzen";
$lang["editmode"]="Bearbeitungsmodus";
$lang["replacealltext"]="Alle Texte ersetzen";
$lang["findandreplace"]="Suchen und ersetzen";
$lang["prependtext"]="Text am Anfang hinzufügen";
$lang["appendtext"]="Text am Ende hinzufügen";
$lang["removetext"]="Text entfernen / Option(en)";
$lang["find"]="Finden";
$lang["andreplacewith"]="...und ersetzen mit...";
$lang["relateallresources"]="Alle Ressourcen dieser Kollektion miteinander verknüpfen";

# New for 1.5
$lang["columns"]="Spalten";
$lang["contactsheetconfiguration"]="Konfiguration Kontaktblatt";
$lang["thumbnails"]="Vorschaubilder";
$lang["contactsheetintrotext"]="Bitte wählen Sie die Blattgröße und die Spaltenanzahl für Ihr Kontaktblatt.";
$lang["size"]="Größe";
$lang["orientation"]="Ausrichtung";
$lang["requiredfield"]="Das ist ein Pflichtfeld";
$lang["requiredfields"]="Bitte überprüfen Sie das Formular. Die folgenden Felder wurden noch nicht ausgefüllt: ";
$lang["requiredantispam"]="Der Anti-Spam Code wurde nicht korrekt eingegeben, bitte versuchen Sie es erneut";
$lang["viewduplicates"]="Doppelte Ressourcen anzeigen";
$lang["duplicateresources"]="Doppelte Ressourcen";
$lang["duplicateresourcesfor"]="Doppelte Ressourcen für ";
$lang["noresourcesfound"]="Keine Ergebnisse gefunden";
$lang["userlog"]="Benutzer-Statistik";
$lang["ipaddressrestriction"]="IP-Adressen Beschränkung (optional)";
$lang["wildcardpermittedeg"]="Wildcard erlaubt; z.B.";

# New for 1.6
$lang["collection_download_original"]="Originaldatei";
$lang["newflag"]="NEUE!";
$lang["link"]="Link";
$lang["uploadpreview"]="Nur ein Vorschaubild hochladen";
$lang["starttypingusername"]="(Bitte Anfangsbuchstaben vom Benutzernamen / Namen / Gruppennamen eingeben)";
$lang["requestfeedback"]="Rückmeldung anfordern<br />(Sie erhalten die Antwort per e-mail)";
$lang["sendfeedback"]="Rückmeldung abschicken";
$lang["feedbacknocomments"]="Sie haben keine Kommentare für die Ressourcen in der Kollektion abgegeben.<br />Clicken Sie die Sprechblase neben den Ressourcen an, um einen Kommentar hinzuzufügen.";
$lang["collectionfeedback"]="Rückmeldung zur Kollektion";
$lang["collectionfeedbackemail"]="Folgende Rückmeldung wurde abgegeben:";
$lang["feedbacksent"]="Ihre Rückmeldung wurde abgeschickt.";
$lang["newarchiveresource"]="Neue archivierte Ressource";
$lang["nocategoriesselected"]="Keine Kategorie ausgewählt";
$lang["showhidetree"]="Baum anzeigen/verstecken";
$lang["clearall"]="Alles zurücksetzen";
$lang["clearcategoriesareyousure"]="Sind Sie sicher, dass Sie alle ausgewählten Optionen zurücksetzen wollen?";

$lang["archive"]="Archiv";
$lang["collectionviewhover"]="Ressourcen dieser Kollektion anzeigen";
$lang["collectioncontacthover"]="Kontaktabzug der Ressourcen dieser Kollektion erstellen";
$lang["original"]="Original";

$lang["password_not_min_length"]="Das Passwort muss mindestens ? Zeichen lang sein";
$lang["password_not_min_alpha"]="Das Passwort muss mindestens ? Buchstaben (a-z, A-Z) enthalten";
$lang["password_not_min_uppercase"]="Das Passwort muss mindestens ? Großbuchstaben (A-Z) enthalten";
$lang["password_not_min_numeric"]="Das Passwort muss mindestens ? Ziffern (0-9) enthalten";
$lang["password_not_min_special"]="Das Passwort muss mindestens ? Sonderzeichen (!@$%&* etc.) enthalten";
$lang["password_matches_existing"]="Das eingegebene Passwort ist identisch mit dem bestehenden Passwort";
$lang["password_expired"]="Ihr Passwort ist abgelaufen. Sie müssen ein neues Passwort eingeben";
$lang["max_login_attempts_exceeded"]="Sie haben die maximale Anzahl an Login Versuchen überschritten. Sie müssen ? Minuten warten, bis Sie es erneut versuchen können.";

$lang["newlogindetails"]="Dies sind Ihre neuen Login-Daten."; # For new password mail
$lang["youraccountdetails"]="Ihre Login-Daten"; # Subject of mail sent to user on user details save

$lang["copyfromcollection"]="Aus Kollektion kopieren";
$lang["donotcopycollection"]="Nicht aus einer Kollektion kopieren";

$lang["resourcesincollection"]="Ressourcen in dieser Kollektion"; # E.g. 3 resources in this collection
$lang["removefromcurrentcollection"]="Aus aktueller Kollektion entfernen";
$lang["showtranslations"]="+ Übersetzungen zeigen";
$lang["hidetranslations"]="- Übersetzungen verbergen";
$lang["archivedresource"]="Archivierte Ressourcen";

$lang["managerelatedkeywords"]="Verknüpfte Stichworte verwalten";
$lang["keyword"]="Stichwort";
$lang["relatedkeywords"]="Verknüpfte Stichworte";
$lang["matchingrelatedkeywords"]="Passende verknüpfte Stichworte";
$lang["newkeywordrelationship"]="Neue Verknüpfung für Stichworte hinzufügen...";
$lang["searchkeyword"]="Stichwort für Suche";

$lang["exportdata"]="Daten-Export";
$lang["exporttype"]="Export Typ";

$lang["managealternativefiles"]="Alternative Dateien verwalten";
$lang["managealternativefilestitle"]="Alternative Dateien verwalten";
$lang["alternativefiles"]="Alternative Dateien";
$lang["filetype"]="Dateiformat";
$lang["filedeleteconfirm"]="Wollen Sie diese Datei wirklich löschen?";
$lang["addalternativefile"]="Alternative Datei hinzufügen";
$lang["editalternativefile"]="Alternative Datei bearbeiten";
$lang["description"]="Beschreibung";
$lang["notuploaded"]="Nicht hochgeladen";
$lang["uploadreplacementfile"]="Datei ersetzen";
$lang["backtomanagealternativefiles"]="Zurück zu Alternative Dateien verwalten";


$lang["resourceistranscoding"]="Ressource wird momentan umgewandelt";
$lang["cantdeletewhiletranscoding"]="Sie können Ressourcen nicht löschen, während Sie umgewandelt werden.";

$lang["maxcollectionthumbsreached"]="Zu viele Ressourcen in dieser Kollektion, um Thumbnails anzuzeigen. Thumbnails werden jetzt versteckt.";

$lang["ratethisresource"]="Wie bewerten Sie diese Ressource?";
$lang["ratingthankyou"]="Vielen Dank für Ihre Bewertung.";
$lang["ratings"]="Bewertungen";
$lang["rating_lowercase"]="Bewertung";
$lang["ratingremovehover"]="Anklicken, um Ihre Bewertung zu entfernen";
$lang["ratingremoved"]="Ihre Bewertung wurde entfernt.";

$lang["cannotemailpassword"]="Sie können dem Benutzer das bestehende Passwort nicht per E-Mail senden, da es nicht gespeichert wird (nur ein verschlüsselter Hash wird gespeichert).<br /><br />Bitte nutzen Sie den 'Vorschlagen' Button oben, der ein neues Passwort generiert und die E-Mail Funktion wieder ermöglicht.";

$lang["userrequestnotification1"]="Die Login Anfrage wurde mit den folgenden Daten gestellt:";
$lang["userrequestnotification2"]="Wenn Sie den Benutzer erstellen möchten, folgen Sie bitte dem untenstehenden Link und legen den Benutzer dort an.";
$lang["ipaddress"]="IP-Adresse";
$lang["userresourcessubmitted"]="Die folgenden Ressourcen wurden von Benutzern zur Überprüfung eingesandt:";
$lang["userresourcesapproved"]="Ihre eingereichten Ressourcen wurden freigegeben:";
$lang["userresourcesunsubmitted"]="Die folgenden Ressourcen wurden von Benutzern zurückgezogen und müssen nicht mehr überprüft werden:";
$lang["viewalluserpending"]="Alle von Benutzern zur Überprüfung eingesandten Ressourcen anzeigen:";

# New for 1.7
$lang["installationcheck"]="Installation überprüfen";
$lang["managefieldoptions"]="Feldoptionen verwalten";
$lang["matchingresourcesheading"]="Passende Ressourcen";
$lang["backtofieldlist"]="Zurück zur Feldliste";
$lang["rename"]="Umbenennen";
$lang["showalllanguages"]="Alle Sprachen anzeigen";
$lang["hidealllanguages"]="Alle Sprachen verstecken";
$lang["clicktologinasthisuser"]="Als dieser Benutzer anmelden";
$lang["addkeyword"]="Stichwort hinzufügen";
$lang["selectedresources"]="Ausgewählte Ressourcen";
$lang["addresourcebatchlocalfolder"]="Ressourcen hinzufügen - aus Upload Ordner";
$lang["phpextensions"]="PHP Extensions";

# Setup Script
$lang["setup-alreadyconfigured"]="Ihre ResourceSpace installation ist bereits konfiguriert. Um die Installation neu zu konfigurieren, können Sie die Datei <pre>include/config.php</pre> und dann diese Seite neu laden.";
$lang["setup-successheader"]="Glückwunsch!";
$lang["setup-successdetails"]="Ihre ResourceSpace Installation ist abgeschlossen. Weitere Konfigurationsoptionen finden Sie in der Datei 'include/default.config.php'.";
$lang["setup-successnextsteps"]="Nächste Schritte:";
$lang["setup-successremovewrite"]="Sie können nun den Schreibzugriff auf den Ordner 'include/' entfernen.";
$lang["setup-visitwiki"]='Besuchen Sie das <a target="_blank" href="http://wiki.resourcespace.org/index.php/?title=main_Page">ResourceSpace Documentation Wiki</a> für weitere Informationen über die Anpassung Ihrer Installation';
$lang["php-config-file"]="PHP config: '%phpinifile'"; # %phpinifile will be replaced, e.g. PHP config: '/etc/php5/apache2/php.ini'
$lang["setup-checkconfigwrite"]="Schreibzugriff auf Konfigurationsverzeichnis:";
$lang["setup-checkstoragewrite"]="Schreibzugriff auf Datenverzeichnis:";
$lang["setup-welcome"]="Willkommen bei ResourceSpace";
$lang["setup-introtext"]="Danke, dass Sie sich für ResourceSpace entschieden haben.  Dieser Konfigurationsassistent wird Ihnen helfen, ResourceSpace einzurichten, und muss nur einmal ausgeführt werden.";
$lang["setup-checkerrors"]="Fehler gefunden.<br />Bitte beheben Sie diese Fehler und laden Sie dann diese Seite erneut.";
$lang["setup-errorheader"]="In Ihrer Konfiguration wurden Fehler gefunden.  Eine detaillierte Fehlerbeschreibung finden Sie unten.";
$lang["setup-warnheader"]="Einige Ihrer Einstellungen haben zu Warnungen geführt.  Details finden Sie unten. Das bedeutet nicht, dass Ihre Konfiguration fehlerhaft ist.";
$lang["setup-basicsettings"]="Grundeinstellungen";
$lang["setup-basicsettingsdetails"]="Dies sind die Grundeinstellungen für Ihre ResourceSpace Installation. Pflichtfelder sind mit einem <strong>*</strong> markiert.";
$lang["setup-dbaseconfig"]="Datenbank Konfiguration";
$lang["setup-mysqlerror"]="Fehler in Ihren MySQL Einstellungen:";
$lang["setup-mysqlerrorversion"]="MySQL Version 5 oder neuer benötigt.";
$lang["setup-mysqlerrorserver"]="Server nicht erreichbar.";
$lang["setup-mysqlerrorlogin"]="Login fehlgeschlagen. (Benutzername und Passwort prüfen)";
$lang["setup-mysqlerrordbase"]="Zugriff auf Datenbank fehlgeschlagen.";
$lang["setup-mysqlerrorperns"]="Bitte Benutzerrechte prüfen.  Konnte keine Tabelle erstellen.";
$lang["setup-mysqltestfailed"]="Test fehlgeschlagen (MySQL konnte nicht bestätigt werden)";
$lang["setup-mysqlserver"]="MySQL Server:";
$lang["setup-mysqlusername"]="MySQL Benutzername:";
$lang["setup-mysqlpassword"]="MySQL Passwort:";
$lang["setup-mysqldb"]="MySQL Datenbank:";
$lang["setup-mysqlbinpath"]="MySQL Tools Pfad:";
$lang["setup-generalsettings"]="Allgemeine Einstellungen";
$lang["setup-baseurl"]="Basis URL:";
$lang["setup-emailfrom"]="Absender für E-Mails:";
$lang["setup-emailnotify"]="E-Mail Benachrichtigung:";
$lang["setup-spiderpassword"]="Spider Passwort:";
$lang["setup-scramblekey"]="Scramble Schlüssel:";
$lang["setup-apiscramblekey"]="API Scramble Schlüssel:";
$lang["setup-paths"]="Pfade";
$lang["setup-pathsdetail"]="Geben Sie den Pfad zu den Tools ohne abschließenden Schrägstrich ein. Um ein Tool zu deaktivieren, lassen Sie die Angabe leer. Automatisch erkannte Pfade sind bereits eingetragen.";
$lang["setup-applicationname"]="Name der Installation:";
$lang["setup-basicsettingsfooter"]="HINWEIS: Auf dieser Seite befinden sich alle <strong>erforderlichen</strong> Einstellungen.  Wenn Sie nicht an den erweiterten Optionen interessiert sind, können Sie unten klicken, um die Installation sofort zu starten.";
$lang["setup-if_mysqlserver"]='IP Adresse oder <abbr title="Fully Qualified Domain Name">FQDN</abbr> Ihres MySQL Servers.  Wenn MySQL auf dem selben Server wie ResourceSpace installiert ist, geben Sie bitte &quot;localhost&quot; an.';
$lang["setup-if_mysqlusername"]="Der MySQL Benutzername. Dieser Benutzer muss in der unten angegebenen Datenbank das Recht zum Erstellen von Tabellen haben.";
$lang["setup-if_mysqlpassword"]="Das Passwort zum oben angegebenen MySQL Benutzer.";
$lang["setup-if_mysqldb"]="Name der MySQL Datenbank. (Die Datenbank muss bereits existieren)";
$lang["setup-if_mysqlbinpath"]="Pfad zu den MySQL Tools, z.B. mysqldump. HINWEIS: Diese Angabe wird nur benötigt, wenn Sie die Export Funktion nutzen wollen.";
$lang["setup-if_baseurl"]="Die Basis URL für diese Installation ohne abschließenden Schrägstrich.";
$lang["setup-if_emailfrom"]="Diese E-Mail Adresse wird von ResourceSpace als Absender für E-Mails benutzt.";
$lang["setup-if_emailnotify"]="An diese E-Mail Adresse werden Ressourcen-, Benutzer- und Suchanfragen gesendet.";
$lang["setup-if_spiderpassword"]="Das Spider Passwort ist ein Pflichtfeld.";
$lang["setup-if_scramblekey"]="Um verschlüsselte Pfade zu aktivieren, fügen Sie hier eine zufällige Zeichenkette (ähnlich einem Passwort) ein. Wenn diese Installation öffentlich zugänglich ist, wird dies dringend empfohlen. Um verschlüsselte Pfade nicht zu aktivieren, lassen Sie das Feld bitte leer. Eine zufällige Zeichenkette ist bereits vorgewählt worden, kann aber geändert werden, z.B. um die Einstellungen einer bestehenden Installation wiederherzustellen.";
$lang["setup-if_apiscramblekey"]="Wählen Sie für den API Scramble Schlüssel eine zufällige Zeichenkette (ähnlich einem Passwort), wenn Sie planen, die API zu nutzen.";
$lang["setup-if_applicationname"]="Name dieser Installation (z.B. 'Meine Firma Bilddatenbank').";
$lang["setup-err_mysqlbinpath"]="Konnte Pfad nicht bestätigen. Leer lassen zum deaktivieren.";
$lang["setup-err_baseurl"]="Basis URL muss ausgefüllt werden.";
$lang["setup-err_baseurlverify"]="Basis URL scheint falsch zu sein (konnte license.txt nicht laden).";
$lang["setup-err_spiderpassword"]="Passwort für spider.php. WICHTIG: Wählen Sie hier eine zufällige Zeichenkette für jede Installation. Ihre Ressourcen sind zugreifbar für jeden, der dieses Passwort kennt. Eine zufällige Zeichenkette ist bereits vorgewählt worden, kann aber geändert werden, z.B. um die Einstellungen einer bestehenden Installation wiederherzustellen.";
$lang["setup-err_scramblekey"]="Wenn diese Installation öffentlich zugänglich ist, wird die Nutzung von verschlüsselten Pfaden dringend empfohlen.";
$lang["setup-err_apiscramblekey"]="Wenn diese Installtion öffentlich zugänglich ist, wird das Setzen des API Scramble Schlüssels dringend empfohlen.";
$lang["setup-err_path"]="Konnte Pfad nicht bestätigen von";
$lang["setup-emailerr"]="Ungültige E-Mail Adresse.";
$lang["setup-rs_initial_configuration"]="ResourceSpace: Erstkonfiguration";
$lang["setup-include_not_writable"]="'/include' nicht beschreibbar. Nur während der Konfiguration nötig.";
$lang["setup-override_location_in_advanced"]="Ort überschreiben in 'Erweiterte Einstellungen'.";
$lang["setup-advancedsettings"]="Erweiterte Einstellungen";
$lang["setup-binpath"]="%bin Pfad"; #%bin will be replaced, e.g. "Imagemagick Path"
$lang["setup-begin_installation"]="Installation beginnen!";
$lang["setup-generaloptions"]="Allgemeine Optionen";
$lang["setup-allow_password_change"]="Änderung des Passworts erlauben?";
$lang["setup-enable_remote_apis"]="APIs aktivieren?";
$lang["setup-if_allowpasswordchange"]="Benutzern das Ändern ihres Passworts erlauben.";
$lang["setup-if_enableremoteapis"]="Zugriff auf API Plugins erlauben.";
$lang["setup-allow_account_requests"]="Benutzern erlauben, einen Account anzufragen?";
$lang["setup-display_research_request"]="Suchanfragen Funktion anzeigen?";
$lang["setup-if_displayresearchrequest"]="Benutzern erlauben, Ressourcen anzufragen mittels eines Formulars, das dann per E-Mail versandt wird.";
$lang["setup-themes_as_home"]="Themen Seite als Startseite verwenden?";
$lang["setup-remote_storage_locations"]="Remote Storage";
$lang["setup-use_remote_storage"]="Remote Storage nutzen?";
$lang["setup-if_useremotestorage"]="Auswählen, um Remote Storage (anderer Server für filestore) für RS zu nutzen.";
$lang["setup-storage_directory"]="Storage Verzeichnis";
$lang["setup-if_storagedirectory"]="In welchem Verzeichnis sollen die Dateien abgelegt werden. Kann absolut sein (/var/www/blah/blah) oder relativ zur RS Installation. HINWEIS: Kein / am Ende.";
$lang["setup-storage_url"]="Storage URL";
$lang["setup-if_storageurl"]="Wie kann per HTTP auf das Storage Verzeichnis zugegriffen werden? Kann absolut sein (http://files.example.com) oder relativ zur RS Installation. HINWEIS: Kein / am Ende.";
$lang["setup-ftp_settings"]="FTP Einstellungen";
$lang["setup-if_ftpserver"]="Nur notwendig, wenn Sie planen, die FTP Upload Funktion zu nutzen.";
$lang["design-options"]="Design Optionen";
$lang["use-slim-theme"]="SlimHeader Design verwenden";
$lang["setup-if_slimtheme"]="SlimHeader Design verwenden für einen schmaleren Header mit verlinktem Logo.";
$lang["setup-login_to"]="Login zu";
$lang["setup-configuration_file_output"]="Ausgabe der Konfigurationsdatei";

# Collection log - actions
$lang["collectionlog"]="Kollektionen Log";
$lang["collectionlogheader"]="Kollektionen Log - %collection"; # %collection will be replaced, e.g. Collection Log - My Collection
$lang["collectionlog-r"]="Ressource entfernt";
$lang["collectionlog-R"]="Alle Ressourcen entfernt";
$lang["collectionlog-D"]="Alle Ressourcen gelöscht";
$lang["collectionlog-d"]="Ressource gelöscht"; // this shows external deletion of any resources related to the collection.
$lang["collectionlog-a"]="Ressource hinzugefügt";
$lang["collectionlog-c"]="Ressource hinzugefügt (kopiert)";
$lang["collectionlog-m"]="Ressource kommentiert";
$lang["collectionlog-*"]="Ressource bewertet";
$lang["collectionlog-S"]="Kollektion weitergegeben an "; //  + notes field
$lang["collectionlog-E"]="Kollektion per E-Mail weitergegeben an ";//  + notes field
$lang["collectionlog-s"]="Ressource weitergegeben an ";//  + notes field
$lang["collectionlog-T"]="Kollektion nicht mehr weitergegeben an ";//  + notes field
$lang["collectionlog-t"]="Ressource  nicht mehr weitergegeben an ";//  + notes field
$lang["collectionlog-X"]="Kollektion gelöscht";
$lang["collectionlog-b"]="Stapelverarbeitung";
$lang["collectionlog-Z"]="Kollektion heruntergeladen";

$lang["viewuncollectedresources"]="Ressourcen anzeigen, die nicht in einer Kollektion enthalten sind";

# Collection requesting
$lang["requestcollection"]="Kollektion anfordern";

# Metadata report
$lang["metadata-report"]="Metadaten Bericht";

# Video Playlist
$lang["videoplaylist"]="Video Wiedergabeliste";

$lang["collection"]="Kollektion";
$lang["idecline"]="Ablehnen"; # For terms and conditions

$lang["mycollection_notpublic"]="'Meine Kollektion' kann nicht in eine öffentliche Kollektion oder ein Thema umgewandelt werden. Bitte erstellen Sie für diesen Zweck eine neue Kollektion.";

$lang["resourcemetadata"]="Ressourcen-Felder";
$lang["columnheader-expires"]=$lang["expires"]="Läuft ab";
$lang["expires-date"]="Läuft ab am: %date%"; # %date will be replaced, e.g. Expires: Never
$lang["never"]="Niemals";

 $lang["approved"]="Freigegeben";
 $lang["notapproved"]="Nicht freigegeben";

 $lang["userrequestnotification3"]="Wenn diese Anfrage gültig ist, klicken Sie den untenstehenden Link, um die Details des Benutzers anzusehen und den Benutzer freizugeben.";

 $lang["ticktoapproveuser"]="Sie müssen dieses Kästchen aktivieren, um den Benutzer zu aktivieren.";

 $lang["managerequestsorders"]="Anfragen / Bestellungen verwalten";
 $lang["editrequestorder"]="Anfrage / Bestellung bearbeiten";
 $lang["requestorderid"]="Anfrage / Bestellung Nr.";
 $lang["viewrequesturl"]="Um diese Anfrage anzusehen, klicken Sie bitte diesen Link:";
 $lang["requestreason"]="Grund für die Anfrage";

 $lang["resourcerequeststatus0"]="Warten";
 $lang["resourcerequeststatus1"]="Akzeptiert";
 $lang["resourcerequeststatus2"]="Abgelehnt";

 $lang["ppi"]="PPI"; # (Pixels Per Inch - used on the resource download options list).

 $lang["useasthemethumbnail"]="Diese Ressource als Thumbnail für Thema nutzen?";
 $lang["sessionexpired"]="Sie wurden automatisch ausgeloggt, weil Sie länger als 30 Minuten inaktiv waren. Bitte geben Sie Ihre Login Daten erneut ein, um fortzufahren.";

 $lang["resourcenotinresults"]="Die aktuelle Ressource ist nicht mehr in Ihren Suchergebnissen. Daher ist eine Navigation mit zurück/nächste nicht möglich.";
 $lang["publishstatus"]="Speichern mit Veröffentlichungsstatus:";
 $lang["addnewcontent"]="Neuer Inhalt (Seite,Name)";
 $lang["hitcount"]="Zugriffszähler";
 $lang["downloads"]="Downloads";

 $lang["addremove"]="Hinzufügen/Entfernen";

##  Translations for standard log entries
 $lang["all_users"]="alle Benutzer";
 $lang["new_resource"]="Neue Ressource";

 $lang["invalidextension_mustbe"]="Ungültige Erweiterung, muss eine der folgenden sein";
 $lang["invalidextension_mustbe-extensions"]="Ungültige Erweiterung, muss eine der folgenden sein %EXTENSIONS."; # Use %EXTENSIONS, %extensions or %Extensions as a placeholder. The placeholder will be replaced with the filename extensions, using the same case. E.g. "Invalid extension, must be %EXTENSIONS" -> "Invalid extension, must be JPG"
 $lang["allowedextensions"]="Erlaubte Erweiterungen";
 $lang["allowedextensions-extensions"]="Erlaubte Erweiterungen: %EXTENSIONS"; # Use %EXTENSIONS, %extensions or %Extensions as a placeholder. The placeholder will be replaced with the filename extensions, using the same case. E.g. "Allowed Extensions: %EXTENSIONS" -> "Allowed Extensions: JPG, PNG"

 $lang["alternativebatchupload"]="Alternative Dateien hochladen";

 $lang["confirmdeletefieldoption"]="Wollen Sie wirklich diese Option LÖSCHEN?";

 $lang["cannotshareemptycollection"]="Diese Kollektion ist leer und kann daher nicht weitergegeben werden.";
$lang["cannotshareemptythemecategory"]="Diese Themenkategorie enthält keine Themen und kann daher nicht weitergegeben werden.";

 $lang["requestall"]="Alle anfordern";
 $lang["requesttype-email_only"]=$lang["resourcerequesttype0"]="Nur E-Mail";
 $lang["requesttype-managed"]=$lang["resourcerequesttype1"]="Verwaltete Anfrage";
 $lang["requesttype-payment_-_immediate"]=$lang["resourcerequesttype2"]="Zahlung – sofort";
 $lang["requesttype-payment_-_invoice"]=$lang["resourcerequesttype3"]="Zahlung – auf Rechnung";

$lang["requestsent"]="Ihre Ressourcenanfrage wurde zur Freigabe eingereicht ";
$lang["requestsenttext"]="Ihre Ressourcenanfrage wurde zur Freigabe eingereicht und wird in Kürze bearbeitet.";
$lang["requestupdated"]="Ihre Ressourcenanfrage wurde aktualisiert ";
$lang["requestassignedtouser"]="Ihre Ressourcenanfrage wurde % zur Überprüfung zugeordnet.";
 $lang["requestapprovedmail"]="Ihre Anfrage wurde akzeptiert. Bitte Klicken Sie den untenstehenden Link, um die angefragten Ressourcen anzuzeigen und herunterzuladen.";
 $lang["requestdeclinedmail"]="Es tut uns leid, Ihre Anfrage für die Ressourcen in der Kollektion wurde abgelehnt.";

 $lang["resourceexpirymail"]="Die folgenden Ressourcen sind abgelaufen:";
 $lang["resourceexpiry"]="Ablauf";

 $lang["requestapprovedexpires"]="Ihr Zugriff auf diese Ressourcen wir ablaufen am";

 $lang["pleasewaitsmall"]="(bitte warten)";
 $lang["removethisfilter"]="(diesen Filter entfernen)";

 $lang["no_exif"]="EXIF/IPTC/XMP Metadaten für diesen Upload nicht importieren";
 $lang["difference"]="Unterschied";
 $lang["viewdeletedresources"]="Gelöschte Ressourcen anzeigen";
 $lang["finaldeletion"]="Diese Ressource ist bereits im Status 'gelöscht'. Diese Aktion wird die Ressource vollständig vom System entfernen.";
 $lang["diskerror"]="Kein Speicherplatz verfügbar";

 $lang["nocookies"]="Ein Cookie konnte nicht richtig gesetzt werden. Bitte stellen Sie sicher, dass Cookies in Ihrem Browser aktiviert sind.";

 $lang["selectedresourceslightroom"]="Ausgewählte Ressourcen (Lightroom kompatible Liste):";

# Plugins Manager
 $lang['plugins-noneinstalled'] = "Derzeit keine Plugins aktiviert.";
 $lang['plugins-noneavailable'] = "Derzeit keine Plugins verfügbar.";
 $lang['plugins-availableheader'] = 'Verfügbare Plugins';
 $lang['plugins-installedheader'] = 'Derzeit aktivierte Plugins';
 $lang['plugins-author'] = 'Autor';
 $lang['plugins-version'] = 'Version';
 $lang['plugins-instversion'] = 'Installierte Version';
 $lang['plugins-uploadheader'] = 'Plugin hochladen';
 $lang['plugins-uploadtext'] = '.rsp Datei zum Installieren auswählen.';
 $lang['plugins-deactivate'] = 'Deaktivieren';
 $lang['plugins-moreinfo'] = 'Weitere Infos';
 $lang['plugins-activate'] = 'Aktivieren';
 $lang['plugins-purge'] = 'Konfiguration bereinigen';
 $lang['plugins-rejmultpath'] = 'Archiv enthält mehrere Pfade. (Sicherheitsrisiko)';
 $lang['plugins-rejrootpath'] = 'Archiv enthält absolute Pfade. (Sicherheitsrisiko)';
 $lang['plugins-rejparentpath'] = 'Archiv enthält Pfade mit übergeordneten Verzeichnissen (../). (Sicherheitsrisiko)';
 $lang['plugins-rejmetadata'] = 'Beschreibungsdatei des Archivs nicht gefunden.';
 $lang['plugins-rejarchprob'] = 'Es gab ein Problem beim entpacken des Archivs:';
 $lang['plugins-rejfileprob'] = 'Plugin muss im .rsp Format hochgeladen werden.';
 $lang['plugins-rejremedy'] = 'Wenn Sie dem Plugin vertrauen, können Sie es manuell installieren, in dem Sie es im plugins Verzeichnis entpacken.';
 $lang['plugins-uploadsuccess'] = 'Plugin erfolgreich hochgeladen.';
 $lang['plugins-headertext'] = 'Plugins erweitern die Funktionalität von ResourceSpace.';
 $lang['plugins-legacyinst'] = 'Aktiviert durch die Datei config.php';
 $lang['plugins-uploadbutton'] = 'Plugin hochladen';
$lang['plugins-download'] = 'Konfiguration&nbsp;herunterladen';
$lang['plugins-upload-title'] = 'Konfiguration aus Datei laden';
$lang['plugins-upload'] = 'Konfiguration hochladen';
$lang['plugins-getrsc'] = 'Datei:';
$lang['plugins-saveconfig'] = 'Konfiguration speichern';
$lang['plugins-saveandexit'] = 'Speichern und zurück';
$lang['plugins-didnotwork'] = 'Das hat leider nicht funktioniert. Bitte wählen Sie eine gültige .rsc Datei für dieses Plugin aus und klicken dann den \'Konfiguration hochladen\' Button.';
$lang['plugins-goodrsc'] = 'Konfiguration erfolgreich hochgeladen. Klicken Sie den  \'Konfiguration speichern\' Button zum Speichern.';
$lang['plugins-badrsc'] = 'Ungültige .rsc Datei.';
$lang['plugins-wrongplugin'] = 'Diese .rsc Datei ist für das %plugin Plugin. Bitte wählen Sie eine Datei für dieses Plugin.'; // %plugin is replaced by the name of the plugin being configured.
$lang['plugins-configvar'] = 'Setzt Konfigurationsvariable: $%cvn'; //%cvn is replaced by the name of the config variable being set

#Location Data
 $lang['location-title'] = 'Geodaten';
 $lang['location-add'] = 'Geodaten hinzufügen';
 $lang['location-edit'] = 'Geodaten bearbeiten';
 $lang['location-details'] = 'Karte doppelklicken, um einen Pin zu platzieren. Anschließend können Sie dann Pin an die gewünschte Stelle ziehen.';
 $lang['location-noneselected']="Kein Ort ausgewählt";
 $lang['location'] = 'Ort';
$lang['mapzoom'] = 'Karten Zoom';
$lang['openstreetmap'] = "OpenStreetMap";
$lang['google_terrain'] = "Google Terrain";
$lang['google_default_map'] = "Google Standard Karte";
$lang['google_satellite'] = "Google Satellit";
$lang["markers"] = "Markierungen";

 $lang["publiccollections"]="Öffentliche Kollektionen";
 $lang["viewmygroupsonly"]="Nur meine Gruppen anzeigen";
 $lang["usemetadatatemplate"]="Metadatenvorlage nutzen";
 $lang["undometadatatemplate"]="(Vorlagenauswahl rückgängig machen)";

 $lang["accountemailalreadyexists"]="Unter dieser E-Mail Adresse gibt es bereits einen Benutzer";

 $lang["backtothemes"]="Zurück zu Themen";
 $lang["downloadreport"]="Download Bericht";

#Bug Report Page
 $lang['reportbug']="Bug Bericht für ResourceSpace Team vorbereiten";
 $lang['reportbug-detail']="Die folgenden Informationen werden im Bug Bericht enthalten sein. Sie können alle Werte ändern, bevor Sie den Bericht abschicken.";
 $lang['reportbug-login']="HINWEIS: Klicken Sie hier, um sich einzuloggen, BEVOR Sie auf vorbereiten klicken.";
 $lang['reportbug-preparebutton']="Fehlerbericht vorbereiten";

 $lang["enterantispamcode"]="<strong>Anti-Spam</strong><br /> Bitte geben Sie den folgenden Code ein:";

 $lang["groupaccess"]="Gruppenzugriff";
 $lang["plugin-groupsallaccess"]="Dieses Plugin ist für alle Gruppen aktiv";
 $lang["plugin-groupsspecific"]="Dieses Plugin ist nur für ausgewählte Gruppen aktiv";


 $lang["associatedcollections"]="Verbundene Kollektionen";
 $lang["emailfromuser"]="E-Mail senden von ";
 $lang["emailfromsystem"]="Haken entfernen, um die E-Mail von der System-Adresse zu senden: ";



 $lang["previewpage"]="Vorschauseite";
 $lang["nodownloads"]="Keine Downloads";
 $lang["uncollectedresources"]="Ressourcen, die nicht in Kollektionen enthalten sind";
 $lang["nowritewillbeattempted"]="Es werden keine Daten geschrieben";
 $lang["notallfileformatsarewritable"]="Nicht alle Dateiformate können mit dem exiftool geschrieben werden";
 $lang["filetypenotsupported"]="Dateiformat %EXTENSION nicht unterstützt"; # Use %EXTENSION, %extension or %Extension as a placeholder. The placeholder will be replaced with the filename extension, using the same case. E.g. "%EXTENSION filetype not supported" -> "JPG filetype not supported"
 $lang["exiftoolprocessingdisabledforfiletype"]="Exiftool Verarbeitung für dieses Dateiformat (%EXTENSION) deaktiviert"; # Use %EXTENSION, %extension or %Extension as a placeholder. The placeholder will be replaced with the filename extension, using the same case. E.g. "Exiftool processing disabled for file type %EXTENSION" -> "Exiftool processing disabled for file type JPG"
 $lang["nometadatareport"]="Kein Metadaten Bericht";
 $lang["metadatawritewillbeattempted"]="Schreiben der Metadaten wird versucht.";
 $lang["metadatatobewritten"]="Metadaten, die geschrieben werden";
 $lang["embeddedvalue"]="Eingebetteter Wert";
 $lang["exiftooltag"]="Exiftool Tag";
 $lang["error"]="Fehler";
 $lang["exiftoolnotfound"]="Exiftool konnte nicht gefunden werden.";
 $lang["existing_tags"]="Existierende Exiftool Tags";
 $lang["new_tags"]="Neue Exiftool Tags (werden beim Herunterladen hinzugefügt)";
 $lang["date_of_download"]="[Datum des Downloads]";
 $lang["field_ref_and_name"]="%ref% - %name%"; # %ref% and %name% will be replaced, e.g. 3 – Country

 $lang["indicateusage"]="Bitte beschreiben Sie die geplante Nutzung dieser Ressource.";
 $lang["usage"]="Nutzung";
 $lang["usagecomments"]="Nutzung";
 $lang["indicateusagemedium"]="Nutzung auf Medium";
 $lang["usageincorrect"]="Sie müssen die geplante Nutzung und das Medium angeben";

 $lang["savesearchassmartcollection"]="Als Smarte Kollektion speichern";
 $lang["smartcollection"]="Smarte Kollektion";
 $lang["dosavedsearch"]="Gespeicherte Suche ausführen";


 $lang["uploadertryjava"]="Wenn Sie Probleme mit dem Upload haben, versuchen Sie bitte den <strong>Java Uploader</strong>.";
 $lang["uploadertryplupload"]="<strong>NEW</strong> - Versuchen Sie den neuen Uploader.";
 $lang["getjava"]="Um sicherzustellen, dass Sie die neueste Java Version installiert haben, besuchen Sie bitte die Java Website.";

 $lang["all"]="Alle";
 $lang["allresourcessearchbar"]="Alle Ressourcen";
 $lang["allcollectionssearchbar"]="Alle Kollektionen";
 $lang["backtoresults"]="Zurück zu den Suchergebnissen";
 $lang["continuetoresults"]="Weiter zu den Suchergebnissen";

 $lang["preview_all"]="Alle in Vorschau zeigen";

 $lang["usagehistory"]="Nutzungsprotokoll";
 $lang["usagebreakdown"]="Nutzungsanalyse";
 $lang["usagetotal"]="Downloads gesamt";
 $lang["usagetotalno"]="Gesamtzahl der Downloads";
 $lang["ok"]="OK";

 $lang["random"]="Zufällig";
 $lang["userratingstatsforresource"]="Bewertungsstatistik für diese Ressource";
 $lang["average"]="Durchschnitt";
 $lang["popupblocked"]="Das Popup-Fenster wurde von Ihrem Browser geblockt.";
 $lang["closethiswindow"]="Fenster schließen";

 $lang["requestaddedtocollection"]="Diese Ressource wurde zu Ihrer aktuellen Kollektion hinzugefügt. Sie können die Ressourcen in Ihrer Kollektion mit dem Link \'Alle anfordern\' anfordern.";

# E-commerce text
 $lang["buynow"]="Jetzt kaufen";
 $lang["yourbasket"]="Ihr Warenkorb";
 $lang["addtobasket"]="Zum Warenkorb hinzufügen";
 $lang["yourbasketisempty"]="Ihr Warenkorb ist leer.";
 $lang["yourbasketcontains-1"]="Ihre Warenkorb enthält 1 Objekt.";
 $lang["yourbasketcontains-2"]="Ihre Warenkorb enthält %qty Objekte."; # %qty will be replaced, e.g. Your basket contains 3 items.
 $lang["buy"]="Kaufen";
 $lang["buyitemaddedtocollection"]="Diese Ressource wurde zu Ihrem Warenkorb hinzugefügt. Sie können alle Ressourcen im Warenkorb mit dem Link \'Jetzt kaufen\' erwerben.";
 $lang["buynowintro"]="Bitte wählen Sie die Größen aus, die Sie benötigen.";
 $lang["nodownloadsavailable"]="Für diese Ressource gibt es leider keine Downloads.";
 $lang["proceedtocheckout"]="Weiter zur Kasse";
 $lang["totalprice"]="Gesamtpreis";
 $lang["price"]="Preis";
 $lang["waitingforpaymentauthorisation"]="Wir haben leider noch keine Zahlungsbestätigung erhalten. Bitte warten Sie einen Augenblick und klicken Sie dann 'Aktualisieren'.";
 $lang["reload"]="Aktualisieren";
 $lang["downloadpurchaseitems"]="Gekaufte Ressourcen herunterladen";
 $lang["downloadpurchaseitemsnow"]="Bitte benutzen Sie die untenstehenden Links, um Ihre gekauften Ressourcen jetzt herunterzuladen.<br><br>Verlassen Sie diese Seite nicht, bis Sie alle Ressourcen heruntergeladen haben.";
 $lang["alternatetype"]="Alternative Art";
 $lang["viewpurchases"]="Meine Einkäufe";
 $lang["viewpurchasesintro"]="Bitte nutzen Sie die untenstehenden Links, um auf Ihre gekaufte Ressourcen zuzugreifen.";
 $lang["orderdate"]="Kaufdatum";
 $lang["removefrombasket"]="Aus dem Warenkorb entfernen";
 $lang["total-orders-0"] = "<strong>Gesamt: 0</strong> Bestellungen";
 $lang["total-orders-1"] = "<strong>Gesamt: 1</strong> Bestellung";
 $lang["total-orders-2"] = "<strong>Gesamt: %number</strong> Bestellungen"; # %number will be replaced, e.g. Total: 5 Orders
 $lang["purchase_complete_email_admin"] = "Kaufbenachrichtigung";
 $lang["purchase_complete_email_admin_body"] = "Die folgende Bestellung wurde erfolgreich ausgeführt.";
 $lang["purchase_complete_email_user"] = "Kaufbestätigung";
 $lang["purchase_complete_email_user_body"] = "Vielen Dank für Ihren Einkauf. Bitte nutzen Sie den untenstehenden Link, um auf Ihre gekauften Objekte zuzugreifen.";


 $lang["subcategories"]="Unterkategorien";
 $lang["subcategory"]="Unterkategorie";
 $lang["back"]="Zurück";

 $lang["pleasewait"]="Bitte warten...";

 $lang["autorotate"]="Bilder automatisch drehen?";

# Reports
# Report names (for the default reports)
$lang["report-keywords_used_in_resource_edits"]="Benutzte Stichworte beim Bearbeiten von Ressourcen";
$lang["report-keywords_used_in_searches"]="Benutzte Stichworte in der Suche";
$lang["report-resource_download_summary"]="Zusammenfassung der Ressourcendownloads";
$lang["report-resource_views"]="Ressourcen Aufrufe";
$lang["report-resources_sent_via_e-mail"]="Per E-Mail weitergegebene Ressourcen";
$lang["report-resources_added_to_collection"]="Zu Kollektionen hinzugefügte Ressourcen";
$lang["report-resources_created"]="Erstellte Ressourcen";
$lang["report-resources_with_zero_downloads"]="Ressourcen ohne Downloads";
$lang["report-resources_with_zero_views"]="Ressourcen ohne Aufrufe";
$lang["report-resource_downloads_by_group"]="Ressourcendownloads nach Gruppe";
$lang["report-resource_download_detail"]="Ressourcendownloads im Detail";
$lang["report-user_details_including_group_allocation"]="Benutzer Details inkl. Gruppenzuordnung";
$lang["report-expired_resources"]="Abgelaufene Ressourcen";

#Column headers (for the default reports)
$lang["columnheader-keyword"]="Stichwort";
$lang["columnheader-entered_count"]="Anzahl";
$lang["columnheader-searches"]="Suchanfragen";
$lang["columnheader-date_and_time"]="Datum / Uhrzeit";
$lang["columnheader-downloaded_by_user"]="Heruntergeladen von";
$lang["columnheader-user_group"]="Benutzergruppe";
$lang["columnheader-resource_title"]="Titel der Ressource";
$lang["columnheader-title"]="Titel";
$lang["columnheader-downloads"]="Downloads";
$lang["columnheader-group_name"]="Gruppenname";
$lang["columnheader-resource_downloads"]="Ressourcendownloads";
$lang["columnheader-views"]="Aufrufe";
$lang["columnheader-added"]="Hinzugefügt";
$lang["columnheader-creation_date"]="Erstellungsdatum";
$lang["columnheader-sent"]="Versendet";
$lang["columnheader-last_seen"]="Zuletzt gesehen";

 $lang["period"]="Zeitraum";
 $lang["lastndays"]="Letzte ? Tage"; # ? is replaced by the system with the number of days, for example "Last 100 days".
 $lang["specificdays"]="Spezifische Anzahl von Tagen";
 $lang["specificdaterange"]="Spezifischer Zeitraum";
 $lang["to"]="bis";

 $lang["emailperiodically"]="Neue regelmäßige E-Mail erstellen";
 $lang["emaileveryndays"]="Diesen Bericht regelmäßig alle ? per E-Mail versenden";
 $lang["newemailreportcreated"]="Eine neue regelmäßige E-Mail wurde erstellt. Sie können die E-Mail stoppen, indem Sie den Link am Ende der E-Mail anklicken.";
 $lang["unsubscribereport"]="Um sich von diesem Bericht abzumelden, klicken Sie bitte diesen Link an:";
 $lang["unsubscribed"]="Abgemeldet";
 $lang["youhaveunsubscribedreport"]="Sie wurden von dieser regelmäßigen E-Mail abgemeldet.";
 $lang["sendingreportto"]="Sende Bericht an";
 $lang["reportempty"]="Keine passenden Daten für den ausgewählen Bericht und Zeitraum gefunden.";

 $lang["purchaseonaccount"]="Zum Konto hinzufügen";
 $lang["areyousurepayaccount"]="Sind Sie sicher, dass Sie diesen Einkauf zu Ihrem Konto hinzufügen wollen?";
 $lang["accountholderpayment"]="Zahlung Kontoinhaber";
 $lang["subtotal"]="Zwischensumme";
 $lang["discountsapplied"]="Angewendete Rabatte";
 $lang["log-p"]="Gekaufte Ressource";
 $lang["viauser"]="durch Benutzer";
 $lang["close"]="Schließen";

# Installation Check
 $lang["repeatinstallationcheck"]="Installation erneut überprüfen";
 $lang["shouldbeversion"]="sollte Version ? oder höher sein"; # E.g. "should be 4.4 or greater"
 $lang["phpinivalue"]="PHP.INI Wert für '?'"; # E.g. "PHP.INI value for 'memory_limit'"
 $lang["writeaccesstofilestore"]="Schreibzugriff auf $storagedir";
 $lang["nowriteaccesstofilestore"]="$storagedir nicht beschreibbar";
 $lang["writeaccesstohomeanim"]="Schreibzugriff auf $homeanim_folder";
 $lang["nowriteaccesstohomeanim"]="$homeanim_folder nicht beschreibbar. Ändern Sie die Berechtigungen, um die Beschnitt-Funktion des transform Plugins für die Startseiten-Animation zu ermöglichen.";
 $lang["blockedbrowsingoffilestore"]="Browsen des 'filestore' Verzeichnisses nicht erlaubt";
 $lang["noblockedbrowsingoffilestore"]="filestore scheint durchsuchbar zu sein; entfernen Sie 'Indexes' aus den Apache 'Options'.";
 $lang["execution_failed"]="Unerwartete Ausgabe des %command Befehls. Ausgabe war '%output'.";  # %command and %output will be replaced, e.g. Execution failed; unexpected output when executing convert command. Output was '[stdout]'.
 $lang["exif_extension"]="EXIF Erweiterung";
 $lang["archiver_utility"]="Kompressions Befehl";
 $lang["zipcommand_deprecated"]="Bitte nutzen Sie \$collection_download und \$collection_download_settings an Stelle von \$zipcommand.";
 $lang["zipcommand_overridden"]="Aber bitte beachten Sie, dass \$zipcommand definiert ist und übergangen wird.";
 $lang["lastscheduledtaskexection"]="Letzte geplante Ausführung der Aufgaben (Tage)";
 $lang["executecronphp"]="Relevanzberechnung wird nicht effektiv sein und regelmäßige E-Mail Berichte nicht versandt werden. Stellen Sie sicher, dass <a href='../batch/cron.php'>batch/cron.php</a> mindestens einmal täglich per cron job oder ähnlich gestartet wird.";
 $lang["shouldbeormore"]="sollte ? oder höher sein"; # E.g. should be 200M or greater
 $lang["config_file"]="(config: %file)"; # %file will be replaced, e.g. (config: /etc/php5/apache2/php.ini)
 $lang['large_file_support_64_bit'] = 'Untertützung für große Dateien (64bit Plattform)';
 $lang['large_file_warning_32_bit'] = 'WARNUNG: 32bit PHP wird genutzt. Dateien größer als 2GB werden nicht unterstützt.';

 $lang["starsminsearch"]="Sterne (Minimum)";
 $lang["anynumberofstars"]="Beliebige Anzahl Sterne";
 $lang["star"]="Stern";
 $lang["stars"]="Sterne";

 $lang["noupload"]="Kein Upload";

# System Setup
# System Setup Tree Nodes (for the default setup tree)
 $lang["treenode-root"]="Root";
 $lang["treenode-group_management"]="Gruppenverwaltung";
 $lang["treenode-new_group"]="Neue Gruppe";
 $lang["treenode-new_subgroup"]="Neue Untergruppe";
 $lang["treenode-resource_types_and_fields"]="Ressourcen Typen / Felder";
 $lang["treenode-new_resource_type"]="Neuen Ressourcen Typ";
 $lang["treenode-new_field"]="Neues Feld";
 $lang["treenode-reports"]="Berichte";
 $lang["treenode-new_report"]="Neuer Bericht";
 $lang["treenode-downloads_and_preview_sizes"]="Download- / Vorschaugrößen";
 $lang["treenode-new_download_and_preview_size"]="Neue Download- / Vorschaugröße";
 $lang["treenode-database_statistics"]="Datenbank Statistiken";
 $lang["treenode-permissions_search"]="Suche nach Berechtigungen";
 $lang["treenode-no_name"]="(ohne Namen)";

 $lang["treeobjecttype-preview_size"]="Vorschaugröße";

 $lang["permissions"]="Berechtigungen";

# System Setup File Editor
 $lang["configdefault-title"]="(Optionen von hier kopieren und einfügen)";
 $lang["config-title"]="(BITTE BEACHTEN: Sollte diese Datei nicht mehr ausführbar sein (z.B. durch Syntaxfehler), muss der Fehler direkt auf dem Server behoben werden!)";

# System Setup Properties Pane
 $lang["file_too_large"]="Datei zu groß";
 $lang["field_updated"]="Feld aktualisiert";
 $lang["zoom"]="Zoom";
 $lang["deletion_instruction"]="Leer lassen und speichern, um die Datei zu löschen";
 $lang["upload_file"]="Datei hochladen";
 $lang["item_deleted"]="Eintrag gelöscht";
 $lang["viewing_version_created_by"]="Ansichtsversion erstellt durch";
 $lang["on_date"]="am";
 $lang["launchpermissionsmanager"]="Berechtigungs-Manager öffnen";
 $lang["confirm-deletion"]="Sind Sie sicher?";
$lang["accept_png_gif_only"]="Nur .png oder .gif Erweiterung akzeptiert";
$lang["ensure_file_extension_match"]="Sicherstellen, dass Datei und Erweiterung übereinstimmen";

# Permissions Manager
$lang["permissionsmanager"]="Berechtigungsmanager";
$lang["backtogroupmanagement"]="Zurück zur Gruppenverwaltung";
$lang["searching_and_access"]="Suchen / Zugriff";
$lang["metadatafields"]="Metadatenfelder";
$lang["resource_creation_and_management"]="Ressourcen verwalten";
$lang["themes_and_collections"]="Themen / Kollektionen";
$lang["administration"]="Administration";
$lang["other"]="Sonstiges";
$lang["custompermissions"]="Eigene Berechtigungen";
$lang["searchcapability"]="Suchmöglichkeiten";
$lang["access_to_restricted_and_confidential_resources"]="Kann eingeschränkte Ressourcen herunterladen und vertrauliche Ressourcen ansehen<br>(normalerweise nur Administratoren)";
$lang["restrict_access_to_all_available_resources"]="Zugriff auf alle verfügbaren Ressourcen einschränken";
$lang["can_make_resource_requests"]="Kann Ressourcen anfragen";
$lang["show_watermarked_previews_and_thumbnails"]="Vorschau/Thumbnails mit Wasserzeichen anzeigen";
$lang["can_see_all_fields"]="Kann alle Felder sehen";
$lang["can_see_field"]="Kann Feld sehen";
$lang["can_edit_all_fields"]="Kann alle Felder bearbeiten<br>(für bearbeitbare Ressourcen)";
$lang["can_edit_field"]="Kann Feld bearbeiten";
$lang["can_see_resource_type"]="Kann Ressourcen-Typ sehen";
$lang["restricted_access_only_to_resource_type"]="Eingeschränkter Zugriff nur auf Ressourcen-Typ";
$lang["restricted_upload_for_resource_of_type"]="Eingeschränkter Upload für Ressourcen-Typ";
$lang["edit_access_to_workflow_state"]="Zugriff auf Workflow Status";
$lang["can_create_resources_and_upload_files-admins"]="Kann Ressourcen erstellen / Dateien hochladen<br>(Administratoren; Ressourcen erhalten den Status 'Aktiv')";
$lang["can_create_resources_and_upload_files-general_users"]="Kann Ressourcen erstellen / Dateien hochladen<br>(Normale Benutzer; Ressourcen erhalten den Status 'Benutzer-Beiträge: Freischaltung noch nicht erledigt')";
$lang["can_delete_resources"]="Kann Ressourcen löschen<br>(die der Benutzer bearbeiten kann)";
$lang["can_manage_archive_resources"]="Kann archivierte Ressourcen verwalten";
$lang["can_manage_alternative_files"]="Kann alternative Dateien verwalten";
$lang["can_tag_resources_using_speed_tagging"]="Kann Ressourcen via 'Speed Tagging' taggen<br>(falls konfiguriert)";
$lang["enable_bottom_collection_bar"]="Kollektionen erlauben ('Leuchtkasten')";
$lang["can_publish_collections_as_themes"]="Kann Kollektionen als Themen veröffentlichen";
$lang["can_see_all_theme_categories"]="Kann alle Themenkategorien sehen";
$lang["can_see_theme_category"]="Kann Themenkategorie sehen";
$lang["can_see_theme_sub_category"]="Kann Themenunterkategorie sehen";
$lang["display_only_resources_within_accessible_themes"]="Bei Suchabfragen nur Ressourcen anzeigen, die sich in Themen befinden, auf die der Benutzer Zugriff hat";
$lang["can_access_team_centre"]="Kann auf die Administration zugreifen";
$lang["can_manage_research_requests"]="Kann Suchanfragen verwalten";
$lang["can_manage_resource_requests"]="Kann Ressourcenanfragen verwalten";
$lang["can_manage_content"]="Kann Inhalte verwalten (Intro/Hilfetexte)";
$lang["can_bulk-mail_users"]="Kann Massenmail senden";
$lang["can_manage_users"]="Kann Benutzer verwalten";
$lang["can_manage_keywords"]="Kann Stichworte verwalten";
$lang["can_access_system_setup"]="Kann auf die Systemeinstellungen zugreifen";
$lang["can_change_own_password"]="Kann das eigene Passwort ändern";
$lang["can_manage_users_in_children_groups"]="Kann nur Benutzer in Untergruppen zur eigenen Benutzergruppe verwalten";
$lang["can_email_resources_to_own_and_children_and_parent_groups"]="Kann Ressourcen nur an Benutzer aus der eigenen Gruppe oder Untergruppen weitergeben";

$lang["nodownloadcollection"]="Sie haben keinen Zugriff, um Ressourcen aus dieser Kollektion herunterzuladen.";

$lang["progress"]="Fortschritt";
$lang["ticktodeletethisresearchrequest"]="Auswählen, um diese Anfrage zu löschen";

$lang["done"]="Fertig.";

$lang["latlong"]="Breite / Länge";
$lang["geographicsearch"]="Geographische Suche";
$lang["geographicsearchresults"]="Ergebnisse der geographischen Suche";

$lang["geographicsearch_help"]="Ziehen, um einen Suchbereich zu erstellen.";

$lang["purge"]="Bereinigen";
$lang["purgeuserstitle"]="Benutzer bereinigen";
$lang["purgeusers"]="Benutzer bereinigen";
$lang["purgeuserscommand"]="Benutzer-Accounts löschen, die in den letzten % Monaten nicht aktiv waren, aber vor diesem Zeitraum erstellt wurden.";
$lang["purgeusersconfirm"]="% Benutzer-Accounts löschen. Sind Sie sicher?";
$lang["pleaseenteravalidnumber"]="Bitte geben Sie eine gültige Zahl ein";
$lang["purgeusersnousers"]="Keine Benutzer-Accounts zu bereinigen.";

$lang["editallresourcetypewarning"]="Warnung: Durch das Ändern des Ressourcen-Typs werden sämtliche spezifischen Metadaten für den jetzigen Ressourcen-Typ der Ressourcen gelöscht.";
$lang["editresourcetypewarning"]="Warnung: Durch das Ändern des Ressourcen-Typs werden sämtliche spezifischen Metadaten für den jetzigen Ressourcen-Typ dieser Ressource gelöscht.";

$lang["geodragmode"]="Zieh-Modus";
$lang["geodragmodearea"]="Auswahl";
$lang["geodragmodeareaselect"]="Suchbereich auswählen";
$lang["geodragmodepan"]="schwenken";

$lang["substituted_original"] = "ersetztes Original";
$lang["use_original_if_size"] = "Original benutzen, wenn die ausgewählte Größe nicht verfügbar ist?";

$lang["originals-available-0"] = "verfügbar"; # 0 (originals) available
$lang["originals-available-1"] = "verfügbar"; # 1 (original) available
$lang["originals-available-2"] = "verfügbar"; # 2+ (originals) available

$lang["inch-short"] = "in";
$lang["centimetre-short"] = "cm";
$lang["megapixel-short"]="MP";
$lang["at-resolution"] = "@"; # E.g. 5.9 in x 4.4 in @ 144 PPI

$lang["deletedresource"] = "Gelöschte Ressource";
$lang["deletedresources"] = "Gelöschte Ressourcen";
$lang["nopreviewresources"]= "Ressourcen ohne Vorschau";
$lang["action-delete_permanently"] = "Dauerhaft löschen";

$lang["horizontal"] = "Horizontal";
$lang["vertical"] = "Vertikal";

$lang["cc-emailaddress"] = "CC %emailaddress"; # %emailaddress will be replaced, e.g. CC [your email address]
$lang["list-recipients-label"] = "Alle Empfänger in der E-Mail auflisten?";
$lang["list-recipients"] = "Diese Nachricht wurde an die folgenden E-Mail Adressen versendet:";

$lang["sort"] = "Sortieren";
$lang["sortcollection"] = "Kollektion sortieren";
$lang["emptycollection"] = "Leere Kollektion";
$lang["deleteresources"] = "Ressourcen löschen";
$lang["emptycollectionareyousure"]="Sind Sie sicher, dass Sie alle Ressourcen aus dieser Kollektion entfernen wollen?";

$lang["error-cannoteditemptycollection"]="Sie können eine leere Kollektion nicht bearbeiten.";
$lang["error-permissiondenied"]="Zugriff verweigert.";
$lang["error-permissions-login"]="Bitte loggen Sie sich ein, um diese Seite anzusehen";
$lang["error-oldphp"] = "Benötigt PHP Version %version oder höher."; # %version will be replaced with, e.g., "5.2"
$lang["error-collectionnotfound"]="Kollektion nicht gefunden.";

$lang["header-upload-subtitle"] = "Schritt %number: %subtitle"; # %number, %subtitle will be replaced, e.g. Step 1: Specify Default Content For New Resources
$lang["local_upload_path"] = "Lokaler Upload Ordner";
$lang["ftp_upload_path"] = "FTP Ordner";
$lang["foldercontent"] = "Ordnerinhalt";
$lang["intro-local_upload"] = "Wählen Sie eine oder mehrere Dateien vom lokalen Upload Ordner aus und klicken Sie auf <b>Upload</b>. Nachdem die Dateien hochgeladen sind, können Sie aus dem Upload Ordner gelöscht werden.";
$lang["intro-ftp_upload"] = "Wählen Sie eine oder mehrere Dateien vom FTP Ordner aus und klicken Sie <b>Upload</b> an.";
$lang["intro-java_upload"] = "Klicken Sie auf <b>Durchsuchen</b>, um eine oder mehrere Dateien auszuwählen, und klicken Sie dann <b>Upload</b> an.";
$lang["intro-java_upload-replace_resource"] = "Klicken Sie auf <b>Durchsuchen</b>, um eine oder mehrere Dateien auszuwählen, und klicken Sie dann <b>Upload</b> an.";
$lang["intro-single_upload"] = "Klicken Sie auf <b>Durchsuchen</b>, um eine Datei auszuwählen, und klicken Sie dann <b>Upload</b> an.";
$lang["intro-plupload"] = "Klicken Sie auf <b>+ Dateien</b>, um eine oder mehrere Dateien auszuwählen, und klicken Sie dann <b>Hochladen</b> an.";
$lang["intro-plupload_dragdrop"] = "Ziehen Sie oder Klicken Sie <b>+ Dateien</b>, um eine oder mehrere Dateien auszuwählen, und klicken Sie dann <b>Hochladen</b> an.";
$lang["intro-plupload_upload-replace_resource"] = "Klicken Sie auf <b>+ Dateien</b>, um eine Datei auszuwählen, und klicken Sie dann <b>Hochladen</b> an.";
$lang["intro-batch_edit"] = "Bitte wählen Sie die Standard-Uploadeinstellungen und die Standardwerte für die Metadaten der Ressourcen, die Sie hochladen wollen.";
$lang["plupload-maxfilesize"] = "Die maximale erlaubte Upload-Dateigröße ist %s.";
$lang["pluploader_warning"]="Ihr Browser unterstützt unter Umständen keine sehr großen Uploads. Wenn Sie damit Probleme haben, aktualisieren Sie bitte Ihren Browser oder versuchen Sie einen der untenstehenden Links.";
$lang["getsilverlight"]="Um sicherzustellen, dass Sie die neueste Version von Silverlight installiert haben, besuchen Sie bitte die Microsoft Silverlight Website.";
$lang["getbrowserplus"]="Um die neueste Version von BrowserPlus zu installieren, besuchen Sie bitte die Yahoo BrowserPlus Website.";
$lang["pluploader_usejava"]="Den alten Java-Uploader benutzen.";

$lang["collections-1"] = "(<strong>1</strong> Kollektion)";
$lang["collections-2"] = "(<strong>%number</strong> Kollektionen)"; # %number will be replaced, e.g. 3 Collections
$lang["total-collections-0"] = "<strong>Gesamt: 0</strong> Kollektionen";
$lang["total-collections-1"] = "<strong>Gesamt: 1</strong> Kollektion";
$lang["total-collections-2"] = "<strong>Gesamt: %number</strong> Kollektionen"; # %number will be replaced, e.g. Total: 5 Collections
$lang["owned_by_you-0"] = "(<strong>0</strong> eigene)";
$lang["owned_by_you-1"] = "(<strong>1</strong> eigene)";
$lang["owned_by_you-2"] = "(<strong>%mynumber</strong> eigene)"; # %mynumber will be replaced, e.g. (2 owned by you)

$lang["listresources"]= "Ressourcen:";
$lang["action-log"]="Log anzeigen";

$lang["saveuserlist"]="Diese Liste speichern";
$lang["deleteuserlist"]="Diese Liste löschen";
$lang["typeauserlistname"]="Geben Sie einen Namen ein...";
$lang["loadasaveduserlist"]="Gespeicherte Benutzerliste laden";

$lang["searchbypage"]="Seite suchen";
$lang["searchbyname"]="Namen suchen";
$lang["searchbytext"]="Text suchen";
$lang["saveandreturntolist"]="Speichern und zurück zur Liste";
$lang["backtomanagecontent"]="Zurück zu Inhalte verwalten";
$lang["editcontent"]="Inhalt bearbeiten";

$lang["confirmcollectiondownload"]="Bitte warten Sie, bis das ZIP-Archiv erstellt wird. Dieser Vorgang kann abhängig von der Gesamtgröße der Ressourcen eine Weile dauern.";
$lang["collectiondownloadinprogress"]='Bitte warten Sie, bis das ZIP-Archiv erstellt wird. Dieser Vorgang kann abhängig von der Gesamtgröße der Ressourcen eine Weile dauern.<br /><br />Um weiter zu arbeiten, öffnen Sie bitte ein <a href=\"home.php\" target=\"_blank\">&gt; Neues Browserfenster</a><br /><br />';
$lang["preparingzip"]="Vorbereiten...";
$lang["filesaddedtozip"]="Dateien kopiert";
$lang["fileaddedtozip"]="Datei kopiert";
$lang["zipping"]="Komprimieren";
$lang["zipcomplete"]="Ihr Download sollte begonnen haben. Sie können diese Seite verlassen.";

$lang["starttypingkeyword"]="Geben Sie den Anfang eines Stichworts ein...";
$lang["createnewentryfor"]="Neuen Eintrag erstellen für";
$lang["confirmcreatenewentryfor"]="Sind Sie sicher, dass Sie einen neuen Eintrag für '%%' in der Stichwortliste erstellen wollen?";

$lang["editresourcepreviews"]="Ressourcenvorschau bearbeiten";
$lang["can_assign_resource_requests"]="Kann Ressourcenanfragen zuweisen";
$lang["can_be_assigned_resource_requests"]="Kann Ressourcenanfragen zugewiesen bekommen (und nur die zugewiesenen Ressourcenanfragen sehen)";

$lang["declinereason"]="Grund für Ablehnung";
$lang["approvalreason"]="Grund für Bestätigung";

$lang["requestnotassignedtoyou"]="Diese Anfrage ist Ihnen nicht länger zugewiesen. Sie ist nun Benutzer % zugewiesen.";
$lang["requestassignedtoyou"]="Ressourcenanfrage zugewiesen";
$lang["requestassignedtoyoumail"]="Eine Ressourcenanfrage wurde Ihnen zur Freigabe zugewiesen. Bitte nutzen Sie den untenstehenden Link, um die Ressourcenanfrage zu erlauben oder abzulehnen.";

$lang["manageresources-overquota"]="Ressourcenverwaltung deaktiviert – Sie haben Ihr Datenvolumen überschritten";
$lang["searchitemsdiskusage"]="Verbrauchten Speicherplatz dieser Ergebnisse berechnen";
$lang["matchingresourceslabel"]="Passende Ressourcen";

$lang["saving"]="Speichern...";
$lang["saved"]="Gespeichert";

$lang["resourceids"]="Ressourcen-ID(s)";

$lang["warningrequestapprovalfield"]="!!! Warnung - Ressourcen-ID % - bitte beachten Sie den folgenden Hinweis vor der Freigabe !!!";

$lang["yyyy-mm-dd"]="JJJJ-MM-TT";

$lang["resources-with-requeststatus0-0"]="(0 ausstehend)"; # 0 Pending
$lang["resources-with-requeststatus0-1"]="(1 ausstehend)"; # 1 Pending
$lang["resources-with-requeststatus0-2"]="(%number ausstehend)"; # %number will be replaced, e.g. 3 Pending
$lang["researches-with-requeststatus0-0"]="(0 nicht zugeordnet)"; # 0 Unassigned
$lang["researches-with-requeststatus0-1"]="(1 nicht zugeordnet)"; # 1 Unassigned
$lang["researches-with-requeststatus0-2"]="(%number nicht zugeordnet)"; # %number will be replaced, e.g. 3 Unassigned

$lang["byte-symbol"]="B";
$lang["kilobyte-symbol"]="KB";
$lang["megabyte-symbol"]="MB";
$lang["gigabyte-symbol"]="GB";
$lang["terabyte-symbol"]="TB";

$lang["upload_files"]="Dateien hochladen";
$lang["upload_files-to_collection"]="Dateien hochladen (in die Kollektion '%collection')"; # %collection will be replaced, e.g. Upload Files (to the collection 'My Collection')

$lang["ascending"] = "Aufsteigend";
$lang["descending"] = "Absteigend";
$lang["sort-type"] = "Sortierungstyp";
$lang["collection-order"] = "Reihenfolge der Kollektionen";
$lang["save-error"]="!! Fehler beim automatischen Speichern - bitte speichern Sie manuell !!";

$lang["theme_home_promote"]="Auf der Startseite hervorheben?";
$lang["theme_home_page_text"]="Text auf der Startseite";
$lang["theme_home_page_image"]="Bild auf der Startseite";
$lang["ref-title"] = "%ref - %title"; # %ref and %title will be replaced, e.g. 3 - Sunset

$lang["error-pageload"] = "Entschuldigung, beim Laden der Seite ist ein Fehler aufgetreten. Wenn Sie eine Suche ausführen, versuchen Sie bitte, Ihre Suchanfrage genauer zu gestalten. Wenn das Problem weiterhin besteht, kontaktieren Sie bitte Ihren Systemadministrator";

$lang["copy-field"]="Feld kopieren";
$lang["copy-to-resource-type"]="In Ressourcen-Typ kopieren";
$lang["synchronise-changes-with-this-field"]="Änderungen mit diesem Feld synchronisieren";
$lang["copy-completed"]="Kopieren fertiggestellt. Das neue Feld hat die ID ?.";

$lang["nothing-to-display"]="Nichts anzuzeigen.";
$lang["report-send-all-users"]="Bericht an alle aktiven Benutzer senden?";

$lang["contactsheet-single"]="1 pro Seite";
$lang["contact_sheet-include_header_option"]="Kopfzeile anzeigen?";
$lang["contact_sheet-add_link_option"]="Links zur Ressource hinzufügen?";
$lang["contact_sheet-add_logo_option"]="Logo am Seitenanfang hinzufügen?";
$lang["contact_sheet-single_select_size"]="Bildqualität";

$lang["caps-lock-on"]="Achtung! Feststelltaste ist aktiv";
$lang["collectionnames"]="Kollektionennamen";
$lang["findcollectionthemes"]="Themen";
$lang["upload-options"]="Uploadoptionen";
$lang["user-preferences"]="Benutzereinstellungen";
$lang["allresources"]="Alle Ressourcen";

$lang["smart_collection_result_limit"]="Smarte Kollektion: Limit für Suchergebnisse";

$lang["untaggedresources"]="Ressourcen ohne %field Daten";

$lang["secureyouradminaccount"]="Willkommen! Um den Server abzusichern, müssen Sie jetzt Ihre Passwort ändern.";
$lang["resources-all-types"]="Alle Ressourcen-Typen";
$lang["search-mode"]="Suchen nach...";
$lang["action-viewmatchingresults"]="Übereinstimmende Ergebnisse anzeigen";
$lang["nomatchingresults"]="Keine übereinstimmenden Ergebnisse";
$lang["matchingresults"]="übereinstimmende Ergebnisse"; # e.g. 17 matching results=======
$lang["resources"]="Ressourcen";
$lang["share-resource"]="Ressource weitergeben";
$lang["scope"]="Bereich";
$lang["downloadmetadata"]="Metadaten herunterladen";
$lang["downloadingmetadata"]="Lädt Metadaten herunter";
$lang["file-contains-metadata"]="Die Datei, die Sie herunterladen, enthält sämtliche Metadaten der Ressource.";
$lang["metadata"]="Metadaten";
$lang["textfile"]="Textdatei";

# Comments field titles, prompts and default placeholders
$lang['comments_box-title']="Kommentare";
$lang['comments_box-policy']="Kommentar Regelung";
$lang['comments_box-policy-placeholder']="Bitte ergänzen Sie Ihren Text unter comments_policy bei den Seitenhinhalten";		# only shown if Admin User and no policy set"
$lang['comments_in-response-to']="Antwort auf";
$lang['comments_respond-to-this-comment']="Antwort";
$lang['comments_in-response-to-on']="auf";
$lang['comments_anonymous-user']="Anonym";
$lang['comments_submit-button-label']="Absenden";
$lang['comments_body-placeholder']="Kommentar hinzufügen";
$lang['comments_fullname-placeholder']="Ihr Name (benötigt)";
$lang['comments_email-placeholder']="Ihre E-Mail Adresse (benötigt)";
$lang['comments_website-url-placeholder']="Website";
$lang['comments_flag-this-comment']="Diesen Kommentar melden";
$lang['comments_flag-has-been-flagged']="Dieser Kommentar wurde gemeldet";
$lang['comments_flag-reason-placeholder']="Grund für die Meldung";
$lang['comments_validation-fields-failed']="Bitte stellen Sie sicher, dass alle Pflichtfelder ausgefüllt wurden.";
$lang['comments_block_comment_label']="Kommentar blocken";
$lang['comments_flag-email-default-subject']="Kommentar gemeldet";
$lang['comments_flag-email-default-body']="Dieser Kommentar wurde gemeldet:";
$lang['comments_flag-email-flagged-by']="Gemeldet von:";
$lang['comments_flag-email-flagged-reason']="Grund für die Meldung:";
$lang['comments_hide-comment-text-link']="Kommentar entfernen";
$lang['comments_hide-comment-text-confirm']="Sind Sie sicher, dass Sie den Kommentar entfernen wollen?";

# testing updated request emails
$lang["request_id"]="Anfrage ID:";
$lang["user_made_request"]="Der folgende Benutzer hat eine Anfrage gestellt:";

$lang["download_collection"]="Kollektion herunterladen";

$lang["all-resourcetypes"] = "Ressourcen"; # Will be used as %resourcetypes% if all resourcetypes are searched.
$lang["all-collectiontypes"] = "Kollektionen"; # Will be used as %collectiontypes% if all collection types are searched.
$lang["resourcetypes-no_collections"] = "Alle %Resourcetypes%"; # Use %RESOURCETYPES%, %resourcetypes% or %Resourcetypes% as a placeholder. The placeholder will be replaced with the resourcetype in plural (or $lang["all-resourcetypes"]), using the same case. E.g. "All %resourcetypes%" -> "All photos"
$lang["no_resourcetypes-collections"] = "Alle %Collectiontypes%"; # Use %COLLECTIONTYPES%, %collectiontypes% or %Collectiontypes% as a placeholder. The placeholder will be replaced with the collectiontype (or $lang["all-collectiontypes"]), using the same case. E.g. "All %collectiontypes%" -> "All my collections"
$lang["resourcetypes-collections"] = "Alle %Resourcetypes% und alle %Collectiontypes%"; # Please find the comments for $lang["resourcetypes-no_collections"] and $lang["no_resourcetypes-collections"]!
$lang["resourcetypes_separator"] = ", "; # The separator to be used when converting the array of searched resourcetype to a string. E.g. ", " -> "photos, documents"
$lang["collectiontypes_separator"] = ", "; # The separator to be used when converting the array of searched collections to a string. E.g. ", " -> "public collections, themes"
$lang["hide_view_access_to_workflow_state"]="Zugriff auf Status blockieren";
$lang["collection_share_status_warning"]="Warnung - diese Kollektion hat Ressourcen in den folgenden Zuständen, bitte beachten Sie, dass diese Ressourcen für andere Benutzer verfügbar sein werden";
$lang["contactadmin"]="Administrator kontaktieren";
$lang["contactadminintro"]="Bitte geben Sie Ihre Nachricht ein und klicken Sie 'Absenden'.";
$lang["contactadminemailtext"]=" hat Ihnen eine Ressource gesendet";
$lang["showgeolocationpanel"]="Geodaten anzeigen";
$lang["hidegeolocationpanel"]="Geodaten ausblenden";
$lang["download_usage_option_blocked"]="Diese Nutzungsoption ist nicht verfügbar. Bitte kontaktieren Sie Ihren Administrator";

$lang["tagcloudtext"]="Mit welchen Begriffen haben die Benutzer Ressourcen versehen? Je häufiger ein Begriff vorkommt, desto größer wird er angezeigt.<br /><br />Sie können außerdem jeden Begriff anklicken, um eine Suche auszuführen.";
$lang["tagcloud"]="Tagwolke";

$lang["email_link_expires_never"]="Dieser Link wird niemals ablaufen";
$lang['email_link_expires_date']="Dieser Link läuft ab am ";
$lang['email_link_expires_days']="Link läuft ab: ";
$lang['expire_days']='Tage';
$lang['expire_day']='Tag';
$lang["collection_order_description"]="Kollektionssortierung";
$lang["view_shared_collections"]="Weitergegebene Kollektionen anzeigen";
$lang["shared_collections"]="Weitergegebene Kollektionen";
$lang["internal"]="Intern";
$lang["managecollectionslink"]="Kollektionen verwalten";
$lang["showcollectionindropdown"]="Kollektionen in Dropdown Menü anzeigen";
$lang["sharerelatedresources"]="Verwandte Ressourcen einschließen.<br>Eine neue Kollektion wird erstellt und weitergegeben, wenn eine dieser Ressourcen ausgewählt wird.";
$lang["sharerelatedresourcesaddremove"]="Wenn verwandte Ressourcen weitergegeben werden, anderen Benutzern erlauben, Ressourcen aus der neuen Kollektion zu entfernen bzw. hinzuzufügen.";
$lang["create_empty_resource"]="Upload überspringen und eine neue Ressource ohne zugeordneter Datei erstellen";
$lang["entercollectionname"]="Namen eingeben, dann Return drücken";
$lang["embedded_metadata"]="Eingebettete Metadaten";
$lang["embedded_metadata_extract_option"]="Extrahieren";
$lang["embedded_metadata_donot_extract_option"]="Nicht extrahieren";
$lang["embedded_metadata_append_option"]="Am Ende einfügen";
$lang["embedded_metadata_prepend_option"]="Am Anfang einfügen";
$lang["embedded_metadata_custom_option"]="Benutzerdefiniert";
$lang["related_resource_confirm_delete"]="This will remove the relationship but will not delete the resource. ";
$lang["batch_replace_filename_intro"]="Um eine Reihe von Ressourcen zu ersetzen, können Sie Dateien hochladen, deren Namen den eindeutigen Ressourcen-IDs entspricht. Alternativ können Sie ein Metadatenfeld auswählen, welches den Dateinamen enthält und das System vergleicht die Dateinamen mit denen der hochgeladenen Dateien zum Ersetzen";
$lang["batch_replace_use_resourceid"]="Dateinamen mit Ressourcen-IDs vergleichen";
$lang["batch_replace_filename_field_select"]="Bitte wählen Sie das Feld aus, welches den Dateinamen enthält.";
$lang["plupload_log_intro"] ="Upload Zusammenfassung - Serverzeit: ";
$lang["no_access_to_collection"]="Sie haben leider keinen Zugriff auf diese Kollektion.";
$lang["internal_share_grant_access"]="Offenen Zugriff für ausgewählte Benutzer gewähren?";
$lang["internal_share_grant_access_collection"]="Offenen Zugriff für ausgewählte Benutzer gewähren (für Ressourcen, auf die Sie Bearbeitungszugriff haben)?";

# For merging filename with title functionality:
$lang['merge_filename_title_question'] = 'Dateinamen im Titel verwenden (wenn kein eingebetteter Titel gefunden wird)?';
$lang['merge_filename_title_do_not_use'] = 'Nicht verwenden';
$lang['merge_filename_title_replace'] = 'Ersetzen';
$lang['merge_filename_title_prefix'] = 'Voranstellen';
$lang['merge_filename_title_suffix'] = 'Anhängen';
$lang['merge_filename_title_include_extensions'] = 'Dateierweiterungen verwenden?';
$lang['merge_filename_title_spacer'] = 'Abstand';

# For sending a collection with all the resources uploaded at one time:
$lang['send_collection_to_admin_emailedcollectionname'] = 'Kollektion per E-Mail gesendet';
$lang['send_collection_to_admin_emailsubject'] = 'Kollektion hochgeladen durch ';
$lang['send_collection_to_admin_usercontributedcollection'] = ' hat diese Ressourcen in einer Kollektion hochgeladen';
$lang['send_collection_to_admin_additionalinformation'] = 'Zusätzliche Informationen';
$lang['send_collection_to_admin_collectionname'] = 'Name der Kollektion: ';
$lang['send_collection_to_admin_numberofresources'] = 'Anzahl der Ressourcen: ';

# User group management
$lang['page-title_user_group_management'] = "Benutzergruppen verwalten";
$lang['page-subtitle_user_group_management'] = "In diesem Bereich können Sie Benutzergruppen hinzufügen, ändern und entfernen.";
$lang['action-title_create_user_group_called'] = "Benutzergruppe mit Namen erstellen...";
$lang['action-title_filter_by_parent_group'] = "Filter für übergeordnete Benutzergruppe";
$lang['action-title_filter_by_permissions'] = "Berechtigungsfilter";
$lang["fieldhelp-permissions_filter"]="Sie können eine einzelne Berechtigung oder eine mit Komma getrennte Liste eingeben. Teilnamen und Muster sind nicht erlaubt. Groß- und Kleinschreibung wird unterschieden!";

# User group management edit
$lang['page-title_user_group_management_edit'] = "Benutzergruppe ändern";
$lang['page-subtitle_user_group_management_edit'] = "In diesem Bereich können Sie die Eigenschaften einer Benutzergruppe ändern.";
$lang["action-title_remove_user_group_logo"]="Anwählen um das Benutzergruppenlogo zu entfernen";
$lang["action-title_see_wiki_for_advanced_options"]="Im <a href='http://wiki.resourcespace.org/index.php?title=Main_Page#System_Administrator.27s_Guide'>WIKI</a> finden Sie Informationen für weiterführende Optionen.";

# Report management
$lang['page-title_report_management'] = "Reporte verwalten";
$lang['page-subtitle_report_management'] = "In diesem Bereich können Sie Reporte hinzufügen, ändern und löschen.";
$lang['action-title_create_report_called'] = "Report mit Namen erstellen...";

# Report management edit
$lang['page-title_report_management_edit'] = "Report ändern";
$lang['page-subtitle_report_management_edit'] = "In diesem Bereich können Sie den Inhalt eines Reports festlegen.";
$lang["fieldtitle-tick_to_delete_report"] = "Anwählen um diesen Report zu entfernen";

# size management
$lang['page-title_size_management'] = "Bildgrößen verwalten";
$lang['page-subtitle_size_management'] = "In diesem Bereich können Sie die Bildgrößen für Vorschau und zum Herunterladen verändern.";
$lang['action-title_create_size_with_id'] = "Größe mit " . $lang['property-id'] . " erstellen...";

# size management edit
$lang['page-title_size_management_edit'] = "Bildgröße ändern";
$lang['page-subtitle_size_management_edit'] = "In diesem Bereich können Sie die Details zu einer Bildgröße festlegen.";
$lang["fieldtitle-tick_to_delete_size"] = "Anwählen um diese Größe zu entfernen";

##########################################################################################
# Non page-specific items that need to be merged above when system admin project completed
##########################################################################################

$lang["admin_resource_type_field"]="Metadatenfeld verwalten";
$lang["admin_resource_type_field_count"]="Metadatenfelder";
$lang["admin_resource_type_field_create"]="Metadatenfeld mit Namen erstellen...";
$lang["admin_resource_type_field_reorder_information"]="Um die Anzeigereihenfolge der Felder zu ändern, können Sie die Zeilen per Drag&Drop verschieben.";
$lang["admin_resource_type_field_reorder_select_restype"]="Wählen Sie einen Ressourcetypen oder globale Felder aus, um das Umordnen von Feldern zu erlauben.";
$lang["admin_resource_type_fields"]="Metadatenfelder verwalten";
$lang["fieldhelp-tick_to_delete_group"]="Sie können keine Benutzergruppen löschen, die noch Benutzer enthalten oder einer anderen Gruppe übergeordnet sind";
$lang["fieldtitle-tick_to_delete_group"]="Anwählen um diese Gruppe zu entfernen";
$lang["property-contains"]="Enthält";
$lang["property-groups"]="Gruppen";
$lang["property-user_group"]="Benutzergruppe";
$lang["property-user_group_parent"]="Übergeordnete Benutzergruppe";
$lang["property-user_group_remove_parent"]="(übergeordnete Gruppe entfernen)";
$lang['action-move-up'] = 'Nach oben';
$lang['action-move-down'] = 'Nach unten';



$lang["about__about"]="Ihr Text zu \"Über uns\" hier.";
$lang["all__researchrequest"]="Lassen Sie unser Team nach den benötigten Resourcen suchen.";
$lang["all__searchpanel"]="Suche nach Beschreibung, Schlagworten und Ressourcen IDs";
$lang["change_language__introtext"]="Bitte wählen Sie Ihre Sprache aus:";
$lang["change_password__introtext"]="Neues Passwort unten eingeben, um es zu ändern.";
$lang["collection_edit__introtext"]="Organisieren und verwalten Sie Ihre Arbeit, indem Sie Ressourcen in Gruppen zusammenstellen. Erstellen Sie Kollektionen wie Sie sie benötigen.\n\n<br />\n\nAlle Kollektionen in Ihrer Liste erscheinen im \"Meine Kollektionen\" Menü am unteren Ende des Fensters.\n\n<br /><br />\n\n<strong>Privater Zugriff</strong> erlaubt nur Ihnen und ausgewählten Benutzern, die Kollektion zu anzusehen. Ideal, um Ressourcen für die eigene Arbeit zusammenzustellen und im Team weiterzugeben.\n\n<br /><br />\n\n<strong>Öffentlicher Zugriff</strong> erlaubt allen Benutzern, die Kollektion zu finden und anzusehen.\n\n<br /><br />\n\nSie können aussuchen, ob Sie anderen Benutzern (öffentlicher Zugriff oder ausgewählte Benutzer beim privaten Zugriff) erlauben, Ressourcen hinzuzufügen oder zu löschen.";
$lang["collection_email__introtext"]="Bitte füllen Sie das untenstehende Formular aus, um die Kollektion per E-Mail weiterzugeben. Der/die Benutzer werden statt eines Dateianhangs einen Link zu dieser Kollektion erhalten und können dann die passenden Ressourcen auswählen und herunterladen.";
$lang["collection_manage__findpublic"]="Öffentliche Kollektionen sind für alle Benutzer zugängliche Gruppen von Ressourcen. Um öffentliche Kollektionen zu finden, geben Sie die ID, oder einen Teil des Kollektions- bzw. Benutzernamens ein. Fügen Sie dann die Kollektion zu Ihren Kollektionen hinzu, um auf die Ressourcen zuzugreifen.";
$lang["collection_manage__introtext"]="Organisieren und verwalten Sie Ihre Arbeit, indem Sie Ressourcen in Gruppen zusammenstellen. Erstellen Sie Kollektionen wie Sie sie benötigen. Sie können Kollektionen an andere weitergeben oder einfach Gruppen von Ressourcen zusammen halten. Alle Kollektionen in Ihrer Liste finden Sie im \"Meine Kollektionen\" Menü am unteren Ende des Fensters.";
$lang["collection_manage__newcollection"]="Um eine neue Kollektion zu erstellen, geben Sie bitte einen Kurznamen an.";
$lang["collection_public__introtext"]="Öffentliche Kollektionen werden von anderen Benutzern erstellt und freigegeben.";
$lang["contact__contact"]="Ihre Kontaktdaten hier.";
$lang["contribute__introtext"]="Sie können Ihre eigenen Ressourcen hochladen. Wenn Sie eine Ressource erstellen, wird diese zunächst durch uns geprüft. Nachdem Sie die Datei hochgeladen und die Felder ausgefüllt haben, setzen Sie bitte den Status auf \"Benutzer-Beiträge: Überprüfung noch nicht erledigt\".";
$lang["delete__introtext"]="Bitte geben Sie Ihr Passwort ein, um zu bestätigen, dass Sie diese Ressource löschen wollen.";
$lang["done__collection_email"]="Eine E-Mail mit Link zur Kollektion wurde an die angegebenen Benutzer gesendet. Die Kollektion wurde zur Liste Ihrer Kollektionen hinzugefügt.";
$lang["done__deleted"]="Die Ressource wurde gelöscht.";
$lang["done__research_request"]="Ein Mitglied unseres Teams wird sich um Ihre Anfrage kümmern. Wir werden Sie per e-mail über den aktuellen Stand informieren. Wenn Ihre Anfrage bearbeitet ist, erhalten Sie eine e-mail mit einem Link zu den Ressourcen, die wir für Ihre Anfrage empfehlen.";
$lang["done__resource_email"]="Eine E-Mail mit Link zur Ressource wurde an die angegebenen Benutzer gesendet.";
$lang["done__resource_request"]="Ihre Anfrage wurde abgeschickt und wird in Kürze bearbeitet.";
$lang["done__user_password"]="Eine E-Mail mit Ihrem Benutzernamen und Passwort wurde an Sie gesendet.";
$lang["done__user_request"]="Ihre Anfrage nach einem Zugang wurde abgeschickt und wird in Kürze bearbeitet.";
$lang["download_click__introtext"]="Um die Datei herunterzuladen, klicken Sie bitte mit der rechten Maustaste auf den untenstehenden Link und wählen Sie \"Speichern unter...\". Sie können dann auswählen an welchem Ort Sie die Datei abspeichern wollen. Um die Datei im Browser zu öffnen, klicken Sie den Link bitte mit der linken Maustaste.";
$lang["download_progress__introtext"]="Ihr Download wird in Kürze starten. Nachdem der Download abgeschlossen ist, wählen Sie bitte einen der folgenden Links.";
$lang["edit__batch"]="";
$lang["edit__multiple"]="Bitte wählen Sie die Felder aus, die Sie verändern wollen. Felder, die Sie nicht anwählen, werden nicht verändert.";
$lang["help__introtext"]="Diese Anleitungen helfen Ihnen, ResourceSpace und Ihre Medien effektiv zu nutzen.</p>\n<p>Benutzen Sie \"Themen\", um Ressourcen nach Themen zu erkunden oder nutzen Sie die Suche um spezifische Ressourcen zu finden.</p>\n<p><a href=\"http://www.montala.net/downloads/resourcespace-GettingStarted.pdf\">User Guide</a> (PDF, englisch)<br /><a target=\"_blank\" href=\"http://wiki.resourcespace.org/index.php/?title=main_Page\">Online Dokumentation</a> (Wiki)</p>";
$lang["home__help"]="Hilfe für die Arbeit mit ResourceSpace";
$lang["home__mycollections"]="Hier können Sie Ihre Kollektionen organisieren, verwalten und weitergeben.";
$lang["home__restrictedtext"]="Bitte klicken Sie auf den Link, den Sie per E-Mail erhalten haben, um auf die für Sie ausgesuchten Ressourcen zuzugreifen.";
$lang["home__restrictedtitle"]="Willkommen bei ResourceSpace";
$lang["home__themes"]="Von unserem Team vorausgewählte Bilder";
$lang["home__welcometext"]="Ihr Einleitungstext hier";
$lang["home__welcometitle"]="Willkommen bei ResourceSpace";
$lang["login__welcomelogin"]="Willkommen bei ResourceSpace. Bitte loggen Sie sich ein...";
$lang["research_request__introtext"]="Unser Team unterstützt Sie dabei, die optimalen Ressourcen für Ihre Projekte zu finden. Füllen Sie dieses Formular bitte möglichst vollständig aus, damit wir Ihre Anforderungen erfüllen können.\n<br /><br />\nWir werden Sie kontinuierlich informieren. Sobald wir Ihre Anfrage bearbeitet haben, werden Sie eine E-Mail mit einem Link zu den von uns empfohlenen Bildern erhalten.";
$lang["resource_email__introtext"]="Geben Sie dieses Bild per E-Mail weiter. Es wird ein Link versendet. Sie können außerdem eine persönliche Nachricht in die E-Mail einfügen.";
$lang["resource_request__introtext"]="Die Ressource, die Sie herunterladen möchten, ist nicht online verfügbar. Die Informationen zur Ressource werden automatisch per E-Mail versendet. Zusätzlich können Sie weitere Bemerkungen hinzufügen.";
$lang["search_advanced__introtext"]="<strong>Suchtipp</strong><br />\nJeder Bereich, den Sie nicht ausfüllen / anklicken, liefert alle Ergebnisse aus dem Bereich.";
$lang["tag__introtext"]="Verbessern Sie die Suchergebnisse, indem Sie Ressourcen taggen. Sagen Sie, was Sie sehen, getrennt durch Leerzeichen oder Komma... z.B.: Hund, Haus, Ball, Geburtstag, Kuchen. Geben Sie den vollen Namen von Personen in Fotos und die Ort der Aufnahme an, wenn bekannt.";
$lang["team_archive__introtext"]="Um einzelne Ressourcen im Archiv zu bearbeiten, suchen Sie einfach nach den Ressourcen und klicken auf \"bearbeiten\" unter \"Ressourcen-Werkzeuge\". Alle Ressourcen, die archiviert werden sollen, werden in der Liste \"Archivierung noch nicht erledigt\" angezeigt. Von dieser Liste aus können Sie weitere Informationen ergänzen und die Ressource ins Archiv verschieben.";
$lang["team_batch__introtext"]="";
$lang["team_batch_select__introtext"]="";
$lang["team_batch_upload__introtext"]="";
$lang["team_copy__introtext"]="Geben Sie die ID der Ressource ein, die Sie kopieren möchten. Nur die Metadaten der Ressource werden kopiert – hochgeladene Dateien werden nicht kopiert.";
$lang["team_home__introtext"]="Willkommen in der Administration. Bitte benutzen Sie die untenstehenden Links, um die Ressourcen zu verwalten, auf Ressourcenanfragen zu antworten, Themen zu verwalten und die Systemeinstellungen zu bearbeiten.";
$lang["team_report__introtext"]="Bitte wählen Sie einen Bericht und einen Zeitraum. Der Bericht kann in Microsoft Excel oder einer anderen Tabellenkalkulation geöffnet werden.";
$lang["team_research__introtext"]="Organisieren und verwalten Sie Ihre \"Ressourcenanfragen\".<br /><br />Wählen Sie \"Anfrage bearbeiten\", um die Details der Anfrage zu sehen und sie einem Teammitglied zuzuweisen. Es ist möglich, eine Antwort auf einer existierenden Kollektion aufzubauen. Geben Sie dazu die Kollektions-ID in der Ansicht zur Bearbeitung ein.<br /><br />Wenn die Ressourcenanfrage zugewiesen ist, wählen Sie \"Kollektion bearbeiten\", um die Anfrage zu Ihren Kollektionen hinzuzufügen. So können Sie Ressourcen zu dieser Kollektion hinzufügen.<br /><br />Wenn die Kollektion vollständig ist, wählen Sie \"Anfrage bearbeiten\", stellen Sie den Status auf \"abgeschlossen\" und eine E-Mail wird automatisch an den Anfrager geschickt. Diese E-Mail enthält einen Link zur erstellten Kollektion, welche außerdem automatisch zu den Kollektionen des Benutzers hinzugefügt wird.";
$lang["team_resource__introtext"]="Fügen Sie einzelne Ressourcen hinzu oder nutzen Sie den Stapelupload. Um einzelne Ressourcen zu bearbeiten, suchen Sie nach der Ressource und wählen Sie \"bearbeiten\" unter den \"Ressourcen-Werkzeugen\".";
$lang["team_stats__introtext"]="Statistiken werden auf Basis der aktuellsten Daten erstellt. Aktivieren Sie die Checkbox, um alle Statistiken für das gewählte Jahr auszugeben.";
$lang["team_user__introtext"]="In diesem Bereich können Sie Benutzer hinzufügen, löschen und verändern.";
$lang["terms__introtext"]="Sie müssen zuerst die Nutzungsbedingungen akzeptieren.\n\n";
$lang["terms__terms"]="Ihre Nutzungsbedingungen hier.";
$lang["terms and conditions__terms and conditions"]="Ihre Nutzungsbedingungen hier.";
$lang["themes__findpublic"]="Öffentliche Kollektionen sind Kollektionen, die von anderen Benutzern freigegeben wurden.";
$lang["themes__introtext"]="Themen sind von unserem Team zusammengestellte Gruppen von Ressourcen.";
$lang["themes__manage"]="Organisieren und bearbeiten Sie Ihre Themen. Themen sind besonders hervorgehobene Kollektionen. <br /><br /><strong>1 Um einen neuen Eintrag in einem Thema anzulegen, müssen Sie zuerst eine neue Kollektion anlegen</strong><br />Wählen Sie <strong>Meine Kollektionen</strong> aus der oberen Navigation und legen Sie eine neue <strong>öffentliche</strong> Kollektion an. Stellen Sie sicher, dass Sie einen Namen für Ihr Thema eingeben. Um die aktuelle Kollektion einem bestehenden Thema zuzuordnen, nutzen Sie einen bestehenden Themennamen. Wenn Sie einen noch nicht vergebenen Themennamen angeben, erstellen Sie ein neues Thema. <br /><br /><strong>2 Um den Inhalt eines bestehenden Themas zu ändern, </strong><br />wählen Sie <strong>\'Kollektion bearbeiten\'</strong>. Die Ressourcen in dieser Kollektion erscheinen unten im <strong>\'Meine Kollektionen\'</strong> Bereich. Nutzen Sie die Standardwerkzeuge um Resourcen zu bearbeiten, hizuzufügen oder zu löschen.<br /><br /><strong>3 Um eine Kollektion umzubenennen oder unter einem anderen Thema anzuzeigen,</strong><br />wählen Sie <strong>\'bearbeiten\'</strong> und bearbeiten Sie die Themenkategorie oder die Kollektionsnamen. <br /><br /><strong>4 Um eine Kollektion aus einem Thema zu entfernen,</strong><br />wählen Sie<strong> \'bearbeiten\'</strong> und löschen Sie den Eintrag im Feld \"Themen-Kategorie\".";
$lang["upload__introtext"]="";
$lang["upload_swf__introtext"]="";
$lang["user_password__introtext"]="Bitte geben Sie Ihre E-Mail Adresse ein. Ihre Zugangsdaten werden dann an per E-Mail an Sie versendet.";
$lang["user_request__introtext"]="Um einen Zugang anzufordern, füllen Sie bitte das untenstehende Formular aus.";
$lang["view__storyextract"]="Story:";

