var selmenu = null;
var selhold = false;
var overMenuLast = false;
var onDropRmenu = false;

function bodyMouseDown() {
  menuUnselect();
  Pop._closeByControlBox();
}
function menuMouseDown() {
  Pop._closeByControlBox();
  var a = event.srcElement;
  if (a != null && a.tagName == "A") {
    if (isMenu(a)) {
      // Toggle menu dropdown
      if (isSelected(a)) {
        menuUnselect();
      } else {
        menuSelect(a);
      }
    } else if (isRmenu(a)) {
      // Show right menu
      rmenuSelect(a);
    } 
  }
  event.cancelBubble = true;    
}
function menuMouseOver() {
  var a = event.srcElement;
  onDropRmenu = false;
  if (a.tagName == "A") {
    if (selmenu != null || selhold) {
      if (isMenu(a)) {
        // Show menu dropdown if one is already visible
        overMenuLast = true;
        if (selhold || selmenu.anchor.id != a.id) {
          selhold = false;
          menuSelect(a, true);
        }
      } else {
        overMenuLast = false;
        if (isRmenu(a) && ! a.disabled) {
          // Over a right menu, schedule the dropdown
          a.className = "rmenu rsel";  
          if (selmenu.schedrmenuid != a.id) {
            selmenu.schedrmenuid = a.id;
            setTimeout("schedRmenu('" + a.id + "')", 500);
            return;
          }        
        } 
        if (selmenu.rmenu != null) {
          if (isOnDropRmenu(a)) {
            // Over a drop rmenu anchor, select the parent anchor
            selmenu.rmenu.anchor.className = "rmenu rsel"; 
            onDropRmenu = true;
          } else {
            setTimeout("schedRmenuOff('" + selmenu.rmenu.anchor.id + "')", 500);
          }
        }
      }
    }
  } else {
    if (selmenu != null && selmenu.rmenu != null) {
      if (a.id == selmenu.rmenu.dropmenu.id || parent(a).id == selmenu.rmenu.dropmenu.id || grand(a).id == selmenu.rmenu.dropmenu.id) {
        // Over a drop rmenu, select the parent anchor
        selmenu.rmenu.anchor.className = "rmenu rsel";
        return;
      }
    }
    if (a.id == "menubar") {
      if (selmenu != null && overMenuLast) {
        selhold = true;
        menuUnselect();
      } else {
        selhold = false;
      }
    }
  }
}
function menuMouseOut() {
  var a = event.srcElement;
  if (a != null && a.tagName == "A") {
    if (isRmenu(a) && ! a.disabled) {
      // Exiting rmenu, reset selected class
      a.className = "rmenu";
    } else if (isOnDropRmenu(a)) {
      // Exiting drop rmenu, unselect the parent anchor
      selmenu.rmenu.anchor.className = "rmenu";
    }
  }
}
function menuClick() {
  var a = event.srcElement;
  if (a != null) {
    if (a.tagName == "A" && isAction(a)) {
      if (! a.disabled) {
        menuUnselect();
        eval(a.id + "()");
      }
    } else if (a.id == "menubar") {
      menuUnselect();
      selhold = false;
    }
  }
  event.cancelBubble = true;
}
function menuToggleCheck() {  // returns new checked value
  var checked = ! isMenuChecked(event.srcElement);
  var id = event.srcElement.id;
  menuSetCheck$(id, checked);
  return checked;
}
function menuSetCheck$(id, checked) {
  var as = menu$$(id);  
  for (var i = 0; i < as.length; i++) {  // toggle both menu and tool
    as[i].className = (checked) ? "check-on" : "check-off";
  }
}
function isMenuChecked(a) {
  if (a == null) return false;
  return a.className == "check-on";
}
function isMenuChecked$(id) {
  return isMenuChecked(menu$(id));
}
/*
 * Get current checked value or set to a new value
 * If toSet not supplied, returns current checked value (true/false)
 * If supplied: 
 *    - if toSet == current checked value, returns null 
 *    - else sets the value and returns toSet (true/false)
 */
