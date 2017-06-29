<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once '../vendor/swiftmailer/swiftmailer/lib/swift_required.php';

function sendRecoveryEmail(Request $request, Response $response, $db) {
    //check to see if valid email address from user table
    $body = $request->getBody();
    $input = json_decode($body);
    $email = $input->email;
    $dataObj = checkIfValidEmail($email, $db);

    if( !$dataObj){
         //log
         return error422($response, "No email found");
    }

    //add random hash to user row and date

    $bytes = openssl_random_pseudo_bytes(32);
    $reset_hash = base64url_encode($bytes);

    $isInserted = insertPasswordResetInfo($email, $reset_hash, $db);
    if( !$isInserted){
        return error422($response, "Password reset not inserted into database");
    }

    //send email to that user plus 
    $link = client_site."#/password_recovery?requestID=".$reset_hash;
    sendEmailWithLink($email, $dataObj, $link);

    $response = $response->withStatus(201);
    return $response;
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function checkIfValidEmail($email, $db){
    
    $sqlQuery = "SELECT * FROM users 
        WHERE email = :email 
        AND reset_date = '0000-00-00 00:00:00'
        AND (reset_hash IS NULL or reset_hash = '')
        AND deleted_date = '0000-00-00 00:00:00'";
    $params = array(':email' => $email);
    $data = db_get($db, $sqlQuery, $params);

    if(count($data) > 0){
        $tempObj = new stdClass();
        $tempObj->id = $data[0]['id'];
        $tempObj->username = $data[0]['username'];
        return  $tempObj;
    }
    return false;
}

function insertPasswordResetInfo($email, $reset_hash, $db) {
    try{
        $sqlInsert = "UPDATE users SET reset_hash = :reset_hash, reset_date = now()
            WHERE email = :email AND deleted_date = '0000-00-00 00:00:00'";
        $stmt = $db->prepare($sqlInsert);
        $stmt->execute(array(':reset_hash' => $reset_hash, ':email' => $email));
        if(!$stmt->rowCount()){
            throw new PDOException('Could not set hash');
        }
        return true;
    }catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            // Take some action if there is a key constraint violation, i.e. duplicate name
            echo "A foreign key constraint has been violated";
        } else {
            echo "Unauthorized operation: ".$e->getMessage();
        }
    }catch(Exception $e){
        echo "Unsuccessful update of series: ".$e->getMessage();
    }
    return false;
}

function sendEmailWithLink($emailTo, $data, $link) {

    $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, "ssl")
    ->setUsername(mail_username)
    ->setPassword(mail_password);

    $mailer = Swift_Mailer::newInstance($transport);

    $message = Swift_Message::newInstance('Requesting Password Reset')
    ->setFrom(array('Watch.List@watchlistadmin.ca' => 'Watch List Admin'))
    ->setTo(array($emailTo))
    ->setBody(buildBody($link, $data->username), 'text/html');

    $result = $mailer->send($message);
    return $result;
}

