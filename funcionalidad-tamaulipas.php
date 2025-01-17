<?php
/*
Plugin Name: Gobierno de Tamaulipas | Funcionalidad Tamaulipas
Plugin URI: http://www.tamaulipas.gob.mx
Description: Catalogo de shortcodes de Bootstrap 5 y funcionalidades para themes del Gobierno de Tamaulipas
Author: Departamento de Diseño de Interfaces Gráficas
Version: 1.3.3.3
*/


class FuncionalidadTamaulipasUpdater {
	private $plugin_slug = 'funcionalidad-tamaulipas';
	private $update_url = 'https://raw.githubusercontent.com/desarrollowebtamaulipas/funcionalidad-tamaulipas/refs/heads/main/update.json';

	public function __construct() {
		add_filter('plugins_api', [$this, 'plugin_info'], 20, 3);
		add_filter('site_transient_update_plugins', [$this, 'check_for_updates']);
		add_action('upgrader_process_complete', [$this, 'clear_cache'], 10, 2);
	}

	public function plugin_info($res, $action, $args) {
		if ($action !== 'plugin_information' || $args->slug !== $this->plugin_slug) {
			return $res;
		}

		$remote = $this->get_remote_info();
		if (!$remote) {
			return $res;
		}

		$res = (object) [
			'name' => $remote['name'],
			'slug' => $this->plugin_slug,
			'version' => $remote['version'],
			'author' => 'Departamento de Diseño de Interfaces Gráficas',
			'download_link' => $remote['download_url'],
			'requires' => $remote['requires'],
			'tested' => $remote['tested'],
		];

		return $res;
	}

	public function check_for_updates($transient) {
		if (empty($transient->checked)) {
			return $transient;
		}

		$remote = $this->get_remote_info();
		if (!$remote || version_compare($remote['version'], $transient->checked[$this->plugin_slug . '/' . $this->plugin_slug . '.php'], '<=')) {
			return $transient;
		}

		$transient->response[$this->plugin_slug . '/' . $this->plugin_slug . '.php'] = (object) [
			'slug' => $this->plugin_slug,
			'new_version' => $remote['version'],
			'package' => $remote['download_url'],
		];

		return $transient;
	}

	public function clear_cache($upgrader, $options) {
		if ($options['action'] === 'update' && $options['type'] === 'plugin') {
			delete_transient($this->plugin_slug . '_update_info');
		}
	}

	private function get_remote_info() {
		$remote = get_transient($this->plugin_slug . '_update_info');
		if ($remote === false) {
			$response = wp_remote_get($this->update_url, ['timeout' => 10]);
			if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
				return false;
			}

			$remote = json_decode(wp_remote_retrieve_body($response), true);
			set_transient($this->plugin_slug . '_update_info', $remote, 12 * HOUR_IN_SECONDS);
		}

		return $remote;
	}
}

new FuncionalidadTamaulipasUpdater();



// Row
function bootstrap_row_shortcode($atts, $content = null) {
	// Atributos del Shortcode
	$atts = shortcode_atts(
		array(
			'xclass' => ''
		),
		$atts,
		'row'
	);

	// Estructura HTML del Row
	$output = '<div class="row ' . esc_attr($atts['xclass']) . '">' . do_shortcode($content) . '</div>';
	
	$output = apply_filters('the_content', $output);

	return $output;
}
add_shortcode('row', 'bootstrap_row_shortcode');


// Columnas
function bootstrap_col_shortcode($atts, $content = null) {
	// Atributos del Shortcode
	$atts = shortcode_atts(
		array(
			'size' => 'md-6', // Column size (e.g., col-sm-6)
			'offset' => false, // Column offset (e.g., offset-md-2)
			'order' => false, // Column order (e.g., order-lg-1)
			'xclass' => false // Additional classes for the column (optional)
		),
		$atts,
		'col'
	);

	// String de Atributos
	$size_class = '';
	$offset_class = '';
	$order_class = '';

	// Agrega solo si contiene
	if (!empty($atts['size'])) {
		$size_class = 'col-' . esc_attr($atts['size']);
	}
	if (!empty($atts['offset'])) {
		$offset_class = 'offset-' . esc_attr($atts['offset']);
	}
	if (!empty($atts['order'])) {
		$order_class = 'order-' . esc_attr($atts['order']);
	}

	// Junta todas las clases
	$classes = $size_class . ' ' . $offset_class . ' ' . $order_class . ' ' . esc_attr($atts['xclass']);

	// Estructura HTML de la Columna
	$output = '<div class="' . trim($classes) . '">' . do_shortcode($content) . '</div>';

	return $output;
}
add_shortcode('col', 'bootstrap_col_shortcode');



