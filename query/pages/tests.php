<?php

require_once "../classes/Components.class.php";



$checkbox1 = Components::checkbox(array(
  "name" => "name-checkbox1",
  "label" => "Checkbox label",
  "checked" => true
));
$checkbox2 = Components::checkbox(array(
  "name" => "name-checkbox2",
  "label" => "Checkbox label"
));

$radios = Components::radios(array(
  "name" => "name-radios",
  "list" => array(
    "1" => "One",
    "2" => "Two",
    "3" => "Three"
  ),
  "default" => "3"
));

$select = Components::select(array(
  "name" => "name-select",
  "label" => "Label",
  "list" => array(
    "One",
    "Two",
    "Three",
    "Four"
  ),
  "default" => ""
));

$text = Components::text(array(
  "name" => "name-text",
  "label" => "Texte"
));
$number = Components::number(array(
  "name" => "name-number",
  "label" => "Nombre"
));

$textarea = Components::textarea(array(
  "name" => "name-textarea",
  "label" => "Long texte"
));




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
