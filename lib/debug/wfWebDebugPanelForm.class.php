<?php

/**
 * wfWebDebugPanelForm displays information related to your form
 *
 * @package    symfony
 * @subpackage debug
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfWebDebugPanelTimer.class.php 12982 2008-11-13 17:25:10Z hartym $
 */
class wfWebDebugPanelForm extends sfWebDebugPanel
{
  protected $forms = array();
  
  protected $panel_content = null;
  
  protected $form_warning = false;
  
  public function __construct(sfWebDebug $webDebug)
  {
    parent::__construct($webDebug);
    
    $this->webDebug->getEventDispatcher()->notify(new sfEvent($this, 'debug.web.forms'));
  }
  
  public function addForm(sfForm $form)
  {
    $this->forms[] = $form;
  }
  
  public function getTitle()
  {
    // fire panel generation to see if we have any form warnings
    $this->getPanelContent();
    
    if ($this->form_warning)
    {
      return sprintf('<span style="background: #d8000c;"><img src="%s/bricks.png" alt="Forms" /> forms</span>', $this->getImageRoot());
    }
    else
    {
      return sprintf('<img src="%s/bricks.png" alt="Forms" /> forms', $this->getImageRoot());
    }
  }

  public function getPanelTitle()
  {
    return 'Forms';
  }

  public function getPanelContent()
  {
    if (is_null($this->panel_content))
    {
      if (count($this->forms) == 0)
      {
        $panel = '<div style="font-weight: bold; margin-bottom: 30px;">No forms found on this page</div>';
      }
      else
      {
        $panel = '<table class="sfWebDebugForms" style="width: 600px;"><tr><th>Form class</th><th>Fields</th></tr>';
        foreach($this->forms as $form)
        {
          $field_data = '';
          foreach($form->getFormFieldSchema() as $key => $field)
          {
            $field_data .= $this->generateFieldRow($key, $form);
          }
          
          $panel .= sprintf('<tr><td class="sfWebDebugFormClass">%s</td><td class="sfWebDebugFormValidators"><table><tr><th>field</th><th>Widget</th><th>Hidden?</th><th>Widget Options</th><th>Validator</th><th>Required?</th><th>Validator Options</th></tr>%s</table></td></tr>', get_class($form), $field_data);
        }
        
        $panel .= '</table>';
      }
      
      $this->panel_content = $panel;
    }

    return $this->panel_content;
  }
  
  protected function generateFieldRow($field_name, sfForm $form)
  {
    $widget = $form->getWidgetSchema()->offsetGet($field_name);
    $validator = $form->getValidatorSchema()->offsetGet($field_name);
    
    // add default and label for widgets
    
    if ($widget)
    {
      $data = sprintf(
        '<td style="border-bottom: 1px solid #666;">%s</td><td style="text-align: center; border-bottom: 1px solid #666;">%s</td><td style="border-bottom: 1px solid #666;">%s</td>',
        get_class($widget),
        $this->getBooleanImage($widget->getOption('is_hidden')),
        $this->parseOptions($widget->getOptions(), 'widget')
      );
    }
    else
    {
      $this->form_warning = true;
      
      $data = '<td colspan="3" style="text-align: center; font-weight: bold; background: #d8000c;">NO WIDGET SET</td>';
    }
    
    if ($validator)
    {
      $data .= sprintf(
        '<td style="border-bottom: 1px solid #666;">%s</td><td style="text-align: center; border-bottom: 1px solid #666;">%s</td><td style="border-bottom: 1px solid #666;">%s</td>',
        get_class($validator),
        $this->getBooleanImage($validator->getOption('required')),
        $this->parseOptions($validator->getOptions(), 'validator')
      );
    }
    else
    {
      $this->form_warning = true;
      
      $data .= '<td colspan="3" style="text-align: center; font-weight: bold; background: #d8000c;">NO VALIDATOR SET</td>';
    }
    
    return sprintf('<tr><td style="border-bottom: 1px solid #666;">%s</td>%s</tr>', $field_name, $data);
  }
  
  /**
   * This nees to generate a more dynamic path, this assumes symfony is at the web root
   */
  protected function getImageRoot()
  {
    return '/wfWebDebugFormPlugin/images';
  }
  
  protected function getBooleanImage($bool)
  {
    return sprintf('<img src="%s/%s" />', $this->getImageRoot(), ($bool) ? 'accept.png' : 'cancel.png');
  }
  
  /**
   * Returns an options array in a readable format
   */
  protected function parseOptions($options, $type)
  {
    // cleanup options that aren't really important
    if ($type == 'widget')
    {
      unset(
        $options['id_format'],
        $options['is_hidden'],
        $options['needs_multipart'],
        $options['type'],
        $options['default'],
        $options['label'],
        $options['value_attribute_value']
      );
    }
    elseif ($type == 'validator')
    {
      unset(
        $options['required'],
        $options['trim']
      );
    }
    
    $str = '';
    foreach($options as $key => $value)
    {
      if (is_object($value))
      {
        $print_value = 'Class: ' . get_class($value);
      }
      elseif (is_array($value))
      {
        $avoid_export = false;
        foreach($value as $arr_value)
        {
          if (is_object($arr_value) || is_array($arr_value))
          {
            $avoid_export = true;
            break;
          }
        }
        
        if ($avoid_export)
        {
          $print_value = sprintf('Array(%s)', count($value));
        }
        else
        {
          $print_value = var_export($value, true);
        }
        
        
      }
      elseif(is_null($value))
      {
        $print_value = '<i>null</i>';
      }
      else
      {
        $print_value = $value;
      }
      
      $str .= sprintf("<div style=\"white-space:nowrap;\">'%s' => %s</div>", $key, $print_value);
    }
    
    return $str;
  }
}
