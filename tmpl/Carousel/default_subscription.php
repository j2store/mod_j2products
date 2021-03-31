<?php
defined('_JEXEC') or die;
$subscriptionproducts = $product->params->get('subscriptionproduct',array());

$subscription_period_units = isset($subscriptionproducts->subscription_period_units)? $subscriptionproducts->subscription_period_units: 1;
$subscription_period = isset($subscriptionproducts->subscription_period)? $subscriptionproducts->subscription_period: 'D';
$subscription_length = isset($subscriptionproducts->subscription_length)? $subscriptionproducts->subscription_length: 0;
$subscription_signup_fee = isset($subscriptionproducts->subscription_signup_fee)? $subscriptionproducts->subscription_signup_fee: 0;
$subscription_recurring_type = isset($subscriptionproducts->recurring_type)? $subscriptionproducts->recurring_type: 'multiple';
$subscription_free_trial = isset($subscriptionproducts->subscription_free_trial)? $subscriptionproducts->subscription_free_trial: 0;
$subscription_trial_period = isset($subscriptionproducts->subscription_trial_period)? $subscriptionproducts->subscription_trial_period: 'D';
switch ($subscription_trial_period){
    case 'D':
        $subscription_trial_period_string_text = 'COM_J2STORE_PRODUCT_SUBSCRIPTION_DAYS';
        break;
    case 'W':
        $subscription_trial_period_string_text = 'COM_J2STORE_PRODUCT_SUBSCRIPTION_WEEKS';
        break;
    case 'M':
        $subscription_trial_period_string_text = 'COM_J2STORE_PRODUCT_SUBSCRIPTION_MONTHS';
        break;
    case 'Y':
        $subscription_trial_period_string_text = 'COM_J2STORE_PRODUCT_SUBSCRIPTION_YEAR';
        break;
}
$subscription_period_string = JText::_('COM_J2STORE_PRODUCT_SUBSCRIPTION_DAYS');
switch ($subscription_period){
	case 'D':
        $subscription_period_string = JText::_('COM_J2STORE_PRODUCT_SUBSCRIPTION_DAYS');
        $subscription_period_string_text = 'COM_J2STORE_PRODUCT_SUBSCRIPTION_DAYS';
        break;
    case 'W':
        $subscription_period_string = JText::_('COM_J2STORE_PRODUCT_SUBSCRIPTION_WEEKS');
        $subscription_period_string_text = 'COM_J2STORE_PRODUCT_SUBSCRIPTION_WEEKS';
        break;
    case 'M':
        $subscription_period_string = JText::_('COM_J2STORE_PRODUCT_SUBSCRIPTION_MONTHS');
        $subscription_period_string_text = 'COM_J2STORE_PRODUCT_SUBSCRIPTION_MONTHS';
        break;
    case 'Y':
        $subscription_period_string = JText::_('COM_J2STORE_PRODUCT_SUBSCRIPTION_YEAR');
        $subscription_period_string_text = 'COM_J2STORE_PRODUCT_SUBSCRIPTION_YEAR';
        break;
}
$subscription_period_units_text = $subscription_period_units;
switch ($subscription_period_units){
	case '1':
        $subscription_period_units_text = JText::_('COM_J2STORE_PRODUCT_SUBSCRIPTION_PERIOD_UNITS_EVERY');
        break;
    case '2':
        $subscription_period_units_text = JText::_('COM_J2STORE_PRODUCT_SUBSCRIPTION_PERIOD_UNITS_EVERY2');
        break;
    case '3':
        $subscription_period_units_text = JText::_('COM_J2STORE_PRODUCT_SUBSCRIPTION_PERIOD_UNITS_EVERY3');
        break;
    case '4':
        $subscription_period_units_text = JText::_('COM_J2STORE_PRODUCT_SUBSCRIPTION_PERIOD_UNITS_EVERY4');
        break;
    case '5':
        $subscription_period_units_text = JText::_('COM_J2STORE_PRODUCT_SUBSCRIPTION_PERIOD_UNITS_EVERY5');
        break;
    case '6':
        $subscription_period_units_text = JText::_('COM_J2STORE_PRODUCT_SUBSCRIPTION_PERIOD_UNITS_EVERY6');
        break;
}
$plugin = JPluginHelper::getPlugin('j2store', 'app_subscriptionproduct');
$subscription_params = new JRegistry($plugin->params);
$showDuration = $subscription_params->get('show_duration', 1);
if($showDuration){
    ?>
<div class="subscriptionproducts">
	<span class="j2store-subscription_duration_text"><b>
<?php if($subscription_length >= 0){?>
		<?php
		if($subscription_recurring_type != 'single'){
			echo $subscription_period_units_text; ?> <?php echo $subscription_period_string;
		}
		if($subscription_length > 0) {
			echo ' ' . JText::_('J2STORE_PRODUCT_SUBSCRIPTION_TEXT_FOR') . ' ';
		}
		if($subscription_length > 1){
			echo ' '.$subscription_length.' '.JText::_($subscription_period_string_text.'_PLURAL');
		} else if($subscription_length == 1){
			echo ' '.$subscription_length.' '.JText::_($subscription_period_string_text);
		}
		if($subscription_free_trial > 0){
            echo ' '.JText::_('J2STORE_PRODUCT_SUBSCRIPTION_TEXT_WITH_A').' ';
            if($subscription_free_trial > 1){
                echo ' '.$subscription_free_trial.' '.JText::_($subscription_trial_period_string_text.'_PLURAL');
            } else if($subscription_free_trial == 1){
                echo ' '.$subscription_free_trial.' '.JText::_($subscription_trial_period_string_text);
            }
            echo ' '.JText::_('J2STORE_PRODUCT_SUBSCRIPTION_TEXT_FREE_TRIAL');
        }
		if($subscription_signup_fee){
			echo ' '.JText::_('J2STORE_PRODUCT_SUBSCRIPTION_TEXT_AND_A').' ';
			echo J2Store::product()->displayPrice($subscription_signup_fee, $product);
			echo ' '.JText::_('J2STORE_PRODUCT_SUBSCRIPTION_SIGN_UP_FEE');
		}
		?>
<?php } else {
?>
	<?php echo JText::_('J2STORE_PRODUCT_SUBSCRIPTION_TEXT_FOR_LIFE_TIME').' ';
	?>
	<?php
    if($subscription_free_trial > 0){
        echo ' '.JText::_('J2STORE_PRODUCT_SUBSCRIPTION_TEXT_WITH_A').' ';
        if($subscription_free_trial > 1){
            echo ' '.$subscription_free_trial.' '.JText::_($subscription_trial_period_string_text.'_PLURAL');
        } else if($subscription_free_trial == 1){
            echo ' '.$subscription_free_trial.' '.JText::_($subscription_trial_period_string_text);
        }
        echo ' '.JText::_('J2STORE_PRODUCT_SUBSCRIPTION_TEXT_FREE_TRIAL');
    }
	if($subscription_signup_fee){
		echo ' '.JText::_('J2STORE_PRODUCT_SUBSCRIPTION_TEXT_AND_A').' ';
		echo J2Store::product()->displayPrice($subscription_signup_fee, $product);
		echo ' '.JText::_('J2STORE_PRODUCT_SUBSCRIPTION_SIGN_UP_FEE');
	}
	?>
<?php
}?>
		</b></span>
</div>
<?php } ?>