// Botones
function bootstrap_button_shortcode($atts, $content = null) {
	// Atributos del Shortcode
	$atts = shortcode_atts(
		array(
			'type' => 'primary',
			'link' => '#',
			'target' => false,
			'size' => 'md',
			'xclass' => false,
			'data' => false
		),
		$atts,
		'button'
	);

	// Revisa si tiene tamaño y añade el prefijo "btn-" si es necesario
	$size_class = '';
	if ($atts['size'] != '') {
		$size_class = 'btn-' . esc_attr($atts['size']);
	}

	// Añadir el atributo data solo si no está vacío
	$data_attr = '';
	if (!empty($atts['data'])) {
		$data_attr = ' data-' . esc_attr($atts['data']);
	}
	
	// Añadir el atributo target solo si no está vacío
	$target_attr = '';
	if (!empty($atts['target'])) {
		$target_attr = ' target="' . esc_attr($atts['target']) . '"';
	}

	// Generamos el HTML del botón con el contenido
	$output = '<a href="' . esc_url($atts['link']) . '" class="btn btn-' . esc_attr($atts['type']) . ' ' . $size_class . ' ' . esc_attr($atts['xclass']) . '"' . $data_attr . $target_attr . '>' . do_shortcode($content) . '</a>';
	
	return $output;
}

add_shortcode('button', 'bootstrap_button_shortcode');



// Grupo de botones
function bootstrap_button_group_shortcode($atts, $content = null) {
	// Atributos del Shortcode
	$atts = shortcode_atts(
		array(
			'vertical' => false,
			'size'	=>	'md',
			'xclass' => 'mb-4' // Clases adicionales
		),
		$atts,
		'button-group'
	);
	
	// Definir la clase de grupo de botones
	$btn_group = 'btn-group';
	
	// Si el atributo vertical es "true" o "1", añade la clase btn-group-vertical
	if ($atts['vertical'] === 'true' || $atts['vertical'] === '1') {
		$btn_group = ' btn-group-vertical';
	}
	
	// Generamos el HTML del grupo de botones
	$output = '<div class="clearfix ' . esc_attr($atts['xclass']) . '"><div class="' . esc_attr($btn_group) . ' btn-group-' . esc_attr($atts['size']) . '" role="group">';
	$output .= do_shortcode(shortcode_unautop($content));
	$output .= '</div></div>';
	
	return $output;
}

add_shortcode('button-group', 'bootstrap_button_group_shortcode');



// Acordiones
function bootstrap_accordion_shortcode($atts, $content = null) {
	// Atributos del Shortcode
	$atts = shortcode_atts(
		array(
			'id' => 'accordion',
			'xclass' => false,
		),
		$atts,
		'accordion'
	);

	// Generamos el HTML del acordeón
	$output = '<div class="accordion ' . esc_attr($atts['xclass']) . '" id="' . esc_attr($atts['id']) . '">';
	$output .= do_shortcode($content);
	$output .= '</div>';
	
	$output = apply_filters('the_content', $output);
	return $output;
}
add_shortcode('accordion', 'bootstrap_accordion_shortcode');

// Función para crear los items del acordeón
function bootstrap_accordion_item_shortcode($atts, $content = null) {
	// Parseamos los atributos de la shortcode
	$atts = shortcode_atts(
		array(
			'title' => 'Titulo',
			'parent' => false,
			'collapsed' => 'true',
		),
		$atts,
		'accordion-item'
	);
	
	$item_id = uniqid();
	
	// Determinamos el estado del acordeón
	$aria_expanded = ($atts['collapsed'] === 'true') ? 'false' : 'true';

	// Generamos el HTML del item del acordeón
	$output = '<div class="accordion-item">';
	$output .= '<h2 class="accordion-header" id="heading-' . $item_id . '">';
	$output .= '<button class="accordion-button' . ($atts['collapsed'] === 'true' ? ' collapsed' : '') . '" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-' . $item_id . '" aria-expanded="' . $aria_expanded . '" aria-controls="collapse-' . $item_id . '">';
	$output .= esc_html($atts['title']);
	$output .= '</button>';
	$output .= '</h2>';
	$output .= '<div id="collapse-' . $item_id . '" class="accordion-collapse collapse' . ($atts['collapsed'] === 'true' ? '' : ' show') . '" aria-labelledby="heading-' . $item_id . '" data-bs-parent="#' . esc_attr($atts['parent']) . '">';
	$output .= '<div class="accordion-body">' . do_shortcode($content) . '</div>';
	$output .= '</div>';
	$output .= '</div>';
	
	$output = apply_filters('the_content', $output);
	return $output;
}
add_shortcode('accordion-item', 'bootstrap_accordion_item_shortcode');



