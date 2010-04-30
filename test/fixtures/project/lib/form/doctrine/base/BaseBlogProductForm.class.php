<?php

/**
 * BlogProduct form base class.
 *
 * @method BlogProduct getObject() Returns the current form's model object
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseBlogProductForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'blog_id'    => new sfWidgetFormInputHidden(),
      'product_id' => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'blog_id'    => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'blog_id', 'required' => false)),
      'product_id' => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'product_id', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('blog_product[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'BlogProduct';
  }

}
