<?php
/**
 * Implements hook_form_system_theme_settings_alter().
 *
 * @param $form
 *   Nested array of form elements that comprise the form.
 * @param $form_state
 *   A keyed array containing the current state of the form.
 */
function digital_archive_theme_form_system_theme_settings_alter(&$form, &$form_state, $form_id = NULL)  {
  // Work-around for a core bug affecting admin themes. See issue #943212.
  if (isset($form_id)) {
    return;
  }
  $form['digital_archive_theme_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Custom Theme Settings'),
    '#collapsible' => FALSE,
    '#collapsed' => FALSE,
  );
  $form['digital_archive_theme_settings']['show_subsequent_pages'] = array(
   '#type' => 'select',
   '#title' => t('Show Subsequent pages view on book objects'),
   '#options' => array(
      0 => t('No'),
      1 => t('Yes'),
    ),
   '#default_value' => theme_get_setting('show_subsequent_pages'),
   '#description' => t('Set this to <em>Yes</em> if you would like this enable the subsequent pages view on book objects.'),
  );
  $form['digital_archive_theme_settings']['show_image_clip'] = array(
    '#type' => 'select',
    '#title' => t('Show large image clipper'),
    '#options' => array(
      0 => t('No'),
      1 => t('Yes'),
    ),
    '#default_value' => theme_get_setting('show_image_clip'),
    '#description' => t('Allow the usage of the large image clipper on large image content model objects.'),
  );
}
