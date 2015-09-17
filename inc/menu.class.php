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


 
class PluginConsumablesMenu extends CommonGLPI {
   static $rightname = 'plugin_consumables';

   static function getMenuName() {
      return _n('Consumable request', 'Consumable requests', 1, 'consumables');
   }

   static function getMenuContent() {
      global $CFG_GLPI;

      $menu                                           = array();
      $menu['title']                                  = self::getMenuName();
      $menu['page']                                   = "/plugins/consumables/front/wizard.php";
      if (PluginConsumablesWizard::canCreate()) {
         $menu['links']['search']                        = PluginConsumablesWizard::getSearchURL(false);
         $menu['links']['add']                        = PluginConsumablesWizard::getSearchURL(false);
      }

      return $menu;
   }

   static function removeRightsFromSession() {
      if (isset($_SESSION['glpimenu']['plugins']['types']['PluginConsumablesMenu'])) {
         unset($_SESSION['glpimenu']['plugins']['types']['PluginConsumablesMenu']); 
      }
      if (isset($_SESSION['glpimenu']['plugins']['content']['pluginconsumablesmenu'])) {
         unset($_SESSION['glpimenu']['plugins']['content']['pluginconsumablesmenu']); 
      }
   }
}