// Tablas
function table_bootstrap_shortcode($atts, $content = null) {
	// Atributos disponibles y valores por defecto
	$atts = shortcode_atts(
		array(
			'responsive' => false, // Valor por defecto de 'responsivo' es falso
			'xclass' => '', // Clases adicionales para la etiqueta <table>
			'striped' => false, // Si la tabla debe ser 'striped'
			'bordered' => false, // Si la tabla debe tener borde
			'hover' => false, // Si la tabla debe tener hover
			'small' => false, // Si la tabla debe ser pequeña
		),
		$atts,
		'table'
	);

	// Construir la clase de la tabla basada en los atributos
	$table_class = 'table';
	if ($atts['striped']) {
		$table_class .= ' table-striped';
	}
	if ($atts['bordered']) {
		$table_class .= ' table-bordered';
	}
	if ($atts['hover']) {
		$table_class .= ' table-hover';
	}
	if ($atts['small']) {
		$table_class .= ' table-sm';
	}
	// Agregar cualquier clase adicional especificada en el atributo 'class'
	if (!empty($atts['xclass'])) {
		$table_class .= ' ' . esc_attr($atts['xclass']);
	}

	// Comprobamos si se especificó el atributo responsivo
	if ($atts['responsive']) {
		// Si es responsivo, envolvemos la tabla en un div con la clase 'table-responsive'
		$tabla_con_bootstrap = '<div class="table-responsive">';
		// Buscar la etiqueta <table> dentro del contenido y agregar las clases necesarias
		$tabla_con_bootstrap .= preg_replace('/<table(.*?)>/i', '<table$1 class="' . $table_class . '">', do_shortcode($content));
		$tabla_con_bootstrap .= '</div>';
	} else {
		// Si no es responsivo, simplemente devolvemos la tabla sin cambios
		$tabla_con_bootstrap = preg_replace('/<table(.*?)>/i', '<table$1 class="' . $table_class . '">', do_shortcode($content));
	}

	// Devolvemos el contenido procesado
	$tabla_con_bootstrap = apply_filters('the_content', $tabla_con_bootstrap);
	return $tabla_con_bootstrap;
}
add_shortcode('table', 'table_bootstrap_shortcode');


// Visor de PDF
function pdfjs_custom_viewer_shortcode($atts) {
	// Extraer los atributos con valores por defecto
	$atts = shortcode_atts(
		array(
			'url' => '',        // URL del PDF
			'width' => '100%',  // Ancho por defecto
			'height' => '800px' // Alto por defecto
		),
		$atts,
		'pdf'
	);

	// Verificar que se haya proporcionado una URL
	if (empty($atts['url'])) {
		return 'Por favor, proporciona una URL válida del archivo PDF.';
	}

	// URL base del visor de PDF de Google
	$google_viewer_url = 'https://docs.google.com/viewer?url=';
	
	// Generar el HTML para el visor de PDF
	$output = '
	<iframe class="mt-4 mb-4" src="' . esc_url($google_viewer_url . esc_url($atts['url'])) . '&embedded=true" 
			width="' . esc_attr($atts['width']) . '" 
			height="' . esc_attr($atts['height']) . '" 
			style="border: none;"></iframe>';
	
	return $output;

}
add_shortcode('pdf', 'pdfjs_custom_viewer_shortcode');


// Alertas
function bootstrap_alert_shortcode($atts, $content = null) {
	// Atributos del Shortcode
	$atts = shortcode_atts(
		array(
			'type' => 'primary', // Tipo de alerta: primary, success, danger, etc.
			'dismissible' => 'false', // Si la alerta es descartable
			'xclass' => '' // Clases adicionales
		),
		$atts,
		'alert'
	);

	// Añadir la clase para las alertas descartables
	$dismissible_class = '';
	$dismiss_button = '';
	if ($atts['dismissible'] === 'true' || $atts['dismissible'] === '1') {
		$dismissible_class = ' alert-dismissible fade show';
		$dismiss_button = '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
	}

	// Generar el HTML de la alerta
	$output = '<div class="alert alert-' . esc_attr($atts['type']) . $dismissible_class . ' ' . esc_attr($atts['xclass']) . '" role="alert">';
	$output .= do_shortcode($content); // Añadir el contenido de la alerta
	$output .= $dismiss_button; // Añadir el botón de cerrar si es descartable
	$output .= '</div>';

	return $output;
}

add_shortcode('alert', 'bootstrap_alert_shortcode');