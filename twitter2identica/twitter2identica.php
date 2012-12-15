<?php
  // TCtoICsync 0.1b - Post Twitter.com tweets as Identi.ca dents
  // Copyright (C) 2010 Kevin Niehage (@weizenspreu)
  //
  // This program is free software: you can redistribute it and/or modify
  // it under the terms of the GNU General Public License as published by
  // the Free Software Foundation, either version 3 of the License, or
  // (at your option) any later version.
  //
  // This program is distributed in the hope that it will be useful,
  // but WITHOUT ANY WARRANTY; without even the implied warranty of
  // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  // GNU General Public License for more details.
  //
  // You should have received a copy of the GNU General Public License
  // along with this program. If not, see <http://www.gnu.org/licenses/>.
  //
  // This tool is based on the work of @cdevroe, @einfachben and @hermes42.
  //
  // HowTo:
  // ======
  //
  // In order to use this Twitter.Com to Identi.Ca synchronization
  // tool you have to specify the following values:
  //
  // * $EXECUTE_PASSWORD:  Will protect you from malicious callers.
  //                       Only the people knowing this password
  //                       can call the synchronization process.
  //                       This password has to be provided as the
  //                       GET parameter "pwd" when calling this script.
  // * $IDENTICA_NICK:     This is your identi.ca nickname. It is
  //                       needed for using the identi.ca API.
  // * $IDENTICA_PASSWORD: This is your identi.ca password. It is
  //                       needed for using the identi.ca API.
  // * $TWITTER_NICK:      This is your twitter nickname. It is
  //                       needed for formating the messages that
  //                       are read from the twitter RSS feed.
  // * $AUTO_REFRESH:      This feature enables a small JavaScript
  //                       auto-refresh feature. This value defines
  //                       the number of seconds the page waits before
  //                       it refreshes itself. A value of zero or less
  //                       deactivates the refresh feature.
  //
  // This tool is not useful if you are not able to call it frequently.
  // A good way to do so is to employ a CRON job. Alternatively you can
  // activate the $AUTO_REFRESH feature.
  //
  // You can use this script for more than one twitter/identi.ca account.
  // To do so you have to read $IDENTICA_NICK, $IDENTICA_PASSSWORD and
  // $TWITTER_NICK from some other place (like a database).
  //
  // If you do not want others to see the status of your synchronization
  // you can protect the "*.date" and "*.lock" files with a small ".htaccess"
  // file that contains the following code (remove the leading ">"):
  // > RewriteEngine on
  // > Options +FollowSymLinks
  // >
  // > RewriteBase /
  // >
  // > RewriteRule ^(.*)\.date$ - [L,R=404]
  // > RewriteRule ^(.*)\.lock$ - [L,R=404]

  /* THESE SETTINGS ARE FREE TO EDIT */

  $EXECUTE_PASSWORD = "[EXECUTE_PASSWORD]";

  //Set this to be able to call the script directly by a cronjob (php /var/www/blaaa)
  $_GET["pwd"]=$EXECUTE_PASSWORD;

  $AUTO_REFRESH      = 0;
  $IDENTICA_NICK     = "[IDENTICA_NICK]";
  $IDENTICA_PASSWORD = "[IDENTICA_PASSWORD]";
  $TWITTER_NICK      = "[TWITTER_NICK]";


  /* STOP EDITING HERE IF YOU DO NOT KNOW WHAT YOU ARE DOING */

  $DATE_FILE    = dirname(__FILE__) . "/" . $TWITTER_NICK . ".date";
  $IDENTICA_API = "https://identi.ca/api/statuses/update.xml";
  $LOCK_FILE    = dirname(__FILE__) . "/" . $TWITTER_NICK . ".lock";
  $TWITTER_RSS  = "https://api.twitter.com/1/statuses/user_timeline.rss?screen_name=" . $TWITTER_NICK;
  $USER_AGENT   = "TCtoICsync 0.1b";

  /* STOP EDITING HERE */

  // we are going to output some UTF-8
  header("Content-Type: text/html; charset=utf-8");

  // only allow authorized access
  if (isset($_GET["pwd"]) && ($_GET["pwd"] == $EXECUTE_PASSWORD)) {
    // stop other executions from making trouble
    $locked = "0";
    if (file_exists($LOCK_FILE)) {
      $locked = file_get_contents($LOCK_FILE);
    }

    if ($locked == "0") {
      try {
        // disallow next execution
        file_put_contents($LOCK_FILE, "1");

        // retrieve Twitter RSS feed of user
        $options = array(
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_SSL_VERIFYHOST => false,
          CURLOPT_SSL_VERIFYPEER => false,
          CURLOPT_USERAGENT      => $USER_AGENT,
          CURLOPT_VERBOSE        => 1
        );
        $curl = curl_init($TWITTER_RSS);
        curl_setopt_array($curl, $options);
        $xml    = curl_exec($curl);
        $header = curl_getinfo($curl);
        curl_close($curl);

        // only proceed if Twitter RSS feed could be retrieved
        if ($header["http_code"] == 200) {
          print("Twitter RSS feed retrieved: " . $TWITTER_RSS . "<br />\n");

          // load last synchronization date
          $previousDate = null;
          if (file_exists($DATE_FILE)) {
            $previousDate = file_get_contents($DATE_FILE);
          }

          // parse feed
          $xml = new SimpleXMLElement($xml);

          // convert SimpleXMLElement to array
          $index      = 0;
          $latestDate = $previousDate;
          $messages   = null;
          foreach ($xml->channel[0]->item as $item) {
            if (($item->title != null) && ($item->pubDate != null)) {
              $currentDate = strtotime($item->pubDate);

              // only synchronize new tweets
              if (($previousDate == null) || ($previousDate < $currentDate)) {
                $temp = html_entity_decode($item->title, ENT_QUOTES, "UTF-8");

                // check if tweet belongs to selected twitter user
                if (stripos($temp, $TWITTER_NICK) === 0) {
                  $messages[$index++] = substr($temp, strlen($TWITTER_NICK)+2);

                  if ($latestDate == null) {
                    $latestDate = $currentDate;
                  } else {
                    if ($latestDate < $currentDate) {
                      $latestDate = $currentDate;
                    }
                  }
                }
              }
            }
          }

          if (($messages != null) && (count($messages) > 0)) {
            // reverse messages
            $messages = array_reverse($messages);
            ksort($messages);

            // handle Twitter messages
            foreach ($messages as $message) {
              // call identi.ca API
              $options = array(
                CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => "status=" . urlencode($message) . "&source=" . urlencode($USER_AGENT),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT      => $USER_AGENT,
                CURLOPT_USERPWD        => $IDENTICA_NICK . ":" . $IDENTICA_PASSWORD,
                CURLOPT_VERBOSE        => 1
              );
              $curl = curl_init($IDENTICA_API);
              curl_setopt_array($curl, $options);
              $output = curl_exec($curl);
              $header = curl_getinfo($curl);
              curl_close($curl);

              if ($header["http_code"] == 200) {
                print("Tweet synchronized: " . $message . "<br />\n");
              } else {
                print("Tweet could not be synchronized: " . $message . "<br />\n" . $output . "<br />\n");
              }
            }
          }

          // save last synchronization date
          if ($latestDate != null) {
            file_put_contents($DATE_FILE, $latestDate);
          }
        } else {
          print("Twitter RSS feed could not be retrieved: " . $TWITTER_RSS . "<br />\n");
        }
      } catch (Exception $e) {
        print("An exception has occured. Execution has been aborted.<br />\n");
      }

      // allow next execution
      file_put_contents($LOCK_FILE, "0");
    } else {
      print("The synchronization is already running.<br />\n");
    }
  } else {
    print("You are not authorized to execute this action.<br />\n");
  }
  print("DONE!<br />\n");

  if ($AUTO_REFRESH > 0) {
    print("<br />\nWill auto-refresh in " . $AUTO_REFRESH . " seconds.<br />\n");
    print("<script tyle=\"text/JavaScript\">\n");
    print("<!--\n");
    print("  setTimeout(\"location.reload(true);\", " . $AUTO_REFRESH*1000 . ");\n");
    print("//-->\n");
    print("</script>\n");
  }

  // allow next execution
  file_put_contents($LOCK_FILE, "0");
?>