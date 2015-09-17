<?php

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
  -------------------------------------------------------------------------
  Consumables plugin for GLPI
  Copyright (C) 2003-2011 by the consumables Development Team.

  https://forge.indepnet.net/projects/consumables
  -------------------------------------------------------------------------

  LICENSE

  This file is part of consumables.

  Consumables is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Consumables is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Consumables. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */


if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginConsumablesMenu
 * 
 * This class shows the plugin main page
 * 
 * @package    Consumables
 * @author     Ludovic Dupont
 */
class PluginConsumablesValidation extends CommonDBTM {
      
   private $request;
   
   static $rightname = "plugin_consumables";

   function __construct(){
      parent::__construct();
      
      $this->forceTable("glpi_plugin_consumables_requests");
      $this->request = new PluginConsumablesRequest();
   }
   
   static function getTypeName($nb=0) {
      return __('Consumable validation', 'consumables');
   }
   
   /**
    * Have I the global right to "request group" the Object
    * May be overloaded if needed (ex KnowbaseItem)
    *
    * @return booleen
   **/
   static function canValidate() {
      return Session::haveRight("plugin_consumables_validation", 1);
   }
   
   /**
    * Show consumable validation
    */
   function showConsumableValidation(){
      global $CFG_GLPI;
      
      if (!$this->canView()){
         return false;
      }
      
      // Wizard title
      echo "<div class='consumables_wizard_title'><p>";
      echo "<img class='consumables_wizard_img' src='".$CFG_GLPI['root_doc']."/plugins/consumables/pics/consumablevalidation.png' alt='consumablevalidation'/>&nbsp;";
      _e("Consumable validation", "consumables");
      echo "</p></div>";
      
      $rand = mt_rand();
      
      $fields = $this->find("`status` NOT IN ('".CommonITILValidation::REFUSED."','".CommonITILValidation::ACCEPTED."')", "`date_mod`");

      echo "<div class='center'>"; 
      
      if (!empty($fields)) {
         if ($this->canValidate()) {
            Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
            $massiveactionparams = array('item' => __CLASS__, 'container' => 'mass'.__CLASS__.$rand);
            Html::showMassiveActions($massiveactionparams);
         }

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr>";
         echo "<th colspan='7'>".self::getTypeName()."</th>";
         echo "</tr>";
         echo "<tr>";
         echo "<th width='10'>";
         if ($this->canValidate()) {
            echo Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
         }
         echo "</th>";
         echo "<th>".__('Requester')."</th>";
         echo "<th>"._n('Consumable type', 'Consumable types', 1)."</th>";
         echo "<th>"._n('Consumable', 'Consumables', 1)."</th>";
         echo "<th>".__('Number', 'consumables')."</th>";
         echo "<th>".__("Give to")."</th>";
         echo "<th>"._sx("item", "State")."</th>";
         echo "</tr>";

         foreach ($fields as $field) {
            echo "<tr class='tab_bg_1'>";
            echo "<td width='10'>";
            if ($this->canValidate()) {
               Html::showMassiveActionCheckBox(__CLASS__, $field['id']);
            }
            echo "</td>";

            echo "<td>";
            echo getUserName($field['requesters_id']);
            echo "</td>";

            echo "<td>";
            echo Dropdown::getDropdownName("glpi_consumableitemtypes", $field['consumableitemtypes_id']);
            echo "</td>";

            echo "<td>";
            echo Dropdown::getDropdownName("glpi_consumableitems", $field['consumables_id']);
            echo "</td>";

            echo "<td>";
            echo $field['number'];
            echo "</td>";

            echo "<td>";
            if (!empty($field['give_itemtype'])) {
               $give_item = getItemForItemtype($field['give_itemtype']);
               $give_item->getFromDB($field['give_items_id']);
               echo $give_item->getLink();
            }
            echo "</td>";
            
            echo "<td>";
            $bgcolor = CommonITILValidation::getStatusColor($field['status']);
            $status  = CommonITILValidation::getStatus($field['status']);
            echo "<div style='background-color:".$bgcolor.";'>".$status."</div>";
            echo "</td>";
            echo "</tr>";
         }
         if ($this->canValidate()) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm(); 
         }

         echo "</table>";
      } else {
         echo __("No item to display");
      }
      
      echo "</div>";
      
      // Footer
      if ($this->canCreate() && $this->canValidate()) {
         echo "<br/><table width='100%'>";
         echo "<tr>";
         echo "<td class='consumables_wizard_button'>";
         echo "<div id='dialog-confirm'></div>";
         echo "<input type=\"button\" class=\"consumable_previous_button submit\" name=\"previous\" value=\""._sx('button', 'Cancel')."\" onclick=\"consumables_cancel('".$CFG_GLPI['root_doc']."/plugins/consumables/front/wizard.php');\">";
         echo "<input type='hidden' name='requesters_id' value='".Session::getLoginUserID()."'>";
         echo "</td>";
         echo "</tr>";
         echo "</table>";
      }
      
