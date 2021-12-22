<?php

namespace Drupal\stripe_terminal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Stripe Terminal routes.
 */
class StripeTerminalController extends ControllerBase {

  public function build() {
    $build['content'] = [
      '#theme' => 'page_template',
    ];

    return $build;
  }

  public function getSK() {
    $config = \Drupal::config('stripe_terminal.settings');
    return $config->get('stripe_sk');
  }

  public function token() {

    \Stripe\Stripe::setApiKey($this->getSK());

    try {
      // The ConnectionToken's secret lets you connect to any Stripe Terminal reader
      // and take payments with your Stripe account.
      // Be sure to authenticate the endpoint for creating connection tokens.
      $connectionToken = \Stripe\Terminal\ConnectionToken::create();

      $response = new Response(json_encode(array('secret' => $connectionToken->secret)));
      $response->headers->set('Content-Type', 'application/json');

      return $response;
    
    } catch (Error $e) {
      http_response_code(500);
      $response = new Response(json_encode(array('error' => $e->getMessage())));
      $response->headers->set('Content-Type', 'application/json');
    }
  }

  public function createPayment() {

    \Stripe\Stripe::setApiKey($this->getSK());

    try {
      $json_str = file_get_contents('php://input');
      $json_obj = json_decode($json_str);
    
      // For Terminal payments, the 'payment_method_types' parameter must include
      // 'card_present' and the 'capture_method' must be set to 'manual'

      if($json_obj) {
        $intent = \Stripe\PaymentIntent::create([
          'amount' => $json_obj->amount,
          'currency' => 'cad',
          'payment_method_types' => ['card_present', 'interac_present'],
          'capture_method' => 'manual',
        ]);
  
        $response = new Response(json_encode(array('client_secret' => $intent->client_secret)));
        $response->headers->set('Content-Type', 'application/json');
      }

      return $response;
    
    } catch (Error $e) {
      http_response_code(500);
      $response = new Response(json_encode(array('error' => $e->getMessage())));
      $response->headers->set('Content-Type', 'application/json');
    }
  }

  public function capturePayment() {
    
    \Stripe\Stripe::setApiKey($this->getSK());

    try {
      // retrieve JSON from POST body
      $json_str = file_get_contents('php://input');
      $json_obj = json_decode($json_str);

      $intent = \Stripe\PaymentIntent::retrieve($json_obj->id);
      $intent = $intent->capture();

      $response = new Response(json_encode($intent));
      $response->headers->set('Content-Type', 'application/json');

      return $response;

    } catch (Error $e) {
      http_response_code(500);
      $response = new Response(json_encode(array('error' => $e->getMessage())));
      $response->headers->set('Content-Type', 'application/json');
    }
  }
}
