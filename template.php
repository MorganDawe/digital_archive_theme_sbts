<?php
/**
 * @file
 * Contains the theme's functions to manipulate Drupal's default markup.
 *
 * Complete documentation for this file is available online.
 * @see https://drupal.org/node/1728096
 */

/**
 * Implements hook_form_alter().
 */
function digital_archive_theme_form_islandora_collection_search_form_alter(&$form, &$form_state, $form_id) {
  $label = array(
    '#markup' => "<h1>" . t("Search") . "</h1>",
    '#weight' => -1,
  );
  $for = array(
    '#markup' => "<h1>" . t("For") . "</h1>",
    '#weight' => 0,
  );
  $form['simple']['search_label'] = $label;
  $form['simple']['search_form'] = $for;
}

/**
 * Implements hook_form_alter().
 */
function digital_archive_theme_form_islandora_solr_simple_search_form_alter(&$form, &$form_state, $form_id) {
  $form['simple']['islandora_simple_search_query']['#attributes']['size'] = 30;
  $form['simple']['islandora_simple_search_query']['#title_display'] = 'invisible';
}

function digital_archive_theme_menu_local_tasks_alter(&$data, $router_item, $root_path) {
  //http://drupal.stackexchange.com/questions/87404/how-to-style-menu-local-tasks
  // hack job way of doing this, but it works.
  if (isset($data['tabs']) && count($data['tabs'])) {
    $output = &$data['tabs'][0]['output'];
    foreach ($output as $key => $value) {
      $tab = &$data['tabs'][0]['output'][$key];
      if ($value['#link']['title'] == "View") {
        $tab['#link']['localized_options']['attributes']['class'][] = 'my-eye';
      } else if ($value['#link']['title'] == "Edit") {
        $tab['#link']['localized_options']['attributes']['class'][] = 'menu-edit';
      } else if ($value['#link']['title'] == "Node export") {
        $tab['#link']['localized_options']['attributes']['class'][] = 'node-export';
      } else if ($value['#link']['title'] == "Devel") {
        $tab['#link']['localized_options']['attributes']['class'][] = 'devel-link';
      } else if ($value['#link']['title'] == "MARCXML") {
        $tab['#link']['localized_options']['attributes']['class'][] = 'code-icon';
      } else if ($value['#link']['title'] == "Manage") {
        $tab['#link']['localized_options']['attributes']['class'][] = 'icn-gears';
      } else if ($value['#link']['title'] == "IP Embargo") {
        $tab['#link']['localized_options']['attributes']['class'][] = 'map-signs';
      } else if ($value['#link']['title'] == "Pages") {
        $tab['#link']['localized_options']['attributes']['class'][] = 'menu-pages';
      }  else if ($value['#link']['title'] == "Document") {
        $tab['#link']['localized_options']['attributes']['class'][] = 'document';
      }
    }
  }
}

/**
 * Implements hook_js_alter().
 */
function digital_archive_theme_js_alter(&$javascript) {
  if (isset($javascript['sites/all/modules/islandora_solr_table_of_contents/js/table_of_contents.js'])){
    $javascript['sites/all/modules/islandora_solr_table_of_contents/js/table_of_contents.js']['data'] =
    'sites/all/themes/nyhs_theme/js/table_of_contents.js';
  }
}

/**
 * Implements islandora_hook_view_cmodel_pid_alter().
 */
function digital_archive_theme_islandora_collectionCModel_islandora_view_object_alter(AbstractObject $object, &$rendered){
  if (isset($rendered['wrapper']['description'])) {
    $rendered['wrapper']['description'] = NULL;
  }
  if (isset($rendered['wrapper']['collections'])) {
    $rendered['wrapper']['collections'] = NULL;
  }
  if (isset($rendered['wrapper']['metadata'])) {
    $rendered['wrapper']['metadata'] = NULL;
  }
}

/**
 * Implements HOOK_views_pre_render();
 */
function digital_archive_theme_views_pre_render(&$view) {
  if ($view->name=='subsequent_pages') {
    foreach($view->result as $r => $result) {
      $view->result[$r]->{'SORT_BY_ME'} = intval($view->result[$r]->{'RELS_EXT_isPageNumber_literal_s'});
    }
    usort($view->result, 'sort_objects_by_page_num');
  }
}

/**
 * Helper function, sorts
 * @param unknown $a
 * @param unknown $b
 * @return number
 */
