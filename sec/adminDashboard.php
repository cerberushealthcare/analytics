<?php 
require_once "inc/noCache.php";
require_once "php/data/LoginSession.php";
require_once "php/dao/TemplateReaderDao.php";
//require_once "php/dao/SurveyDao.php";
//
LoginSession::verify_forUser()->requires($login->admin);
$templates = TemplateReaderDao::getMyTemplates();
$surveys = array(); //SurveyDao::getSurveys($login->userId);
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <!-- Copyright (c)2006 by LCD Solutions, Inc.  All rights reserved. -->
  <!-- http://www.clicktate.com -->
  <head>
    <title>clicktate : Dashboard</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <meta http-equiv="Content-Language" content="en-us" />
    <link rel="stylesheet" type="text/css" href="css/med.css" media="screen" />
  </head>
  <body>
    <?php include "inc/banner.php" ?>
    <div id="breadcrumb">
      <h1>Dashboard</h1>
    </div>
    <p>
      <a href="welcome.php">Back to Home</a>
    </p>
    <table border=0 cellpadding=0 cellspacing=0 width=100%>
      <tr>
        <td valign=top width=50%>
          <div class="roundBox">
            <div class="roundTitle">My Templates</div>
            <div class="roundContent" style="height:300px">
              <table border=0 cellpadding=0 cellspacing=0>
                <?php foreach ($templates as $k => $template) { ?>
                  <tr style="padding-bottom:10px">
                    <td><a href="adminTemplate.php?id=<?=$template->id ?>" title="Click to edit template"><img src="img/notebook.gif"></a></td>
                    <td width=5 nowrap></td>
                    <td valign="middle">
                      <span class="template">
                        <a href="adminTemplate.php?id=<?=$template->id ?>" title="Click to edit template"><?=$template->uid ?></a>
                      </span><br>
                      <span >
                        <?=$template->name ?> &#8226; <?=$template->desc ?><br>
                      </span>
                      <span style="color:#909090; font-size:8pt">
                        Created <?=$template->dateCreated ?> &#8226; Last updated <?=$template->dateUpdated?><br>
                      </span>
                    </td>
                  </tr>
                <?php } ?>
                <tr>
                  <td colspan=3 bgcolor=#909090 height=1></td>
                </tr>
                <tr style="padding-top:10px">
                  <td><a href="adminTemplate.php?id=" title="Click to add new template"><img src="img/notebook.gif"></a></td>
                  <td width=5></td>
                  <td valign="middle">
                    <span class="template">
                      <a href="adminTemplate.php?id=" title="Click to add new template">Create a New Template...</a>
                    </span><br>
                  </td>
                </tr>
              </table>
            </div>
            <div class="roundBottom"><img src="img/bl.gif"></div>
          </div>
        </td>
        <td width=10 nowrap></td>
        <td valign=top width=50%>
          <div class="roundBox">
            <div class="roundTitle">My Surveys</div>
            <div class="roundContent" style="height:300px">
              <table border=0 cellpadding=0 cellspacing=0>
                <?php foreach ($surveys as $k => $survey) { ?>
                  <tr style="padding-bottom:10px">
                    <td><a href="adminSurvey.php?id=<?=$survey->id ?>" title="Click to edit survey"><img src="img/notebook.gif"></a></td>
                    <td width=5 nowrap></td>
                    <td valign="middle">
                      <span class="template">
                        <a href="adminSurvey.php?id=<?=$survey->id ?>" title="Click to edit survey"><?=$survey->name ?></a>
                      </span><br>
                      <span >
                        <?=$survey->desc ?><br>
                      </span>
                    </td>
                  </tr>
                <?php } ?>
                <tr>
                  <td colspan=3 bgcolor=#909090 height=1></td>
                </tr>
                <tr style="padding-top:10px">
                  <td><a href="adminSurvey.php?id=" title="Click to add new survey"><img src="img/notebook.gif"></a></td>
                  <td width=5></td>
                  <td valign="middle">
                    <span class="template">
                      <a href="adminSurvey.php?id=" title="Click to add new survey">Create a New Survey...</a>
                    </span><br>
                  </td>
                </tr>
              </table>
            </div>
            <div class="roundBottom"><img src="img/bl.gif"></div>
          </div>
        </td>
      </tr>
    </table>
  </body>
</html>