<?php
/*
Plugin Name: Gobierno de Tamaulipas | Funcionalidad Tamaulipas
Plugin URI: http://www.tamaulipas.gob.mx
Description: Catalogo de shortcodes de Bootstrap 5 y funcionalidades para themes del Gobierno de Tamaulipas
Author: Departamento de Diseño de Interfaces Gráficas
Version: 1.2.2
*/


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