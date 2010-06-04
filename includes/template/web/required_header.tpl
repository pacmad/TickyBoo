  <!-- Required Header .tpl Start -->
  <!--
  <link rel="stylesheet" type="text/css" href="css/flick/jquery-ui-1.8.1.custom.css" media="screen" />
  <link rel='stylesheet' href='css/jquery.tooltip.css' media="screen" />
   -->
  {minify type='css' files='css/flick/jquery-ui-1.8.1.custom.css,css/jquery.tooltip.css'}

  <link rel="icon" href="favicon.ico" type="image/x-icon" />
   
  {minify type='js' base='scripts/jquery' files='jquery.min.js,jquery.ui.js,jquery.ajaxmanager.js,jquery.json-2.2.min.js,jquery.form.js,jquery.validate.min.js,jquery.validate.add-methods.js,jquery.simplemodal-1.3.5.js,jquery.countdown.pack.js,jquery.tooltip.min.js'}
   
  {minify type='js' files='scripts/shop.jquery.forms.js'}

  <script type="text/javascript">
  	var lang = new Object();
  	lang.required = '{!mandatory!}';        lang.phone_long = '{!phone_long!}'; lang.phone_short = '{!phone_short!}';
  	lang.fax_long = '{!fax_long!}';         lang.fax_short = '{!fax_short!}';
  	lang.email_valid = '{!email_valid!}';   lang.email_match = '{!email_match!}';
  	lang.pass_short = '{!pass_too_short!}'; lang.pass_match = '{!pass_match!}';
  	lang.not_number = '{!not_number!}';     lang.condition ='{!check_condition!}';
  </script>

  {literal}
  <style type="text/css">
    #simplemodal-overlay {background-color:#ffffff;}
    #simplemodal-container {background-color:#ffffff; border:2px solid #004088; padding:12px;}
    #simplemodal-container a.modalCloseImg {
      background:url(images/unchecked.gif) no-repeat; /* adjust url as required */
      width:25px; height:29px;
      display:inline; z-index:3200;
      position:absolute; top:-15px;
      right:-18px; cursor:pointer;
    }
  </style>

  <script type="text/javascript">
    jQuery(document).ready(function(){
      jQuery("*[class*='has-tooltip']").tooltip({
        delay:40,
        showURL:false,
        bodyHandler: function() {
          if(jQuery(this).children('*[class*="is-tooltip"]').first().html() != ''){
            return jQuery(this).children('*[class*="is-tooltip"]').first().html();
          }else{
            return false;
          }
        }
      });
    });

    var showDialog = function(element){
      jQuery.get(jQuery(element).attr('href'),
        function(data){
          jQuery("#showdialog").html(data);
          jQuery("#showdialog").modal({
            autoResize:true,
            maxHeight:500,
            maxWidth:800
          });
        }
      );
      return false;
    }

    function BasicPopup(a) {
      showDialog(a);
      /*
      var url = a.href;
      if (win = window.open(url, a.target || "_blank", 'width=640,height=200,left=300,top=300,toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=0,resizable=0'))
      {
        win.focus();
        win.focus();
        return false;
      }
      */
      return false;
    }
  </script>
  {/literal}
  <!-- Required Header .tpl  end -->