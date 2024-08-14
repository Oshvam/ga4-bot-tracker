<?php
/*
Plugin Name: GA4 Bot Tracker
Description: Plugin para rastrear bots de Google utilizando la API Measurement de GA4.
Version: 1.0.1
Tested up to: 6.1
Author: Jose Ángel Martínez Díaz
Author URI: https://www.linkedin.com/in/joseangelmartinezdiaz/
*/

// Esta parte registra el menú de configuración en el lateral
add_action('admin_menu', 'ga4_bot_tracker_menu');

function ga4_bot_tracker_menu() {
    add_menu_page(
        'GA4 Bot Tracker Settings',
        'GA4 Bot Tracker',
        'manage_options',
        'ga4-bot-tracker',
        'ga4_bot_tracker_settings_page',
        'dashicons-chart-bar'
    );
}

// Página de configuración
function ga4_bot_tracker_settings_page() {
    ?>
    <div class="wrap">
        <h1>GA4 Bot Tracker Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('ga4_bot_tracker_options');
            do_settings_sections('ga4-bot-tracker');
            submit_button();
            ?>
        </form>
		<hr>
        <h3>Disclaimer</h3><p>Este plugin está en una fase muy temprana de su desarrollo, por lo que es muy posible que falle o registre errores. Los datos de Googlebot no son 100% exactos debido a diferentes casuísticas. Si tienes un sitio con pocas visitas, posiblemente tardes en ver datos en la propiedad.</p>
		<p>No atiendo peticiones sobre el plugin, puedes avisar de errores o proponer mejoras en el repositorio oficial de <strong><a href="https://github.com/Oshvam/ga4-bot-tracker/tree/main"target="_blank">GitHub</a></strong>.</p>
		<p>Si te gusta mi trabajo, por favor, considera seguirme en <strong><a href="https://www.linkedin.com/in/joseangelmartinezdiaz/" target="_blank">LinkedIn</a><strong> o </strong><a href="https://x.com/elotroseo" target="_blank">X (Antes Twitter)</a><strong>.
    </div>
    <?php
}

// Registrar configuraciones
add_action('admin_init', 'ga4_bot_tracker_settings_init');

function ga4_bot_tracker_settings_init() {
    register_setting('ga4_bot_tracker_options', 'ga4_bot_tracker_options');

    add_settings_section(
        'ga4_bot_tracker_section',
        'Configuración del plugin',
        'ga4_bot_tracker_section_callback',
        'ga4-bot-tracker'
    );

    add_settings_field(
        'ga4_bot_tracker_measurement_id',
        'Measurement ID',
        'ga4_bot_tracker_measurement_id_render',
        'ga4-bot-tracker',
        'ga4_bot_tracker_section'
    );

    add_settings_field(
        'ga4_bot_tracker_api_secret',
        'API Secret',
        'ga4_bot_tracker_api_secret_render',
        'ga4-bot-tracker',
        'ga4_bot_tracker_section'
    );

    add_settings_field(
        'ga4_bot_tracker_client_id',
        'Client ID',
        'ga4_bot_tracker_client_id_render',
        'ga4-bot-tracker',
        'ga4_bot_tracker_section'
    );
}

    echo '<img src="https://developers.google.com/static/analytics/images/terms/lockup_ic_Analytics_horiz_272px_clr.png?hl=es-419" width="150px"><hr><p>Para usar este plugin necesitarás las claves de la API Measurement Protocol de GA4. Antes de continuar, necesitarás crear una propiedad independiente, <strong>bajo ningún concepto uses tu propiedad de GA4 principal</strong>, estos datos se guardan en una propiedad por separado. Si no sabes hacerlo, puedes crear una propiedad siguiendo las instrucciones de <strong><a href="https://support.google.com/analytics/answer/9744165?hl=es#upgrade&zippy=%2Csecciones-de-este-art%C3%ADculo" target=_blank">este enlace</a></strong>.</p><p>Además, tendrás que crear las claves API de Measurement Protocol, puedes aprender a obtenerlas desde la documentación oficial de Google Analitycs en <strong><a href="https://developers.google.com/analytics/devguides/collection/protocol/ga4/reference?hl=es&client_type=firebase" target="_blank">este enlace</a></strong>. Si aún así tienes  dudas, pulsa <strong><a href="https://github.com/Oshvam/ga4-bot-tracker/blob/main/img/measurement_id_tutorial.png" target="_blank">en este enlace</a></strong> para ver una imágen de donde debes sacar las claves gratuitas.</p><p>El plugin filtra por User Agent y además, para evitar falsos positivos, también filtra por las IP oficiales de Google que puedes consultar en <strong><a href="https://developers.google.com/search/apis/ipranges/googlebot.json" target="_blank">este enlace</a></strong>.</p>';
function ga4_bot_tracker_section_callback() {
}

function ga4_bot_tracker_measurement_id_render() {
    $options = get_option('ga4_bot_tracker_options');
    ?>
    <input type='text' name='ga4_bot_tracker_options[ga4_bot_tracker_measurement_id]' value='<?php echo $options['ga4_bot_tracker_measurement_id']; ?>'>
    <?php
}

function ga4_bot_tracker_api_secret_render() {
    $options = get_option('ga4_bot_tracker_options');
    ?>
    <input type='text' name='ga4_bot_tracker_options[ga4_bot_tracker_api_secret]' value='<?php echo $options['ga4_bot_tracker_api_secret']; ?>'>
    <?php
}

