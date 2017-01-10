<script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
<script src="//pilot.datatrans.biz/upp/payment/js/datatrans-1.0.2.js"></script>

<form id="paymentForm"
      <?php foreach($this->params as $key => $value) : ?>
      data-<?=$key?>="<?=$value?>"
      <?php endforeach; ?>
    <button id="paymentButton"><?=$this->translate("Pay")?></button>
</form>
<script type="text/javascript">
    $("#paymentButton").click(function () {
        Datatrans.startPayment({'form': '#paymentForm'});
    });
</script>