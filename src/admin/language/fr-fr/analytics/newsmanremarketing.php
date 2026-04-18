<?php
$_['heading_title'] = 'NewsMAN Remarketing';

// Text
$_['text_extension'] = 'Extensions';
$_['text_success'] = 'Succès : Vous avez modifié NewsMAN Remarketing !';
$_['text_edit'] = 'Modifier NewsMAN Remarketing';
$_['text_signup'] = 'Connectez-vous à votre <a href="https://www.newsman.app/" target="_blank"><u>compte NewsMAN</u></a> et obtenez votre ID';
$_['text_default'] = 'Par défaut';
$_['text_extension_version'] = 'Version de l\'extension Newsman';
$_['text_newsman_settings'] = 'Paramètres Newsman';
$_['text_config_for_store'] = 'Configuration pour le magasin : %s (ID : %s)';
$_['text_store'] = 'Magasin';
$_['text_remarketing_id_status'] = 'Statut de l\'ID Remarketing';
$_['text_remarketing_id_valid'] = 'L\'ID Remarketing est valide';
$_['text_remarketing_id_invalid'] = 'L\'ID Remarketing est invalide ou il y a une erreur API temporaire !';
$_['text_remarketing_id_hint'] = 'Statut de la connexion avec NewsMAN Remarketing';

// Entry
$_['entry_tracking'] = 'ID NewsMAN Remarketing';
$_['entry_status'] = 'Statut';
$_['entry_anonymize_ip'] = 'Anonymiser l\'adresse IP';
$_['entry_send_telephone'] = 'Envoyer le numéro de téléphone';
$_['entry_theme_cart_compatibility'] = 'Compatibilité du panier avec le thème';
$_['entry_theme_cart_compatibility_help'] = 'Activez cette option pour la détection la plus fiable des modifications du panier dans n\'importe quel thème (utilise le polling en arrière-plan et écoute les requêtes AJAX/fetch). Désactivez pour utiliser un mécanisme plus léger qui lit le contenu du panier directement à partir du bloc minicart par défaut du thème OpenCart 4 (pas de polling en arrière-plan, mais ne fonctionne que si votre thème utilise le bloc minicart standard <code>#cart</code>). Si vous désactivez cette option, videz le cache OpenCart puis utilisez l\'outil <strong>Check installation</strong> Remarketing de newsman.app pour vérifier que les événements du panier sont détectés correctement.';
$_['entry_order_date'] = 'Date de commande minimale';

// Theme compatibility
$_['entry_theme_event_inject'] = 'Mode de compatibilité du thème';
$_['help_theme_event_inject'] = 'Activez cette option si votre thème ne rend pas les scripts de remarketing. Lorsque cette option est activée, les scripts de remarketing sont injectés via un événement OpenCart au lieu de compter sur le thème pour les afficher.';
$_['text_theme_event_inject_warning'] = 'Après avoir modifié ce paramètre, vérifiez le code source de votre vitrine pour vous assurer que les scripts de remarketing apparaissent exactement une fois. L\'activation de cette option sur un thème qui affiche déjà les analytics peut entraîner des scripts de remarketing en double.';

// Error
$_['error_permission'] = 'Attention : Vous n\'avez pas la permission de modifier NewsMAN Remarketing !';
$_['error_code'] = 'L\'ID de suivi est requis !';