      // Init consumable cart javascript
      echo "<script type='text/javascript'>";
      echo "consumables_initJs('".$CFG_GLPI['root_doc']."');";
      echo "</script>";
   }
   
   
   /**
    * Validation consumable
    * 
    * @param type $params
    */
   function validationConsumable($params, $state=CommonITILValidation::WAITING) {
      
      $datas = $this->request->getUserConsumables($params['requesters_id'], "`consumables_id` = ".$params['consumables_id']);
      
      foreach ($datas as $data) {
         $this->update(array('id' => $data['id'], 'status' => $state, 'validators_id' => Session::getLoginUserID()));
      }
      
      return true;
   }
   
   
   function getForbiddenStandardMassiveAction() {

      $forbidden = parent::getForbiddenStandardMassiveAction();

      $forbidden[] = 'update';
      $forbidden[] = 'purge';

      return $forbidden;
   }
   
  /**
    * Get the specific massive actions
    *
    * @since version 0.84
    * @param $checkitem link item to check right   (default NULL)
    *
    * @return an array of massive actions
    * */
   function getSpecificMassiveActions($checkitem = NULL) {
      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);
      $prefix = $this->getType().MassiveAction::CLASS_ACTION_SEPARATOR;
      
      if ($isadmin) {
         $actions[$prefix.'validate'] = __('Validate');
         $actions[$prefix.'refuse']   = __('Refuse', 'consumables');
      }

      return $actions;
   }
   
   
   /**
    * Massive actions display
    * 
    * @param $input array of input datas
    *
    * @return array of results (nbok, nbko, nbnoright counts)
    * */
   static function showMassiveActionsSubForm(MassiveAction $ma) {
      
      $itemtype = $ma->getItemtype(false);

      switch ($itemtype) {
         case self::getType():
            switch ($ma->getAction()) {
               case "validate":case "refuse":
                  echo "<textarea cols='80' rows='7' name='comment'></textarea><br><br>";
                  break;
            }
            return parent::showMassiveActionsSubForm($ma);
      }
   }
   
   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
   **/
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {
      $itemtype   = $ma->getItemtype(false);
      $item       = new PluginConsumablesRequest();
      $validation = new PluginConsumablesValidation();
      $consumable = new Consumable();
      $input = $ma->getInput();

      if (count($ids)) {
         switch ($ma->getAction()) {
            case "validate" :
               foreach ($ids as $key => $val) {
                  if ($item->can($key, UPDATE)) {
                     $item->getFromDB($key);

                     // Get available consumables
                     $outConsumable = array();
                     $availables = $consumable->find("`consumableitems_id` = '".$item->fields['consumables_id']."' AND `items_id` = 0");
                     foreach($availables as $available){
                        $outConsumable[] = $available;
                     }
                     
                     // Check if enough stock
                     if (!empty($outConsumable) && count($outConsumable) >= $item->fields['number']) {
                        // Give consumable
                        $result = array(1);
                        for ($i = 0; $i < $item->fields['number']; $i++) {
                           if (isset($outConsumable[$i]) && $consumable->out($outConsumable[$i]['id'], $item->fields['give_itemtype'], $item->fields['give_items_id'])) {
                              $result[] = 1;
                           } else {
                              $result[] = 0;
                           }
                        }

                        if (!in_array(0, $result)) {
                           // Validation status update
                           $validation->validationConsumable($item->fields, CommonITILValidation::ACCEPTED);
                           // Send notification
                           NotificationEvent::raiseEvent(PluginConsumablesNotificationTargetRequest::CONSUMABLE_RESPONSE, 
                                 $item, array('entities_id'       => $_SESSION['glpiactive_entity'],
                                              'consumablerequest' => $item->fields,
                                              'comment'           => $input['comment']));
                           
                           $ma->addMessage("<span style='color:green'>".sprintf(__('Consumable %s validated', 'consumables'), Dropdown::getDropdownName("glpi_consumableitems", $item->fields['consumables_id']))."</span>");
                           $ma->itemDone($validation->getType(), $key, MassiveAction::ACTION_OK);
                        } else {
                           $ma->itemDone($validation->getType(), $key, MassiveAction::ACTION_KO);
                        }
                        
                     } else {
                        $ma->addMessage(sprintf(__('Not enough stock for consumable %s', 'consumables'), Dropdown::getDropdownName("glpi_consumableitems", $item->fields['consumables_id'])));
                        $ma->itemDone($validation->getType(), $key, MassiveAction::ACTION_KO);
                     }
                     
                  } else {
                     $ma->itemDone($validation->getType(), $key, MassiveAction::ACTION_NORIGHT);
                     $ma->addMessage($validation->getErrorMessage(ERROR_RIGHT));
                  }
               }
               break;

            case "refuse" :
               foreach ($ids as $key => $val) {
                  if ($item->can($key, UPDATE)) {
                     // Validation status update
                     if ($validation->validationConsumable($item->fields, CommonITILValidation::REFUSED)) {
                        // Send notification
                        NotificationEvent::raiseEvent(PluginConsumablesNotificationTargetRequest::CONSUMABLE_RESPONSE, 
                              $item, array('entities_id'       => $_SESSION['glpiactive_entity'],
                                           'consumablerequest' => $item->fields,
                                           'comment'           => $input['comment']));
                        $ma->itemDone($validation->getType(), $key, MassiveAction::ACTION_OK);
                        $ma->addMessage(__('Consumable refused', 'consumables'));
                     } else {
                        $ma->itemDone($validation->getType(), $key, MassiveAction::ACTION_KO);
                     }
                  } else {
                     $ma->itemDone($validation->getType(), $key, MassiveAction::ACTION_NORIGHT);
                     $ma->addMessage($validation->getErrorMessage(ERROR_RIGHT));
                  }
               }
               break;
               
            default :
               return parent::doSpecificMassiveActions($ma->POST);
         }
      }
   }

}
?>