function ga4_bot_tracker_client_id_render() {
    $options = get_option('ga4_bot_tracker_options');
    ?>
    <input type='text' name='ga4_bot_tracker_options[ga4_bot_tracker_client_id]' value='<?php echo $options['ga4_bot_tracker_client_id']; ?>'>
    <?php
}

// Enganchar la función de seguimiento al footer (debajo del cierre de la etiqueta body)
add_action('wp_footer', 'ga4_bot_tracker_execute');

function ga4_bot_tracker_execute() {
    $options = get_option('ga4_bot_tracker_options');
    $measurement_id = $options['ga4_bot_tracker_measurement_id'];
    $api_secret = $options['ga4_bot_tracker_api_secret'];
    $client_id = $options['ga4_bot_tracker_client_id'];

    // Rango de IPs conocidas de Googlebot (https://developers.google.com/search/apis/ipranges/googlebot.json)
    $googlebotIpRanges = [
        '66.249.64.0/19',
		'66.249.64.0/27',
        '66.249.64.128/27',
        '66.249.64.160/27',
        '66.249.64.224/27',
        '66.249.64.32/27',
        '66.249.64.64/27',
        '66.249.64.96/27',
        '66.249.65.0/27',
        '66.249.65.160/27',
        '66.249.65.192/27',
        '66.249.65.224/27',
        '66.249.65.32/27',
        '66.249.65.64/27',
        '66.249.65.96/27',
        '66.249.66.0/27',
        '66.249.66.160/27',
        '66.249.66.192/27',
        '66.249.66.32/27',
        '66.249.66.64/27',
        '66.249.66.96/27',
        '66.249.68.0/27',
        '66.249.68.32/27',
        '66.249.68.64/27',
        '66.249.69.0/27',
        '66.249.69.128/27',
        '66.249.69.160/27',
        '66.249.69.192/27',
        '66.249.69.224/27',
        '66.249.69.32/27',
        '66.249.69.64/27',
        '66.249.69.96/27',
        '66.249.70.0/27',
        '66.249.70.128/27',
        '66.249.70.160/27',
        '66.249.70.192/27',
        '66.249.70.224/27',
        '66.249.70.32/27',
        '66.249.70.64/27',
        '66.249.70.96/27',
        '66.249.71.0/27',
        '66.249.71.128/27',
        '66.249.71.160/27',
        '66.249.71.192/27',
        '66.249.71.224/27',
        '66.249.71.32/27',
        '66.249.71.64/27',
        '66.249.71.96/27',
        '66.249.72.0/27',
        '66.249.72.128/27',
        '66.249.72.160/27',
        '66.249.72.192/27',
        '66.249.72.224/27',
        '66.249.72.32/27',
        '66.249.72.64/27',
        '66.249.72.96/27',
        '66.249.73.0/27',
        '66.249.73.128/27',
        '66.249.73.160/27',
        '66.249.73.192/27',
        '66.249.73.224/27',
        '66.249.73.32/27',
        '66.249.73.64/27',
        '66.249.73.96/27',
        '66.249.74.0/27',
    ];

    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $remoteAddr = $_SERVER['REMOTE_ADDR'];

    if (isGooglebot($userAgent) && isIpInRange($remoteAddr, $googlebotIpRanges)) {
        // Inicia el tiempo de la visita
        $start_time = microtime(true);

        // Envía el evento session_start
        $data = array(
            'client_id' => $client_id,
            'events' => array(
                array(
                    'name' => 'session_start',
                    'params' => array(
                        'user_agent' => $userAgent,
                        'ip_override' => $remoteAddr
                    ),
                ),
            ),
        );

        $url = 'https://www.google-analytics.com/mp/collect?measurement_id=' . $measurement_id . '&api_secret=' . $api_secret;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if ($response === false) {
            error_log('Error de cURL: ' . curl_error($ch));
        } else {
            error_log('Respuesta de GA4 (session_start): ' . $response);
        }
        curl_close($ch);

        // Calcula el tiempo de interacción en milisegundos
        $end_time = microtime(true);
        $engagement_time_msec = round(($end_time - $start_time) * 1000);

        // Envía los eventos page_view y bot_engagement
        $data = array(
            'client_id' => $client_id,
            'events' => array(
                array(
                    'name' => 'page_view',
                    'params' => array(
                        'page_location' => $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                        'page_referrer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
                        'page_title' => wp_title('', false),
                        'user_agent' => $userAgent,
                        'ip_override' => $remoteAddr
                    ),
                ),
                array(
                    'name' => 'bot_engagement',
                    'params' => array(
                        'engagement_time_msec' => $engagement_time_msec
                    ),
                ),
            ),
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if ($response === false) {
            error_log('Error de cURL: ' . curl_error($ch));
        } else {
            error_log('Respuesta de GA4: ' . $response);
        }
        curl_close($ch);
    }
}

function isGooglebot($userAgent) {
    $botPattern = "/\b(Googlebot|bingbot|Googlebot-News|Googlebot-Image|Googlebot-Video)\b/i";
    return preg_match($botPattern, $userAgent);
}

function isIpInRange($ip, $ranges) {
    foreach ($ranges as $range) {
        list($subnet, $bits) = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;
        if (($ip & $mask) == $subnet) {
            return true;
        }
    }
    return false;
}

?>
