$(document).ready(function () {
    $.get("../alert/alert.txt", function(a) {
        0 < a.length && ($("#body").animate({marginTop:"25px"}, 100), $("#head").animate({marginTop:"25px"}, 100), $("#alerttext").text(a));
  });
});