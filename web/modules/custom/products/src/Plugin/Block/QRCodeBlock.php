<?php

namespace Drupal\products\Plugin\Block;

use Drupal\node\NodeInterface;
use Drupal\Core\Block\BlockBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Drupal\Core\Routing\CurrentRouteMatch;

/**
 * TimezoneBlock to fetch location from config form.
 *
 * @Block(
 *  id = "qb_code_block",
 *  admin_label = @translation("QR Code Block"),
 * )
 */
class QRCodeBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * Constructor to initialized variable based on parameter passed.
   *
   * @param array $configuration
   *   Block configuration.
   * @param array $plugin_id
   *   Block onfiguration plugin id.
   * @param array $plugin_definition
   *   Block plugin definition.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   The current route match.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CurrentRouteMatch $currentRouteMatch) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->currentRouteMatch = $currentRouteMatch;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {

    // Instantiates TimezoneBlock class.
    return new static(
          // Load the service required to construct this class.
          $configuration,
          $plugin_id,
          $plugin_definition,
          $container->get('current_route_match'),
    );

  }

  /**
   * To render the template.
   */
  public function build() {

    $node = $this->currentRouteMatch->getParameter('node');

    if ($node instanceof NodeInterface) {

      $bundle = $node->bundle();

      if (!empty($bundle) && $bundle == 'products') {

        $url = $node->get('field_app_purchase_link')->uri;

        if (@strpos($url, 'internal') !== FALSE) {
          $url = str_replace('internal:', '', $url);
        }

        $generator = new BarcodeGeneratorPNG(['temp' => 'sites/default/files/tmp']);
        $blackColor = [0, 0, 0];
        $barcode = base64_encode($generator->getBarcode($url, $generator::TYPE_CODE_128, 2, 500, $blackColor));

        $data = ['barcode' => $barcode, 'url' => $url];

        return [
          '#theme' => 'qrcode_display',
          '#data' => $data,
          '#cache' => [
            'contents' => ['url', 'protocol_version'],
            'tags' => ['node_list:products'],
          ],

        ];
      }
    }
  }

}
