<?php
    if (!defined ('PRESTASHOP_CONNECT_CLASS')) {
    	define ('PRESTASHOP_CONNECT_CLASS', 1);
    	class PrestashopConnect {
    		function __construct(){
                global $link, $id_lang, $protocol_content, $error_wprestashop,$path;
                
                if(!$error_wprestashop){
                    require_once ( $path.DS.'config'.DS.'config.inc.php' );
                    require_once ( $path.DS.'config'.DS.'defines.inc.php' );
                    define('_PS_BASE_URL_', Tools::getShopDomain(true));
                    include_once( $path.DS.'classes'.DS.'Link.php');
                    $link = new LinkCore();
                    $id_lang = Configuration::get('PS_LANG_DEFAULT');
            		$useSSL = ((isset($this->ssl) AND $this->ssl AND Configuration::get('PS_SSL_ENABLED')) OR Tools::usingSecureMode()) ? true : false;
            		$protocol_content = ($useSSL) ? 'https://' : 'http://';
                    
                    if(!file_exists($path.'wajax.php')&&is_dir($path)){
                        if (!copy(dirname (__FILE__).DS.'wajax.php',$path.'/wajax.php')) {
                            admin_error_copy();
                        }
                    }
                }
    		}
            
            /** Category GetTree*/
            function getTree($resultParents, $resultIds, $maxDepth, $id_category = 1, $currentDepth = 0){
        		global $link;
                
                                           
        		$children = array();
        		if (isset($resultParents[$id_category]) AND sizeof($resultParents[$id_category]) AND ($maxDepth == 0 OR $currentDepth < $maxDepth))
        			foreach ($resultParents[$id_category] as $subcat)
        				$children[] = $this->getTree($resultParents, $resultIds, $maxDepth, $subcat['id_category'], $currentDepth + 1);
        		if (!isset($resultIds[$id_category]))
        			return false;
        		return array('id' => $id_category, 'link' => $link->getCategoryLink($id_category, $resultIds[$id_category]['link_rewrite']),
        					 'name' => $resultIds[$id_category]['name'], 'desc'=> $resultIds[$id_category]['description'],
        					 'children' => $children);
                             
        	}
            function __buildQueryCategories(){
                $id_lang = Configuration::get('PS_LANG_DEFAULT');
        		$maxdepth = Configuration::get('BLOCK_CATEG_MAX_DEPTH');
        		$groups = _PS_DEFAULT_CUSTOMER_GROUP_;
                
                $query = '
        		SELECT c.id_parent, c.id_category, cl.name, cl.description, cl.link_rewrite
        		FROM `'._DB_PREFIX_.'category` c
        		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (c.`id_category` = cl.`id_category` AND `id_lang` = '.$id_lang.')
        		LEFT JOIN `'._DB_PREFIX_.'category_group` cg ON (cg.`id_category` = c.`id_category`)
        		WHERE (c.`active` = 1 OR c.`id_category` = 1)
        		'.((int)($maxdepth) != 0 ? ' AND `level_depth` <= '.(int)($maxdepth) : '').'
        		AND cg.`id_group` IN ('.pSQL($groups).')
        		GROUP BY id_category
        		ORDER BY `level_depth` ASC, '.(Configuration::get('BLOCK_CATEG_SORT') ? 'cl.`name`' : 'c.`position`').' '.(Configuration::get('BLOCK_CATEG_SORT_WAY') ? 'DESC' : 'ASC');
                
                return $query;
                
            }
            
            function GetWCategories(){                
                $query = $this->__buildQueryCategories();
                if (!$result = Db::getInstance()->ExecuteS($query)) return;
        		foreach ($result as &$row){
        			$resultParents[$row['id_parent']][] = &$row;
        			$resultIds[$row['id_category']] = &$row;
        		}
                
                $blockCategTree = $this->getTree($resultParents, $resultIds, Configuration::get('BLOCK_CATEG_MAX_DEPTH'));
                return $blockCategTree;
            }
            
            function __BuildBestsellers($pageNumber,$nbProducts){
          		
                $id_lang = Configuration::get('PS_LANG_DEFAULT');
        		
        		$groups = FrontController::getCurrentCustomerGroups();
        		$sqlGroups = (count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1');
                
                $query = '
        		SELECT p.id_product, pl.`link_rewrite`, pl.`name`, pl.`description_short`, i.`id_image`, il.`legend`, ps.`quantity` AS sales, p.`ean13`, p.`upc`, cl.`link_rewrite` AS category
        		FROM `'._DB_PREFIX_.'product_sale` ps
        		LEFT JOIN `'._DB_PREFIX_.'product` p ON ps.`id_product` = p.`id_product`
        		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.(int)$id_lang.')
        		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
        		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
        		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (cl.`id_category` = p.`id_category_default` AND cl.`id_lang` = '.(int)$id_lang.')
        		WHERE p.`active` = 1
        		AND p.`id_product` IN (
        			SELECT cp.`id_product`
        			FROM `'._DB_PREFIX_.'category_group` cg
        			LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
        			WHERE cg.`id_group` '.$sqlGroups.'
        		)
        		ORDER BY sales DESC
        		LIMIT '.(int)($pageNumber * $nbProducts).', '.(int)($nbProducts);
                
                return $query;
            }
            
            function getWBestsellers($pageNumber = 0,$nbProducts = 5){
                global $link;
                $currency = new Currency((int)(Configuration::get('PS_CURRENCY_DEFAULT')));
                $id_lang = Configuration::get('PS_LANG_DEFAULT');

        		if ($pageNumber < 0) $pageNumber = 0;
        		if ($nbProducts < 1) $nbProducts = 10;
                
                $query = $this->__BuildBestsellers($pageNumber,$nbProducts);
                
        		if (!$result = Db::getInstance()->ExecuteS($query)) return;

        		foreach ($result AS &$row){
        		 	$row['link'] = $link->getProductLink($row['id_product'], $row['link_rewrite'], $row['category'], $row['ean13']);
        		 	$row['id_image'] = Product::defineProductImage($row, $id_lang);
                    $row['price'] = Tools::displayPrice($this->priceCalculation((int)($row['id_product'])));
        		}
        		if (!$result AND !Configuration::get('PS_BLOCK_BESTSELLERS_DISPLAY'))
        			return;

                return $result;
                
            }
            
            function __buildQueryNews($pageNumber, $nbProducts, $orderBy, $orderWay){
                
                $id_lang = Configuration::get('PS_LANG_DEFAULT');
        		$groups = FrontController::getCurrentCustomerGroups();
        		$sqlGroups = (count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1');
                
                
                $query = '
        		SELECT p.*, pl.`description`, pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, p.`ean13`, p.`upc`,
        			i.`id_image`, il.`legend`, t.`rate`, m.`name` AS manufacturer_name, DATEDIFF(p.`date_add`, DATE_SUB(NOW(), INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY)) > 0 AS new,
        			(p.`price` * ((100 + (t.`rate`))/100)) AS orderprice, pa.id_product_attribute
        		FROM `'._DB_PREFIX_.'product` p
        		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.(int)($id_lang).')
        		LEFT OUTER JOIN `'._DB_PREFIX_.'product_attribute` pa ON (p.`id_product` = pa.`id_product` AND `default_on` = 1)
        		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
        		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)($id_lang).')
        		LEFT JOIN `'._DB_PREFIX_.'tax_rule` tr ON (p.`id_tax_rules_group` = tr.`id_tax_rules_group`
        		   AND tr.`id_country` = '.(int)Country::getDefaultCountryId().'
        		   AND tr.`id_state` = 0)
        	    LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = tr.`id_tax`)
        		LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
        		WHERE p.`active` = 1
        		AND DATEDIFF(p.`date_add`, DATE_SUB(NOW(), INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY)) > 0
        		AND p.`id_product` IN (
        			SELECT cp.`id_product`
        			FROM `'._DB_PREFIX_.'category_group` cg
        			LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
        			WHERE cg.`id_group` '.$sqlGroups.'
        		)
        		ORDER BY '.(isset($orderByPrefix) ? pSQL($orderByPrefix).'.' : '').'`'.pSQL($orderBy).'` '.pSQL($orderWay).'
        		LIMIT '.(int)($pageNumber * $nbProducts).', '.(int)($nbProducts);
                
                
                return $query;
            }
            function GetWNewsproduct($pageNumber = 0, $nbProducts = 5, $orderBy = 'date_add', $orderWay = 'DESC'){
                global $link;
                $id_lang = Configuration::get('PS_LANG_DEFAULT');
                $query = $this->__buildQueryNews($pageNumber,$nbProducts,$orderBy,$orderWay);
        
        		if (!$result = Db::getInstance()->ExecuteS($query)) return;
                
                foreach ($result AS &$row){
                    $row['link'] = $link->getProductLink($row['id_product'], $row['link_rewrite'], $row['category'], $row['ean13']);
                    $row['price'] = Tools::displayPrice($this->priceCalculation((int)($row['id_product'])));
        		}
                
                return $result;
            }
            function __buildQueryFeature($p, $n, $orderBy, $orderWay, $active){
                $category = new Category(1, Configuration::get('PS_LANG_DEFAULT'));

        		$id_supplier = (int)(Tools::getValue('id_supplier'));
                $orderByPrefix = 'cp';
                $id_lang = Configuration::get('PS_LANG_DEFAULT');
                
        		$sql = '
        		SELECT p.*, pa.`id_product_attribute`, pl.`description`, pl.`description_short`, pl.`available_now`, pl.`available_later`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, i.`id_image`, il.`legend`, m.`name` AS manufacturer_name, tl.`name` AS tax_name, t.`rate`, cl.`name` AS category_default, DATEDIFF(p.`date_add`, DATE_SUB(NOW(), INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY)) > 0 AS new,
        			(p.`price` * IF(t.`rate`,((100 + (t.`rate`))/100),1)) AS orderprice
        		FROM `'._DB_PREFIX_.'category_product` cp
        		LEFT JOIN `'._DB_PREFIX_.'product` p ON p.`id_product` = cp.`id_product`
        		LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (p.`id_product` = pa.`id_product` AND default_on = 1)
        		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (p.`id_category_default` = cl.`id_category` AND cl.`id_lang` = '.(int)($id_lang).')
        		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.(int)($id_lang).')
        		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
        		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)($id_lang).')
        		LEFT JOIN `'._DB_PREFIX_.'tax_rule` tr ON (p.`id_tax_rules_group` = tr.`id_tax_rules_group`
        		                                           AND tr.`id_country` = '.(int)Country::getDefaultCountryId().'
        	                                           	   AND tr.`id_state` = 0)
        	    LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = tr.`id_tax`)
        		LEFT JOIN `'._DB_PREFIX_.'tax_lang` tl ON (t.`id_tax` = tl.`id_tax` AND tl.`id_lang` = '.(int)($id_lang).')
        		LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
        		WHERE cp.`id_category` = '.(int)($category->id).($active ? ' AND p.`active` = 1' : '').'
        		'.($id_supplier ? 'AND p.id_supplier = '.(int)$id_supplier : '');
        
    			$sql .= ' ORDER BY '.(isset($orderByPrefix) ? $orderByPrefix.'.' : '').'`'.pSQL($orderBy).'` '.pSQL($orderWay).'
    			LIMIT '.(((int)($p) - 1) * (int)($n)).','.(int)($n);

                return $sql;
            }

        	public function getWFeatured($p = 1, $n = 5, $orderBy = 'position', $orderWay = 'ASC', $active= true){
                
                global $link;
                
                
                $sql = $this->__buildQueryFeature($p, $n, $orderBy, $orderWay, $active);
        		if (!$result = Db::getInstance()->ExecuteS($sql)) return;
                foreach ($result AS &$row){
        		 	$row['link'] = $link->getProductLink($row['id_product'], $row['link_rewrite'], $row['category'], $row['ean13']);
                    $row['price'] = Tools::displayPrice($this->priceCalculation((int)($row['id_product'])));
        		}
                return $result;
            }
            
        	public static function priceCalculation($id_product, $id_product_attribute = NULL, $use_tax= false)
        	{
        		
                $id_shop = (int)(Shop::getCurrentShop());
                $id_country = (int)Country::getDefaultCountryId();
                $id_state = 0;
                $id_county = 0;
                $id_currency = (int)(Configuration::get('PS_CURRENCY_DEFAULT'));
                $id_group = _PS_DEFAULT_CUSTOMER_GROUP_;
                $quantity = 1;
                if (Tax::excludeTaxeOption())
                	$use_tax = false;
                $only_reduc = false;
                $decimals = 2;
                $use_reduc = true;
                $with_ecotax = true;
                $specific_price = NULL;
                $use_groupReduction = true;
                
                
        		if ($id_product_attribute === NULL)
        			$product_attribute_label = 'NULL';
        		else
        			$product_attribute_label = ($id_product_attribute === false ? 'false' : $id_product_attribute);
        		$cacheId = $id_product.'-'.$id_shop.'-'.$id_currency.'-'.$id_country.'-'.$id_state.'-'.$id_county.'-'.$id_group.'-'.$quantity.'-'.$product_attribute_label.'-'.($use_tax?'1':'0').'-'.$decimals.'-'.($only_reduc?'1':'0').'-'.($use_reduc?'1':'0').'-'.$with_ecotax;
        
        		// reference parameter is filled before any returns
        		$specific_price = SpecificPrice::getSpecificPrice((int)($id_product), $id_shop, $id_currency, $id_country, $id_group, $quantity);

        
        		// fetch price & attribute price
        		$cacheId2 = $id_product.'-'.$id_product_attribute;

    			$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
    			SELECT p.`price`,
    			'.($id_product_attribute ? 'pa.`price`' : 'IFNULL((SELECT pa.price FROM `'._DB_PREFIX_.'product_attribute` pa WHERE id_product = '.(int)$id_product.' AND default_on = 1), 0)').' AS attribute_price,
    			p.`ecotax`
    			'.($id_product_attribute ? ', pa.`ecotax` AS attribute_ecotax' : '').'
    			FROM `'._DB_PREFIX_.'product` p
    			'.($id_product_attribute ? 'LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON pa.`id_product_attribute` = '.(int)($id_product_attribute) : '').'
    			WHERE p.`id_product` = '.(int)$id_product);

        
            if (!$specific_price || $specific_price['price'] == 0)
        		  $price = (float)$result['price'];
            else
              $price = (float)$specific_price['price'];
        
        		// convert only if the specific price is in the default currency (id_currency = 0)
        		if (!$specific_price OR !($specific_price['price'] > 0 AND $specific_price['id_currency']))
        			$price = Tools::convertPrice($price, $id_currency);
        
        		// Attribute price
        		$attribute_price = Tools::convertPrice(array_key_exists('attribute_price', $result) ? (float)($result['attribute_price']) : 0, $id_currency);
        		if ($id_product_attribute !== false) // If you want the default combination, please use NULL value instead
        			$price += $attribute_price;
        
        		// TaxRate calculation
        		$tax_rate = Tax::getProductTaxRateViaRules((int)$id_product, (int)$id_country, (int)$id_state, (int)$id_county);
        		if ($tax_rate === false)
        			$tax_rate = 0;
        
        		// Add Tax
        		if ($use_tax)
        			$price = $price * (1 + ($tax_rate / 100));
        		$price = Tools::ps_round($price, $decimals);
        
        		// Reduction
        		$reduc = 0;
        		if (($only_reduc OR $use_reduc) AND $specific_price)
        		{
        			if ($specific_price['reduction_type'] == 'amount')
        			{
        				$reduction_amount = $specific_price['reduction'];
        
        				if (!$specific_price['id_currency'])
        					$reduction_amount = Tools::convertPrice($reduction_amount, $id_currency);
        				$reduc = Tools::ps_round(!$use_tax ? $reduction_amount / (1 + $tax_rate / 100) : $reduction_amount, $decimals);
        			}
        			else
        				$reduc = Tools::ps_round($price * $specific_price['reduction'], $decimals);
        		}
        
        		if ($only_reduc)
        			return $reduc;
        		if ($use_reduc)
        			$price -= $reduc;
        
        		// Group reduction
        		if ($use_groupReduction)
        		{
        			if ($reductionFromCategory = (float)(GroupReduction::getValueForProduct($id_product, $id_group)))
        				$price -= $price * $reductionFromCategory;
        			else // apply group reduction if there is no group reduction for this category
        				$price *= ((100 - Group::getReductionByIdGroup($id_group)) / 100);
        		}
        
        		$price = Tools::ps_round($price, $decimals);
        		// Eco Tax
        		if (($result['ecotax'] OR isset($result['attribute_ecotax'])) AND $with_ecotax)
        		{
        			$ecotax = $result['ecotax'];
        			if (isset($result['attribute_ecotax']) && $result['attribute_ecotax'] > 0)
        				$ecotax = $result['attribute_ecotax'];
        
        			if ($id_currency)
        				$ecotax = Tools::convertPrice($ecotax, $id_currency);
        			if ($use_tax)
        			{
        				$taxRate = TaxRulesGroup::getTaxesRate((int)Configuration::get('PS_ECOTAX_TAX_RULES_GROUP_ID'), (int)$id_country, (int)$id_state, (int)$id_county);
        				$price += $ecotax * (1 + ($taxRate / 100));
        			}
        			else
        				$price += $ecotax;
        		}
        		$price = Tools::ps_round($price, $decimals);
        		if ($price < 0)
        			$price = 0;

        		return $price;
        	}
    	}
    }