function sort_objects_by_page_num($a, $b) {
  if($a->{'SORT_BY_ME'} == $b->{'SORT_BY_ME'}){
    return 0;
  }
  return ($a->{'SORT_BY_ME'} < $b->{'SORT_BY_ME'}) ? -1 : 1;
}

/**
 * Implements hook_preprocess().
 */
function digital_archive_theme_preprocess_islandora_basic_collection_wrapper(&$variables) {
  $islandora_object = menu_get_object('islandora_object', 2);
  nyhs_theme_built_add_vars_for_collection_page($variables, $variables['islandora_object']);
}

/**
 * Implements hook_preprocess().
 */
function digital_archive_theme_preprocess_islandora_objects_subset(&$variables) {
  $islandora_object = menu_get_object('islandora_object', 2);
  nyhs_theme_built_add_vars_for_collection_page($variables, $islandora_object);
}

/**
 * Implements hook_preprocess().
 */
function digital_archive_theme_preprocess_islandora_book_page(&$variables) {
  // Include the required metadata functionality.
  module_load_include('inc', 'islandora', 'includes/metadata');

  // Included for use of get parents from rels functionality.
  module_load_include('inc', 'islandora', 'includes/utilities');

  // Be sure to add the required Drupal libraries for the metadata form.
  drupal_add_js('misc/form.js');
  drupal_add_js('misc/collapse.js');

  // Set our metadata variable to be printed in the template.
  $variables['object_metadata'] = islandora_solr_metadata_display_callback($variables['object']);
  $variables['parent_collections'] = islandora_get_parents_from_rels_ext($variables['object']);
}

function digital_archive_theme_preprocess_islandora_solr_wrapper(&$variables) {
  $variables['islandora_solr_result_count'] = "<div class='solr-result-label-count'><h1>Search Results</h1>" . "<div>" . t("Displaying ") . $variables['islandora_solr_result_count'] . "</div></div>";
  module_load_include('inc', 'islandora_solr', 'includes/blocks');

  module_load_include('inc', 'islandora_solr', 'includes/blocks');
  $variables['solr_display_switch'] = nyhs_theme_block_render('islandora_solr', 'display_switch');//islandora_solr_display();
  $variables['solr_sort'] = nyhs_theme_block_render('islandora_solr', 'sort');//islandora_solr_sort();
}

/**
 * Implements hook_preprocess_page().
 */
function digital_archive_theme_preprocess_page(&$variables) {
  $current_path = current_path();
  $cp_exp = explode("/", $current_path);
  $flag = FALSE;
  $islandora_object = menu_get_object('islandora_object', 2);

  // Dont show this extra data on the search results page.
  if (count($cp_exp) > 1 && $cp_exp[0] != "islandora" && $cp_exp[1] != "search") {
    $flag = TRUE;
  } else if (count($cp_exp) > 1 && $cp_exp[0] == "islandora" && $cp_exp[1] == "search") {
    drupal_set_title("Advanced Search");
  }
  if (isset($islandora_object) && !in_array('islandora:collectionCModel', $islandora_object->models) && $flag == FALSE) {
    $variables['description'] = islandora_solr_metadata_description_callback($islandora_object);
  }
  $alias = drupal_get_path_alias($current_path);
  if (count($cp_exp) >= 1 && $cp_exp[0] == "islandora" && count($cp_exp) > 1 && ($cp_exp[0] != "islandora" || $cp_exp[1] != "search")) {
    $variables['service_links'] = nyhs_theme_block_render('service_links', 'service_links_not_node');
  }
}

/**
 * Implements hook_preprocess().
 */
function digital_archive_theme_preprocess_islandora_objects(&$variables) {
  foreach ($variables['display_links'] as $key => $value ) {
    if ($variables['display_links'][$key]{'title'} == "Grid view") {
      $variables['display_links'][$key]['attributes']['class'][] = 'display-grid';
    } else if ($variables['display_links'][$key]{'title'} == "List view") {
      $variables['display_links'][$key]['attributes']['class'][] = 'display-default';
    }
  }
}

/**
 * Setup template variables for Solr Display Generation.
 *
 * @param unknown $variables
 * @param unknown $islandora_object
 */
