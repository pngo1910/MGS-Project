
HTMLCollection.prototype.map = Array.prototype.map;

var insertSelected = function(link) {
  var span = document.createElement('span');
  span.setAttribute('class', 'selected');
  span.textContent = link.textContent;
  // link.parentNode.appendChild(span);
  // link.parentNode.appendChild(span);

  // link.parentNode.insertBefore(span, link.nextSibling);
  // link.parentNode.insertBefore(span, link);

    // var a = document.createElement('a');
    // a.textContent = link.textContent;
    // a.setAttribute('class', 'selected');
    // link.parentNode.appendChild(a);

};

// <!-- <li><a> | </a></li>
// <li><a href="/store/" class="head-link">Store</a></li> -->

var ajaxload = function(url) {
  var request = new XMLHttpRequest();
  request.overrideMimeType('text/xml');
  request.open('get', url, false);
  request.send();
  return request.responseXML;
};

document.getElementsByClassName('head-link').map(function(link) {
  link.addEventListener('click', function(e) {
    // if (link.text != localStorage.subaheader)
    //textContent
    if (link.text != localStorage.subheader)
      localStorage.removeItem('selectedlink');

    localStorage.subheader = link.text;
  }, false);

  if (!localStorage.subheader) localStorage.subheader = 'Search';

  if (link.text == localStorage.subheader) {
    //link.setAttribute('style', 'display:none');
    insertSelected(link);
  }
});

var xml = ajaxload('/subheader.php?header=' + localStorage.subheader);
var logolink = xml.getElementById('logo-link');

if (logolink != null) {
  var mainlogolink = document.getElementById('logo-link');
  mainlogolink.setAttribute('href', logolink.getAttribute('href'));
  mainlogolink.addEventListener('click', function() {
    localStorage.removeItem('selectedlink');
  }, false);

  logolink.parentNode.removeChild(logolink);
}

xml.getElementsByTagName('ul').map(function(navbar) {
  var removelink = null;

  navbar.getElementsByTagName('a').map(function(link) {
    // links loaded by ajax don't have click events
    link.addEventListener('click', function(e) {
      localStorage.selectedlink = link.textContent;
      window.location = link.getAttribute('href');
    }, false);

    if (link.textContent == localStorage.selectedlink)
      insertSelected(removelink = link);
  });

  //if (removelink != null) removelink.parentNode.removeChild(removelink);
  var submenu = document.getElementById('submenu');
  if (submenu != null) submenu.appendChild(navbar);
});
