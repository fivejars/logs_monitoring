<?php

// phpcs:ignore PHPCompatibility.Keywords.ForbiddenNamesAsDeclared.resourceFound
namespace Drupal\logs_monitoring\Plugin\rest\resource;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\logs_monitoring\Utils\FileReader;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides a resource that indicate about errors in the logs.
 *
 * @RestResource(
 *   id = "logs_monitoring",
 *   label = @Translation("Logs monitoring"),
 *   uri_paths = {
 *     "canonical" = "/admin/reports/logs-monitoring"
 *   }
 * )
 */
class LogsMonitoring extends ResourceBase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration,
                                    $plugin_id,
                                    $plugin_definition,
                              array $serializer_formats,
                              LoggerInterface $logger,
                              ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('config.factory')
    );
  }

  /**
   * Responds to GET requests.
   *
   * Indicate that there are errors in the logs file and returns appropriate
   * status code. Also show log name, where error found.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The response containing the requested variety data.
   */
  public function get(): JsonResponse {
    $config = $this->configFactory->getEditable('logs_monitoring.settings');
    $result = [];
    $status_code = 200;
    $logs_path = explode(PHP_EOL, $config->get('path_to_logs'));
    $words = explode(PHP_EOL, $config->get('search_words'));
    array_walk($words, function (&$item) {
      $item = trim($item);
    });

    foreach ($logs_path as $path) {
      $path = trim($path);
      if (file_exists($path)) {
        $is_error = $this->isErrorFound($path, (int) $config->get('lines_count'), $words);
        $result[basename($path)] = [
          'status' => $is_error ? 'NOK' : 'OK',
          'last_modified' => date('Y-m-d H:i:s', filemtime($path)),
        ];
        if ($status_code != 500 && $is_error) {
          $status_code = 500;
        }
      }
      else {
        $result[basename($path)]['status'] = 'Not found.';
        $status_code = 200;
      }

    }

    return new JsonResponse($result, $status_code);
  }

  /**
   * Check if error is found in the log file.
   *
   * @param string $filepath
   *   Path to the log file.
   * @param int $lines
   *   Count of lines in the end, where search will be performed.
   * @param array $words
   *   Words which indicate an error.
   *
   * @return bool
   *   Indicate if error found.
   */
  protected function isErrorFound(string $filepath, int $lines, array $words): bool {
    $tail = FileReader::tailCustom($filepath, $lines);

    foreach ($words as $word) {
      if (mb_stripos($tail, $word) !== FALSE) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
