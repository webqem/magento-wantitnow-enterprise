<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_SalesRule
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * SalesRule Validator Model
 *
 * Allows dispatching before and after events for each controller action
 *
 * @category   Mage
 * @package    Mage_SalesRule
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Webqem_Mailcall_Model_SalesRule_Validator extends Mage_SalesRule_Model_Validator
{
    
     /**
     * Quote item free shipping ability check
     * This process not affect information about applied rules, coupon code etc.
     * This information will be added during discount amounts processing
     *
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @return  Mage_SalesRule_Model_Validator
     */
	 
	 /*
	 * compatibility Magento Enterprise > 1.0
	 */
    public function processFreeShipping(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        $address = $this->_getAddress($item);
        $item->setFreeShipping(false);

        foreach ($this->_getRules() as $rule) {
            /* @var $rule Mage_SalesRule_Model_Rule */
            if (!$this->_canProcessRule($rule, $address)) {
                continue;
            }

            if (!$rule->getActions()->validate($item)) {
                continue;
            }

            switch ($rule->getSimpleFreeShipping()) {
                case Mage_SalesRule_Model_Rule::FREE_SHIPPING_ITEM:
                    $item->setFreeShipping($rule->getDiscountQty() ? $rule->getDiscountQty() : true);
                    break;

                case Mage_SalesRule_Model_Rule::FREE_SHIPPING_ADDRESS:
                    $address->setFreeShipping(true);
                    break;
					
				case Webqem_Mailcall_Model_Carrier_Mailcall::MAILCALL_FREE_SHIPPING_PROMO:
					$address->setFreeShipping(-1);
                    break;
            }
            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }
        return $this;
    }
	
	/*
	 * compatibility Magento == 1.32
	 */
	 public function process(Mage_Sales_Model_Quote_Item_Abstract $item){
		//Changed the version number check to 1.0 for enterprise - Mike @ Mailcall 06/12/2012
		 if(Mage::getVersion()<'1.0'){
			 $this->_process_1324($item);
		 }else{
			 return parent::process($item);
		 }
	 }
	
	protected function _process_1324(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        $item->setFreeShipping(false);
        $item->setDiscountAmount(0);
        $item->setBaseDiscountAmount(0);
        $item->setDiscountPercent(0);

        $quote = $item->getQuote();
        if ($item instanceof Mage_Sales_Model_Quote_Address_Item) {
            $address = $item->getAddress();
        } elseif ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }

        $customerId = $quote->getCustomerId();
        $ruleCustomer = Mage::getModel('salesrule/rule_customer');
        $appliedRuleIds = array();

        
		//Commented out for Enterprise version
		foreach ($this->_rules as $rule) {
            /* @var $rule Mage_SalesRule_Model_Rule */
            /**
             * already tried to validate and failed
             */
            if ($rule->getIsValid() === false) {
                continue;
            }

            if ($rule->getIsValid() !== true) {
                /**
                 * too many times used in general
                 */
                if ($rule->getUsesPerCoupon() && ($rule->getTimesUsed() >= $rule->getUsesPerCoupon())) {
                    $rule->setIsValid(false);
                    continue;
                }
                /**
                 * too many times used for this customer
                 */
                $ruleId = $rule->getId();
                if ($ruleId && $rule->getUsesPerCustomer()) {
                    $ruleCustomer->loadByCustomerRule($customerId, $ruleId);
                    if ($ruleCustomer->getId()) {
                        if ($ruleCustomer->getTimesUsed() >= $rule->getUsesPerCustomer()) {
                            continue;
                        }
                    }
                }
                $rule->afterLoad();
                /**
                 * quote does not meet rule's conditions
                 */
                if (!$rule->validate($address)) {
                    $rule->setIsValid(false);
                    continue;
                }
                /**
                 * passed all validations, remember to be valid
                 */
                $rule->setIsValid(true);
            }

            /**
             * although the rule is valid, this item is not marked for action
             */
            if (!$rule->getActions()->validate($item)) {
                continue;
            }
            $qty = $item->getQty();
            if ($item->getParentItem()) {
                $qty*= $item->getParentItem()->getQty();
            }
            $qty = $rule->getDiscountQty() ? min($qty, $rule->getDiscountQty()) : $qty;
            $rulePercent = min(100, $rule->getDiscountAmount());
            $discountAmount = 0;
            $baseDiscountAmount = 0;
            switch ($rule->getSimpleAction()) {
                case 'to_percent':
                    $rulePercent = max(0, 100-$rule->getDiscountAmount());
                    //no break;

                case 'by_percent':
                    if ($step = $rule->getDiscountStep()) {
                        $qty = floor($qty/$step)*$step;
                    }
                    $discountAmount    = ($qty*$item->getCalculationPrice() - $item->getDiscountAmount()) * $rulePercent/100;
                    $baseDiscountAmount= ($qty*$item->getBaseCalculationPrice() - $item->getBaseDiscountAmount()) * $rulePercent/100;

                    if (!$rule->getDiscountQty() || $rule->getDiscountQty()>$qty) {
                        $discountPercent = min(100, $item->getDiscountPercent()+$rulePercent);
                        $item->setDiscountPercent($discountPercent);
                    }
                    break;

                case 'to_fixed':
                    $quoteAmount = $quote->getStore()->convertPrice($rule->getDiscountAmount());
                    $discountAmount    = $qty*($item->getCalculationPrice()-$quoteAmount);
                    $baseDiscountAmount= $qty*($item->getBaseCalculationPrice()-$rule->getDiscountAmount());
                    break;

                case 'by_fixed':
                    if ($step = $rule->getDiscountStep()) {
                        $qty = floor($qty/$step)*$step;
                    }
                    $quoteAmount = $quote->getStore()->convertPrice($rule->getDiscountAmount());
                    $discountAmount    = $qty*$quoteAmount;
                    $baseDiscountAmount= $qty*$rule->getDiscountAmount();
                    break;

                case 'cart_fixed':
                    $cartRules = $address->getCartFixedRules();
                    if (!isset($cartRules[$rule->getId()])) {
                        $cartRules[$rule->getId()] = $rule->getDiscountAmount();
                    }
                    if ($cartRules[$rule->getId()] > 0) {
                        $quoteAmount = $quote->getStore()->convertPrice($cartRules[$rule->getId()]);
                        $discountAmount = min($item->getRowTotal(), $quoteAmount);
                        $baseDiscountAmount = min($item->getBaseRowTotal(), $cartRules[$rule->getId()]);
                        $cartRules[$rule->getId()] -= $baseDiscountAmount;
                    }
                    $address->setCartFixedRules($cartRules);
                    break;

                case 'buy_x_get_y':
                    $x = $rule->getDiscountStep();
                    $y = $rule->getDiscountAmount();
                    if (!$x || $y>=$x) {
                        break;
                    }
                    $buy = 0; $free = 0;
                    while ($buy+$free<$qty) {
                        $buy += $x;
                        if ($buy+$free>=$qty) {
                            break;
                        }
                        $free += min($y, $qty-$buy-$free);
                        if ($buy+$free>=$qty) {
                            break;
                        }
                    }
                    $discountAmount    = $free*$item->getCalculationPrice();
                    $baseDiscountAmount= $free*$item->getBaseCalculationPrice();
                    break;
            }

            $result = new Varien_Object(array(
                'discount_amount'      => $discountAmount,
                'base_discount_amount' => $baseDiscountAmount,
            ));
            Mage::dispatchEvent('salesrule_validator_process', array(
                'rule'    => $rule,
                'item'    => $item,
                'address' => $address,
                'quote'   => $quote,
                'qty'     => $qty,
                'result'  => $result,
            ));

            $discountAmount = $result->getDiscountAmount();
            $baseDiscountAmount = $result->getBaseDiscountAmount();

            $discountAmount     = $quote->getStore()->roundPrice($discountAmount);
            $baseDiscountAmount = $quote->getStore()->roundPrice($baseDiscountAmount);
            $discountAmount     = min($item->getDiscountAmount()+$discountAmount, $item->getRowTotal());
            $baseDiscountAmount = min($item->getBaseDiscountAmount()+$baseDiscountAmount, $item->getBaseRowTotal());

            $item->setDiscountAmount($discountAmount);
            $item->setBaseDiscountAmount($baseDiscountAmount);

            switch ($rule->getSimpleFreeShipping()) {
                case Mage_SalesRule_Model_Rule::FREE_SHIPPING_ITEM:
                    $item->setFreeShipping($rule->getDiscountQty() ? $rule->getDiscountQty() : true);
                    break;

                case Mage_SalesRule_Model_Rule::FREE_SHIPPING_ADDRESS:
                    $address->setFreeShipping(true);
                    break;
				case Webqem_Mailcall_Model_Carrier_Mailcall::MAILCALL_FREE_SHIPPING_PROMO:
					$address->setFreeShipping(-1);
                    break;
            }

            $appliedRuleIds[$rule->getRuleId()] = $rule->getRuleId();

            if ($rule->getCouponCode() && ( strtolower($rule->getCouponCode()) == strtolower($this->getCouponCode()))) {
                $address->setCouponCode($this->getCouponCode());
            }

            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }
        $item->setAppliedRuleIds(join(',',$appliedRuleIds));
        $address->setAppliedRuleIds($this->mergeIds($address->getAppliedRuleIds(), $appliedRuleIds));
        $quote->setAppliedRuleIds($this->mergeIds($quote->getAppliedRuleIds(), $appliedRuleIds));
        return $this;
    }

   
}
