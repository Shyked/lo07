<?php

require_once "../classes/Components.class.php";



$checkbox1 = Components::checkbox("name-checkbox1", "Checkbox label", true);
$checkbox2 = Components::checkbox("name-checkbox2", "Checkbox label");

$radios = Components::radios("name-radios", array(
  "1" => "One",
  "2" => "Two",
  "3" => "Three"
), "3");

$select = Components::select("name-select", "Label", array(
  "One",
  "Two",
  "Three",
  "Four"
), "");

$text = Components::text("name-text", "Texte");
$number = Components::number("name-number", "Nombre");

$textarea = Components::textarea("name-textarea", "Long texte");




echo <<<HTML

  <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--12-col">
    <div class="lo07-card-title">
      Formulaire delamorkitu
    </div>

    <div class="lo07-card-body">
      <form method='post' action='query/actions/test.php' data-onresponse="onresponse">
        <h5>Checkbox</h5>
        <div class="lo07-text-block">{$checkbox1}</div>
        <div class="lo07-text-block">{$checkbox2}</div>
        <h5>Radios</h5>
        <div class="lo07-text-block">{$radios}</div>
        <h5>Select</h5>
        <div class="lo07-text-block">{$select}</div>
        <h5>Texte</h5>
        <div class="lo07-text-block">{$text}</div>
        <div class="lo07-text-block">{$number}</div>
        <div class="lo07-text-block">{$textarea}</div>
        <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent lo07-submit" onclick="this.form.submit();">Envoyer</button>
      </form>
    </div>
  </div>

  <script>
    var onresponse = function(response, error) {

    };
  </script>

HTML;
