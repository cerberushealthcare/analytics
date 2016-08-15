<?
/**
 * Facesheet Tracking 
 */
?>
<div id='fsp-trk' class='pop' style='width:780px'>
  <div id='fsp-trk-cap' class='pop-cap'>
    <div id='fsp-trk-cap-text'>
      Tracking
    </div>
    <a href='javascript:FaceTrack.fpClose()' class='pop-close'></a>
  </div>
  <div class='pop-content' style='padding:0'>
    <div id='tracking-table-tile'>
      &nbsp; <!-- TrackingTable -->
    </div>
    <div class="pop-cmd cmd-right" style='padding:0 13px 13px 13px; margin:0'>
      <table class='h'>
        <tr>
          <th>
          </th>
          <td>
            <a href="javascript:" onclick="FaceTrack.fpAdd()" class="cmd new">Add From Order Set...</a>
            <span>&nbsp;</span>
            <a href="javascript:" onclick="FaceTrack.fpAddByLookup()" class="cmd new">Add By Lookup...</a>
            <span>&nbsp;</span>
            <a href="javascript:FaceTrack.fpClose()" class="cmd none">&nbsp;&nbsp;Exit&nbsp;&nbsp;</a>
          </td>
        </tr>
      </table>
    </div>
  </div>
</div>