function buildBody($link, $name){
    return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
      <title>Set up a new password for [Product Name]</title>
      <!-- 
      The style block is collapsed on page load to save you some scrolling.
      Postmark automatically inlines all CSS properties for maximum email client 
      compatibility. You can just update styles here, and Postmark does the rest.
      -->
      <style type="text/css" rel="stylesheet" media="all">
      /* Base ------------------------------ */
      
      *:not(br):not(tr):not(html) {
        font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
        box-sizing: border-box;
      }
      
      body {
        width: 100% !important;
        height: 100%;
        margin: 0;
        line-height: 1.4;
        background-color: #F2F4F6;
        color: #74787E;
        -webkit-text-size-adjust: none;
      }
      
      p,
      ul,
      ol,
      blockquote {
        line-height: 1.4;
        text-align: left;
      }
      
      a {
        color: #3869D4;
      }
      
      a img {
        border: none;
      }
      
      td {
        word-break: break-word;
      }
      /* Layout ------------------------------ */
      
      .email-wrapper {
        width: 100%;
        margin: 0;
        padding: 0;
        -premailer-width: 100%;
        -premailer-cellpadding: 0;
        -premailer-cellspacing: 0;
        background-color: #F2F4F6;
      }
      
      .email-content {
        width: 100%;
        margin: 0;
        padding: 0;
        -premailer-width: 100%;
        -premailer-cellpadding: 0;
        -premailer-cellspacing: 0;
      }
      /* Masthead ----------------------- */
      
      .email-masthead {
        padding: 25px 0;
        text-align: center;
      }
      
      .email-masthead_logo {
        width: 94px;
      }
      
      .email-masthead_name {
        font-size: 16px;
        font-weight: bold;
        color: #bbbfc3;
        text-decoration: none;
        text-shadow: 0 1px 0 white;
      }
      /* Body ------------------------------ */
      
      .email-body {
        width: 100%;
        margin: 0;
        padding: 0;
        -premailer-width: 100%;
        -premailer-cellpadding: 0;
        -premailer-cellspacing: 0;
        border-top: 1px solid #EDEFF2;
        border-bottom: 1px solid #EDEFF2;
        background-color: #FFFFFF;
      }
      
      .email-body_inner {
        width: 570px;
        margin: 0 auto;
        padding: 0;
        -premailer-width: 570px;
        -premailer-cellpadding: 0;
        -premailer-cellspacing: 0;
        background-color: #FFFFFF;
      }
      
      .email-footer {
        width: 570px;
        margin: 0 auto;
        padding: 0;
        -premailer-width: 570px;
        -premailer-cellpadding: 0;
        -premailer-cellspacing: 0;
        text-align: center;
      }
      
      .email-footer p {
        color: #AEAEAE;
      }
      
      .body-action {
        width: 100%;
        margin: 30px auto;
        padding: 0;
        -premailer-width: 100%;
        -premailer-cellpadding: 0;
        -premailer-cellspacing: 0;
        text-align: center;
      }
      
      .body-sub {
        margin-top: 25px;
        padding-top: 25px;
        border-top: 1px solid #EDEFF2;
      }
      
      .content-cell {
        padding: 35px;
      }
      
      .preheader {
        display: none !important;
        visibility: hidden;
        mso-hide: all;
        font-size: 1px;
        line-height: 1px;
        max-height: 0;
        max-width: 0;
        opacity: 0;
        overflow: hidden;
      }
      /* Attribute list ------------------------------ */
      
      .attributes {
        margin: 0 0 21px;
      }
      
      .attributes_content {
        background-color: #EDEFF2;
        padding: 16px;
      }
      
      .attributes_item {
        padding: 0;
      }
      /* Related Items ------------------------------ */
      
      .related {
        width: 100%;
        margin: 0;
        padding: 25px 0 0 0;
        -premailer-width: 100%;
        -premailer-cellpadding: 0;
        -premailer-cellspacing: 0;
      }
      
      .related_item {
        padding: 10px 0;
        color: #74787E;
        font-size: 15px;
        line-height: 18px;
      }
      
      .related_item-title {
        display: block;
        margin: .5em 0 0;
      }
      
      .related_item-thumb {
        display: block;
        padding-bottom: 10px;
      }
      
      .related_heading {
        border-top: 1px solid #EDEFF2;
        text-align: center;
        padding: 25px 0 10px;
      }
      /* Discount Code ------------------------------ */
      
      .discount {
        width: 100%;
        margin: 0;
        padding: 24px;
        -premailer-width: 100%;
        -premailer-cellpadding: 0;
        -premailer-cellspacing: 0;
        background-color: #EDEFF2;
        border: 2px dashed #9BA2AB;
      }
      
      .discount_heading {
        text-align: center;
      }
      
      .discount_body {
        text-align: center;
        font-size: 15px;
      }
      /* Social Icons ------------------------------ */
      
      .social {
        width: auto;
      }
      
      .social td {
        padding: 0;
        width: auto;
      }
      
      .social_icon {
        height: 20px;
        margin: 0 8px 10px 8px;
        padding: 0;
      }
      /* Data table ------------------------------ */
      
      .purchase {
        width: 100%;
        margin: 0;
        padding: 35px 0;
        -premailer-width: 100%;
        -premailer-cellpadding: 0;
        -premailer-cellspacing: 0;
      }
      
      .purchase_content {
        width: 100%;
        margin: 0;
        padding: 25px 0 0 0;
        -premailer-width: 100%;
        -premailer-cellpadding: 0;
        -premailer-cellspacing: 0;
      }
      
      .purchase_item {
        padding: 10px 0;
        color: #74787E;
        font-size: 15px;
        line-height: 18px;
      }
      
      .purchase_heading {
        padding-bottom: 8px;
        border-bottom: 1px solid #EDEFF2;
      }
      
      .purchase_heading p {
        margin: 0;
        color: #9BA2AB;
        font-size: 12px;
      }
      
      .purchase_footer {
        padding-top: 15px;
        border-top: 1px solid #EDEFF2;
      }
      
      .purchase_total {
        margin: 0;
        text-align: right;
        font-weight: bold;
        color: #2F3133;
      }
      
      .purchase_total--label {
        padding: 0 15px 0 0;
      }
      /* Utilities ------------------------------ */
      
      .align-right {
        text-align: right;
      }
      
      .align-left {
        text-align: left;
      }
      
      .align-center {
        text-align: center;
      }
      /*Media Queries ------------------------------ */
      
      @media only screen and (max-width: 600px) {
        .email-body_inner,
        .email-footer {
          width: 100% !important;
        }
      }
      
      @media only screen and (max-width: 500px) {
        .button {
          width: 100% !important;
        }
      }
      /* Buttons ------------------------------ */
      
      .button {
        background-color: #3869D4;
        border-top: 10px solid #3869D4;
        border-right: 18px solid #3869D4;
        border-bottom: 10px solid #3869D4;
        border-left: 18px solid #3869D4;
        display: inline-block;
        color: #FFF;
        text-decoration: none;
        border-radius: 3px;
        box-shadow: 0 2px 3px rgba(0, 0, 0, 0.16);
        -webkit-text-size-adjust: none;
      }
      
      .button--green {
        background-color: #22BC66;
        border-top: 10px solid #22BC66;
        border-right: 18px solid #22BC66;
        border-bottom: 10px solid #22BC66;
        border-left: 18px solid #22BC66;
      }
      
      .button--red {
        background-color: #FF6136;
        border-top: 10px solid #FF6136;
        border-right: 18px solid #FF6136;
        border-bottom: 10px solid #FF6136;
        border-left: 18px solid #FF6136;
      }
      /* Type ------------------------------ */
      
      h1 {
        margin-top: 0;
        color: #2F3133;
        font-size: 19px;
        font-weight: bold;
        text-align: left;
      }
      
      h2 {
        margin-top: 0;
        color: #2F3133;
        font-size: 16px;
        font-weight: bold;
        text-align: left;
      }
      
      h3 {
        margin-top: 0;
        color: #2F3133;
        font-size: 14px;
        font-weight: bold;
        text-align: left;
      }
      
      p {
        margin-top: 0;
        color: #74787E;
        font-size: 16px;
        line-height: 1.5em;
        text-align: left;
      }
      
      p.sub {
        font-size: 12px;
      }
      
      p.center {
        text-align: center;
      }
      </style>
    </head>
    <body>
      <span class="preheader">Use this link to reset your password. The link is only valid for 24 hours.</span>
      <table class="email-wrapper" width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td align="center">
            <table class="email-content" width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td class="email-masthead">
                  <a href="http://simmons.byethost6.com" class="email-masthead_name">
          The Watch List
        </a>
                </td>
              </tr>
              <!-- Email Body -->
              <tr>
                <td class="email-body" width="100%" cellpadding="0" cellspacing="0">
                  <table class="email-body_inner" align="center" width="570" cellpadding="0" cellspacing="0">
                    <!-- Body content -->
                    <tr>
                      <td class="content-cell">
                        <h1>Hi '.$name.',</h1>
                        <p>You recently requested to reset your password for your Watch List account. Use the button below to reset it. <strong>This password reset is only valid for the next 24 hours.</strong></p>
                        <!-- Action -->
                        <table class="body-action" align="center" width="100%" cellpadding="0" cellspacing="0">
                          <tr>
                            <td align="center">
                              <!-- Border based button
                        https://litmus.com/blog/a-guide-to-bulletproof-buttons-in-email-design -->
                              <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                  <td align="center">
                                    <table border="0" cellspacing="0" cellpadding="0">
                                      <tr>
                                        <td>
                                          <a href="'.$link.'" class="button button--green" target="_blank">Reset your password</a>
                                        </td>
                                      </tr>
                                    </table>
                                  </td>
                                </tr>
                              </table>
                            </td>
                          </tr>
                        </table>
                        <p>For security, if you did not request a password reset, please ignore this email or contact brent.simmons@unb.ca if you have questions.</p>
                        <p>Thanks,
                          <br>Brent</p>
                        <!-- Sub copy -->
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </body>
  </html>';
}

?>