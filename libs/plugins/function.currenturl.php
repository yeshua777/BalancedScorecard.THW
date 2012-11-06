      <?php

      function smarty_function_currenturl($params, &$smarty) {
      /* The name of the variable to assign the url string to */

      $var = null;

      /* Process the submitted parameters */

      foreach ($params as $_key=>$_value) {

      switch ($_key) {
 
      case 'var':

      $$_key = $_value;
  
      break;

      default:

      $smarty->trigger_error("currenturl: attribute '$_key' is not accepted",

      E_USER_NOTICE);

      break;

      }

      }

      /* Build the url */

      if($var != null && $var != ''){

      $smarty->assign($var,currentUrlselfURL());

      } else {
      return currentUrlselfURL();
      }
      }

      function currentUrlselfURL() {
      $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
      $protocol = currentUrlstrleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
      $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
      return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];

      }

      function currentUrlstrleft($s1, $s2) {

      return substr($s1, 0, strpos($s1, $s2));

      }

      ?>