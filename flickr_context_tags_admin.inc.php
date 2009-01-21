<?php

function flickr_context_tags_settings() {
  $form = array();
  
  $mt_ns = variable_get('flickr_context_tags_mech_namespace', '');
  //Make a qualified guess
  if (empty($mt_ns)) {
    $matches = array();
    $site_name = variable_get('site_name','');
    if (preg_match('/^\s*([^\s\.]+)/', $site_name, $matches)) {
      $mt_ns = drupal_strtolower($matches[1]);
    }
  }
  
  $form['flickr_context_tags_mech_namespace'] = array(
    '#type' => 'textfield',
    '#title' => t('Machine-tag namespace'),
    '#default_value' => $mt_ns,
  );
  
  $form['flickr_context_tags_default_place_id'] = array(
    '#type' => 'textfield',
    '#title' => t('Default place id'),
    '#description' => t('The default place id can be used to keep the flickr pictures relevant'),
    '#default_value' => variable_get('flickr_context_tags_default_place_id', ''),
  );
  
  $form['flickr_context_tags_force_default_place_id'] = array(
    '#type' => 'checkbox',
    '#title' => t('Always use the default place id'),
    '#default_value' => variable_get('flickr_context_tags_force_default_place_id', FALSE),
  );
  
  // Tag configuration textarea
  $paths = variable_get('flickr_context_tags_contexts', array());
  $form['flickr_context_tags_contexts'] = array(
    '#type' => 'textarea',
    '#title' => t('Context tags for paths'),
    '#default_value' => flickr_context_tags_pack_contexts($paths),
    '#description' => t('Enter the path expression followed by a list of comma-separated tags'),
  );
  
  // Piggyback on the system settings form for buttons and so on
  $form = system_settings_form($form);
  // ...but replace the #submit function
  $form['#submit'] = array('flickr_context_tags_settings_submit');
  return $form;
}

function flickr_context_tags_pack_contexts($paths) {
  $txt = '';
  foreach($paths as $path => $tags) {
    $txt .= $path . ' ' . join(', ', $tags) . "\n";
  }
  return $txt;
}

function flickr_context_tags_unpack_contexts($txt) {
  $paths = array();
  $lines = split("\n", $txt);
  foreach ($lines as $line) {
    $line = trim($line);
    $matches = array();
    if (preg_match('/([^\s]+)\s+(.*)/', $line, $matches)) {
      $path = $matches[1];
      $tags = preg_split('/\s*,\s*/', $matches[2]);
      $paths[$path] = $tags;
    }
  }
  return $paths;
}

function flickr_context_tags_settings_submit($form, $state) {
  $values = $state['values'];
  $paths = flickr_context_tags_unpack_contexts($values['flickr_context_tags_contexts']);
  variable_set('flickr_context_tags_contexts', $paths);
  variable_set('flickr_context_tags_mech_namespace', $values['flickr_context_tags_mech_namespace']);
  variable_set('flickr_context_tags_default_place_id', $values['flickr_context_tags_default_place_id']);
  variable_set('flickr_context_tags_force_default_place_id', $values['flickr_context_tags_force_default_place_id']);
}