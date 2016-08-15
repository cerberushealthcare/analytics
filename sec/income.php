<?
require_once "php/data/LoginSession.php";
require_once 'inc/uiFunctions.php';
//
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <style>
DIV.bod {
  width:600px;
  margin:30px auto;
  font-family:Arial;
  font-size:11pt;
}
P {
  font-family:Arial;
  font-size:11pt;
  line-height:1.3em;
}
H1 {
  font-size:16pt;
  color:#008E80;
}
H2 {
  font-size:13pt;
  margin-top:2em;
  color:#008E80;
}
H3 {
  font-size:11pt;
  margin-top:1.5em;
  color:black;
}
TABLE.ip {
  width:100%;
  margin:0 auto;
  font-size:10pt;
  border-left:1px solid #c0c0c0;
  border-right:1px solid #c0c0c0;
}
TABLE.ip TH {
  text-align:left;
  padding:5px 10px;
}
TABLE.ip TD {
  padding:5px;
}
TABLE.ip TR {
  border-top:1px solid #c0c0c0;
  border-bottom:1px solid #c0c0c0;
}
TABLE.ip THEAD TR {
  background-color:#DAECF8;
}
TABLE.ip TBODY TH {
  font-weight:normal;
  padding-left:25px;
}
TABLE.ip TBODY TR.r2 {
  background-color:#f2f2f2;
}
TABLE.ip TBODY TR.t {
  background-color:#EEF6FC;
}
TABLE.ip TBODY TR.t TH {
  font-weight:bold;
  padding-left:10px;
}
TABLE.ip TBODY TR.t TD {
  font-weight:bold;
}
UL.pp {
  list-style:disc;
  margin:0 30px;
}
UL.pp LI {
  margin:2px 0;
}
    </style>
 </head>
  <body>
      <div class='bod'>
        <div style='text-align:center;margin:20px 0'>
          <h1>Improve Your Income with Clicktate</h1>
        </div>
        <p>
          Here are three ways you can <b>improve your income</b> using Clickate 4.0.
        </p>
        <hr style='border-top:1px solid black' />
        <h2>1. Incentive dollars</h2>
        <p>
          Use Clicktate to do your government attestation and receive incentive dollars.
        </p>
        <div style='padding:0 15px'>
          <table class='ip'>
            <thead>
              <tr>
                <th>Year of adoption</th>
                <th>2012</th>
                <th>2013</th>
                <th>2014</th>
              </tr>
            </thead>
            <tbody>
              <tr class='r1'>
                <th>Payment for 2012</th>
                <td>$18,000</td>
                <td></td>
                <td></td>
              </tr>
              <tr class='r2'>
                <th>Payment for 2013</th>
                <td>$12,000</td>
                <td>$15,000</td>
                <td></td>
              </tr>
              <tr class='r1'>
                <th>Payment for 2014</th>
                <td>$8,000</td>
                <td>$12,000</td>
                <td>$12,000</td>
              </tr>
              <tr class='r2'>
                <th>Payment for 2015</th>
                <td>$4,000</td>
                <td>$8,000</td>
                <td>$8,000</td>
              </tr>
              <tr class='r1'>
                <th>Payment for 2016</th>
                <td>$2,000</td>
                <td>$4,000</td>
                <td>$4,000</td>
              </tr>
              <tr class='t'>
                <th>Total Payment</th>
                <td>$44,000</td>
                <td>$39,000</td>
                <td>$24,000</td>
              </tr>
            </tbody>
          </table>
          <h3>Medicaid EHR Incentive Payment Schedule</h3>
          <table class='ip'>
            <thead>
              <tr>
                <th>Year of adoption</th>
                <th>2012</th>
                <th>2013</th>
                <th>2014</th>
                <th>2015</th>
                <th>2016</th>
              </tr>
            </thead>
            <tbody>
              <tr class='r1'>
                <th>2012</th>
                <td>$21,250</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr class='r2'>
                <th>2013</th>
                <td>$8,500</td>
                <td>$21,250</td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr class='r1'>
                <th>2014</th>
                <td>$8,500</td>
                <td>$8,500</td>
                <td>$21,250</td>
                <td></td>
                <td></td>
              </tr>
              <tr class='r2'>
                <th>2015</th>
                <td>$8,500</td>
                <td>$8,500</td>
                <td>$8,500</td>
                <td>$21,250</td>
                <td></td>
              </tr>
              <tr class='r1'>
                <th>2016</th>
                <td>$8,500</td>
                <td>$8,500</td>
                <td>$8,500</td>
                <td>$8,500</td>
                <td>$21,250</td>
              </tr>
              <tr class='r2'>
                <th>2017</th>
                <td>$8,500</td>
                <td>$8,500</td>
                <td>$8,500</td>
                <td>$8,500</td>
                <td>$8,500</td>
              </tr>
              <tr class='r1'>
                <th>2018</th>
                <td></td>
                <td>$8,500</td>
                <td>$8,500</td>
                <td>$8,500</td>
                <td>$8,500</td>
              </tr>
              <tr class='r2'>
                <th>2019</th>
                <td></td>
                <td></td>
                <td>$8,500</td>
                <td>$8,500</td>
                <td>$8,500</td>
              </tr>
              <tr class='r1'>
                <th>2020</th>
                <td></td>
                <td></td>
                <td></td>
                <td>$8,500</td>
                <td>$8,500</td>
              </tr>
              <tr class='r2'>
                <th>2019</th>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>$8,500</td>
              </tr>
              <tr class='t'>
                <th>Total Payment</th>
                <td>$63,750</td>
                <td>$63,750</td>
                <td>$63,750</td>
                <td>$63,750</td>
                <td>$63,750</td>
              </tr>
            </tbody>
          </table>
        </div>
        <h2>2. Refer a colleague (or two or three... or more)</h2>
        <p> 
          For every customer that you refer to Clicktate, you will receive <b>$27 of their monthly cost</b> for as long as they are a customer!
        </p>
        <p> 
          Refer ten new users, and you'll receive a check each quarter for $810... that's $3,240 per year.
        </p>
        <h2>3. Become a Clicktate investor</h2>
        <p> 
          For a limited time, we are offering the opportunity to own part of Clicktate. 
          Call us for investment details.
        </p>
        <div style='padding:20px 0 0 0'>
          <hr style='border-top:1px solid black' />
        </div>
        <h2>Putting it all together</h2>
        <p> 
          Let's assume Dr. Jones follows these steps for 2013.
          He receives his incentive payment for using Clicktate.
          He refers five colleagues to Clicktate, who each continue to use the system.
        </p>
        <p>
          In 2013, Dr. Jones will receive:
        </p>
        <ul class='pp'>
          <li>$15,000 in Incentive Payments</li>
          <li>$1,620 in Referal Payments</li>
        </ul>
        <p>
          <b>That's a total of $16,620 in 2013</b>.
        </p>
        <p>
          Next year, Dr. Jones refers another five providers to Clicktate. 
          He again receives his incentive payment for using Clicktate.
        </p>
        <p>
          In 2014, Dr. Jones will receive:
        </p>
        <ul class='pp'>
          <li>$12,000 in Incentive Payments</li>
          <li>$3,240 in Referal Payments</li>
        </ul>
        <p>
          <b>That's a total of $15,240 in 2014</b>.
        </p>
      </div>
  </body>
</html>
