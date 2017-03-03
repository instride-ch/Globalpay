<form id="paymentForm"
      <?php foreach($this->params as $key => $value) : ?>
      data-<?=$key?>="<?=$value?>"
      <?php endforeach; ?>
</form>
<script type="text/javascript">
    if(!window.jQuery){
        var jq = document.createElement('script'); jq.type = 'text/javascript';
        jq.src = '//code.jquery.com/jquery-1.11.2.min.js';
        document.getElementsByTagName('head')[0].appendChild(jq);
    }
    $(document).ready(function() {
        $.getScript('//pilot.datatrans.biz/upp/payment/js/datatrans-1.0.2.js', function () {
            Datatrans.startPayment({
                'form': '#paymentForm',
                'closed': function() {
                    $(document).trigger("DatatransLightboxClose");
                    $('#paymentForm').remove();
                }
            });
        });
    });
</script>
