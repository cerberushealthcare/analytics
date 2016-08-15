<!DOCTYPE html>
<html>
<head>
  <meta charset='utf-8'>
  <meta name='viewport' content='user-scalable=no,width=device-width,initial-scale=1.0,maximum-scale=1.0'>
  <meta name='apple-mobile-web-app-capable' content='yes'>
  <meta name='apple-mobile-web-app-status-bar-style' content='black'>
  <link rel='apple-touch-icon-precomposed' href='icons/icon_60x60.png'>
  <link rel='apple-touch-startup-image' href='icons/splash_320x460.png'>
  <link rel='stylesheet' href='css/jquery.mobile-1.3.2.min.css'>
  <link rel='stylesheet' href='css/my-jquery-overrides.css?<?=$v?>'>
  <link rel='stylesheet' href='css/my.css?<?=$v?>'>
  <script type='text/javascript' src='js/jquery.js'></script>
  <script type='text/javascript' src='js/jquery.mobile-1.3.2.min.js'></script>
  <script type='text/javascript' src='js/lcd.js?<?=$v?>'></script>
  <script type='text/javascript' src='js/my-plugins.js?<?=$v?>'></script>
  <script type='text/javascript' src='js/data/SongList.js?<?=$v?>'></script>
  <script type='text/javascript' src='js/ui/Pages.js?<?=$v?>'></script>
  <title>Chordbook</title>
</head>
<body>
  <div id='test'></div>
</body>
<script>
$(document).ready(function() {
  /*
  var Dog = rec({
    name:null,
    onrevive:function() {
      alert(this.name);
    }
  })
  var Dogs = recs_local('dogs', Dog, {
    test:function() {
      alert(this.length);
    }
  }) 
  Dogs.erase();
  var array = Dogs.fetch();
  alert(array.length);
  */
  /*
  var s = [{'name':'Buttercup'},{'name':'Bear'}];
  var dogs = Dogs.revive(s);
  alert(dogs.length);
  dogs.save();
  */
  Song = rec({
    /*
     id
     artist
     title
     */
    getSortValue:function() {
      return this.artist + '|' + this.title;
    },
  })
  SongList = (function() {
    var parent = recs_local('song-list', Song);
    return make(parent, {
      fetch:function() {
        var us = parent.fetch.call(this);
        if (us.length == 0)
          us = this.revive([{artist:"Neil Diamond",title:"Solitary Man",body:"[Em]Melinda was [Am]mine 'til the [G]time that I [Em]found her\n[G]Holding [Am]Jim, [G]loving [Am]him\nThen Sue came along, loved me strong, that's what I thought\nMe and Sue, that died too\n[G]Don't know that I [C]will, but un-[G]til I can [D]find me\nA girl who will[C]stay and won't[G]play games be-[D]hind me\nI'll be what I [Em]am, [D]Solitary [Em]man\nI've had it to here being where love's a small word\nA part time thing, paper ring\nI know it's been done having one girl to love you\nRight or wrong, weak or strong"},{artist:"Beatles",title:"I'm Only Sleeping",body:null},{artist:"Guster",title:"Amsterdam",body:null},{artist:"Beatles",title:"Across The Universe",body:null}]);
        return us;
      },
      //
      onrevive:function() {
        this.sort();
      },
      sort:function() {
        parent.sort.call(this);
        this.reindex();
      }
    })
  })();
  var sl = SongList.fetch();
  sl.push(Song.revive({artist:"Abba",title:"Waterloo",body:null}));
  sl.sort();
  $('#test').text(JSON.stringify(sl));
})
</script>
</html>