<?php
$_['heading_title'] = 'NewsMAN Remarketing';

// Text
$_['text_extension'] = 'Estensioni';
$_['text_success'] = 'Successo: Hai modificato NewsMAN Remarketing!';
$_['text_edit'] = 'Modifica NewsMAN Remarketing';
$_['text_signup'] = 'Accedi al tuo <a href="https://www.newsman.app/" target="_blank"><u>account NewsMAN</u></a> e ottieni il tuo ID';
$_['text_default'] = 'Predefinito';
$_['text_extension_version'] = 'Versione estensione Newsman';
$_['text_newsman_settings'] = 'Impostazioni Newsman';
$_['text_config_for_store'] = 'Configurazione per il negozio: %s (ID: %s)';
$_['text_store'] = 'Negozio';
$_['text_remarketing_id_status'] = 'Stato ID Remarketing';
$_['text_remarketing_id_valid'] = 'L\'ID Remarketing è valido';
$_['text_remarketing_id_invalid'] = 'L\'ID Remarketing non è valido o si è verificato un errore API temporaneo!';
$_['text_remarketing_id_hint'] = 'Stato della connessione con NewsMAN Remarketing';

// Entry
$_['entry_tracking'] = 'ID NewsMAN Remarketing';
$_['entry_status'] = 'Stato';
$_['entry_anonymize_ip'] = 'Anonimizza indirizzo IP';
$_['entry_send_telephone'] = 'Invia numero di telefono';
$_['entry_theme_cart_compatibility'] = 'Compatibilità Carrello Tema';
$_['entry_theme_cart_compatibility_help'] = 'Abilita per il rilevamento più affidabile delle modifiche al carrello su qualsiasi tema (utilizza polling in background e ascolta le richieste AJAX/fetch). Disabilita per utilizzare un meccanismo più leggero che legge il contenuto del carrello direttamente dal blocco minicart predefinito del tema OpenCart 4 (nessun polling in background, ma funziona solo se il tuo tema utilizza il blocco minicart standard <code>#cart</code>). Se disabiliti questa opzione, svuota la cache di OpenCart e poi utilizza lo strumento <strong>Check installation</strong> Remarketing di newsman.app per verificare che gli eventi del carrello siano rilevati correttamente.';
$_['entry_order_date'] = 'Data ordine minima';

// Theme compatibility
$_['entry_theme_event_inject'] = 'Modalità compatibilità tema';
$_['help_theme_event_inject'] = 'Abilita questa opzione se il tuo tema non visualizza gli script di remarketing. Quando abilitato, gli script di remarketing vengono iniettati tramite un evento OpenCart invece di affidarsi al tema per visualizzarli.';
$_['text_theme_event_inject_warning'] = 'Dopo aver modificato questa impostazione, controlla il codice sorgente della tua vetrina per verificare che gli script di remarketing appaiano esattamente una volta. Abilitare questa opzione su un tema che già visualizza gli analytics potrebbe causare script di remarketing duplicati.';

// Error
$_['error_permission'] = 'Attenzione: Non hai il permesso di modificare NewsMAN Remarketing!';
$_['error_code'] = 'ID di tracciamento richiesto!';
