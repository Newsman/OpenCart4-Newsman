<?php
$_['heading_title'] = 'NewsMAN Remarketing';

// Text
$_['text_extension'] = 'Tillägg';
$_['text_success'] = 'Klart: Du har ändrat NewsMAN Remarketing!';
$_['text_edit'] = 'Redigera NewsMAN Remarketing';
$_['text_signup'] = 'Logga in på ditt <a href="https://www.newsman.app/" target="_blank"><u>NewsMAN-konto</u></a> och hämta ditt ID';
$_['text_default'] = 'Standard';
$_['text_extension_version'] = 'Newsman tilläggsversion';
$_['text_newsman_settings'] = 'Newsman inställningar';
$_['text_config_for_store'] = 'Konfiguration för butik: %s (ID: %s)';
$_['text_store'] = 'Butik';
$_['text_remarketing_id_status'] = 'Remarketing-ID-status';
$_['text_remarketing_id_valid'] = 'Remarketing-ID är giltigt';
$_['text_remarketing_id_invalid'] = 'Remarketing-ID är ogiltigt eller så finns det ett tillfälligt API-fel!';
$_['text_remarketing_id_hint'] = 'Anslutningsstatus med NewsMAN Remarketing';

// Entry
$_['entry_tracking'] = 'NewsMAN Remarketing-ID';
$_['entry_status'] = 'Status';
$_['entry_anonymize_ip'] = 'Anonymisera IP-adress';
$_['entry_send_telephone'] = 'Skicka telefonnummer';
$_['entry_theme_cart_compatibility'] = 'Temakompatibilitet Varukorg';
$_['entry_theme_cart_compatibility_help'] = 'Aktivera för den mest tillförlitliga detekteringen av varukorgsändringar i alla teman (använder bakgrundspolling och lyssnar på AJAX/fetch-förfrågningar). Inaktivera för att använda en lättare mekanism som läser varukorgsinnehållet direkt från OpenCart 4-temats standard-minicart-block (ingen bakgrundspolling, men fungerar bara om ditt tema använder standard-minicart-blocket <code>#cart</code>). Om du inaktiverar detta alternativ, rensa OpenCart-cachen och använd sedan verktyget <strong>Check installation</strong> Remarketing från newsman.app för att verifiera att varukorgens händelser detekteras korrekt.';
$_['entry_order_date'] = 'Minsta orderdatum';

// Theme compatibility
$_['entry_theme_event_inject'] = 'Temakompatibilitetsläge';
$_['help_theme_event_inject'] = 'Aktivera detta om ditt tema inte visar remarketing-skripten. När det är aktiverat injiceras remarketing-skripten via en OpenCart-händelse istället för att förlita sig på att temat visar dem.';
$_['text_theme_event_inject_warning'] = 'Efter att ha ändrat denna inställning, kontrollera källkoden på din butikssida för att verifiera att remarketing-skripten visas exakt en gång. Om du aktiverar detta på ett tema som redan visar analytics kan det resultera i dubbla remarketing-skript.';

// Error
$_['error_permission'] = 'Varning: Du har inte behörighet att ändra NewsMAN Remarketing!';
$_['error_code'] = 'Spårnings-ID krävs!';
