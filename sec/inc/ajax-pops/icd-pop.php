<?
// ICD pop
// To call: showIcd(code)  // code optional
// Callback: icdCallback(code, desc)  // selected code, or null if user cleared
?>
<div id="pop-icd" class="pop" onmousedown="event.cancelBubble = true" style='width:670px'>
  <div id="pop-icd-cap" class="pop-cap" unselectable="on">
    <div id="pop-icd-cap-text" unselectable="on">
      Clicktate - ICD Codes
    </div>
    <a href="javascript:Pop.close()" class="pop-close"></a>
  </div>
  <div id="pop-content" class="pop-content">
    <div class="med">
      <table border=0 cellpadding=0 cellspacing=0>
        <tr>
          <th>Search For</th>
        </tr>
        <tr>
          <td><input id="icd-search" type="text" size="50" value="" onkeyup="ifCrClick2('icd-search-button')" /></td>
          <td><input id="icd-search-button" class="medSearch" type="button" value="Search..." onclick="doIcdSearch()" /></td>
        </tr>
      </table>
      <div id="icd-tree" onfocus="unselectText2();">
        <ul id="icd-tree-ul">
        </ul>
      </div>
      <table border=0 cellpadding=0 cellspacing=0>
        <tr>
          <th>Selected Code</th>
          <th id="icd-nav">
            <a id="icd-nav-prev" href="javascript:" onclick="icdPrev();" ondblclick="icdPrev();">< Prev</a>
            |
            <a id="icd-nav-next" href="javascript:" onclick="icdNext();" ondblclick="icdNext();">Next ></a>
          </th>
        </tr>
        <tr>
          <td colspan="2">
            <div id="icd-info">
              <table border="0" cellpadding="0" cellspacing="0">
                <tr>
                  <td class="top">
                    <div id="icd-info-code"></div>
                  </td>
                  <td>
                    <span id="icd-info-desc">
                    </span>
                    <div id="icd-info-syn">
                    </div>
                  </td>
                </tr>
                <tr id="icd-info-exc">
                  <th class="sub">
                    Excludes
                  </th>
                  <td id="icd-info-exc-text" class="sub">
                  </td>
                </tr>
                <tr id="icd-info-inc">
                  <th class="sub">
                    Includes
                  </th>
                  <td id="icd-info-inc-text" class="sub">
                  </td>
                </tr>
                <tr id="icd-info-note">
                  <th class="sub">
                    Notes
                  </th>
                  <td id="icd-info-note-text" class="sub">
                  </td>
                </tr>
              </table>
            </div>
          </td>
        </tr>
      </table>
    </div>
    <div class="pop-cmd cmd-fixed">
      <a class="cmd ok" id="icd-ok" href="javascript:" onclick='icdOk(); return false'>OK</a>
      <a class="cmd delete" id="icd-delete" href="javascript:" onclick='icdClear(); return false'>Clear</a>
      <a class="cmd none" href="javascript:" onclick='Pop.close();'>Cancel</a>
    </div>
  </div>
</div>
