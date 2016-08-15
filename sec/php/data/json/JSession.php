<?php
require_once "php/dao/_util.php";
require_once "php/data/json/_util.php";

class JSession {

  public $id;
  public $templateId;
  public $clientId;
  public $dos;
  public $dosfull;
  public $dosdate;
  public $dossql; 
  public $cid;  // User-assigned client ID
  public $cname;
  public $csex;
  public $cbirth;
  public $cdata1;
  public $cdata2;
  public $cdata3;
  public $cdata4;
  public $cdata5;
  public $cdata6;
  public $cdata7;
  public $cdata8;
  public $cdata9;
  public $actions;
  public $html;
  public $uname;
  public $ucompany;
  public $uaddress;
  public $uphone;
  public $closed;
  public $signature;
  public $licenseState;
  public $license;
  public $dea;
  public $npi;
  public $dateCreated;
  public $dcsql;  // date created sql
  public $dateUpdated;
  public $noteDate;
  public $createdBy;
  public $updatedBy;
  public $sendTo;
  public $sendToId;
  public $assignedTo;
  public $assignedToId;
  public $uaddressonly;
  public $ucitystatezip;
  public $caddress;
  public $ccitystatezip;
  public $title;
  public $standard;
  public $lockedBy;  // name of user (non-self) who has session lock on this note
  
  // Optional children 
  public $template;
  public $map;
  public $meds;  // med history, for loading med picker
  
  // Closed values
  const NOT_CLOSED = 0;
  const CLOSED_DEPRECATED_1 = 1;  // legacy signature style, session.data replaced by HTML
  const CLOSED_DEPRECATED_2 = 2;  // embedded signature style, session.data replaced by HTML
  const CLOSED = 3;  // actions remain in session.data; new HTML field
    
  public static function buildLabel($title, $templateName, $closed, $standard) {
    $label = $title;
    if ($closed) {
      $label .= " (Signed)";
    }
    //if ($standard) {
    //  $label .= " *";
    //}
    return $label;
  }
  
  public function __construct($id, $templateId, $dos, $clientId, $cid, $cname, $csex, $cbirth, $cdata1, $cdata2, $cdata3, $cdata4, $cdata5, $cdata6, $cdata7, $cdata8, $cdata9, $actions, $html, $uname, $ucompany, $uaddress, $uphone, $closed, $signature, $licenseState, $license, $dea, $npi, $dateCreated, $dateUpdated, $createdBy, $updatedBy, $sendTo, $sendToId, $assignedTo, $assignedToId, $uaddressonly, $ucitystatezip, $caddress, $ccitystatezip, $title, $standard, $lockedBy, $noteDate) {
    $this->id = $id;
    $this->templateId = $templateId;
    $this->dossql = $dos; 
    $this->dos = formatConsoleDate($dos);
    $this->dosfull = formatFullDate($dos);
    $this->dosdate = formatDate($dos);
    $this->clientId = $clientId;
    $this->cid = $cid;
    $this->cname = $cname;
    $this->csex = ($csex == "M" ? 1 : 0);
    $this->cbirth = $cbirth;
    $this->cdata1 = $cdata1;
    $this->cdata2 = $cdata2;
    $this->cdata3 = $cdata3;
    $this->cdata4 = $cdata4;
    $this->cdata5 = $cdata5;
    $this->cdata6 = $cdata6;
    $this->cdata7 = $cdata7;
    $this->cdata8 = $cdata8;
    $this->cdata9 = $cdata9;
    $this->actions = $actions;
    $this->html = $html;
    $this->uname = $uname;
    $this->ucompany = $ucompany;
    $this->uaddress = $uaddress;
    $this->uphone = $uphone;
    $this->closed = $closed;
    $this->signature = $signature;
    $this->licenseState = $licenseState;
    $this->license = $license;
    $this->dea = $dea;
    $this->npi = $npi;
    $this->dcsql = $dateCreated;
    $this->dateCreated = formatTimestamp($dateCreated);
    $this->noteDate = $noteDate;
    $this->dateUpdated = formatTimestamp($dateUpdated);
    $this->createdBy = $createdBy;
    $this->updatedBy = $updatedBy;
    $this->sendTo = $sendTo;
    $this->sendToId = $sendToId;
    $this->assignedTo = $assignedTo;
    $this->assignedToId = $assignedToId;
    $this->uaddressonly = $uaddressonly;
    $this->ucitystatezip = $ucitystatezip;
    $this->caddress = $caddress;
    $this->ccitystatezip = $ccitystatezip;
    $this->title = $title;
    $this->standard = $standard;
    $this->lockedBy = $lockedBy;
  }
  public function out() {
    if ($this->closed == 2) {
      //$data = qq("actions", addslashes($this->actions));
      $data = qq("actions", $this->actions);
    } else {
      $data = qqo("actions", $this->actions);
    }
    return cb(qq("id", $this->id)
        . C . qq("tid", $this->templateId)
        . C . qq("dossql", $this->dossql)
        . C . qq("dos", $this->dos)
        . C . qq("dosfull", $this->dosfull)
        . C . qq("dosdate", $this->dosdate)
        . C . qq("clientId", $this->clientId)
        . C . qq("cid", $this->cid)
        . C . qq("cname", $this->cname)
        . C . qqo("csex", $this->csex)
        . C . qq("cbirth", $this->cbirth)
        . C . qq("cdata1", $this->cdata1)
        . C . qq("cdata2", $this->cdata2)
        . C . qq("cdata3", $this->cdata3)
        . C . qq("cdata4", $this->cdata4)
        . C . qq("cdata5", $this->cdata5)
        . C . qq("cdata6", $this->cdata6)
        . C . qq("cdata7", $this->cdata7)
        . C . qq("cdata8", $this->cdata8)
        . C . qq("cdata9", $this->cdata9)
        . C . $data
        . C . qq("html", $this->html, true)
        . C . qq("uname", $this->uname)
        . C . qq("ucompany", $this->ucompany)
        . C . qq("uaddress", $this->uaddress)
        . C . qq("uphone", $this->uphone)
        . C . qqo("closed", $this->closed)
        . C . qq("signature", $this->signature)
        . C . qq("licenseState", $this->licenseState) 
        . C . qq("license", $this->license) 
        . C . qq("dea", $this->dea) 
        . C . qq("npi", $this->npi)
        . C . qq("dcsql", $this->dcsql)
        . C . qq("dateCreated", $this->dateCreated)
        . C . qq("noteDate", $this->noteDate) 
        . C . qq("dateUpdated", $this->dateUpdated) 
        . C . qq("createdBy", $this->createdBy) 
        . C . qq("updatedBy", $this->updatedBy) 
        . C . qq("sendTo", $this->sendTo) 
        . C . qq("sendToId", $this->sendToId) 
        . C . qq("assignedTo", $this->assignedTo) 
        . C . qq("assignedToId", $this->assignedToId) 
        . C . qq("uaddressonly", $this->uaddressonly) 
        . C . qq("ucitystatezip", $this->ucitystatezip) 
        . C . qq("caddress", $this->caddress) 
        . C . qq("ccitystatezip", $this->ccitystatezip)
        . C . qq("title", $this->title) 
        . C . qqo("standard", $this->standard) 
        . C . qqj("template", $this->template) 
        . C . qqo("map", jsonencode($this->map)) 
        . C . qq("lockedBy", $this->lockedBy) 
        . C . qqo("meds", jsonencode($this->meds))
        );
  }
}
?>