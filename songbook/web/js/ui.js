function fix_page() {
  fix_bounce('page');
}
function fix_height(e) {
  var h = $('#head').height();
  e.each(function() {
    $(this).height($(window).height() - h - 10);
  })
}
function fix_bounce(elems) {
  var cont;
  var startY;
  var idOfContent = "";
  nonbounce_touchmoveBound = false;
  var isContent = function(elem) {
    var id = elem.getAttribute("id");
    while (id !== idOfContent && elem.nodeName.toLowerCase() !== "body") {
      elem = elem.parentNode;
      id = elem.getAttribute("id");
    }
    return (id === idOfContent);
  }
  var touchstart = function(evt) {
    if (!isContent(evt.target)) {
      evt.preventDefault();
      return false;
    }
    startY = (evt.touches) ? evt.touches[0].screenY : evt.screenY;
  }
  var touchmove = function(evt) {
    var elem = evt.target;
    var y = (evt.touches) ? evt.touches[0].screenY : evt.screenY;
    if (cont.scrollTop === 0 && startY <= y) {
      evt.preventDefault();
    }
    if (cont.scrollHeight - cont.offsetHeight === cont.scrollTop && startY >= y) {
      evt.preventDefault();
    }
  }
  if (typeof elems === "string") {
    cont = document.getElementById(elems);
    if (cont) {
      idOfContent = cont.getAttribute("id");
      window.addEventListener("touchstart", touchstart, false);
    }
  }
  if (!nonbounce_touchmoveBound) {
    window.addEventListener("touchmove", touchmove, false);
  }
}
