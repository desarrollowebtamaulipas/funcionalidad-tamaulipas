<?php
/*
Plugin Name: Gobierno de Tamaulipas | Funcionalidad Tamaulipas
Plugin URI: http://www.tamaulipas.gob.mx
Description: Catalogo de shortcodes de Bootstrap 5 y funcionalidades para themes del Gobierno de Tamaulipas
Author: Departamento de Diseño de Interfaces Gráficas
Version: 1.5
*/


// Actualización a través de Github
class FuncionalidadTamaulipasUpdater {
	private $plugin_slug = 'funcionalidad-tamaulipas-main';
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
	$output = '<div class="row ' . esc_attr($atts['xclass']) . '">' . do_shortcode(shortcode_unautop($content)) . '</div>';
	
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
	$size_class = !empty($atts['size']) ? 'col-' . esc_attr($atts['size']) : '';
	$offset_class = !empty($atts['offset']) ? 'offset-' . esc_attr($atts['offset']) : '';
	$order_class = !empty($atts['order']) ? 'order-' . esc_attr($atts['order']) : '';

	// Junta todas las clases
	$classes = trim("$size_class $offset_class $order_class " . esc_attr($atts['xclass']));

	// Aplicamos shortcode_unautop solo si está anidado dentro de row
	if (has_filter('the_content', 'wpautop')) {
		$content = shortcode_unautop($content);
	}

	// Estructura HTML de la Columna
	$output = '<div class="' . esc_attr($classes) . '">' . do_shortcode($content) . '</div>';
	
	$output = apply_filters('the_content', $output);
	return $output;
}
add_shortcode('col', 'bootstrap_col_shortcode');