function getMenuCheckedOrChange$(id, toSet) {    
  var isChecked = isMenuChecked$(id);
  if (toSet == null) {
    return isChecked;
  } else {
    if (isChecked == toSet) return;
    menuSetCheck$(id, toSet);
    return toSet;
  }
}
function toolClick() {
  menuClick();
}
function isOnDropRmenu(a) {
  return selmenu != null && selmenu.rmenu != null && ancestor(a).id == selmenu.rmenu.dropmenu.id;
}
function ancestor(a) {
  return a.parentElement.parentElement.parentElement;
}
function grand(a) {
  return a.parentElement.parentElement;
}
function parent(a) {
  return a.parentElement;
}
function menuSelect(a, noFade) {
  if (isSelected(a)) {
    return;
  }
  if (selmenu != null) {
    menuUnselect();
  }
  if (a.disabled) {
    return;
  }
  selmenu = newSelmenu(a);
  selmenu.anchor.className = "push";
  selmenu.dropmenu.className = "dropmenu show";
  if (noFade == null) {
    menuFadeIn(0);
  } else {
    menuFadeIn(70);
  }
}
function menuFadeIn(opacity) {
  if (selmenu == null) {
    return;
  }
  opacity = opacity + 15;
  var filter;
  if (opacity >= 100) {
    filter = document.body.style.filter;
  } else {
    filter = "alpha(opacity=" + opacity + ")"; 
  }
  selmenu.dropmenu.style.filter = filter;
  selmenu.anchor.filter = filter;
  if (opacity < 100) {
    setTimeout("menuFadeIn(" + opacity + ")", 5);
  } 
}
function menuUnselect() {
  if (selmenu == null) {
    return;
  }
  rmenuUnselect();
  selmenu.anchor.className = ""; 
  selmenu.dropmenu.className = "dropmenu";
  selmenu = null;
}
function schedRmenu(id) {
  if (selmenu != null) {
    if (selmenu.schedrmenuid == id) {
      var a = $(id);
      if (isRsel(a)) {
        rmenuSelect($(id));
      }
      selmenu.schedrmenuid = null;
    }
  }
}
function isRsel(a) {
  return a.className == "rmenu rsel";
}
function schedRmenuOff(id) {
  // Cancel if currently over an rmenu
  if (onDropRmenu) {
    return;
  }
  if (selmenu != null && selmenu.rmenu != null) {
    if (selmenu.rmenu.anchor.id == id && selmenu.schedrmenuid == null) {
      rmenuUnselect();
    }
  }
}
function rmenuSelect(a) {
  if (selmenu == null) {
    return;
  }
  if (selmenu.rmenu != null && selmenu.rmenu.anchor.id == a.id) {
    return;
  }
  rmenuUnselect();
  selmenu.schedrmenuid = a.id;
  selmenu.rmenu = newSelmenu(a);
  selmenu.rmenu.dropmenu.className = "droprmenu show";
}
function rmenuUnselect() {
  if (selmenu.rmenu != null) {
    selmenu.rmenu.anchor.className = "rmenu";
    selmenu.rmenu.dropmenu.className = "droprmenu";
  }
  selmenu.rmenu = null;
}
function menu$$(id) {  // return both menu and tool
  return $$$(id, menutool, "A");
}
function menu$(id) {  // return just menu (or tool, if no menu)
  return menu$$(id)[0];
}
function disable(id, hide) {
  var a = menu$$(id);
  for (var i = 0; i < a.length; i++) {
    a[i].className = trim(a[i].className + " mdisabled");
    a[i].disabled = "disabled";
    if (a[i].title && hide) {
      a[i].style.display = "none";  // hide only toolbar anchors 
    }
  }
}
function enable(id, show) {
  var a = menu$$(id);
  for (var i = 0; i < a.length; i++) {
    a[i].className = trim(a[i].className.replace(/mdisabled/, ""));
    a[i].disabled = "";
    if (show) a[i].style.display = "";
  }
}
function isSelected(a) {
  return (selmenu != null && selmenu.anchor.id == a.id);
}
function isMenu(a) {
  return a.id.substring(0, 5) == "menu-";
}
function isRmenu(a) {
  return a.id.substring(0, 6) == "rmenu-";
}
function isAction(a) {
  return a.id.substring(0, 6) == "action";
}
function toolPush() {
  Pop._closeByControlBox();
  var a = event.srcElement;
  if (a != null && a.tagName == "A" && ! a.disabled) {
    addClass(a, "push");
  }
  event.cancelBubble = true;
}
function toolRelease() {
  var a = event.srcElement;
  if (a != null && a.tagName == "A" && hasClass(a, "push")) {
    removeClass(a, "push");
  }
}
function newSelmenu(a) {
  return {
    "anchor":a, 
    "dropmenu":$("drop" + a.id),
    "schedrmenuid":null,
    "droprmenu":null 
    };
}
