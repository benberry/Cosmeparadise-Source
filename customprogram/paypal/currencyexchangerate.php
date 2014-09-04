<?php
$FromCurrency = $_GET["FromCurrency"];
$ToCurrency = $_GET["ToCurrency"];
//First we need to include the class file of the Adaptive Payments
include 'adaptivepayments.php';

// Create an instance, you'll make all the necessary requests through this
// object, if you digged through the code, you'll notice an AdaptivePaymentsProxy class
// wich has in it all of the classes corresponding to every object mentioned on the
// documentation of the API
$ap = new AdaptivePayments();

// Our request envelope
$requestEnvelope = new RequestEnvelope();
$requestEnvelope->detailLevel = 0;
$requestEnvelope->errorLanguage = 'en_US';

// Our base amount, in other words the currency we want to convert to
// other currency type. It's very straighforward, just have a public
// prop. to hold de amount and the current code.
$baseAmountList = new CurrencyList();
$baseAmountList->currency = array( 'amount' => 1, 'code' => $FromCurrency  );

// Our target currency type. Given that I'm from Mexico I would like to
// see it in mexican pesos. Again, just need to provide the code of the
// currency. On the docs you'll have access to the complete list of codes
$convertToCurrencyList = new CurrencyCodeList();
$convertToCurrencyList->currencyCode = $ToCurrency;

// Now create a instance of the ConvertCurrencyRequest object, which is
// the one necessary to handle this request.
// This object takes as parameters the ones we previously created, which
// are our base currency, our target currency, and the req. envelop
$ccReq = new ConvertCurrencyRequest();
$ccReq->baseAmountList = $baseAmountList;
$ccReq->convertToCurrencyList = $convertToCurrencyList;
$ccReq->requestEnvelope = $requestEnvelope;

// And finally we call the ConvertCurrency method on our AdaptivePayment object,
// and assign whatever result we get to our variable
$result = $ap->ConvertCurrency($ccReq);

// Given that our result should be a ConvertCurrencyResponse object, we can
// look into its properties for further display/processing purposes
$resultingCurrencyList = $result->estimatedAmountTable->currencyConversionList[0];
$baseAmount = $resultingCurrencyList->baseAmount->amount;
$baseAmountCode = $resultingCurrencyList->baseAmount->code;
$convertedAmount = $resultingCurrencyList->currencyList->currency[0]->amount;
$convertedAmountCode = $resultingCurrencyList->currencyList->currency[0]->code;

//echo '<br /> $' . $baseAmount . ' ' . $baseAmountCode . ' is $' . $convertedAmount . ' ' . $convertedAmountCode;
//
//// And here just for the sake of knowing how we get the result from Paypal's API
//echo '<pre>';
//print_r($result);
//echo '</pre>';
echo $convertedAmount;