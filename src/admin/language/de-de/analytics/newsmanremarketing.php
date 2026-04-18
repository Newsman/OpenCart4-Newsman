<?php
$_['heading_title'] = 'NewsMAN Remarketing';

// Text
$_['text_extension'] = 'Erweiterungen';
$_['text_success'] = 'Erfolg: Sie haben NewsMAN Remarketing geändert!';
$_['text_edit'] = 'NewsMAN Remarketing bearbeiten';
$_['text_signup'] = 'Melden Sie sich bei Ihrem <a href="https://www.newsman.app/" target="_blank"><u>NewsMAN-Konto</u></a> an und erhalten Sie Ihre ID';
$_['text_default'] = 'Standard';
$_['text_extension_version'] = 'Newsman Erweiterungsversion';
$_['text_newsman_settings'] = 'Newsman Einstellungen';
$_['text_config_for_store'] = 'Konfiguration für Shop: %s (ID: %s)';
$_['text_store'] = 'Shop';
$_['text_remarketing_id_status'] = 'Remarketing-ID-Status';
$_['text_remarketing_id_valid'] = 'Remarketing-ID ist gültig';
$_['text_remarketing_id_invalid'] = 'Remarketing-ID ist ungültig oder es liegt ein vorübergehender API-Fehler vor!';
$_['text_remarketing_id_hint'] = 'Verbindungsstatus mit NewsMAN Remarketing';

// Entry
$_['entry_tracking'] = 'NewsMAN Remarketing-ID';
$_['entry_status'] = 'Status';
$_['entry_anonymize_ip'] = 'IP-Adresse anonymisieren';
$_['entry_send_telephone'] = 'Telefonnummer senden';
$_['entry_theme_cart_compatibility'] = 'Warenkorb-Theme-Kompatibilität';
$_['entry_theme_cart_compatibility_help'] = 'Aktivieren Sie diese Option für die zuverlässigste Erkennung von Warenkorbänderungen in jedem Theme (verwendet Hintergrund-Polling und überwacht AJAX/Fetch-Anfragen). Deaktivieren Sie sie, um einen leichteren Mechanismus zu verwenden, der den Warenkorbinhalt direkt aus dem Standard-Minicart-Block des OpenCart 4-Themes liest (kein Hintergrund-Polling, funktioniert jedoch nur, wenn Ihr Theme den Standard-Minicart-Block <code>#cart</code> verwendet). Wenn Sie diese Option deaktivieren, leeren Sie den OpenCart-Cache und verwenden Sie dann das Tool <strong>Check installation</strong> Remarketing von newsman.app, um zu überprüfen, ob die Warenkorb-Ereignisse korrekt erkannt werden.';
$_['entry_order_date'] = 'Mindestbestelldatum';

// Theme compatibility
$_['entry_theme_event_inject'] = 'Theme-Kompatibilitätsmodus';
$_['help_theme_event_inject'] = 'Aktivieren Sie diese Option, wenn Ihr Theme die Remarketing-Skripte nicht rendert. Wenn aktiviert, werden die Remarketing-Skripte über ein OpenCart-Event eingefügt, anstatt sich darauf zu verlassen, dass das Theme sie ausgibt.';
$_['text_theme_event_inject_warning'] = 'Überprüfen Sie nach dem Ändern dieser Einstellung den Quellcode Ihrer Storefront, um sicherzustellen, dass die Remarketing-Skripte genau einmal erscheinen. Das Aktivieren dieser Option bei einem Theme, das bereits Analytics rendert, kann zu doppelten Remarketing-Skripten führen.';

// Error
$_['error_permission'] = 'Warnung: Sie haben keine Berechtigung, NewsMAN Remarketing zu ändern!';
$_['error_code'] = 'Tracking-ID erforderlich!';
