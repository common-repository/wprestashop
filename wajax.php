<?php
/*
* 2007-2011 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 9068 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
global $cookie,$cart;

require(dirname(__FILE__).'/config/config.inc.php');
require(dirname(__FILE__).'/init.php');



$task = $_REQUEST['task'];
switch ($task) {
    case 'getTotal':
        $total = wprestashop::getTotal();
        $return  = array('total' => $total, 'currency' =>$cart->id_currency );
        break;
    case 'getProducts':
        $products = wprestashop::getProducts(true);
        $return  = array('products' => $products, 'currency' =>$cart->id_currency );
        break;
    case 'getSummaryDetails':
        echo "call";
        break;
    case 'checkLogin':
        $return = wprestashop::checkLogin(true);
        $tmp = wprestashop::getTotal();
        if($tmp){
            $return['total'] = Tools::displayPrice($tmp);
            $return['product'] = $cart->nbProducts();
        }else{
            $return['total'] = 0;
        }
        break;  
    case 'callBlockUser': 
        require(dirname(__FILE__).'/modules/blockuserinfo/blockuserinfo.php');
        $blockuser = new BlockUserInfo();
        echo $blockuser->hookTop($cookie);exit;
        break;  
    case 'callBlockCart': 
        require(dirname(__FILE__).'/modules/blockcart/blockcart.php');
        $blockcart = new BlockCart();
        $params = array('cart'=>$cart, 'cookie' => $cookie);
        echo $blockcart->hookRightColumn($params);exit;
        break; 
    default:
       echo "error";
}





echo json_encode($return);
exit;












class wprestashop{
    function getTotal(){
        global $cart;
        return $cart->getOrderTotal(false,Cart::ONLY_PRODUCTS);
    }
    function getProducts(){
        global $cart;
        return $cart->getProducts(true);
    }
    function getSummaryDetails(){
        global $cart;
        return $cart->getSummaryDetails();
    }
    function checkLogin(){
        global $cookie;
        $customer = new Customer(intval($cookie->id_customer));
        if(!$cookie->isLogged()){
            $return = array('login' => 0);    
        }else{
            $return = array(
                        'login' => 1, 
                        'lastname' => $customer->lastname, 
                        'firstname' => $customer->firstname, 
                        'email'=> $customer->email
                        );    
        }
        return $return;
    }
}

