<?php

echo <<<HTML

<form method='post' action='query/actions/add.php' data-onresponse="onresponse">
  <input type="text" name="test" />
  <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent" onclick="this.form.submit();">Envoyer</button>
</form>


<script>
  var onresponse = function(res) {
    
  };
</script>

HTML;
