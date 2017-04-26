(function() {

  var PageManager = function(pages) {

    this.pages = pages;
    this.currentPage = null;
    this.classPrefix = 'pagemanager-';
    this.pageParam = 'display';
    this.siteName = document.title;


    this._init = function() {
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
        if (e.target.localName == 'a') {
          var urlAction = getURLParams(pageManager.pageParam, e.target.getAttribute('href'));
          if (urlAction != null) {
            pageManager.setCurrentPage(urlAction);
            e.preventDefault();
            return false;
          }
        }
      });
    };


    this.setCurrentPage = function(page) {
      console.info('Goto ' + page);
      if (this.pages[page]) {
        // Change page URL
        var params = getURLParams();
        params[this.pageParam] = page;
        var paramsStr = '';
        for (var id in params) {
          if (paramsStr == '') paramsStr += '?' + id + '=' + params[id];
          else paramsStr += '&' + id + '=' + params[id];
        }
        window.history.pushState(null, this.pages[page].title, window.location.href.split('?')[0] + ((Object.keys(this.pages)[0] == page) ? '' : paramsStr));
        document.title = this.siteName + ' - ' + this.pages[page].title;

        // Store current page
        this.currentPage = page;

        // Change titles on the page
        var titles = document.getElementsByClassName(this.classPrefix + 'title');
        for (var i = 0 ; i < titles.length ; i++) {
          titles[i].innerHTML = this.pages[page].title;
        }

        // Change body
        $.ajax("./query/pages/" + page + ".php", {
          dataType: "html",
          success: function(html) {
            $("#page-body").html(html);
            componentHandler.upgradeAllRegistered();
            $("#page-body form").each(function(id, element) {
              $(element).ajaxForm(function(res) {
                eval(element.getAttribute('data-onresponse'))(res);
              });
              element.submit = function(e) { };
            });
          },
          error: function(res) {
            console.log(res);
            alert("Une erreur est survenue lors de la récupération de la page");
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
      /[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
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
    ajouter: {
      label: 'Ajouter',
      title: 'Ajouter un nouveau cursus',
      icon: 'library_add'
    },
    visualiser: {
      label: 'Visualiser',
      title: 'Visualiser les cursus',
      icon: 'list'
    },
    conformite: {
      label: 'Conformité',
      title: 'Vérifier la conformité des cursus',
      icon: 'check'
    },
  });

})();
