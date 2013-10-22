<?php
# Deutsch
# Sprachdatei für das Transform Plugin
# -------
#
#
# Resource log - actions
$lang['transform']="Bildbearbeitung";
$lang['transformimage']="Bildbearbeitung";
$lang['transformed']="Bild bearbeitet";
$lang['transformblurb']="Um das Bild zu beschneiden, positionieren Sie den Mauszeiger über dem Bild und klicken eine Ecke des neunen Bildzuschnittes an. Sie können nun die Auswahlmaske bei gedrückter Maustaste in alle Richtungen ziehen. Wenn Sie mit der Auswahl des Bildzuschnittes fertig sind, vergeben Sie dafür im Feld Name einen eindeutigen Dateinamen und klicken Sie auf Kopie speichern oder Download. Optional kann das Seitenverhältnis auch über die Felder Breite und/oder Höhe skaliert werden.<br /><br /><strong>Es muss keine Option zur Skalierung ausgewählt werden!</strong> Ohne Angabe von Breite und Höhe bleibt das Seitenverhältnis unverändert.";
$lang['transformblurb-original']="Um das Bild zu beschneiden, positionieren Sie bitte den Mauszeiger über dem Bild und klicken eine Ecke des neunen Bildzuschnittes an. Sie können nun die Auswahlmaske bei gedrückter Maustaste in alle Richtungen ziehen. Wenn Sie mit der Auswahl des Bildzuschnittes fertig sind, können Sie die veränderte Originaldatei speichern. Optional kann das Seitenverhältnis auch über die Felder Breite und/oder Höhe skaliert werden.<br /><br /><strong>Es muss keine Option zur Skalierung ausgewählt werden!</strong> Ohne Angabe von Breite und Höhe bleibt das Seitenverhältnis unverändert.";
$lang['width']="Breite";
$lang['height']="Höhe";
$lang['px']="px";
$lang['noimagefound']="Fehler: Keine Bilddatei gefunden!";
$lang['scaled']="Skaliert";
$lang['cropped']="Zugeschnitten";
$lang['nonnumericcrop']="Fehler: Nicht-numerischer Zuschnitt erforderlich!";
$lang['description_for_alternative_file']="Beschreibung";
$lang['errorspecifiedbiggerthanoriginal']="Fehler: Die angegebene Breite oder Höhe ist größer als die der Originaldatei!";
$lang['errormustchoosecropscale']="Fehler: Sie müssen einen Zuschnittsbereich festlegen oder eine Skalierung definieren!";
$lang['savealternative']="Kopie speichern";
$lang['rotation']="Rotation";
$lang['rotation0']="Keine";
$lang['rotation90']="90 Grad UZ";
$lang['rotation180']="180 Grad UZ";
$lang['rotation270']="90 Grad GUZ";
$lang['fliphorizontal']="Horizontal spiegeln";
$lang['transform_original']="Original bearbeiten";
$lang['priorversion']="Vorherige Version";
$lang['replaced']="Ersetzt";
$lang['replaceslideshowimage']="Bilder der Dia-Show ersetzen";
$lang['slideshowsequencenumber']="Nummerierung der Reihenfolge (1, 2, 3 etc.)";
$lang['slideshowmakelink']="Bild der Dia-Show mit der Ressourcen-Ansicht verknüpfen";
$lang['transformcrophelp']="Klicken Sie mit gedrückter Maustaste auf das Bild und bewegen Sie anschließend den Mauszeiger, um eine Auswahlmaske zu definieren.";
$lang['originalsize']="Originalgröße";
$lang['allow_upscale']="Hochskalierung";
$lang['batchtransform']="Stapelverarbeitung";
$lang['batchtransform-introtext']="<strong>WARNUNG: Die Ausführung dieses Befehls bewirkt eine dauerhafte Veränderung Ressource!</strong>";
$lang['error-crop-imagemagick-not-configured']="Fehler: ImageMagick zum Zuschneiden nicht konfiguriert. Bitte kontaktieren Sie den Administrator.";
$lang['no_resources_found']="Keine Ressource gefunden!";
$lang['batch_transforming_collection']="Stapelverarbeitung der Kollektion %col"; # %col will be replaced with the collection id
$lang['not-transformed']="Bearbeitung nicht möglich: Kein Zugriff!";
$lang['error-unable-to-rename']="Fehler: Die Datei der bearbeiteten Ressource %res konnte nicht umbenannt werden!"; # %res will be replaced with the resource id
$lang['success']="Erfolg!";
$lang['error-transform-failed']="Fehler: Keine Bearbeitung der Ressource %res möglich!"; # %res will be replaced with the resource id
$lang['transform_summary']="Zusammenfassung";
$lang['resources_in_collection-1']="1 Ressource in der Kollektion.";
$lang['resources_in_collection-2']="%qty Ressourcen in der Kollektion."; # %qty will be replaced with the quantity of resources in collection
$lang['resources_transformed_successfully-0']="0 Ressourcen erfolgreich bearbeitet.";
$lang['resources_transformed_successfully-1']="1 Ressource erfolgreich bearbeitet.";
$lang['resources_transformed_successfully-2']="%qty Ressourcen erfolgreich bearbeitet."; # %qty will be replaced with the quantity of transformed resources
$lang['errors-1']="1 Fehler!";
$lang['errors-2']="%qty Fehler!"; # %qty will be replaced with the quantity of errors

$lang['transform_configuration']="Konfiguration der Bildbearbeitung";
$lang['cropper_debug']="ImageMagick debuggen";
$lang['output_formats']="Ausgabeformate";
$lang['input_formats']="Eingabeformate";
$lang['custom_filename']="Eigener Dateiname";
$lang['allow_rotation']="Rotation";
$lang['allow_transform_original']="Original bearbeiten";
$lang['use_repage']="Benutze 'repage'";
$lang['enable_batch_transform']="Stapelverarbeitung";
$lang['cropper_enable_alternative_files']='Speichern als alternative Dateien erlauben';
$lang['enable_replace_slideshow']='Ersetzen der Dia-Show erlauben';
