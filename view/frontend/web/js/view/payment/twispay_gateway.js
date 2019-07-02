/**
 * Twispay_PaymentGateway Magento JS component
 *
 * @category    Twispay
 * @package     Twispay_PaymentGateway
 * @author      Webliant Software
 */
define(
  [
      'uiComponent',
      'Magento_Checkout/js/model/payment/renderer-list'
  ],
  function (
      Component,
      rendererList
  ) {
      'use strict';
      rendererList.push(
          {
              type: 'twispay_gateway',
              component: 'Twispay_PaymentGateway/js/view/payment/method-renderer/twispay_gateway'
          }
      );
      /** Add view logic here if needed */
      return Component.extend({});
  }
);
