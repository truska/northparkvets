<?php
class Menu
{
   public $userLevel;
   public $menu;

   function __construct($userLevel)
   {
      $this->userLevel = $userLevel;

      $query = "SELECT `cms_admin-menu`.`id` AS `id`, `cms_admin-menu`.`title` as `title`, `cms_admin-menu`.`form` AS `form`, `cms_admin-menu`.`section` AS `section`, `cms_admin-menu`.`subsection` AS `subsection`, `cms_admin-menu`.`url` AS `url`, `cms_admin-menu`.`target` AS `target`, `cms_admin-menu`.`icon` AS `icon`, `cms_admin-menu`.`userrole` AS `userrole`, `cms_admin-menu`.`showonweb` AS `showonweb`, `cms_userrole`.`name` AS `rolename`, `cms_userrole`.`level` AS `rolelevel`
      FROM `cms_admin-menu` 
      INNER JOIN `cms_userrole` on `cms_userrole`.`name` = `cms_admin-menu`.`userrole` 
      WHERE `cms_admin-menu`.`subsection` = '0' 
      AND `cms_userrole`.`level` <= '" . $userLevel . "'
      AND `cms_admin-menu`.`showonweb` = 'Yes' 
      ORDER BY `section`";

      $menu = mysqli_fetch_all(DB::query($query), MYSQLI_ASSOC);

      if ($menu) {
         $this->menu = $menu;
      }else {
         $this->menu = null;
      }
   }

   /**
    * Get menu items
    *
    * @return array|null 
    */
   public function getMenu() {
      return $this->menu;
   }

   /**
    * Get submenu items
    *
    * @param int $section Section ID
    * @return array|null 
    */
   public function getSubMenu($section) {

      $query = "SELECT `cms_admin-menu`.`id` AS `id`, `cms_admin-menu`.`title` as `title`, `cms_admin-menu`.`form` AS `form`, `cms_admin-menu`.`section` AS `section`, `cms_admin-menu`.`subsection` AS `subsection`, `cms_admin-menu`.`url` AS `url`, `cms_admin-menu`.`var1` AS `var1`, `cms_admin-menu`.`target` AS `target`, `cms_admin-menu`.`icon` AS `icon`, `cms_admin-menu`.`userrole` AS `userrole`, `cms_admin-menu`.`showonweb` AS `showonweb`, `cms_userrole`.`name` AS `rolename`, `cms_userrole`.`level` AS `rolelevel` 
      FROM `cms_admin-menu` 
      INNER JOIN `cms_userrole` on `cms_userrole`.`name` = `cms_admin-menu`.`userrole`
      WHERE `section` = '" . $section . "' 
      AND `cms_admin-menu`.`subsection` > '0' 
      AND `cms_userrole`.`level` <= '" . $this->userLevel . "'
      AND `cms_admin-menu`.`showonweb` = 'Yes' 
      ORDER BY `subsection`";

      $subMenu = mysqli_fetch_all(DB::query($query), MYSQLI_ASSOC);

      if ($subMenu) {
         return $subMenu;
      }else {
         return [];
      }
   }

   /**
    * Get Icon
    *
    * @param int $iconID   Icon ID
    * @return array|null 
    */
   public function getIcon($iconID) {
      $query = "SELECT `code` FROM `icons` 
      WHERE `id` = '" . $iconID . "'";

      $icon = mysqli_fetch_array(DB::query($query), MYSQLI_ASSOC);

      if ($icon) {
         return $icon['code'];
      }else {
         return null;
      }
   }
   
}