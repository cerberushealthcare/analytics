    <div id='working-float'>
    </div>
    <div id='trial-pop'>
      <div id='trial-pop-c'>
        <div id='trial-pop-h'>
        Try a risk-free trial.<br><br>
        </div>
        <p>Please contact our sales department at 1-888-825-4258 ext. 804<br>to begin your risk-free 14 day trial. 
        <div style='display:none'>
        <form id="form" method="post" action="sec/trial.php" style='margin:0' onkeypress='if (event.keyCode == 13) sub()'>
          <input type='hidden' name='fromPop' value='1' />
          <div>
            <label>Name</label>
            <span class='ok'><input id='uname' type='text' size='40' name='name' /></span>
          </div>
          <div>
            <label>Email</label>
            <span class='ok'><input id='email' type='text' size='45' name='email' /></span>
          </div>
          <div id='warn'>Name and valid email are required.<br/>Please correct.</div>
          <div style='text-align:center;'>
            <a href="javascript:sub()" class="tour">Let's Get Started ></a>
          </div>
        </form>
        </div>
        <div style='text-align:center;'>
          <a href="javascript:closePop2()" class="tour">Close</a>
        </div>
      </div>
    </div>
    <div id="foot">
      <div class="content">
        <div class="foot-text">
          &copy; 2012 LCD Solutions, Inc.<br/>
          All rights reserved.
        </div>
        <div>
          <a href="privacy.php">Privacy Policy</a>
          <span>|</span>
          <a href="terms.php">Terms of Service</a>
          <span>|</span>
          <a style="background:url(img/pdf.gif) no-repeat; padding-left:20px" href="Clicktate-BAA-1.0.pdf">Business Associate Agreement</a>
          <span>|</span>
          <a href="contact-us.php">Contact Us</a>
        </div>
      </div>
    </div>
  </body>
</html>
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
function closePop2() {
  _c = $('curtain');
  _p = $('trial-pop');
  _c.style.display = 'none';
  _p.style.display = 'none';
}
_uacct = "UA-1890602-1";
urchinTracker();
</script>