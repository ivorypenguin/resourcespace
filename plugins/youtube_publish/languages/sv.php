<?php
#
# Swedish Language File for the YouTube Publish Plugin
# Updated by Henrik Frizén 20131114 for svn r5087
# -------
#
#
$lang["youtube_publish_title"]="Youtube-publicering";
$lang["youtube_publish_linktext"]="Publicera på Youtube";
$lang["youtube_publish_configuration"]="Publicera på Youtube – inställningar";
$lang["youtube_publish_notconfigured"] = "Publicera på Youtube är inte konfigurerat. Be administratören konfigurera tillägget på";
$lang["youtube_publish_legal_warning"] = "Genom att klicka på OK intygar du att du äger alla rättigheter till innehållet eller att du har tillåtelse av upphovsrättsinnehavaren att göra innehållet offentligt tillgängligt på Youtube, samt att innehållet i övrigt uppfyller Youtubes användningsvillkor för tjänsten på http://www.youtube.com/t/terms.";
$lang['youtube_publish_resource_types_to_include']="Välj giltiga materialtyper för Youtube";
$lang["youtube_publish_mappings_title"]="Knytning av fält för ResourceSpace – Youtube";
$lang["youtube_publish_title_field"]="Fält för titel";
$lang["youtube_publish_descriptionfields"]="Fält för beskrivning";
$lang["youtube_publish_keywords_fields"]="Fält för taggar";
$lang["youtube_publish_url_field"]="Fält för att lagra webbadressen till Youtube-klippet";
$lang["youtube_publish_allow_multiple"]="Tillåt flera överföringar av samma material?";
$lang["youtube_publish_log_share"]="Delad på Youtube";
$lang["youtube_publish_unpublished"]="opublicerad"; 
$lang["youtube_publishloggedinas"]="Du kommer att publicera till Youtube-kontot: %youtube_username%"; # %youtube_username% will be replaced, e.g. You will be publishing to the YouTube account : My own RS channel
$lang["youtube_publish_change_login"]="Använd ett annat Youtube-konto";
$lang["youtube_publish_accessdenied"]="Du har inte tillåtelse att publicera det här materialet";
$lang["youtube_publish_alreadypublished"]="Detta material har redan publicerats på Youtube.";
$lang["youtube_access_failed"]="Misslyckades att nå Youtubes gränssnitt för överföringstjänster. Kontakta din administratör eller kontrollera inställningarna. ";
$lang["youtube_publish_video_title"]="Videotitel";
$lang["youtube_publish_video_description"]="Videobeskrivning";
$lang["youtube_publish_video_tags"]="Videotaggar";
$lang["youtube_publish_access"]="Sekretessinställning";
$lang["youtube_public"]="offentlig";
$lang["youtube_private"]="privat";
$lang["youtube_publish_public"]="Offentlig";
$lang["youtube_publish_private"]="Privat";
$lang["youtube_publish_unlisted"]="Olistad";
$lang["youtube_publish_button_text"]="Publicera";
$lang["youtube_publish_authentication"]="Autentisering";
$lang["youtube_publish_use_oauth2"]="Använd OAuth 2.0?";
$lang["youtube_publish_oauth2_advice"]="<p><strong>Instruktioner för Youtubes OAuth 2.0</strong><br></p><p>Du måste konfigurera OAuth 2.0 eftersom inga andra autentiseringsmetoder officiellt längre stöds. För att göra det måste du registrera din ResourceSpace-webbplats som ett projekt hos Google och få ett ’client id’ och en ’client secret’. Detta kostar ingenting.</p><list><li>Logga in på Google med valfritt gällande Google-konto (detta konto behöver inte vara kopplat till ditt Youtube-konto), och gå sen till <a href=\"https://code.google.com/apis/console/\" target=\"_blank\">https://code.google.com/apis/console/</a></li><li>Skapa ett nytt projekt (namn och id spelar ingen roll)</li><li>Gå till ’APIs & auth’ och aktivera sen ’YouTube Data API v3’ (i slutet av listan)</li><li>Gå till ’Registered Apps’ och klicka på <b>REGISTER APP</b></li><li>Ange namnet på din RS-installation, välj ’Web Application’ och klicka på <b>Register</b></li><li>Välj ’OAuth2.0 Client ID’</li><li>I fältet ’Redirect Uri’ anger du webbadressen för återanrop som visas längst upp på den här sidan, klicka därefter på <b>Generate</b></li><li>Notera ’Client Id’ och ’Client Secret’ och ange sen dessa uppgifter i fälten nedan</li><li>Klicka på <b>Update</b> under ’Consent Screen’ för att anpassa vad dina användare ser när de först godkänner att din webbplats får överföra videor till Youtube</li><li>(Ej obligatoriskt) Lägg till en ’Developer Key’. Detta är inte nödvändigt för tillfället, men kan bli så i framtiden. En ’Developer Key’ ger en produkt som skickar en api-förfrågan en unik identitiet. Besök <a href=\"http://code.google.com/apis/youtube/dashboard/\" target=\"_blank\" >http://code.google.com/apis/youtube/dashboard/</a> om du vill skaffa en ’Developer Key’.</li></list>";
$lang["youtube_publish_developer_key"]="’Developer Key’"; 
$lang["youtube_publish_oauth2_clientid"]="’Client Id’";
$lang["youtube_publish_oauth2_clientsecret"]="’Client Secret’";
$lang["youtube_publish_callback_url"]="Webbadress för återanrop";
$lang["youtube_publish_username"]="Användarnamn (Youtube)";
$lang["youtube_publish_password"]="Lösenord (Youtube)";
$lang["youtube_publish_existingurl"] = "Existerande Youtube-adress:";
$lang["youtube_publish_notuploaded"] = "Inte överförd";
$lang["youtube_publish_failedupload_nolocation"] = "Fel: Fick ingen en giltig webbadress till Youtube-klippet.";
$lang["youtube_publish_success"] = "Videon publicerad!";
$lang["youtube_publish_renewing_token"] = "Förnyar igenkänningstecknet för åtkomstkontroll";
$lang["youtube_publish_category"]="Kategori";
$lang["youtube_publish_film"]="Film och animering";
$lang["youtube_publish_autos"]="Bilar och fordon";
$lang["youtube_publish_music"]="Musik";
$lang["youtube_publish_animals"]="Djur och husdjur";
$lang["youtube_publish_sports"]="Sport";
$lang["youtube_publish_travel"]="Resor och h&#228;ndelser";
$lang["youtube_publish_games"]="Spel";
$lang["youtube_publish_people"]="M&#228;nniskor och bloggar";
$lang["youtube_publish_comedy"]="Komedi";
$lang["youtube_publish_entertainment"]="N&#246;je";
$lang["youtube_publish_news"]="Nyheter och politik";
$lang["youtube_publish_howto"]="Instruktioner och stil";
$lang["youtube_publish_education"]="Utbildning";
$lang["youtube_publish_tech"]="Vetenskap och teknik";
$lang["youtube_publish_nonprofit"]="Ideellt arbete och aktivism";
