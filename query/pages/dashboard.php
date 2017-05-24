<?php

require_once "../classes/Components.class.php";





echo <<<HTML

  <a class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--6-col lo07-card-with-icon" href="?display=etudiants">
    <div class="lo07-card-title">
      Étudiants
      <i class="material-icons lo07-card-icon">person</i>
    </div>
  </a>

  <a class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--6-col lo07-card-with-icon lo07-card-green" href="?display=reglement">
    <div class="lo07-card-title">
      Règlements
      <i class="material-icons lo07-card-icon">check</i>
    </div>
  </a>

  <a class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--6-col lo07-card-with-icon lo07-card-yellow" href="?display=cursus">
    <div class="lo07-card-title">
      Cursus
      <i class="material-icons lo07-card-icon">trending_up</i>
    </div>
  </a>

  <a class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--6-col lo07-card-with-icon lo07-card-red" href="?display=elements">
    <div class="lo07-card-title">
      Éléments de formation
      <i class="material-icons lo07-card-icon">bubble_chart</i>
    </div>
  </a>

HTML;
