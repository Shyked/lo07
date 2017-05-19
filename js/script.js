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
      $("#pagemanager-menu").html(menuHtml);
      this._initCurrentPage();
      this._initEvents();
    };

    this._initCurrentPage = function() {
      var page = getURLParams(this.pageParam);
      if (this.pages[page]) {
        this.setCurrentPage(page);
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
        var id = null;
        for (id in e.path) {
          if (e.path[id].localName == 'a') {
            hasA = true;
            break;
          }
          count--;
          if (count <= 0) break;
        }
        if (hasA) {
          var urlAction = getURLParams(pageManager.pageParam, e.path[id].getAttribute('href'));
          var urlId = getURLParams(pageManager.idParam, e.path[id].getAttribute('href'));
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

        $("#page-body")[0].classList.add(this.classPrefix + 'body-loading');
        var pagemanager = this;
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
                $(element).ajaxForm(function(res) {
                  eval(element.getAttribute('data-onresponse')).call(element, res.response, res.error);
                });
                element.submit = function(e) { };
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


    this._init();

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
    tests: {
      label: 'Tests',
      title: 'Tests',
      icon: 'pets'
    }
  });

})();
