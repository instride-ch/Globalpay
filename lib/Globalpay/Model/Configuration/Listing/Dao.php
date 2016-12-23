<?php
/**
 * w-vision
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2016 Woche-Pass AG (http://www.w-vision.ch)
 * @license    GNU General Public License version 3 (GPLv3)
 */

 namespace Globalpay\Model\Configuration\Listing;

 use Pimcore;
 use Globalpay\Model;

 /**
  * Class Dao
  * @package Globalpay\Model\Configuration\Listing
  */
 class Dao extends Pimcore\Model\Dao\PhpArrayTable
 {
     /**
      * configure.
      */
     public function configure()
     {
         parent::configure();
         $this->setFile('globalpay_configurations');
     }

     /**
      * Loads a list of Configurations for the specicifies parameters, returns an array of Configuration elements.
      *
      * @return array
      */
     public function load()
     {
         $routesData = $this->db->fetchAll($this->model->getFilter(), $this->model->getOrder());

         $routes = array();
         foreach ($routesData as $routeData) {
             $routes[] = Model\Configuration::getById($routeData['id']);
         }

         $this->model->setConfigurations($routes);

         return $routes;
     }

     /**
      * get total count.
      *
      * @return int
      */
     public function getTotalCount()
     {
         $data = $this->db->fetchAll($this->model->getFilter(), $this->model->getOrder());
         $amount = count($data);

         return $amount;
     }
 }
