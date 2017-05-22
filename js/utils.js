var updateSelect = function(select, values, pickFirst) {
  var list = select.parentElement.getElementsByClassName('mdl-menu')[0];
  list.innerHTML = '';
  for (var id in values) {
    var li = document.createElement('li');
    li.className = 'mdl-menu__item';
    li.setAttribute('data-val', id);
    li.innerHTML = values[id];
    list.appendChild(li);
  }
  getmdlSelect.init('.getmdl-select');
  if (pickFirst && Object.keys(values)[0]) {
    updateInput(select, Object.keys(values)[0], values[Object.keys(values)[0]]);
  }
  else {
    updateInput(select, '', '');
  }
};


var updateInput = function(input, value, displayedValue) {
  if (typeof value === 'undefined') throw 'Value is not defined for ' + input.name;
  if (input.parentElement.classList.contains('getmdl-select')) {
    if (typeof displayedValue == 'undefined') displayedValue = value;
    input.value = displayedValue;
    input.setAttribute('data-val', value);
  }
  else input.value = value;
  if (value.toString().length > 0) {
    input.parentElement.classList.add('is-dirty');
  }
  else {
    input.parentElement.classList.remove('is-dirty');
  }
};

var getColorFromString = function(str) {
  var nameSum = 0;
  for (var idS in str) nameSum += str.charCodeAt(idS);
  var nameSum2 = nameSum + str.charCodeAt(0);
  return 'hsl(' + Math.floor(nameSum * nameSum * 1.04 % 360) + ', ' + Math.floor(40 + (nameSum2 * nameSum2 * 1.02) % 50) + '%, ' + Math.floor(55 + (nameSum2 * 10) % 20) + '%)';
};
