Payson Payment Module - OpenCart

Requirements: 

* Curl

-------------------------------------- 
2013-09-17  (Module 2.8.1)

* Improve: Product list logging.
* Improve: Validation of Order totals to ignore.
* Improve:  optional product list by CREDITCARD / BANK.
* Payment details to database. 

-------------------------------------- 
2013-08-23  (Module 2.8)

* Improve: with free chekout 
* Improve: conversion of invoice fee at chekout.
* Removed code.
* updated version number
* Added logotype in the payment method selection.
* Fixed an issue with 'Order totals to ignore'.

-------------------------------------- 
2013-06-24  (Module 2.7)

* Improve: Geo zone.

-------------------------------------- 
2013-06-14  (Module 2.6)+++

* Improve: Language code SE and SV.
* fixed an issue with invoice fee when it appears in the checkout.
* Updated the argument for PAYSON-MODULE-INFO.

-------------------------------------- 
2013-05-30  (Module 2.5)
 
* Fixed an issue with purchasing gift cards
* Improved error logs even more

-------------------------------------- 
2013-05-28  (Module 2.4)

* Improved error logging
* Improved calculation of order items
* Fixed an issue with the calculation of order total items

-------------------------------------- 
2013-05-27  (Module 2.3)

* Removed the logotype from the payment method selection as this caused the order page in admin to crash for Payson invoice orders
* Moved the invoice terms to confirm step to avoid messing with custom themes.
* Simplified tax calculation for products that are sent to Payson

-------------------------------------- 
2013-05-20  (Module 2.2)

* No longer tries to disable output-compression in module.
* Added a check for minimum order value for using Payson Invoice so it will not be displayed if total is below this value.
* Removed test-shopper@payson.se as the default user when using test mode
* Now also using order id from PaymentDetails
* Re-added use of secure word in return from Payson

-------------------------------------- 
2013-03-12  (Module 2.1)

* Added a variable to keep the module version number
* No longer creates an order with Denied status when denied at Payson as this caused inventory to change and an confirmation mail being sent to customer
* If order already has been created with IPN/Return when either returns to store the order will be updated instead of confirmed again.
* Fixed an issue when using multiple currencies
* Removed some code that are no longer used by invoice module
* Fixed an issue were there would be duplicate items in order list when no tax were added to the product
* Updated version number
* Fixed an issue with test mode were credentials were wrong.
* Fixed an issue with test mode were receiver could be missing.
* Now displays the invoice fee when chosing payment method
* Added an error message when an purchase is denied
* Module are now using OpenCart internal methods to calculate order totals
* Added order totals to ignore in Payson module configuration
* Merged a lot of code from PaysonInvoice to PaysonDirect
* Removed some configuration from PaysonInvoice as same information are entered in PaysonDirect module
* Reorganized PaysonDirect module configration elements
* Now using Payment details on return from Payson instead of relying only on IPN calls
* Better error handling
* No longer creating two tokens for every purchase
* Removed extra code for test mode and integrated this is standard API.

-------------------------------------- 
2013-03-12  (Module 1.5)

*Improve: Support Sandbox.
*Improve: Support discount code.
*Improve: Support discount Girt Voucher.
*Improve: Support discount Low Order Fee.
*Improve: Support discount Handling Fee.
*Improve: Support Output Compression Level.
*Improve: Support Output Compression Level.
*Bugfix:  With language.
*Bugfix:  With denied payments.

-------------------------------------- 
2012-11-13  (Module 1.4)

*Bugfix: Problem with free shipping.

-------------------------------------- 
2012-08-23  (Module 1.3)

*Bugfix: Problem with the configurable products.

-------------------------------------- 
2012-07-04  (Module 1.2)

*Bugfix: Problem with radio-buttons (Paysondirect).
*Bugfix: Problem with Payson logo
*Bugfix: translation translation of tax (Paysoninvoice).
 


-------------------------------------- 
2012-06-27  (Module 1.1)

*Bugfix: currency.

*Bugfix: Secure word (Payson invoice).

*Bugfix: Swedish letters צהו in the admin.

*Improve: Swedish language file.

--------------------------------------
2011-06-14  (Module 1.0)
* New module
