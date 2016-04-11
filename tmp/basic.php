<form method="post" action="https://secure.payfort.com/ncol/test/orderstandard.asp" id=form1
name=form1>
<!-- general parameters: see General Payment Parameters -->
<?php 
$orderId = rand();

$shasignparam = "ACCEPTURL=http://tapetickets.demos.classicinformatics.com/tmp/index.phpclassicinformatics123#AMOUNT=1500classicinformatics123#CURRENCY=AEDclassicinformatics123#LANGUAGE=en_USclassicinformatics123#ORDERID=".$orderId."classicinformatics123#PSPID=testclassicclassicinformatics123#";
$sha1sign = sha1($shasignparam);
$sha1sign = strtoupper($sha1sign);
?>
<input type="hidden" name="PSPID" value="testclassic">
<input type="hidden" name="ORDERID" value="<?php echo $orderId ;?>">
<input type="hidden" name="AMOUNT" value="1500">
<input type="hidden" name="CURRENCY" value="AED">
<input type="hidden" name="LANGUAGE" value="en_US">
<!-- optional customer details, highly recommended for fraud prevention: see General
parameters and optional customer details -->
<input type="hidden" name="SHASIGN" value="<?php echo $sha1sign; ?>">
<!-- dynamic template page: see Look & Feel of the Payment Page -->
<!-- payment methods/page specifics: see Payment method and payment page
specifics -->
<input type="hidden" name="ACCEPTURL" value="http://tapetickets.demos.classicinformatics.com/tmp/index.php">
<input type="submit" value="Click to Pay" id="submit2" name="SUBMIT2">
</form>