// Botones
function bootstrap_button_shortcode($atts, $content = null) {
	// Atributos del Shortcode
	$atts = shortcode_atts(
		array(
			'type' => 'primary',  // Color del botón
			'link' => '#',        // Enlace del botón
			'target' => '',       // Atributo target (_blank, _self, etc.)
			'size' => '',         // Tamaño (lg, sm, etc.)
			'xclass' => '',       // Clases adicionales
			'data' => ''          // Atributos data personalizados
		),
		$atts,
		'button'
	);

	// Asegurar que el tipo siempre tenga un valor
	$type_class = !empty($atts['type']) ? 'btn-' . esc_attr($atts['type']) : 'btn-primary';

	// Definir la clase de tamaño si se especifica
	$size_class = !empty($atts['size']) ? 'btn-' . esc_attr($atts['size']) : '';

	// Generar el atributo data si se especifica
	$data_attr = !empty($atts['data']) ? ' data-' . esc_attr($atts['data']) : '';

	// Generar el atributo target si se especifica
	$target_attr = !empty($atts['target']) ? ' target="' . esc_attr($atts['target']) . '"' : '';

	// Generamos el HTML del botón con el contenido
	$output = '<a href="' . esc_url($atts['link']) . '" class="btn ' . $type_class . ' ' . $size_class . ' ' . esc_attr($atts['xclass']) . '"' . $data_attr . $target_attr . '>' . do_shortcode($content) . '</a>';
	
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



// Tabs
function bootstrap_tabs_shortcode($atts, $content = null) {
	static $tabs_instance = 0;
	$tabs_instance++;

	// Atributos del contenedor
	$atts = shortcode_atts(array(
		'pill'  => 'false',
		'xclass' => '',
	), $atts);

	$is_pill = filter_var($atts['pill'], FILTER_VALIDATE_BOOLEAN);

	$nav_class = $is_pill ? 'nav-pills' : 'nav-tabs';
	$nav_class .= ' ' . esc_attr($atts['xclass']);

	// Limpieza wpautop
	$content = shortcode_unautop($content);
	$content = trim($content);

	// Extraer tabs
	preg_match_all('/\[tab([^\]]*)\](.*?)\[\/tab\]/s', $content, $matches);

	if (empty($matches[0])) return '';

	$tabs_nav = '';
	$tabs_content = '';
	$has_active = false;

	foreach ($matches[1] as $i => $tab_atts) {
		$tab_atts = shortcode_parse_atts($tab_atts);

		$title = $tab_atts['title'] ?? 'Tab ' . ($i + 1);
		$is_active = isset($tab_atts['active']) && $tab_atts['active'] === 'true';

		if ($is_active) {
			$has_active = true;
		}

		$tab_id = 'bs-tab-' . $tabs_instance . '-' . $i;
		$active = $is_active ? 'active' : '';
		$show   = $is_active ? 'show active' : '';

		// Nav
		$tabs_nav .= '
		<li class="nav-item" role="presentation">
			<button class="nav-link ' . $active . '" 
				id="' . $tab_id . '-tab"
				data-bs-toggle="tab"
				data-bs-target="#' . $tab_id . '"
				type="button"
				role="tab">
				' . esc_html($title) . '
			</button>
		</li>';

		// Content
		$tabs_content .= '
		<div class="tab-pane fade ' . $show . '" 
			id="' . $tab_id . '" 
			role="tabpanel">
			' . do_shortcode($matches[2][$i]) . '
		</div>';
	}

	// Si ningún tab tiene active="true", activa el primero
	if (!$has_active) {
		$tabs_nav = preg_replace('/nav-link/', 'nav-link active', $tabs_nav, 1);
		$tabs_content = preg_replace(
			'/tab-pane fade/',
			'tab-pane fade show active',
			$tabs_content,
			1
		);
	}

	return '
	<div class="tabs">
		<ul class="nav ' . $nav_class . '" role="tablist">
			' . $tabs_nav . '
		</ul>
		<div class="tab-content pt-2 pb-5 px-3">
			' . $tabs_content . '
		</div>
	</div>';
}
add_shortcode('tabs', 'bootstrap_tabs_shortcode');

// Tab individual (no renderiza nada)
function bootstrap_tab_shortcode() {
	return '';
}
add_shortcode('tab', 'bootstrap_tab_shortcode');




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



// Datatable
function datatable_shortcode($atts, $content = null) {
	static $datatable_counter = 0;
	$datatable_counter++;

	$atts = shortcode_atts([
		'csv'  => '',
		'id'   => 'datatable-' . $datatable_counter,
		'class'=> 'table table-striped table-bordered align-middle'
	], $atts, 'datatable');

	// Si viene CSV → tabla vacía
	if (!empty($atts['csv'])) {
		return '<table id="'.esc_attr($atts['id']).'" 
					 class="'.esc_attr($atts['class']).'" 
					 data-csv="'.esc_url($atts['csv']).'"></table>';
	}

	// Si NO hay CSV → usa tabla HTML envuelta
	$content = do_shortcode($content);
	$content = preg_replace(
		'/<table(.*?)>/i',
		'<table$1 id="'.esc_attr($atts['id']).'" class="'.esc_attr($atts['class']).'">',
		$content
	);

	return $content;
}
add_shortcode('datatable', 'datatable_shortcode');


function datatable_enqueue_scripts() {
	if (!is_singular()) return;

	global $post;
	if (!has_shortcode($post->post_content, 'datatable')) return;

	wp_enqueue_style( 'datatables-bs5', 'https://cdn.datatables.net/v/bs5/dt-2.3.5/r-3.0.7/datatables.min.css', [], null );
	wp_enqueue_script( 'datatables-bs5', 'https://cdn.datatables.net/v/bs5/dt-2.3.5/r-3.0.7/datatables.min.js', ['jquery'], null, true );

	// Script personalizado
	$js = <<<JS
		jQuery(window).on('load', function() {
		
			// === PARSER CSV (soporta comillas y comas internas) ===
			function parseCSV(text) {
				const rows = [];
				let row = [];
				let cell = '';
				let insideQuotes = false;
		
				for (let i = 0; i < text.length; i++) {
					const char = text[i];
					const next = text[i+1];
		
					if (char === '"' && insideQuotes && next === '"') {
						cell += '"'; i++;
					} else if (char === '"') {
						insideQuotes = !insideQuotes;
					} else if (char === ',' && !insideQuotes) {
						row.push(cell); cell = '';
					} else if ((char === '\\n' || char === '\\r') && !insideQuotes) {
						if (cell !== '' || row.length > 0) {
							row.push(cell); rows.push(row);
						}
						row = []; cell = '';
					} else {
						cell += char;
					}
				}
				if (cell !== '' || row.length > 0) {
					row.push(cell); rows.push(row);
				}
				return rows;
			}
		
			// === TABLAS DESDE CSV ===
			jQuery('table[data-csv]').each(function() {
				let table = jQuery(this);
				let url   = table.data('csv');
		
				fetch(url).then(res => res.text()).then(csv => {
					let rows = parseCSV(csv);
		
					// Construir thead
					let thead = '<thead><tr>';
					rows[0].forEach(col => thead += '<th>' + col + '</th>');
					thead += '</tr></thead>';
		
					// Construir tbody
					let tbody = '<tbody>';
					for (let i = 1; i < rows.length; i++) {
						tbody += '<tr>';
						rows[i].forEach(col => tbody += '<td>' + col + '</td>');
						tbody += '</tr>';
					}
					tbody += '</tbody>';
		
					table.html(thead + tbody);
		
					// Activar DataTable
					new DataTable(table[0], {
						responsive: true,
						paging: true,
						searching: true,
						ordering: true,
						info: true,
						pagingType: 'simple_numbers',
						layout: {
							topStart: 'pageLength',
							topEnd: 'search',
							bottomStart: 'info',
							bottomEnd: 'paging'
						},
						language: {
							url: 'https://cdn.datatables.net/plug-ins/2.0.3/i18n/es-ES.json'
						}
					});
				});
			});
		
			// === TABLAS HTML NORMALES ===
			jQuery('table[id^="datatable-"]').not('[data-csv]').each(function() {
				new DataTable(this, {
					responsive: true,
					paging: true,
					searching: true,
					ordering: true,
					info: true,
					pagingType: 'simple_numbers',
					layout: {
						topStart: 'pageLength',
						topEnd: 'search',
						bottomStart: 'info',
						bottomEnd: 'paging'
					},
					language: {
						url: 'https://cdn.datatables.net/plug-ins/2.0.3/i18n/es-ES.json'
					}
				});
			});
		
		});
		JS;

	wp_add_inline_script('datatables-bs5', $js);
}
add_action('wp_enqueue_scripts', 'datatable_enqueue_scripts');