function digital_archive_theme_built_add_vars_for_collection_page(&$variables, $islandora_object) {
  module_load_include('inc', 'islandora_solr', 'includes/blocks');
  module_load_include('inc', 'islandora', 'includes/metadata');
  $variables['islandora_object'] = $islandora_object;

  $variables['description'] = islandora_retrieve_description_markup($islandora_object);

    // Be sure to add the required Drupal libraries for the metadata form.
  drupal_add_js('misc/form.js');
  drupal_add_js('misc/collapse.js');

  $variables['display_metadata'] = variable_get('islandora_collection_metadata_display', FALSE);
  if ($variables['display_metadata']) {
    $variables['collection_metadata'] = islandora_retrieve_metadata_markup($islandora_object);
  }
  // Add our collection search on collection view pages.
  $variables['collection_search'] = nyhs_theme_block_render('islandora_collection_search', 'islandora_collection_search');

  // Include the required metadata functionality.
  module_load_include('inc', 'islandora', 'includes/metadata');

  $variables['solr_display_switch'] = islandora_solr_display();
  $variables['solr_sort'] = islandora_solr_sort();
}

/**
 * Helper function to render block dynamically.
 *
 * @param unknown $module
 * @param unknown $delta
 * @param string $as_renderable
 * @return unknown
 */
function digital_archive_theme_block_render($module, $delta, $as_renderable = FALSE) {
  $block = block_load($module, $delta);
  $block_content = _block_render_blocks(array($block));
  $build = _block_get_renderable_array($block_content);
  if ($as_renderable) {
    return $build;
  }
  $block_rendered = drupal_render($build);
  return $block_rendered;
}

/**
 * Implements hook_preprocess().
 */
function digital_archive_theme_preprocess_islandora_solr_search_navigation_block(&$variables) {
  if (isset($variables['prev_link'])) {
    $parsed_url = parse_url($variables['prev_link']);

    $object_pid = str_replace("/islandora/object/", "", $parsed_url['path']);
    $object_pid = urldecode($object_pid);
    $prev_obj = islandora_object_load($object_pid);

    $path = $parsed_url['path'] . $parsed_url['query'];
    $variables['prev_pid'] = $object_pid;
    $variables['prev_text'] = $variables['prev_text'] . " ($prev_obj->label)";
    $variables['prev'] = l("", $variables['prev_link'], array('attributes' => array('class' => array('fa', 'fa-backward', 'fa-lg'), 'title' => $variables['prev_text'])));
  };
  if (isset($variables['return_link'])) {
     $variables['return'] = $variables['return_link'];
  }
  if (isset($variables['next_link'])) {
    $parsed_url = parse_url($variables['next_link']);

    $object_pid = str_replace("/islandora/object/", "", $parsed_url['path']);
    $object_pid = urldecode($object_pid);
    $next_obj = islandora_object_load($object_pid);

    $path = $parsed_url['path'] . $parsed_url['query'];
    $variables['next_text'] = $variables['next_text'] . " ($next_obj->label)";
    $variables['next'] = l("", $variables['next_link'], array('attributes' => array('class' => array('fa', 'fa-forward', 'fa-lg'), 'title' => $variables['next_text'])));
  }
}

/**
 * Implements hook_process_islandora_solr_search_navigation_block().
 */
function digital_archive_theme_process_islandora_solr_search_navigation_block(&$variables) {
  if ($variables['prev_link']) {
    $variables['prev_link'] = $variables['prev'];
  }
  if ($variables['next_link']) {
    $variables['next_link'] = $variables['next'];
  }
  if ($variables['return_link']) {
    $variables['return_link'] = '<a title="' . t('Return To Search') . '" class="fa fa-search fa-lg" href="' . $variables['return'] . '"></a>';
  }
}

/* Convert hexdec color string to rgb(a) string */

function digital_archive_theme_hextorgb($color, $opacity = false) {

	$default = 'rgb(0,0,0)';

	//Return default if no color provided
	if(empty($color))
          return $default;

	//Sanitize $color if "#" is provided
        if ($color[0] == '#' ) {
        	$color = substr( $color, 1 );
        }

        //Check if color has 6 or 3 characters and get values
        if (strlen($color) == 6) {
                $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
        } elseif ( strlen( $color ) == 3 ) {
                $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
        } else {
                return $default;
        }

        //Convert hexadec to rgb
        $rgb =  array_map('hexdec', $hex);

        //Check if opacity is set(rgba or rgb)
        if($opacity){
        	if(abs($opacity) > 1)
        		$opacity = 1.0;
        	$output = 'rgba('.implode(",",$rgb).','.$opacity.')';
        } else {
        	$output = 'rgb('.implode(",",$rgb).')';
        }

        //Return rgb(a) color string
        return $output;
}

