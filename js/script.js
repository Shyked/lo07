(function() {

  var PageManager = function(pages) {

    this.pages = pages;
    this.currentPage = null;
    this.classPrefix = 'pagemanager-';
    this.pageParam = 'display';
    this.idParam = 'id';
    this.siteName = document.title;


    this._init = function() {
      var menuHtml = "";
      for (var id in this.pages) {
        menuHtml += "\
          <a class='mdl-navigation__link mdl-js-ripple-effect lo07-ripple-white' href='?display=" + id + "'><span class='mdl-ripple'></span>\
            <i class='material-icons' role='presentation'>" + this.pages[id].icon + "</i>\
            " + this.pages[id].label + "\
          </a>\
        ";
      }
      $("#" + this.classPrefix + "menu").html(menuHtml);
      this._initCurrentPage();
      this._initEvents();
    };

    this._initCurrentPage = function() {
      var page = getURLParams(this.pageParam);
      var id = getURLParams(this.idParam);
      if (this.pages[page]) {
        this.setCurrentPage(page, id);
      }
      else {
        this.setCurrentPage(Object.keys(this.pages)[0]);
      }
    };

    this._initEvents = function() {
      var pageManager = this;
      window.addEventListener('click', function(e) {
        var hasA = false;
        var count = 5;
        var el = e.target;
        do {
          if (el.localName == 'a') {
            hasA = true;
            break;
          }
          count--;
          if (count <= 0) break;
        } while (el = el.parentElement)
        if (hasA && el.getAttribute('href')) {
          var urlAction = getURLParams(pageManager.pageParam, el.getAttribute('href'));
          var urlId = getURLParams(pageManager.idParam, el.getAttribute('href'));
          if (urlAction != null) {
            pageManager.setCurrentPage(urlAction, urlId, true);
            e.preventDefault();
            return false;
          }
        }
      });
      window.addEventListener('popstate', function() {
        var urlAction = getURLParams(pageManager.pageParam, document.location.href);
        var urlId = getURLParams(pageManager.idParam, document.location.href);
        if (urlAction != null) {
          pageManager.setCurrentPage(urlAction, urlId, false);
        }
      });
    };


    this.setCurrentPage = function(page, urlId, pushState) {
      console.info('Goto ' + page);
      if (this.pages[page]) {
        var pagemanager = this;
        
        // Change page URL
        var params = getURLParams();
        delete params[this.pageParam];
        delete params[this.idParam];
        params[this.pageParam] = page;
        if (urlId) params[this.idParam] = urlId;
        var paramsStr = '';
        for (var id in params) {
          if (paramsStr == '') paramsStr += '?' + id + '=' + params[id];
          else paramsStr += '&' + id + '=' + params[id];
        }
        if (pushState) window.history.pushState(null, this.pages[page].title, window.location.href.split('?')[0] + ((Object.keys(this.pages)[0] == page) ? '' : paramsStr));
        document.title = this.siteName + ' - ' + this.pages[page].title;

        // Store current page
        this.currentPage = page;

        // Change titles on the page
        var titles = document.getElementsByClassName(this.classPrefix + 'title');
        for (var i = 0 ; i < titles.length ; i++) {
          titles[i].innerHTML = this.pages[page].title;
        }

        // Highlight current page
        $('#' + this.classPrefix + 'menu').children().each(function(id, tag) {
          if (tag.getAttribute('href').indexOf(pagemanager.pageParam + "=" + pagemanager.currentPage) != -1) tag.classList.add(pagemanager.classPrefix + 'currentMenu');
          else tag.classList.remove(pagemanager.classPrefix + 'currentMenu');
        });

        $("#page-body")[0].classList.add(this.classPrefix + 'body-loading');
        var start = new Date();

        // Change body
        $.ajax("./query/pages/" + page + ".php" + paramsStr, {
          dataType: "html",
          success: function(html) {
            setTimeout(function() {
              $("#page-body").html(html);
              componentHandler.upgradeAllRegistered();
              getmdlSelect.init('.getmdl-select');
              $("#page-body form").each(function(id, element) {
                element.submit = function() {
                  /*var data = $(this).serializeArray().reduce(function(obj, item) {
                    obj[item.name] = item.value;
                    return obj;
                  }, {});*/
                  var additionalData = {};
                  var selects = this.getElementsByClassName('getmdl-select');
                  $(selects).each(function(id, select) {
                    var input = select.getElementsByTagName('input')[0];
                    additionalData[input.name.replace(/_selectLabel$/, '')] = input.getAttribute('data-val');
                  });
                  $(this).ajaxSubmit({
                    'data': additionalData,
                    'success': function(res) {
                      eval(element.getAttribute('data-onresponse')).call(element, res.response, res.error);
                    }
                  });
                  return false;
                };
              });
              $("#page-body")[0].classList.remove(pagemanager.classPrefix + 'body-loading');
            }, Math.max(parseFloat(window.getComputedStyle($('#page-body')[0]).transitionDuration.replace('s', '')) * 1000 - (new Date()).getTime() / 1000 + start.getTime() / 1000, 0));
          },
          error: function(res) {
            console.log(res);
            swal("Oups...", "Une erreur est survenue lors de la récupération de la page", "error");
          }
        });
      }
    };


    var pm = this;
    window.addEventListener('load', function() {
      pm._init();
    });

  };




  // Lib

  function getURLParams(param, url) {
    var vars = {};
    if (typeof url === 'undefined') url = window.location.href;
    url.replace( 
      /[?&]+([^=&#]+)=?([^&#]*)?/gi, // regexp
      function( m, key, value ) { // callback
        vars[key] = value !== undefined ? value : '';
      }
    );

    if ( param ) {
      return vars[param] ? vars[param] : null;  
    }
    return vars;
  }



  window.pageManager = new PageManager({
    dashboard: {
      label: 'Dashboard',
      title: 'Dashboard',
      icon: 'home'
    },
    etudiants: {
      label: 'Étudiants',
      title: 'Gérer les étudiants',
      icon: 'person'
    },
    elements: {
      label: 'El. de formation',
      title: 'Gérer les éléments de formation',
      icon: 'bubble_chart'
    },
    cursus: {
      label: 'Cursus',
      title: 'Gérer les cursus',
      icon: 'trending_up'
    },
    reglement: {
      label: 'Règlement',
      title: 'Gérer les règlement',
      icon: 'check'
    }/*,
    tests: {
      label: 'Tests',
      title: 'Tests',
      icon: 'pets'
    }*/
  });

})();
