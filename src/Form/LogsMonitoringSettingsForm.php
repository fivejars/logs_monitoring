<?php

namespace Drupal\logs_monitoring\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings Form for logs monitoring.
 */
class LogsMonitoringSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'logs_monitoring_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'logs_monitoring.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('logs_monitoring.settings');

    $form['path_to_logs'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Path to the logs'),
      '#default_value' => $config->get('path_to_logs') ?? '',
      '#description' => $this->t('Absolute path to the log file. Each path to the log should be from a new line.'),
      '#rows' => 7,
      '#required' => TRUE,
    ];

    $form['search_words'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Error words to search'),
      '#description' => $this->t('Each word/phrase should be from a new line.'),
      '#default_value' => $config->get('search_words') ?? '',
      '#rows' => 7,
      '#required' => TRUE,
    ];

    $form['lines_count'] = [
      '#type' => 'number',
      '#title' => $this->t('Count of lines to read'),
      '#description' => $this->t('The count of lines from the end in which the search will be performed.'),
      '#default_value' => $config->get('lines_count') ?? 200,
      '#min' => 1,
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('logs_monitoring.settings');
    $config->set('path_to_logs', $form_state->getValue('path_to_logs'))->save();
    $config->set('search_words', $form_state->getValue('search_words'))->save();
    $config->set('lines_count', $form_state->getValue('lines_count'))->save();
    parent::submitForm($form, $form_state);
  }

}
