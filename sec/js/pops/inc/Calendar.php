<div id='pop-calen' class='pop' style='width:220px'>
  <div class='pop-cap'>
    <div>
      Date Entry
    </div>
    <a href='javascript:Calendar.pClose()' class='pop-close'></a>
  </div>
  <div class='pop-content cj'>
    <table class='w100 mb10'>
      <tr>
        <td>
          <img alt='Previous Month' class='hand' src='img/nav-prev.jpg' onclick='Calendar.prevMonth()' ondblclick='Calendar.prevMonth()'>
        </td>
        <td id='cal-title'>
        </td>
        <td align='right'>
          <img alt='Next Month' class='hand' src='img/nav-next.jpg' onclick='Calendar.nextMonth()' ondblclick='Calendar.nextMonth()'>
        </td>
      </tr>
    </table>
    <table id='cal-tbl' cellspacing='0'>
      <thead>
        <tr>
          <th>S</th>
          <th>M</th>
          <th>T</th>
          <th>W</th>
          <th>T</th>
          <th>F</th>
          <th>S</th>
        </tr>
      </thead>
      <tbody id='cal-tbody' onclick='Calendar.click(event)'>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td><span>fred</span></td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
      </tbody>
    </table>
    <div>
      <select id='cal-month' onchange='Calendar.setByCombos()'></select>
      <select id='cal-year' onchange='Calendar.setByCombos()'></select>
    </div>
    <div class='mt5'>
      <a id='cal-today' href='javascript:Calendar.setToday()' title='Current Month'>Today</a>
    </div>
    <div class='pop-cmd'>
      <!-- <a href='javascript:Calendar.pClear()' class='cmd delete'>Clear</a> -->
      <a href='javascript:Calendar.pClose()' class='cmd none'>Cancel</a>
    </div>
  </div>
</div>
<div id='pop-clock' class='pop'>
  <div class='pop-cap'>
    <div>
      Set Time
    </div>
    <a href='javascript:Clock.pClose()' class='pop-close'></a>
  </div>
  <div class='pop-content' style='text-align:center'>
    <ul class='entry'>
      <li> 
        <select id='clkHour' size='12'>
          <option style='background-color:#ffffe0' value='06AM'>6a</option>
          <option style='background-color:#ffffe0' value='07AM'>7</option>
          <option style='background-color:#ffffc0' value='08AM'>8</option>
          <option style='background-color:#ffffc0' value='09AM'>9</option>
          <option style='background-color:#ffff80' value='10AM'>10</option>
          <option style='background-color:#ffff80' value='11AM'>11</option>
          <option style='background-color:#ffff00' value='12PM'>12p</option>
          <option style='background-color:#ffff80' value='01PM'>1</option>
          <option style='background-color:#ffff80' value='02PM'>2</option>
          <option style='background-color:#ffffc0' value='03PM'>3</option>
          <option style='background-color:#ffffc0' value='04PM'>4</option>
          <option style='background-color:#ffffe0' value='05PM'>5</option>
          <option style='background-color:#ffffe0' value='06PM'>6p</option>
          <option style='background-color:#f0f0f0' value='07PM'>7</option>
          <option style='background-color:#e7e7e7' value='08PM'>8</option>
          <option style='background-color:#e0e0e0' value='09PM'>9</option>
          <option style='background-color:#d7d7d7' value='10PM'>10</option>
          <option style='background-color:#c7c7c7' value='11PM'>11</option>
          <option style='background-color:#c0c0c0' value='12AM'>12a</option>
          <option style='background-color:#c7c7c7' value='01AM'>1</option>
          <option style='background-color:#d7d7d7' value='02AM'>2</option>
          <option style='background-color:#e0e0e0' value='03AM'>3</option>
          <option style='background-color:#e7e7e7' value='04AM'>4</option>
          <option style='background-color:#f0f0f0' value='05AM'>5</option>
          <option style='background-color:#f0f0f0' value='06AM'>6</option>
        </select>
        <select id='clkMin' size='12'>
          <option value='00'>00</option>
          <option style='color:#707070' value='05'>05</option>
          <option style='color:#707070' value='10'>10</option>
          <option value='15'>15</option>
          <option style='color:#707070' value='20'>20</option>
          <option style='color:#707070' value='25'>25</option>
          <option value='30'>30</option>
          <option style='color:#707070' value='35'>35</option>
          <option style='color:#707070' value='40'>40</option>
          <option value='45'>45</option>
          <option style='color:#707070' value='50'>50</option>
          <option style='color:#707070' value='55'>55</option>
        </select>
      </li>
    </ul>
    <div class='pop-cmd'>
      <a href='javascript:Clock.pOk()' class='cmd save'>&nbsp;&nbsp;OK&nbsp;&nbsp;</a>
      <!-- <a href='javascript:Clock.pClear()' class='cmd delete'>Clear</a> -->
      <a href='javascript:Clock.pClose()' class='cmd none'>Cancel</a>
    </div>
  </div>
</div>