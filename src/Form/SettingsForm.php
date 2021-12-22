<?php

namespace Drupal\stripe_terminal\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Stripe Terminal settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'stripe_terminal_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['stripe_terminal.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['stripe_sk'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Stripe Secret Key'),
      '#default_value' => $this->config('stripe_terminal.settings')->get('stripe_sk'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getValue('stripe_sk')) {
      $form_state->setErrorByName('stripe_sk', $this->t('The value must not be empty.'));
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('stripe_terminal.settings')
      ->set('stripe_sk', $form_state->getValue('stripe_sk'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
