<?
function logStart() {
  echo "  var _sig = logger.start(arguments);";
}
function logEnd() {
  echo "  logger.end(_sig);";
}
?>
<div id="pop-log" class="pop" onmousedown="event.cancelBubble = true">
  <div id="pop-log-cap" class="pop-cap">
    <div id="pop-log-cap-text">
      Logger
    </div>
    <a id="pop-log-control" href="javascript:closeOverlayPop()" class="pop-close"></a>
  </div>
  <div class="pop-content">
    <div id="pop-log-content" style="border:1px solid #c0c0c0; height:500px; width:900px; font-family:Consolas; font-size:8pt; background:white; padding:5px; overflow-y:scroll;">
    </div>
  </div>
</div>
<script>
function Logger() {
  this.reset();
}
Logger.prototype = {
  timer:null,
  tcum:null,
  logs:null,
  indent:null,
  marks:null,
  wasReset:false,
  reset:function(msg) {
    this.wasReset = true;
    this.timer = new Date();
    this.tcum = 0;
    this.logs = [];
    this.indent = {level:0, text:""};
    this.marks = {};
    this.lastStartName = null;
    if (msg) {
      this.log(msg, true);
    }
  },
  start:function(args, reset) {
    if (reset || ! this.wasReset) {
      this.reset();
    }
    var p = /function\s+(\w+)/.exec(args.callee + "");
    var sig = p[1] + "(" + Array.prototype.slice.call(args).join(",") + ")";
    this.log("<b>&lt;" + sig + "&gt;</b>");
    this.moveIndent(1);
    this.marks[sig] = {"name":p[1], "ix":1};
    this.lastStartName = p[1];
    return sig;
  },
  end:function(sig) {
    this.moveIndent(-1);
    this.log("<b>&lt;/" + sig + "&gt;</b>");
  },
  log:function(msg) {
    var e = (new Date() - this.timer) / 1000;
    var ec = e - this.tcum;
    this.tcum = e;
    msg = ("000" + e.toFixed(2)).slice(-6) + " +" + ("   " + ec.toFixed(2)).slice(-6) + ": " + this.indent.text + msg;
    this.logs.push(msg);
  },
  mark:function(sig) {
    var m = this.marks[sig];
    if (m) {
      this.log(m.name + ": " + m.ix++);
    } else {
      this.log(sig + ": (?)");
    }
  },
  pop:function() {
    $("pop-log-content").innerHTML = this.logs.join("<br/>");
    showOverlayPop("pop-log");
  },
  alert:function() {
    alert(this.logs.join("\n"));
  },
  moveIndent:function(value) {
    this.indent.level += value;
    if (this.indent.level == 0) {
      this.indent.text = "";
    } else {
      var text = [];
      for (var i = 0; i < this.indent.level; i++) {
        text.push("&nbsp;&nbsp;");
      }
      this.indent.text = text.join("");
    }
  }
}
var __l = new Logger();
</script>      