<?php
$_['heading_title'] = 'NewsMAN Remarketing';

// Text
$_['text_extension'] = 'Extensiones';
$_['text_success'] = '¡Éxito: Ha modificado NewsMAN Remarketing!';
$_['text_edit'] = 'Editar NewsMAN Remarketing';
$_['text_signup'] = 'Inicie sesión en su <a href="https://www.newsman.app/" target="_blank"><u>cuenta NewsMAN</u></a> y obtenga su ID';
$_['text_default'] = 'Predeterminado';
$_['text_extension_version'] = 'Versión de la extensión Newsman';
$_['text_newsman_settings'] = 'Configuración de Newsman';
$_['text_config_for_store'] = 'Configuración para la tienda: %s (ID: %s)';
$_['text_store'] = 'Tienda';
$_['text_remarketing_id_status'] = 'Estado del ID de Remarketing';
$_['text_remarketing_id_valid'] = 'El ID de Remarketing es válido';
$_['text_remarketing_id_invalid'] = '¡El ID de Remarketing es inválido o hay un error temporal de API!';
$_['text_remarketing_id_hint'] = 'Estado de la conexión con NewsMAN Remarketing';

// Entry
$_['entry_tracking'] = 'ID de NewsMAN Remarketing';
$_['entry_status'] = 'Estado';
$_['entry_anonymize_ip'] = 'Anonimizar dirección IP';
$_['entry_send_telephone'] = 'Enviar número de teléfono';
$_['entry_theme_cart_compatibility'] = 'Compatibilidad del carrito con el tema';
$_['entry_theme_cart_compatibility_help'] = 'Active esta opción para la detección más fiable de cambios en el carrito en cualquier tema (utiliza polling en segundo plano y escucha las solicitudes AJAX/fetch). Desactive para utilizar un mecanismo más ligero que lee el contenido del carrito directamente desde el bloque de minicart predeterminado del tema OpenCart 4 (sin polling en segundo plano, pero solo funciona si su tema utiliza el bloque de minicart estándar <code>#cart</code>). Si desactiva esta opción, vacíe la caché de OpenCart y luego utilice la herramienta <strong>Check installation</strong> Remarketing de newsman.app para verificar que los eventos del carrito se detectan correctamente.';
$_['entry_order_date'] = 'Fecha mínima de pedido';

// Theme compatibility
$_['entry_theme_event_inject'] = 'Modo de compatibilidad del tema';
$_['help_theme_event_inject'] = 'Active esta opción si su tema no muestra los scripts de remarketing. Cuando está activado, los scripts de remarketing se inyectan a través de un evento de OpenCart en lugar de depender del tema para mostrarlos.';
$_['text_theme_event_inject_warning'] = 'Después de cambiar esta configuración, verifique el código fuente de su tienda para confirmar que los scripts de remarketing aparecen exactamente una vez. Activar esto en un tema que ya muestra analytics puede resultar en scripts de remarketing duplicados.';

// Error
$_['error_permission'] = '¡Advertencia: No tiene permiso para modificar NewsMAN Remarketing!';
$_['error_code'] = '¡Se requiere el ID de seguimiento!';
