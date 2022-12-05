<?php

namespace Drupal\bulk_export_action\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsPreconfigurationInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Export stuff with views bulk operations.
 *
 * @Action(
 *   id = "bulk_export_action",
 *   label = @Translation("Export content in a view"),
 *   type = "",
 *   confirm = TRUE,
 * )
 */
class BulkExportAction extends ViewsBulkOperationsActionBase implements ViewsBulkOperationsPreconfigurationInterface {

  private $logger = \Drupal::logger('bulk_export');

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    $logger->notice("Hi, it's me {$entity->id()}");

    /*
      ==============================
      Default Content Deploy
      ==============================
    */
    try {
      $exporter = \Drupal::service('default_content_deploy.exporter');
      $exporter->setEntityTypeId(
        $entity->getEntityTypeId()
      );

      $exporter->setMode('reference');
      $exporter->setEntityIds(
        [$entity->id()]
      );
      $result = $exporter
        ->export()
        ->getResult();
      $logger->notice($result);

      // How can I trigger an ansible script to promote to stage?
      //$cmd = "ansible-playbook {path/to/folder}/deploy-to-stage.yml";
      //$output = shell_exec($cmd);
      $logger->notice($output);

    }
    catch (\InvalidArgumentException $e) {
      $logger->error($e->getMessage());
    }

  }

   /**
   * {@inheritdoc}
   */
  public function buildPreConfigurationForm(array $form, array $values, FormStateInterface $form_state) {
    $form['bulk_export_action_pre_config'] = [
      '#title' => 'Module path relative to Drupal root\'s index.php (including /content/)',
      '#description' => 'Example: modules/custom/my_content_module/content/',
      '#type' => 'textfield',
      '#default_value' => isset($values['bulk_export_action_pre_config']) ? $values['bulk_export_action_pre_config'] : '',
    ];
    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function access($object, ?AccountInterface $account = NULL, $return_as_object = FALSE) {
    return $object->access('bulk_export', $account, $return_as_object);
  }

}

