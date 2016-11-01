<?
require_once "php/data/LoginSession.php";
require_once 'inc/uiFunctions.php';
//
LoginSession::verify_forUser();
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <link rel='stylesheet' type='text/css' href='css/xb/_clicktate.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/xb/Pop.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/xb/facesheet.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/xb/EntryForm.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/xb/template-pops.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/data-tables.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/TabBar.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/TableLoader.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/TemplateUi.css?<?=Version::getUrlSuffix() ?>' />
    <link rel="stylesheet" type="text/css" href="css/xb/_hover.css?<?=Version::getUrlSuffix() ?>" media="screen" />
    <script language='JavaScript1.2' src='js/_lcd_core.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/_lcd_html.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/ui.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/pages/Pop.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/libs/DateUi.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/yui/yahoo-min.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/yui/event-min.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/yui/connection-min.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/components/TableLoader.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/components/TabBar.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/components/CmdBar.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/components/EntryForm.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/components/DateInput.js?<?=Version::getUrlSuffix() ?>'></script>
    <?php HEAD_RichText() ?>
  </head>
  <body>
    <div id='bodyContainer'>
      <div id='curtain'></div>
      <?php include 'inc/header.php' ?>
      <div id='bodyContent' class='content'>
        <h1>Tester</h1>
        <?php renderBoxStart('wide min-pad', null, null, 'box') ?>
          <a href='javascript:test()'>Test</a>
          <div id='tile'></div>
        <?php renderBoxEnd() ?>
      </div>
      <div id='bottom'><img src='img/brb.png' /></div>
    </div>      
  </body>
  <script>
  Rtb_Upload = {
    create:function(container) {
      return Html.TinyMce.create(container, null, null, Rtb_Upload.Opts).extend(function(self) {
        return {
          init:function() {
            self.initMce(Rtb_Upload.Opts);
          },
          mce_onsetup:function(mce) {
            self.addButton('upload', 'Upload Image', 'img/icons/uploadsm.png', function() {
              ImgUploadPop.pop(self.sid, function(upload) {
                mce.selection.setContent(upload.asHtml());
                mce.focus();
              })
            }) 
          },
          mce_onshow:function(mce) {
            self.setTooltip('image', 'Edit Image');
          },
          load:function(sid, html) {
            self.sid = sid;
            self.setHtml(html);
            return self;
          },
          getHtml:self.getHtml.extend(function(_getHtml) {
            var html = _getHtml();
            var pars = html.split('<p>');
            if (pars.length == 2 && html.beginsWith('<p>') && ! html.contains('<img'))
              return pars[1].split('</p>')[0];
            else
              return html;
          })
        }
      })
    },
    Opts:function(id) {
      var opts = Html.TinyMce.DefaultOpts(id);
      opts.theme_advanced_buttons1 = "bold,italic,underline,strikethrough,|,bullist,numlist,|,outdent,indent,blockquote,|,forecolor,backcolor,|,cut,copy,paste,|,search,replace,|,undo,redo,|,fullscreen",
      opts.theme_advanced_buttons2 = "tablecontrols,|,upload,image";
      return opts;
    }
  }
  //
  ImgUploadPop = {
    //
    pop:function(sid, /*fn(UploadImg)*/callback) {
      return Html.Pop.singleton_pop.apply(this, arguments);
    },
    create:function() {
      var vars = {
        'action':'uploadSessionImage',
        'sid':null};
      return Html.UploadPop.create('Upload Image', vars).extend(function(self) {
        return {
          onpop:function(sid, callback) {
            self.callback = callback;
            self.form.setValue('sid', sid);
          },
          oncomplete:function(upload) {
            self.callback && self.callback(UploadImg.revive(upload));
          }
        }
      })
    }
  }
  //
  UploadImg = Object.Rec.extend({
    /*
    width
    height
    src
    ratio
    */
    MAX:200,
    onload:function() {
      this._width = this.MAX;
      this._height = this._width * this.ratio;
    },
    asHtml:function() {
      return '<p><img src="' + this.src + '" alt="' + this.name + '" height=' + this._height + ' width=' + this._width + '></p>'; 
    }
  })
var tile = _$('tile');
var rtb = Rtb_Upload.create(tile).setHeight(400).setHtml().setFocus();
function test() {
  rtb.setFocus();
}
  </script>
</html>