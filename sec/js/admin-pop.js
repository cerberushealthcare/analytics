var templates;
var template;
var section;
var par;
var question;
function getAllTemplates() {
	template = null;
	section = null;
	par = null;
	question = null;
	sendRequest(2, 'action=getTemplates&id=0');
}
function getTemplate(id) {
	templateId = id;
	template = null;
	section = null;
	par = null;
	question = null;
	sendRequest(2, 'action=getTemplate&id=' + id);
}
function getSection(id) {
  var sid = id + '';
  if (sid.substr(0, 1) != 'S') 
    sid = 'S' + id;
  else {
    sid = id;
    id = sid.substr(1);
  } 
	section = template.sections[sid];
	par = null;
	question = null;
  //sendRequest(2, 'action=getParTemplates&id=' + id);
  sendRequest(2, 'action=getPars2&id=' + id);
}
function getPar(id) {
  if (section.pars.length) {
    for (var i = 0; i < section.pars.length; i++) {
      par = section.pars[i];
      if (par.id == id)
        break;
    }
  } else {
    par = section.pars[id];
  }
	question = null;
	sendRequest(2, 'action=getParInfo&id=' + id);
}
function getQuestions(id) {
  if (section.pars.length) {
    for (var i = 0; i < section.pars.length; i++) {
      par = section.pars[i];
      if (par.id == id)
        break;
    }
  } else {
    par = section.pars[id];
  }
	sendRequest(2, 'action=getQuestions&id='  + id);
}
function parseTemplate(t) {
	template = t;
	showSections();
}
function parseTemplates(t) {
  templates = t;
  showTemplates();
}
function parseParTemplates(pars) {
	section.pars = pars;
	showPars();
}
function getParsCallback(pars) {
  section.pars = pars;
  showPars();  
}
function parseParInfo(pi) {
	par.questions = pi.questions;
	showQuestions();
}
function parseQuestions(questions) {
	par.questions = questions;
	showQuestions();
}
function doBreadcrumb() {
	copy.style.display = "none";
  var h = "";
  var href;
  if (allTemplates) {
    href = "javascript:getAllTemplates()";
    if (template != null) h += "<a href='" + href + "'>";
    h += "All templates";
    if (template != null) h += "</a> > "; 
  } 
  if (template != null) {
  	href = "javascript:getTemplate(" + templateId + ")";
  	if (section != null) h += "<a href='" + href + "'>"
  	h += "T:" + template.uid;
  	if (section != null) h +="</a>";
  }
	if (section != null) {
		href = "javascript:getSection('" + section.id + "')";
		h += " > "
		if (par != null) h += "<a href='" + href + "'>";
		h += "S:" + section.uid;
		if (section != null) h += "</a>";
	}
	if (par != null) {
		href = "javascript:getPar(" + par.id + ")";
		h += " > ";
		if (question != null) h += "<a href='" + href + "'>"
		h += "P:" + par.uid;
		if (question != null) h += "</a>"
 	}
 	if (question != null) {
 		h += " > Q:" + question.uid;
 	}
	document.getElementById("breadcrumb").innerHTML = h;
}
function showSections() {
	doBreadcrumb();
	var h ="<table border=0 cellpadding=0 cellspacing=0>";
	for (var sid in template.sections) {
		var s = template.sections[sid];
		var href = "javascript:getSection(\"" + sid + "\")";
		h += "<tr style='padding-bottom:2px'>";
		h += "<td><a href='" + href + "'><img src='img/folder.gif'></a></td><td width=2></td>";
		h += "<td><a href='" + href + "'>S:<b>" + s.uid + "</b></a></td><td width=15 nowrap></td><td nowrap>" + s.title + "</td>";
		h += "</tr>";
	}
	h += "</table>";
	document.getElementById("content").innerHTML = h;
}
function showTemplates() {
  doBreadcrumb();
  var h ="<table border=0 cellpadding=0 cellspacing=0>";
  for (var tid in templates) {
    var t = templates[tid];
    var href = "javascript:getTemplate(" + tid + ")";
    h += "<tr style='padding-bottom:2px'>";
    h += "<td><a href='" + href + "'><img src='img/folder.gif'></a></td><td width=2></td>";
    h += "<td><a href='" + href + "'>T:<b>" + t.uid + "</b></a></td><td width=15 nowrap></td><td nowrap>" + t.title + "</td>";
    h += "</tr>";
  }
  h += "</table>";
  document.getElementById("content").innerHTML = h;
}
