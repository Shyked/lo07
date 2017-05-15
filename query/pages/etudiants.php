<?php

/* Formulaire qui serait sous la liste des étudiants visible après un clic
 pour ajouter un étudiant */

echo <<<HTML



<script>

function AfficherMasquer() {
  divInfo = document.getElementById('divacacher');
  if (divInfo.style.display == 'none')
  divInfo.style.display = 'block';
  else
  divInfo.style.display = 'none';
 }
 </script>

 <button class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--colored" onclick="AfficherMasquer();">
   <i class="material-icons">add</i>
 </button>



<div id="divacacher" style="display:none;">
<form method='post' action='query/actions/etu.php' data-onresponse="onresponse">
  <p>
    <label for "numEtu">Numéro étudiant</label> <input type="text" name="numEtu" id="numEtu" /> </br>
    <label for="nom">Nom</label> <input type="text" name="nom" id="nom"/> </br>
    <label for="prenom">Prénom</label> <input type="text" name="prenom" id="prenom" /> </br>
    <label for="admission"> Admission </label>
    <select name="admission" id="admission">
      <option value="tc">TC</option>
      <option value="br">BR</option>
    </select> </br>
    <label for="filiere"> Filière </label>
    <select name="filiere" id="filiere">
      <option value="tc">?</option>
      <option value="mpl">MPL</option>
      <option value="msi">MSI</option>
      <option value="mri">MRI</option>
      <option value="lib">LIB</option>
    </select>

  </p>
  <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent" onclick="this.form.submit();">Envoyer</button>
</form>
</div>


<script>
  var onresponse = function(res) {

  };
</script>


HTML;
